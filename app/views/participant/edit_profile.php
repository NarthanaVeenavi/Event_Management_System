<?php

$title = 'Edit Profile | EventFlow';
$pageCss = 'profile.css';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Allow all authenticated user roles.
if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    header('Location: /EventFlow/login.php');
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/profile_controller.php';

function editProfileEscape($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

$userId = (int) $_SESSION['user_id'];
$errors = [];
$successMessage = '';

// Fetch the logged-in user's details regardless of their role.
$stmt = $conn->prepare(
    'SELECT fname, lname, email, department, student_id, role
     FROM users
     WHERE user_id = ?
     LIMIT 1'
);

$stmt->bind_param('i', $userId);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// If the user record does not exist, end the session.
if (!$user) {
    header('Location: /EventFlow/logout.php');
    exit;
}

// Handle profile update.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $controller = new ProfileController($conn);
    $result = $controller->updateProfile($userId, $_POST);

    if ($result['success']) {

        // Update session information.
        $_SESSION['user_fname'] = $result['fname'];
        $_SESSION['user_lname'] = $result['lname'];

        $_SESSION['user_name'] = trim(
            $result['fname'] . ' ' . $result['lname']
        );

        // Update displayed values.
        $user['fname'] = $result['fname'];
        $user['lname'] = $result['lname'];
        $user['email'] = $result['email'];

        $successMessage = 'Your profile has been updated successfully.';

    } else {

        $errors = $result['errors'] ?? [];

        // Preserve submitted values when validation fails.
        $user['fname'] = trim($_POST['fname'] ?? '');
        $user['lname'] = trim($_POST['lname'] ?? '');
        $user['email'] = trim($_POST['email'] ?? '');
    }
}

ob_start();
?>

<div class="profile-page">

    <div class="profile-page-header">
        <div>
            <span class="section-eyebrow">ACCOUNT SETTINGS</span>
            <h1>Edit Profile</h1>
            <p>Update your personal information.</p>
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
                <i class="bi bi-person-lines-fill"></i>
            </div>

            <div>
                <h2>Personal Information</h2>
                <p>Update your name and email address below.</p>
            </div>
        </div>

        <?php if ($successMessage !== ''): ?>
            <div class="alert alert-success" role="status">
                <i class="bi bi-check-circle"></i>
                <?= editProfileEscape($successMessage) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                Please correct the highlighted errors.
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="edit-profile-form">

            <div class="profile-details-grid">

                <!-- First Name -->
                <div class="profile-field">
                    <label for="fname">First Name</label>

                    <input
                        type="text"
                        id="fname"
                        name="fname"
                        maxlength="100"
                        value="<?= editProfileEscape($user['fname']) ?>"
                        required
                    >

                    <?php if (isset($errors['fname'])): ?>
                        <small class="text-danger">
                            <?= editProfileEscape($errors['fname']) ?>
                        </small>
                    <?php endif; ?>
                </div>

                <!-- Last Name -->
                <div class="profile-field">
                    <label for="lname">Last Name</label>

                    <input
                        type="text"
                        id="lname"
                        name="lname"
                        maxlength="100"
                        value="<?= editProfileEscape($user['lname']) ?>"
                        required
                    >

                    <?php if (isset($errors['lname'])): ?>
                        <small class="text-danger">
                            <?= editProfileEscape($errors['lname']) ?>
                        </small>
                    <?php endif; ?>
                </div>

                <!-- Email -->
                <div class="profile-field">
                    <label for="email">Email Address</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        maxlength="254"
                        value="<?= editProfileEscape($user['email']) ?>"
                        required
                    >

                    <?php if (isset($errors['email'])): ?>
                        <small class="text-danger">
                            <?= editProfileEscape($errors['email']) ?>
                        </small>
                    <?php endif; ?>
                </div>

                <!-- Department -->
                <div class="profile-field">
                    <label for="department">Department</label>

                    <input
                        type="text"
                        id="department"
                        value="<?= editProfileEscape(
                            $user['department'] ?: 'Not provided'
                        ) ?>"
                        readonly
                    >
                </div>

                <!-- Student ID -->
                <div class="profile-field">
                    <label for="student_id">Student ID</label>

                    <input
                        type="text"
                        id="student_id"
                        value="<?= editProfileEscape(
                            $user['student_id'] ?: 'Not provided'
                        ) ?>"
                        readonly
                    >
                </div>

                <!-- Role -->
                <div class="profile-field">
                    <label for="role">Account Role</label>

                    <input
                        type="text"
                        id="role"
                        value="<?= editProfileEscape($user['role']) ?>"
                        readonly
                    >
                </div>

            </div>

            <div class="edit-profile-actions">

                <a
                    href="/EventFlow/app/views/participant/profile.php"
                    class="security-action"
                >
                    Cancel
                </a>

                <button type="submit" class="profile-edit-button">
                    <i class="bi bi-check2-circle"></i>
                    Save Changes
                </button>

            </div>

        </form>

    </section>

</div>

<?php
$content = ob_get_clean();

require_once __DIR__ . '/../layout/main.php';
?>