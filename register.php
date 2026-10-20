
<?php

session_start();

require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/controllers/AuthController.php';

$authController = new AuthController($conn);
$result = $authController->register();

$errors = $result['errors'] ?? [];
$old = $result['old'] ?? [];

// Store the success message and redirect
if (!empty($result['success'])) {
    $_SESSION['register_success'] =
        'Your account has been created successfully! You can now sign in.';

    header('Location: /app/views/auth/login.php');
    exit;
}

// Retrieve the success message after redirecting
$successMessage = $_SESSION['register_success'] ?? '';
unset($_SESSION['register_success']);

// Load the registration view
require __DIR__ . '/app/views/auth/register.php';
