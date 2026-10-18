<?php
$errors = $errors ?? [];
$old = $old ?? [];

$title = 'Welcome Back | EventFlow';
$pageCss = 'login.css';

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

    <!-- RIGHT: Login form -->
    <section class="register-form-section">

        <div class="register-form-wrapper">

            <div class="mobile-brand">
                <span>EventFlow</span>
                <small>NORTHBRIDGE UNIVERSITY</small>
            </div>

            <div class="register-heading">
                <h2>Welcome back</h2>
                <p>Sign in to manage your campus events and attendance.</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li>
                                <?= htmlspecialchars(
                                    $error,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form
                id="loginForm"
                action=""
                method="POST"
                class="register-form"
            >

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
                        value="<?= htmlspecialchars(
                            $old['email'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        autocomplete="username"
                        required
                    >
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
                        autocomplete="current-password"
                        required
                    >
                </div>

                <div class="login-options">
                    <label class="remember-option" for="remember">
                        <input
                            type="checkbox"
                            id="remember"
                            name="remember"
                            value="1"
                        >
                        <span>Remember me</span>
                    </label>

                    <a href="#" class="forgot-password">
                        Forgot password?
                    </a>
                </div>

                <button type="submit" class="btn register-submit">
                    Sign in
                    <i class="bi bi-arrow-right"></i>
                </button>

            </form>

            <div class="organizer-note login-role-note">
                <i class="bi bi-shield-check"></i>

                <div>
                    <strong>One account, the right access</strong>
                    <p>
                        Participants join with a university email.
                        Organizer and administrator access is assigned
                        by the university.
                    </p>
                </div>
            </div>

            <div class="signin-prompt">
                New to EventFlow?
                <a href="register.php">Create an account</a>
            </div>

            <div class="support-footer">
                Need assistance?
                <a href="mailto:support@northbridge.edu">
                    Contact support@northbridge.edu
                </a>

                <p>
                    © 2026 EventFlow · Academic operations, simplified.
                </p>
            </div>

        </div>
    </section>

</div>

<?php
$content = ob_get_clean();

require_once __DIR__ . '/../layout/main.php';
?>