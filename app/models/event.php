<?php

require_once __DIR__ . '/../models/user.php';

class UserController
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function deleteUser(int $userId): bool
    {
        // Check whether the user exists and retrieve the current status.
        $check = $this->conn->prepare(
            'SELECT status FROM users WHERE user_id = ?'
        );

        $check->bind_param('i', $userId);
        $check->execute();
        $check->bind_result($currentStatus);

        $userExists = $check->fetch();
        $check->close();

        // Return false if the user does not exist.
        if (!$userExists) {
            return false;
        }

        // Treat an already inactive account as successfully deactivated.
        if ($currentStatus === 'Inactive') {
            return true;
        }

        // Deactivate an active account.
        $stmt = $this->conn->prepare(
            "UPDATE users SET status = 'Inactive' WHERE user_id = ?"
        );

        $stmt->bind_param('i', $userId);
        $stmt->execute();

        $success = $stmt->affected_rows > 0;
        $stmt->close();

        return $success;
    }

    /**
     * Create a user account through Admin User Management.
     */
    public function createUser(array $data): void
    {
        $requiredFields = [
            'fname',
            'lname',
            'department',
            'student_id',
            'email',
            'password',
            'confirm_password',
            'role'
        ];

        foreach ($requiredFields as $field) {
            if (
                !isset($data[$field]) ||
                !is_string($data[$field]) ||
                trim($data[$field]) === ''
            ) {
                throw new InvalidArgumentException(
                    'Please complete all required fields.'
                );
            }
        }

        $firstName = trim($data['fname']);
        $lastName = trim($data['lname']);
        $department = trim($data['department']);
        $studentId = trim($data['student_id']);
        $email = trim($data['email']);
        $password = $data['password'];
        $confirmPassword = $data['confirm_password'];
        $role = $data['role'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                'Please enter a valid email address.'
            );
        }

        if (
            strlen($firstName) > 100 ||
            strlen($lastName) > 100 ||
            strlen($department) > 100 ||
            strlen($studentId) > 100 ||
            strlen($email) > 150
        ) {
            throw new InvalidArgumentException(
                'One or more fields exceed the allowed length.'
            );
        }

        if ($password !== $confirmPassword) {
            throw new InvalidArgumentException(
                'The passwords do not match.'
            );
        }

        if (strlen($password) < 8) {
            throw new InvalidArgumentException(
                'The password must contain at least 8 characters.'
            );
        }

        if (
            !in_array(
                $role,
                ['Participant', 'Organizer', 'Admin'],
                true
            )
        ) {
            throw new InvalidArgumentException(
                'Please select a valid user role.'
            );
        }

        // Check whether the email or student ID already exists.
        $userModel = new User($this->conn);

        if ($userModel->exists($email, $studentId)) {
            throw new InvalidArgumentException(
                'This email address or student ID is already registered.'
            );
        }

        // Hash the password before saving it.
        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $created = $userModel->createByAdmin([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'department' => $department,
            'student_id' => $studentId,
            'email' => $email,
            'password' => $hashedPassword,
            'role' => $role
        ]);

        if (!$created) {
            throw new RuntimeException(
                'Unable to create the user account.'
            );
        }
    }

    // update user profile information by admin
    public function updateUser(int $userId, array $data): void
    {
        $requiredFields = [
            'fname',
            'lname',
            'email',
            'department',
            'student_id',
            'role',
            'status'
        ];

        foreach ($requiredFields as $field) {
            if (
                !isset($data[$field]) ||
                !is_string($data[$field]) ||
                trim($data[$field]) === ''
            ) {
                throw new InvalidArgumentException(
                    'Please complete all required fields.'
                );
            }
        }

        foreach (['fname', 'lname', 'department', 'student_id'] as $field) {
            if (strlen(trim($data[$field])) > 100) {
                throw new InvalidArgumentException(
                    'A field exceeds the allowed length.'
                );
            }
        }

        $fname = trim($data['fname']);
        $lname = trim($data['lname']);
        $email = trim($data['email']);
        $department = trim($data['department']);
        $studentId = trim($data['student_id']);
        $role = $data['role'];
        $status = $data['status'];

        if (
            strlen($email) > 150 ||
            !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            throw new InvalidArgumentException(
                'Please enter a valid email address.'
            );
        }

        if (!in_array($role, ['Participant', 'Organizer', 'Admin'], true)) {
            throw new InvalidArgumentException('Invalid user role.');
        }

        if (!in_array($status, ['Active', 'Inactive'], true)) {
            throw new InvalidArgumentException('Invalid account status.');
        }

        $userModel = new User($this->conn);

        try {
            $userModel->updateByAdmin($userId, [
                'fname' => $fname,
                'lname' => $lname,
                'email' => $email,
                'department' => $department,
                'student_id' => $studentId,
                'role' => $role,
                'status' => $status
            ]);
        } catch (mysqli_sql_exception $e) {
            if ((int) $e->getCode() === 1062) {
                throw new InvalidArgumentException(
                    'That email address or student ID is already in use.'
                );
            }

            throw new RuntimeException('Unable to update the user account.');
        }
    }

}
