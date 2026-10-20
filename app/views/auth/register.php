
<?php
$errors = $errors ?? [];
$old = $old ?? [];
$result = $result ?? [];

$message = $result['message'] ?? '';
$success = !empty($result['success']);

function registerValue(string $key, array $old): string
{
    return htmlspecialchars($old[$key] ?? '', ENT_QUOTES, 'UTF-8');
}

$title = 'Create Account | EventFlow';
$pageCss = 'register.css';

ob_start();
?>

<div class="register-page">

    <!-- LEFT: Branding and promotional content -->
    <section class="register-promo">
        <div class="promo-brand">
            <div class="promo-logo">EventFlow</div>
            <div class="promo-university">NORTHBRIDGE UNIVERSITY</div>
        </div>

        <div class="promo-content">
            <h1>
                Every event.<br>
                Every connection.<br>
                <span>All in one place.</span>
            </h1>

            <p class="promo-description">
                Your campus experience, organized. Discover events,
                record your attendance, and celebrate what you learn.
            </p>

            <div class="promo-features">
                <div class="promo-feature">
                    <div class="feature-icon">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <div>
                        <h3>Discover &amp; register</h3>
                        <p>Find events that move you forward.</p>
                    </div>
                </div>

                <div class="promo-feature">
                    <div class="feature-icon">
                        <i class="bi bi-qr-code-scan"></i>
                    </div>
                    <div>
                        <h3>Scan &amp; attend</h3>
                        <p>A simple check-in, a reliable record.</p>
                    </div>
                </div>

                <div class="promo-feature">
                    <div class="feature-icon">
                        <i class="bi bi-award"></i>
                    </div>
                    <div>
                        <h3>Learn &amp; achieve</h3>
                        <p>Access certificates you have earned.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="promo-footer">
            Academic operations, simplified.
        </div>
    </section>

    <!-- RIGHT: Registration form -->
    <section class="register-form-section">
        <div class="register-form-wrapper">

            <div class="mobile-brand">
                <span>EventFlow</span>
                <small>NORTHBRIDGE UNIVERSITY</small>
            </div>

            <div class="register-heading">
                <h2>Create your account</h2>
                <p>Join your university’s community of curious minds.</p>
            </div>

            <!-- SUCCESS MESSAGE -->
            <?php if ($success && $message !== ''): ?>
                <div class="alert alert-success" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <!-- ERROR MESSAGES -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li>
                                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form
                id="registerForm"
                action=""
                method="POST"
                class="register-form"
            >

                <div class="row g-3">
                    <div class="col-sm-6">
                        <label for="first_name" class="form-label">
                            First name <span>*</span>
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="first_name"
                            name="first_name"
                            placeholder="Maya"
                            value="<?= registerValue('first_name', $old) ?>"
                            autocomplete="given-name"
                            required
                        >
                    </div>

                    <div class="col-sm-6">
                        <label for="last_name" class="form-label">
                            Last name <span>*</span>
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="last_name"
                            name="last_name"
                            placeholder="Chen"
                            value="<?= registerValue('last_name', $old) ?>"
                            autocomplete="family-name"
                            required
                        >
                    </div>
                </div>

                <div class="form-field">
                    <label for="email" class="form-label">
                        University email <span>*</span>
                    </label>
                    <input
                        type="email"
                        class="form-control"
                        id="email"
                        name="email"
                        placeholder="maya.chen@northbridge.edu"
                        value="<?= registerValue('email', $old) ?>"
                        pattern="[A-Za-z0-9._%+-]+@northbridge\.edu"
                        title="Use your official @northbridge.edu email address."
                        autocomplete="email"
                        required
                    >
                    <div class="form-hint">
                        Participants use your official @northbridge.edu email address.
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <label for="student_id" class="form-label">
                            Student ID <span>*</span>
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="student_id"
                            name="student_id"
                            placeholder="2024-0186"
                            value="<?= registerValue('student_id', $old) ?>"
                            required
                        >
                    </div>

                    <div class="col-sm-6">
                        <label for="department" class="form-label">
                            Department <span>*</span>
                        </label>
                        <select
                            class="form-select"
                            id="department"
                            name="department"
                            required
                        >
                            <option value="">Select department</option>

                            <?php
                            $departments = [
                                'Computer Science',
                                'Information Technology',
                                'Software Engineering',
                                'Information Systems',
                                'Engineering',
                                'Business Management',
                                'Other'
                            ];
                            ?>

                            <?php foreach ($departments as $department): ?>
                                <option
                                    value="<?= htmlspecialchars($department, ENT_QUOTES, 'UTF-8') ?>"
                                    <?= (($old['department'] ?? '') === $department) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($department, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-field">
                    <label for="password" class="form-label">
                        Password <span>*</span>
                    </label>
                    <input
                        type="password"
                        class="form-control"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        minlength="8"
                        pattern="(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,}"
                        title="Use at least 8 characters, including one number and one symbol."
                        autocomplete="new-password"
                        required
                    >
                    <div class="password-hint">
                        <i class="bi bi-check-circle"></i>
                        At least 8 characters, one number and one symbol
                    </div>
                </div>

                <div class="form-field">
                    <label for="confirm_password" class="form-label">
                        Confirm password <span>*</span>
                    </label>
                    <input
                        type="password"
                        class="form-control"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Confirm your password"
                        autocomplete="new-password"
                        required
                    >
                    <div id="passwordError" class="password-error" hidden>
                        Passwords do not match. Please try again.
                    </div>
                </div>

                <div class="terms-field">
                    <input
                        type="checkbox"
                        id="terms"
                        name="terms"
                        value="1"
                        required
                    >
                    <label for="terms">
                        I agree to the
                        <a href="#" target="_blank">Terms of Service</a>
                        and
                        <a href="#" target="_blank">Privacy Policy</a>,
                        including the recording of event attendance.
                    </label>
                </div>

                <button type="submit" class="btn register-submit">
                    Create participant account
                    <i class="bi bi-arrow-right"></i>
                </button>

            </form>

            <div class="organizer-note">
                <i class="bi bi-info-circle"></i>
                Organizer access? Contact your department administrator
                after creating your account.
            </div>

            <div class="signin-prompt">
                Already have an account?
                <a href="login.php">Sign in</a>
            </div>

            <div class="support-footer">
                Need assistance?
                <a href="mailto:support@northbridge.edu">
                    Contact support@northbridge.edu
                </a>
                <p>© 2026 EventFlow · Academic operations, simplified.</p>
            </div>

        </div>
    </section>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('registerForm');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const passwordError = document.getElementById('passwordError');

    function checkPasswords() {
        const matches = password.value === confirmPassword.value;

        confirmPassword.setCustomValidity(
            matches || confirmPassword.value === ''
                ? ''
                : 'Passwords do not match.'
        );

        passwordError.hidden =
            confirmPassword.value === '' || matches;

        return matches;
    }

    confirmPassword.addEventListener('input', checkPasswords);
    password.addEventListener('input', checkPasswords);

    form.addEventListener('submit', function (event) {
        if (!checkPasswords()) {
            event.preventDefault();
            confirmPassword.reportValidity();
        }
    });
});
</script>

<?php
$content = ob_get_clean();

require_once __DIR__ . '/../layout/main.php';
