
<?php
$title = 'Change Password | EventFlow';
$pageCss = 'profile.css';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (
    !isset($_SESSION['user_id']) ||
    ($_SESSION['role'] ?? '') !== 'Participant'
) {
    header('Location: /EventFlow/login.php');
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/change_password_controller.php';

function changePasswordEscape($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new ChangePasswordController($conn);

    $result = $controller->changePassword(
        (int) $_SESSION['user_id'],
        $_POST
    );

    if ($result['success']) {
        $successMessage = 'Your password has been changed successfully.';
    } else {
        $errors = $result['errors'];
    }
}

ob_start();
?>

<div class="profile-page">

    <div class="profile-page-header">
        <div>
            <span class="section-eyebrow">ACCOUNT SETTINGS</span>
            <h1>Change Password</h1>
            <p>Keep your EventFlow account secure.</p>
        </div>

        <a
            href="/EventFlow/app/views/participant/profile.php"
            class="profile-edit-button"
        >
            <i class="bi bi-arrow-left"></i>
            Back to Profile
        </a>
    </div>

    <section class="profile-card">

        <div class="profile-card-heading">
            <div class="profile-heading-icon">
                <i class="bi bi-shield-lock"></i>
            </div>

            <div>
                <h2>Update Your Password</h2>
                <p>Enter your current password and choose a new one.</p>
            </div>
        </div>

        <?php if ($successMessage !== ''): ?>
            <div class="alert alert-success" role="status">
                <i class="bi bi-check-circle"></i>
                <?= changePasswordEscape($successMessage) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                Please correct the errors below.
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="change-password-form">

            <div class="password-field">
                <label for="current_password">Current Password</label>

                <input
                    type="password"
                    id="current_password"
                    name="current_password"
                    autocomplete="current-password"
                    required
                >

                <?php if (isset($errors['current_password'])): ?>
                    <small class="password-error">
                        <?= changePasswordEscape($errors['current_password']) ?>
                    </small>
                <?php endif; ?>
            </div>

            <div class="password-field">
                <label for="new_password">New Password</label>

                <input
                    type="password"
                    id="new_password"
                    name="new_password"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >

                <small class="password-hint">
                    Use at least 8 characters.
                </small>

                <?php if (isset($errors['new_password'])): ?>
                    <small class="password-error">
                        <?= changePasswordEscape($errors['new_password']) ?>
                    </small>
                <?php endif; ?>
            </div>

            <div class="password-field">
                <label for="confirm_password">Confirm New Password</label>

                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >

                <?php if (isset($errors['confirm_password'])): ?>
                    <small class="password-error">
                        <?= changePasswordEscape($errors['confirm_password']) ?>
                    </small>
                <?php endif; ?>
            </div>

            <div class="edit-profile-actions">
                <a
                    href="/EventFlow/app/views/participant/profile.php"
                    class="security-action"
                >
                    Cancel
                </a>

                <button type="submit" class="profile-edit-button">
                    <i class="bi bi-shield-check"></i>
                    Update Password
                </button>
            </div>

        </form>

    </section>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/main.php';
?>
