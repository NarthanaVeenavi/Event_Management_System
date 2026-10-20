
<?php

require_once __DIR__ . '/../models/User.php';

class AuthController
{
    private User $userModel;

    public function __construct(mysqli $conn)
    {
        $this->userModel = new User($conn);
    }

    public function register(): array
    {
        $errors = [];
        $old = [];

        // Display the registration page without processing a form.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return [
                'errors' => [],
                'old' => [],
                'message' => '',
                'messageType' => ''
            ];
        }

        // Get submitted form values.
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $studentId = trim($_POST['student_id'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Preserve form values when validation fails.
        $old = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'student_id' => $studentId,
            'department' => $department
        ];

        // Required fields.
        if (
            $firstName === '' ||
            $lastName === '' ||
            $email === '' ||
            $studentId === '' ||
            $department === '' ||
            $password === '' ||
            $confirmPassword === ''
        ) {
            $errors[] = 'Please complete all required fields.';
        }

        // Field length validation.
        if (strlen($firstName) > 100 || strlen($lastName) > 100) {
            $errors[] = 'Names must not exceed 100 characters.';
        }

        if (strlen($studentId) > 100) {
            $errors[] = 'Student ID must not exceed 100 characters.';
        }

        if (strlen($email) > 150) {
            $errors[] = 'Email must not exceed 150 characters.';
        }

        // University email validation.
        if (
            $email !== '' &&
            (
                !filter_var($email, FILTER_VALIDATE_EMAIL) ||
                !preg_match('/@northbridge\.edu$/i', $email)
            )
        ) {
            $errors[] =
                'Please use a valid @northbridge.edu email address.';
        }

        // Allowed departments.
        $departments = [
            'Computer Science',
            'Information Technology',
            'Software Engineering',
            'Information Systems',
            'Engineering',
            'Business Management',
            'Other'
        ];

        if (!in_array($department, $departments, true)) {
            $errors[] = 'Please select a valid department.';
        }

        // Password: at least 8 characters, one number and one symbol.
        if (
            $password !== '' &&
            !preg_match(
                '/^(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,}$/',
                $password
            )
        ) {
            $errors[] =
                'Password must contain at least 8 characters, one number, and one symbol.';
        }

        // Confirm password.
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }

        // Terms agreement.
        if (($_POST['terms'] ?? '') !== '1') {
            $errors[] =
                'You must agree to the Terms of Service and Privacy Policy.';
        }

        // Return validation errors.
        if (!empty($errors)) {
            return [
                'errors' => $errors,
                'old' => $old,
                'message' => '',
                'messageType' => 'error'
            ];
        }

        try {
            // Check for duplicate email or student ID.
            if ($this->userModel->exists($email, $studentId)) {
                return [
                    'errors' => [
                        'An account with this email or student ID already exists.'
                    ],
                    'old' => $old,
                    'message' => '',
                    'messageType' => 'error'
                ];
            }

            // Hash the password before saving it.
            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Create the participant account.
            $created = $this->userModel->create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'department' => $department,
                'student_id' => $studentId,
                'email' => $email,
                'password' => $hashedPassword
            ]);

            if (!$created) {
                return [
                    'errors' => [
                        'Unable to create your account. Please try again.'
                    ],
                    'old' => $old,
                    'message' => '',
                    'messageType' => 'error'
                ];
            }

            // Registration succeeded.
            return [
                'errors' => [],
                'old' => [],
                'success' => true,
                'message' =>
                    'Your participant account has been created successfully!',
                'messageType' => 'success'
            ];

        } catch (mysqli_sql_exception $e) {
            error_log(
                'Registration database error: ' . $e->getMessage()
            );

            return [
                'errors' => [
                    'Unable to create your account right now. Please try again.'
                ],
                'old' => $old,
                'message' => '',
                'messageType' => 'error'
            ];
        }
    }
}
