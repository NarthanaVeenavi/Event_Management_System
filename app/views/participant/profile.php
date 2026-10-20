<?php
$title = 'My Profile | EventFlow';
$pageCss = 'profile.css';

/* Start session */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* Check participant login */
if (
    !isset($_SESSION['user_id'])
) {
    header('Location: /EventFlow/login.php');
    exit;
}

/* Database connection */
require_once __DIR__ . '/../../config/database.php';

/* Get logged-in participant details */
$userId = $_SESSION['user_id'];

$sql = "SELECT
            user_id,
            fname,
            lname,
            department,
            student_id,
            email,
            role,
            status
        FROM users
        WHERE user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $userId);
$stmt->execute();

$result = $stmt->get_result();
$participant = $result->fetch_assoc();

$stmt->close();

/* Redirect if the user no longer exists */
if (!$participant) {
    header('Location: /EventFlow/logout.php');
    exit;
}

/* Prepare display values */
$fullName = trim(
    $participant['fname'] . ' ' . $participant['lname']
);

$nameParts = preg_split('/\s+/', $fullName);

$initials = '';

if (!empty($nameParts[0])) {
    $initials .= substr($nameParts[0], 0, 1);
}

if (count($nameParts) > 1) {
    $initials .= substr($nameParts[count($nameParts) - 1], 0, 1);
}

$initials = strtoupper($initials ?: 'P');

/* Helper for safe HTML output */
function profileEscape($value)
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

ob_start();
?>

<div class="profile-page">

    <!-- Page Heading -->
    <div class="profile-page-header">
        <div>
            <span class="section-eyebrow">ACCOUNT SETTINGS</span>
            <h1>My Profile</h1>
            <p>Manage your personal information and account details.</p>
        </div>
    </div>

    <!-- Profile Overview -->
    <section class="profile-card profile-overview">

        <div class="profile-overview-left">

            <div class="profile-avatar">
                <?= profileEscape($initials) ?>
            </div>

            <div class="profile-details">
                <h2><?= profileEscape($fullName) ?></h2>

                <p><?= profileEscape($participant['email']) ?></p>

                <div class="profile-badges">
                    <span class="profile-role">
                        <i class="bi bi-person-badge"></i>
                        <?= profileEscape($participant['role']) ?>
                    </span>

                    <span class="profile-status">
                        <span class="status-dot"></span>
                        <?= profileEscape($participant['status']) ?>
                    </span>
                </div>
            </div>

        </div>

        <a
            href="/EventFlow/app/views/participant/edit_profile.php"
            class="profile-edit-button"
        >
            <i class="bi bi-pencil-square"></i>
            Edit Profile
        </a>

    </section>

    <!-- Personal Information -->
    <section class="profile-card">

        <div class="profile-card-heading">
            <div class="profile-heading-icon">
                <i class="bi bi-person-lines-fill"></i>
            </div>

            <div>
                <h2>Personal Information</h2>
                <p>Your personal and academic details.</p>
            </div>
        </div>

        <div class="profile-details-grid">

            <div class="profile-field">
                <span>First Name</span>
                <strong><?= profileEscape($participant['fname']) ?></strong>
            </div>

            <div class="profile-field">
                <span>Last Name</span>
                <strong><?= profileEscape($participant['lname']) ?></strong>
            </div>

            <div class="profile-field">
                <span>Email Address</span>
                <strong><?= profileEscape($participant['email']) ?></strong>
            </div>

            <div class="profile-field">
                <span>Department</span>
                <strong>
                    <?= profileEscape(
                        $participant['department'] ?: 'Not provided'
                    ) ?>
                </strong>
            </div>

            <div class="profile-field">
                <span>Student ID</span>
                <strong>
                    <?= profileEscape(
                        $participant['student_id'] ?: 'Not provided'
                    ) ?>
                </strong>
            </div>

            <div class="profile-field">
                <span>Account Role</span>
                <strong><?= profileEscape($participant['role']) ?></strong>
            </div>

        </div>

    </section>

    <!-- Account Security -->
    <section class="profile-card">

        <div class="profile-card-heading">
            <div class="profile-heading-icon security-icon">
                <i class="bi bi-shield-lock"></i>
            </div>

            <div>
                <h2>Account Security</h2>
                <p>Keep your EventFlow account secure.</p>
            </div>
        </div>

        <div class="security-option">

            <div class="security-option-icon">
                <i class="bi bi-key"></i>
            </div>

            <div class="security-option-info">
                <strong>Password</strong>
                <span>Change your password to keep your account secure.</span>
            </div>

            <a
                href="/EventFlow/app/views/participant/change_password.php"
                class="security-action"
            >
                Change Password
                <i class="bi bi-arrow-right"></i>
            </a>

        </div>

    </section>

</div>

<?php
$content = ob_get_clean();

require_once __DIR__ . '/../layout/main.php';
?>