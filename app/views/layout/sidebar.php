<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['role'] ?? 'Participant';

$isAdmin = in_array($role, ['Admin', 'System Admin'], true);
$isOrganizer = ($role === 'Organizer');

$basePath = '/EventFlow/app/views/';

if ($isAdmin) {
    $dashboardUrl = $basePath . 'admin/admin_dashboard.php';
    $portalLabel = 'Admin portal';

    $navigation = [
        [
            'label' => 'Dashboard',
            'icon' => 'bi-grid-1x2',
            'url' => $dashboardUrl,
            'key' => 'admin_dashboard'
        ],
        [
            'label' => 'Event approval',
            'icon' => 'bi-clipboard-check',
            'url' => $basePath . 'admin/event_approvals.php',
            'key' => 'event-approvals',
            'count' => 3
        ],
        [
            'label' => 'User management',
            'icon' => 'bi-people',
            'url' => $basePath . 'admin/users.php',
            'key' => 'users'
        ],
        [
            'label' => 'Attendance reports',
            'icon' => 'bi-bar-chart-line',
            'url' => $basePath . 'admin/attendance-reports.php',
            'key' => 'attendance-reports'
        ]
    ];

} elseif ($isOrganizer) {
    $dashboardUrl = $basePath . 'organizer/organizer_dashboard.php';
    $portalLabel = 'Organizer portal';

    $navigation = [
        [
            'label' => 'Dashboard',
            'icon' => 'bi-grid-1x2',
            'url' => $dashboardUrl,
            'key' => 'dashboard'
        ],
        [
            'label' => 'Create event',
            'icon' => 'bi-plus-circle',
            'url' => $basePath . 'organizer/create_event.php',
            'key' => 'create-event'
        ],
        [
            'label' => 'Event management',
            'icon' => 'bi-calendar-event',
            'url' => $basePath . 'organizer/events.php',
            'key' => 'events'
        ],
        [
            'label' => 'Attendance summary',
            'icon' => 'bi-bar-chart-line',
            'url' => $basePath . 'organizer/attendance.php',
            'key' => 'attendance'
        ],
        [
            'label' => 'QR display',
            'icon' => 'bi-qr-code',
            'url' => $basePath . 'organizer/qr-display.php',
            'key' => 'qr-display'
        ]
    ];

} else {
    $dashboardUrl = $basePath . 'participant/dashboard.php';
    $portalLabel = 'Participant portal';

    $navigation = [
        [
            'label' => 'Dashboard',
            'icon' => 'bi-grid-1x2',
            'url' => $dashboardUrl,
            'key' => 'dashboard'
        ],
        [
            'label' => 'My registrations',
            'icon' => 'bi-ticket-perforated',
            'url' => '#registrations',
            'key' => 'registrations',
            'count' => 3
        ],
        [
            'label' => 'My attendance',
            'icon' => 'bi-calendar-check',
            'url' => '#attendance',
            'key' => 'attendance'
        ],
        [
            'label' => 'Certificates',
            'icon' => 'bi-award',
            'url' => '#certificates',
            'key' => 'certificates'
        ],
        [
            'label' => 'Announcements',
            'icon' => 'bi-megaphone',
            'url' => '#announcements',
            'key' => 'announcements'
        ]
    ];
}

/*
|--------------------------------------------------------------------------
| Current page detection
|--------------------------------------------------------------------------
| Parse the requested URL path rather than relying on PHP_SELF.
*/
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$currentPage = strtolower(
    pathinfo($requestPath ?: '', PATHINFO_FILENAME)
);

$firstName = $_SESSION['user_fname']
    ?? ($isAdmin ? 'Admin' : ($isOrganizer ? 'Organizer' : 'Participant'));

$lastName = $_SESSION['user_lname'] ?? '';

$fullName = trim($firstName . ' ' . $lastName);

$initials = strtoupper(
    substr($firstName, 0, 1) .
    (substr($lastName, 0, 1) ?: '')
);

$department = $_SESSION['department'] ?? '';
$studentId = $_SESSION['student_id'] ?? '';

if ($isAdmin && $department === '') {
    $department = 'System administrator';
}

$escape = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

/*
|--------------------------------------------------------------------------
| Role-specific profile URL
|--------------------------------------------------------------------------
*/
if ($isAdmin) {
    $profileUrl = $basePath . 'admin/profile.php';
} elseif ($isOrganizer) {
    $profileUrl = $basePath . 'organizer/profile.php';
} else {
    $profileUrl = $basePath . 'participant/profile.php';
}
?>

