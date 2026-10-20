<?php
$title = 'Add User | EventFlow';
$pageCss = 'add_user.css';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (
    !isset($_SESSION['user_id']) ||
    !in_array($_SESSION['role'] ?? '', ['Admin', 'System Admin'], true)
) {
    header('Location: /EventFlow/login.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/user_controller.php';

$successMessage = $_SESSION['add_user_success'] ?? '';
unset($_SESSION['add_user_success']);

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verify the CSRF token.
        $submittedToken = $_POST['csrf_token'] ?? '';

        if (
            !is_string($submittedToken) ||
            !hash_equals($_SESSION['csrf_token'], $submittedToken)
        ) {
            throw new RuntimeException(
                'Your session token is invalid. Please refresh the page and try again.'
            );
        }

        // Check the submitted action.
        if (($_POST['action'] ?? '') !== 'create_user') {
            throw new RuntimeException('Invalid form submission.');
        }

        // Create the user through the controller.
        $userController = new UserController($conn);
        $userController->createUser($_POST);

        // Redirect after successful creation to prevent duplicate submissions.
        $_SESSION['add_user_success'] =
            'User account created successfully!';

        header('Location: /EventFlow/app/views/admin/add_user.php');
        exit;

    } catch (InvalidArgumentException $e) {
        $errorMessage = $e->getMessage();

    } catch (RuntimeException $e) {
        $errorMessage = $e->getMessage();

    } catch (Throwable $e) {
        error_log('Add User Error: ' . $e->getMessage());
        $errorMessage =
            'Something went wrong while creating the user. Please try again.';
    }
}

ob_start();
?>

<div class="au-page">

    <?php if ($successMessage !== ''): ?>
        <div class="au-alert au-alert-success" role="status">
            <i class="bi bi-check-circle"></i>

            <span class="au-alert-message">
                <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?>
            </span>

            <button type="button"
                    class="au-alert-close"
                    aria-label="Dismiss success message">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    <?php endif; ?>

    <?php if ($errorMessage !== ''): ?>
        <div class="au-alert au-alert-error" role="alert">
            <i class="bi bi-exclamation-circle"></i>

            <span class="au-alert-message">
                <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </span>

            <button type="button"
                    class="au-alert-close"
                    aria-label="Dismiss error message">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="au-page-header">
        <div>
            <a href="/EventFlow/app/views/admin/users.php" class="au-back-link">
                <i class="bi bi-arrow-left"></i>
                Back to User Management
            </a>

            <h1>Add New User</h1>
            <p>Create a new account and assign the appropriate role.</p>
        </div>

        <div class="au-header-icon">
            <i class="bi bi-person-plus"></i>
        </div>
    </div>

    <!-- Form Card -->
    <div class="au-card">

        <div class="au-card-header">
            <div class="au-section-icon">
                <i class="bi bi-person-vcard"></i>
            </div>

            <div>
                <h2>User Information</h2>
                <p>Enter the details for the new account.</p>
            </div>
        </div>

        <form action="" method="POST" class="au-form">

            <input type="hidden" name="action" value="create_user">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"
            >

            <!-- Personal Information -->
            <div class="au-section-title">
                <i class="bi bi-person"></i>
                Personal Information
            </div>

            <div class="au-form-grid">

                <div class="au-field">
                    <label for="fname">First Name <span>*</span></label>
                    <input
                        type="text"
                        id="fname"
                        name="fname"
                        placeholder="Enter first name"
                        maxlength="100"
                        autocomplete="given-name"
                        required
                    >
                </div>

                <div class="au-field">
                    <label for="lname">Last Name <span>*</span></label>
                    <input
                        type="text"
                        id="lname"
                        name="lname"
                        placeholder="Enter last name"
                        maxlength="100"
                        autocomplete="family-name"
                        required
                    >
                </div>

                <div class="au-field">
                    <label for="student_id">Student / Staff ID <span>*</span></label>
                    <input
                        type="text"
                        id="student_id"
                        name="student_id"
                        placeholder="e.g. 2026-0123"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="au-field">
                    <label for="department">Department <span>*</span></label>
                    <select id="department" name="department" required>
                        <option value="">Select department</option>
                        <option value="Computer Science">Computer Science</option>
                        <option value="Information Technology">Information Technology</option>
                        <option value="Software Engineering">Software Engineering</option>
                        <option value="Business Management">Business Management</option>
                        <option value="Engineering">Engineering</option>
                        <option value="Administration">Administration</option>
                    </select>
                </div>

                <div class="au-field au-full-width">
                    <label for="email">Email Address <span>*</span></label>
                    <div class="au-input-icon">
                        <i class="bi bi-envelope"></i>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Enter email address"
                            maxlength="150"
                            autocomplete="email"
                            required
                        >
                    </div>
                </div>

            </div>

            <!-- Account Information -->
            <div class="au-section-title au-account-title">
                <i class="bi bi-shield-lock"></i>
                Account Information
            </div>

            <div class="au-form-grid">

                <div class="au-field">
                    <label for="password">Password <span>*</span></label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        pattern="(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,}"
                        placeholder="Minimum 8 characters"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >
                    <small>Use at least 8 characters, including one number and one symbol.</small>
                </div>

                <div class="au-field">
                    <label for="confirm_password">Confirm Password <span>*</span></label>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Re-enter password"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <div class="au-field au-full-width">
                    <label for="role">User Role <span>*</span></label>
                    <select id="role" name="role" required>
                        <option value="Participant" selected>Participant</option>
                        <option value="Organizer">Organizer</option>
                        <option value="Admin">Admin</option>
                    </select>
                    <small>
                        Choose the permissions appropriate for this user's responsibilities.
                    </small>
                </div>

            </div>

            <!-- Form Actions -->
            <div class="au-form-footer">
                <p><span>*</span> Required fields</p>

                <div class="au-actions">
                    <a
                        href="/EventFlow/app/views/admin/users.php"
                        class="au-cancel-button"
                    >
                        Cancel
                    </a>

                    <button type="submit" class="au-submit-button">
                        <i class="bi bi-person-plus"></i>
                        Create User
                    </button>
                </div>
            </div>

        </form>
    </div>

</div>

<script>
document.addEventListener('click', function (event) {
    const closeButton = event.target.closest('.au-alert-close');

    if (!closeButton) return;

    const alert = closeButton.closest('.au-alert');

    if (alert) {
        alert.remove();
    }
});
</script>

<?php
$content = ob_get_clean();

require_once __DIR__ . '/../layout/main.php';
?>