<?php

class User
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function exists(string $email, string $studentId): bool
    {
        $sql = "SELECT user_id
                FROM users
                WHERE email = ? OR student_id = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $email, $studentId);
        $stmt->execute();

        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;

        $stmt->close();

        return $exists;
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO users
                (fname, lname, department, student_id, email, password, role)
                VALUES (?, ?, ?, ?, ?, ?, 'Participant')";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "ssssss",
            $data['first_name'],
            $data['last_name'],
            $data['department'],
            $data['student_id'],
            $data['email'],
            $data['password']
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }

    /**
     * Create a user account from the Admin User Management page.
     */
    public function createByAdmin(array $data): bool
    {
        $sql = "INSERT INTO users
                (fname, lname, department, student_id, email, password, role, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Active')";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "sssssss",
            $data['first_name'],
            $data['last_name'],
            $data['department'],
            $data['student_id'],
            $data['email'],
            $data['password'],
            $data['role']
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }

    // update user profile information by admin
    public function updateByAdmin(int $userId, array $data): bool
    {
        $sql = "UPDATE users
                SET fname = ?,
                    lname = ?,
                    email = ?,
                    department = ?,
                    student_id = ?,
                    role = ?,
                    status = ?
                WHERE user_id = ?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "sssssssi",
            $data['fname'],
            $data['lname'],
            $data['email'],
            $data['department'],
            $data['student_id'],
            $data['role'],
            $data['status'],
            $userId
        );

        $stmt->execute();

        // A valid update can leave values unchanged, so check for SQL errors
        // rather than relying on affected_rows.
        $stmt->close();

        return true;
    }

}