<aside class="dashboard-sidebar">

    <a href="<?= $escape($dashboardUrl) ?>" class="dashboard-brand">
        <span class="brand-symbol">
            <i class="bi bi-calendar2-check"></i>
        </span>
        <span>EventFlow</span>
    </a>

    <div class="sidebar-portal">
        <span class="portal-dot"></span>
        <?= $escape($portalLabel) ?>
    </div>

    <div class="sidebar-label">WORKSPACE</div>

    <?php if ($isAdmin): ?>
        <div class="sidebar-admin-overview">
            <span class="admin-overview-icon">
                <i class="bi bi-shield-check"></i>
            </span>
            <div>
                <strong>Administration overview</strong>
                <p>Keep campus events organized.</p>
            </div>
        </div>

    <?php elseif ($isOrganizer): ?>
        <div class="sidebar-admin-overview">
            <span class="admin-overview-icon">
                <i class="bi bi-calendar2-check"></i>
            </span>
            <div>
                <strong>Organizer overview</strong>
                <p>Manage events and attendance.</p>
            </div>
        </div>
    <?php endif; ?>

    <nav class="dashboard-nav">

        <?php foreach ($navigation as $item): ?>

            <?php
            $isActive = false;

            /*
             * Compare the actual current URL with each menu URL.
             * This avoids depending only on the menu key.
             */
            if ($item['url'] !== '#' &&
                strpos($item['url'], '/') === 0) {

                $menuPath = parse_url($item['url'], PHP_URL_PATH);

                $isActive = (
                    rtrim(strtolower($requestPath ?? ''), '/') ===
                    rtrim(strtolower($menuPath ?? ''), '/')
                );
            }

            // Explicit dashboard matching for each role.
            if (
                $item['label'] === 'Dashboard' &&
                $currentPage === (
                    $isAdmin ? 'admin_dashboard' : 'dashboard'
                )
            ) {
                $isActive = true;
            }

            // Organizer event-related pages.
            if (
                $isOrganizer &&
                $item['key'] === 'events' &&
                in_array($currentPage, [
                    'events',
                    'manage-event',
                    'edit-event',
                    'event_details',
                    'event-report'
                ], true)
            ) {
                $isActive = true;
            }

            // Admin user-management pages.
            if (
                $isAdmin &&
                $item['key'] === 'users' &&
                in_array($currentPage, ['users', 'add_user'], true)
            ) {
                $isActive = true;
            }
            ?>

            <a
                href="<?= $escape($item['url']) ?>"
                class="nav-item<?= $isActive ? ' active' : '' ?>"
                <?= $isActive ? 'aria-current="page"' : '' ?>
            >
                <i class="bi <?= $escape($item['icon']) ?>"></i>

                <span><?= $escape($item['label']) ?></span>

                <?php if (isset($item['count'])): ?>
                    <span class="nav-count">
                        <?= (int) $item['count'] ?>
                    </span>
                <?php endif; ?>
            </a>

        <?php endforeach; ?>

    </nav>

    <div class="sidebar-help">
        <div class="help-icon">
            <i class="bi bi-headset"></i>
        </div>

        <strong>Need a hand?</strong>
        <p>We're here to help you.</p>

        <a href="mailto:support@northbridge.edu">
            Help &amp; support
            <i class="bi bi-arrow-up-right"></i>
        </a>
    </div>

    <div class="sidebar-profile">

        <a href="<?= $escape($profileUrl) ?>" class="profile-avatar-link">
            <div class="profile-avatar">
                <?= $escape($initials) ?>
            </div>
        </a>

        <div class="profile-info">
            <strong><?= $escape($fullName) ?></strong>

            <span>
                <?php if ($isAdmin): ?>
                    System administrator
                <?php else: ?>
                    <?= $escape($department) ?>

                    <?php if ($studentId !== ''): ?>
                        · <?= $escape($studentId) ?>
                    <?php endif; ?>
                <?php endif; ?>
            </span>
        </div>

        <button
            type="button"
            class="profile-menu"
            id="profileMenuButton"
            aria-label="Profile options"
            aria-expanded="false"
            aria-controls="profileDropdown"
        >
            <i class="bi bi-three-dots"></i>
        </button>

        <div class="profile-dropdown" id="profileDropdown">
            <a href="/EventFlow/logout.php" class="profile-dropdown-item">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </div>

    </div>

</aside>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const button = document.getElementById('profileMenuButton');
    const dropdown = document.getElementById('profileDropdown');

    if (!button || !dropdown) {
        return;
    }

    button.addEventListener('click', function (event) {
        event.stopPropagation();

        const isOpen = dropdown.classList.toggle('show');
        button.setAttribute('aria-expanded', String(isOpen));
    });

    document.addEventListener('click', function (event) {
        if (
            !dropdown.contains(event.target) &&
            !button.contains(event.target)
        ) {
            dropdown.classList.remove('show');
            button.setAttribute('aria-expanded', 'false');
        }
    });
});
</script>