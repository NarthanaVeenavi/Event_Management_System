
<header class="dashboard-topbar">
    <?php
    $role = $_SESSION['role'] ?? 'Participant';

    $roleLabels = [
        'Admin'         => 'Admin',
        'System Admin'  => 'Admin',
        'Organizer'     => 'Organizer',
        'Participant'   => 'Participant'
    ];

    $breadcrumbRole = $roleLabels[$role] ?? 'Participant';
    ?>

    <div class="breadcrumb">
        <span>EventFlow</span>
        <i class="bi bi-chevron-right"></i>
        <span><?= htmlspecialchars($breadcrumbRole, ENT_QUOTES, 'UTF-8') ?></span>
        <i class="bi bi-chevron-right"></i>
        <strong>
            <?= htmlspecialchars($title ?? 'Dashboard', ENT_QUOTES, 'UTF-8') ?>
        </strong>
    </div>

    <div class="current-date">
        <i class="bi bi-calendar3"></i>
        <span id="current-date"></span>

        <span class="date-separator">|</span>

        <i class="bi bi-clock"></i>
        <span id="current-time"></span>
    </div>

    <div class="topbar-right">
        <span class="university-name">
            <i class="bi bi-mortarboard"></i>
            Northbridge University
        </span>

        <button class="notification-button"
                type="button"
                aria-label="Notifications">
            <i class="bi bi-bell"></i>
            <span class="notification-dot"></span>
        </button>

        <?php
        $firstName = $_SESSION['user_fname'] ?? '';
        $lastName  = $_SESSION['user_lname'] ?? '';

        $initials = strtoupper(
            mb_substr($firstName, 0, 1, 'UTF-8') .
            mb_substr($lastName, 0, 1, 'UTF-8')
        );

        if ($initials === '') {
            $initials = 'P';
        }
        ?>

        <a href="/EventFlow/app/views/participant/profile.php"
            class="topbar-avatar"
            title="My Profile"
            aria-label="View my profile">
                <?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?>
        </a>
        
        <!-- Theme Toggle -->
        <button
            type="button"
            class="theme-toggle"
            id="theme-toggle"
            aria-label="Enable dark mode"
            title="Enable dark mode"
        >
            <i class="bi bi-moon-stars" id="theme-icon"></i>
        </button>

    </div>
</header>
