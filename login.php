
<?php
session_start();

require_once __DIR__ . '/app/config/database.php';

$errors = [];
$old = [];

/**
 * Redirect users according to their role.
 */
function redirectByRole(string $role): void
{
    switch ($role) {
        case 'Admin':
            header('Location: /EventFlow/app/views/admin/admin_dashboard.php');
            exit;

        case 'Organizer':
            header('Location: /EventFlow/app/views/organizer/organizer_dashboard.php');
            exit;

        case 'Participant':
            header('Location: /EventFlow/app/views/participant/dashboard.php');
            exit;

        default:
            header('Location: /EventFlow/login.php');
            exit;
    }
}

// Redirect users who are already logged in.
if (isset($_SESSION['user_id'], $_SESSION['role'])) {
    redirectByRole($_SESSION['role']);
}

// Process login form.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $old['email'] = $email;

    // Validate email and password.
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($password === '') {
        $errors[] = 'Please enter your password.';
    }

    if (empty($errors)) {

        $sql = "SELECT
                    user_id,
                    fname,
                    lname,
                    department,
                    student_id,
                    email,
                    password,
                    role,
                    status
                FROM users
                WHERE email = ?
                LIMIT 1";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param($stmt, 's', $email);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);

            mysqli_stmt_close($stmt);

            // Validate credentials.
            if (
                !$user ||
                !password_verify($password, $user['password'])
            ) {
                $errors[] = 'Invalid email or password.';

            } elseif ($user['status'] !== 'Active') {
                $errors[] =
                    'Your account is inactive. Please contact support.';

            } else {

                // Start a fresh session ID after authentication.
                session_regenerate_id(true);

                // Store authenticated user details.
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_fname'] = $user['fname'];
                $_SESSION['user_lname'] = $user['lname'];

                $_SESSION['user_name'] =
                    $user['fname'] . ' ' . $user['lname'];

                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['department'] = $user['department'];
                $_SESSION['student_id'] = $user['student_id'];

                // Redirect to the appropriate dashboard.
                redirectByRole($user['role']);
            }

        } else {
            error_log('EventFlow login: ' . mysqli_error($conn));

            $errors[] =
                'Unable to sign in right now. Please try again later.';
        }
    }
}

// Display the login page.
require_once __DIR__ . '/app/views/auth/login.php';
