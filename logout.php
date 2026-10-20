
<?php
session_start();

// Clear all session variables.
$_SESSION = [];

// Remove the session cookie from the browser.
if (ini_get('session.use_cookies')) {
    $cookieParams = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $cookieParams['path'],
        $cookieParams['domain'],
        $cookieParams['secure'],
        $cookieParams['httponly']
    );
}

// Destroy the session.
session_destroy();

// Redirect to the login page.
header('Location: /EventFlow/login.php');
exit;
