<?php

class ProfileController
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function updateProfile(int $userId, array $data): array
    {
        $fname = trim($data['fname'] ?? '');
        $lname = trim($data['lname'] ?? '');
        $email = trim($data['email'] ?? '');

        $errors = [];

        // Validate first name.
        if ($fname === '') {
            $errors['fname'] = 'First name is required.';
        } elseif (mb_strlen($fname) > 100) {
            $errors['fname'] = 'First name is too long.';
        }

        // Validate last name.
        if ($lname === '') {
            $errors['lname'] = 'Last name is required.';
        } elseif (mb_strlen($lname) > 100) {
            $errors['lname'] = 'Last name is too long.';
        }

        // Validate email.
        if ($email === '') {
            $errors['email'] = 'Email address is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        } elseif (mb_strlen($email) > 254) {
            $errors['email'] = 'Email address is too long.';
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors
            ];
        }

        // Check whether another account already uses this email.
        $check = $this->conn->prepare(
            'SELECT user_id
             FROM users
             WHERE email = ? AND user_id <> ?
             LIMIT 1'
        );

        $check->bind_param('si', $email, $userId);
        $check->execute();

        $exists = $check->get_result()->num_rows > 0;

        $check->close();

        if ($exists) {
            return [
                'success' => false,
                'errors' => [
                    'email' => 'This email address is already in use.'
                ]
            ];
        }

        // Update the authenticated user's own profile,
        // regardless of their role.
        $stmt = $this->conn->prepare(
            'UPDATE users
             SET fname = ?, lname = ?, email = ?
             WHERE user_id = ?
             LIMIT 1'
        );

        $stmt->bind_param(
            'sssi',
            $fname,
            $lname,
            $email,
            $userId
        );

        $stmt->execute();
        $stmt->close();

        return [
            'success' => true,
            'fname' => $fname,
            'lname' => $lname,
            'email' => $email
        ];
    }
}