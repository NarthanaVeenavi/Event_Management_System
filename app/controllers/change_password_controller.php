
<?php

class ChangePasswordController
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function changePassword(int $userId, array $data): array
    {
        $currentPassword = $data['current_password'] ?? '';
        $newPassword = $data['new_password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';

        $errors = [];

        if ($currentPassword === '') {
            $errors['current_password'] = 'Enter your current password.';
        }

        if ($newPassword === '') {
            $errors['new_password'] = 'Enter a new password.';
        } elseif (strlen($newPassword) < 8) {
            $errors['new_password'] =
                'Your new password must contain at least 8 characters.';
        }

        if ($confirmPassword === '') {
            $errors['confirm_password'] = 'Confirm your new password.';
        } elseif ($newPassword !== $confirmPassword) {
            $errors['confirm_password'] =
                'The new passwords do not match.';
        }

        if ($currentPassword !== '' && $currentPassword === $newPassword) {
            $errors['new_password'] =
                'Your new password must be different from your current password.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Retrieve the participant's existing password hash.
        $stmt = $this->conn->prepare(
            'SELECT password FROM users
             WHERE user_id = ? AND role = ?
             LIMIT 1'
        );

        $role = 'Participant';
        $stmt->bind_param('is', $userId, $role);
        $stmt->execute();

        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            return [
                'success' => false,
                'errors' => [
                    'current_password' => 'Your current password is incorrect.'
                ]
            ];
        }

        // Hash the new password before saving it.
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmt = $this->conn->prepare(
            'UPDATE users
             SET password = ?
             WHERE user_id = ? AND role = ?'
        );

        $stmt->bind_param('sis', $hashedPassword, $userId, $role);
        $stmt->execute();
        $stmt->close();

        return ['success' => true];
    }
}
