
<?php
$title = 'Admin Dashboard | EventFlow';
$pageCss = 'admin_dashboard.css';

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

date_default_timezone_set('Asia/Colombo');

// Temporary dashboard data for UI development.
$stats = [
    [
        'label' => 'Total users',
        'value' => '1,284',
        'detail' => '1,248 participants · 32 organizers · 4 admins',
        'icon' => 'bi-people',
        'color' => 'blue'
    ],
    [
        'label' => 'Active approved events',
        'value' => '6',
        'detail' => '1 live · 5 upcoming this month',
        'icon' => 'bi-calendar2-check',
        'color' => 'green'
    ],
    [
        'label' => 'Pending approvals',
        'value' => '3',
        'detail' => 'Oldest request: 16 Oct 2026',
        'icon' => 'bi-clipboard-check',
        'color' => 'orange'
    ],
    [
        'label' => 'Attendance this month',
        'value' => '1,042',
        'detail' => 'Across 9 live and completed events',
        'icon' => 'bi-person-check',
        'color' => 'purple'
    ]
];

$pendingEvents = [
    [
        'id' => 'EVT-1030',
        'name' => 'Cybersecurity Awareness Bootcamp',
        'organizer' => 'Prof. Elena Cruz',
        'date' => '05 Nov',
        'submitted' => '16 Oct'
    ],
    [
        'id' => 'EVT-1031',
        'name' => 'Design Thinking Sprint',
        'organizer' => 'Daniel Park',
        'date' => '06 Nov',
        'submitted' => '17 Oct'
    ],
    [
        'id' => 'EVT-1033',
        'name' => 'Research Writing Clinic',
        'organizer' => 'Dr. Sara Ahmed',
        'date' => '12 Nov',
        'submitted' => '17 Oct'
    ]
];

$recentDecisions = [
    [
        'name' => 'Career Launch: Meet the Industry',
        'id' => 'EVT-1029',
        'status' => 'Approved',
        'date' => '16 Oct',
        'admin' => 'Alex Santos'
    ],
    [
        'name' => 'Sustainable Campus Challenge',
        'id' => 'EVT-1028',
        'status' => 'Approved',
        'date' => '15 Oct',
        'admin' => 'Alex Santos'
    ]
];

$users = [
    [
        'name' => 'Dr. James Lee',
        'email' => 'james.lee@northbridge.edu',
        'role' => 'Organizer',
        'department' => 'Computer Science',
        'status' => 'Active',
        'initials' => 'JL'
    ],
    [
        'name' => 'Maya Chen',
        'email' => 'maya.chen@northbridge.edu',
        'role' => 'Participant',
        'department' => 'Computer Science',
        'status' => 'Active',
        'initials' => 'MC'
    ]
];

$escape = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

ob_start();
?>

<link rel="stylesheet" href="/EventFlow/assets/css/admin-dashboard.css">

<div class="admin-dashboard">

    <!-- Page heading -->
    <header class="admin-welcome">
        <div>
            <div class="admin-eyebrow">
                <span class="admin-live-dot"></span>
                ADMIN PORTAL
            </div>

            <h1>Administration overview</h1>

            <p>
                Keep campus events trusted, coordinated, and running smoothly.
            </p>
        </div>
    </div>
    </header>

    <!-- Summary cards -->
    <section class="admin-stats-grid" aria-label="Dashboard statistics">

        <?php foreach ($stats as $stat): ?>
            <article class="admin-stat-card">

                <div class="admin-stat-top">
                    <span class="admin-stat-label">
                        <?= $escape($stat['label']) ?>
                    </span>

                    <span class="admin-stat-icon <?= $escape($stat['color']) ?>">
                        <i class="bi <?= $escape($stat['icon']) ?>"></i>
                    </span>
                </div>

                <div class="admin-stat-value">
                    <?= $escape($stat['value']) ?>
                </div>

                <p class="admin-stat-detail">
                    <?= $escape($stat['detail']) ?>
                </p>

            </article>
        <?php endforeach; ?>

    </section>

    <!-- Main dashboard columns -->
    <div class="admin-dashboard-grid">

        <div class="admin-main-column">

            <!-- Event approval queue -->
            <section class="admin-panel">

                <div class="admin-panel-heading">
                    <div>
                        <div class="admin-panel-title-row">
                            <h2>Event approval</h2>
                            <span class="admin-badge pending">3 pending</span>
                        </div>

                        <p>Review events before publishing them.</p>
                    </div>

                    <a
                        href="/EventFlow/app/views/admin/event-approvals.php"
                        class="admin-text-link"
                    >
                        Review all <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <div class="admin-notice">
                    <i class="bi bi-info-circle"></i>
                    Pending events are not visible to participants.
                </div>

                <div class="admin-table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Organizer</th>
                                <th>Submitted</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($pendingEvents as $event): ?>
                                <tr>
                                    <td>
                                        <div class="admin-event-name">
                                            <?= $escape($event['name']) ?>
                                        </div>
                                        <div class="admin-muted">
                                            <?= $escape($event['id']) ?>
                                            · <?= $escape($event['date']) ?>
                                            · Pending
                                        </div>
                                    </td>

                                    <td><?= $escape($event['organizer']) ?></td>

                                    <td><?= $escape($event['submitted']) ?></td>

                                    <td>
                                        <a
                                            class="admin-review-button"
                                            href="/EventFlow/app/views/admin/event-approvals.php"
                                        >
                                            Review
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="admin-panel-footer">
                    <span>3 events awaiting a decision</span>

                    <div class="admin-pagination">
                        <button type="button" disabled aria-label="Previous page">
                            <i class="bi bi-chevron-left"></i>
                            Previous
                        </button>

                        <span class="admin-page-number">1</span>

                        <button type="button" disabled aria-label="Next page">
                            Next
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>

            </section>

            <!-- Recent decisions -->
            <section class="admin-panel">

                <div class="admin-panel-heading">
                    <div>
                        <h2>Recent decisions</h2>
                        <p>Latest event approval activity.</p>
                    </div>

                    <a
                        href="/EventFlow/app/views/admin/event-approvals.php"
                        class="admin-text-link"
                    >
                        View all <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <div class="admin-decision-list">

                    <?php foreach ($recentDecisions as $decision): ?>
                        <div class="admin-decision-item">

                            <div class="admin-decision-icon">
                                <i class="bi bi-check2"></i>
                            </div>

                            <div class="admin-decision-content">
                                <strong><?= $escape($decision['name']) ?></strong>

                                <p>
                                    <?= $escape($decision['id']) ?>
                                    · <?= $escape($decision['status']) ?>
                                    <?= $escape($decision['date']) ?>
                                    by <?= $escape($decision['admin']) ?>
                                </p>
                            </div>

                            <span class="admin-badge approved">
                                <?= $escape($decision['status']) ?>
                            </span>

                        </div>
                    <?php endforeach; ?>

                </div>

            </section>

            <!-- User management preview -->
            <section class="admin-panel">

                <div class="admin-panel-heading">
                    <div>
                        <h2>User management</h2>
                        <p>Quick overview of registered users.</p>
                    </div>

                    <a
                        href="/EventFlow/app/views/admin/users.php"
                        class="admin-text-link"
                    >
                        Manage users <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <div class="admin-table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Department</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="admin-user-cell">
                                            <span class="admin-user-avatar">
                                                <?= $escape($user['initials']) ?>
                                            </span>

                                            <div>
                                                <strong>
                                                    <?= $escape($user['name']) ?>
                                                </strong>
                                                <div class="admin-muted">
                                                    <?= $escape($user['email']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td><?= $escape($user['role']) ?></td>

                                    <td><?= $escape($user['department']) ?></td>

                                    <td>
                                        <span class="admin-status">
                                            <span></span>
                                            <?= $escape($user['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </section>

        </div>

        <!-- Right dashboard column -->
        <aside class="admin-side-column">

            <!-- System health -->
            <section class="admin-panel admin-health-panel">

                <div class="admin-panel-heading">
                    <div>
                        <h2>System health</h2>
                        <p>Service availability</p>
                    </div>

                    <span class="admin-health-icon">
                        <i class="bi bi-activity"></i>
                    </span>
                </div>

                <div class="admin-health-status">
                    <span class="admin-health-check">
                        <i class="bi bi-check-lg"></i>
                    </span>

                    <div>
                        <strong>All services operational</strong>
                        <p>All systems are running normally.</p>
                    </div>
                </div>

                <div class="admin-health-services">
                    <div>
                        <span>Attendance validation</span>
                        <span class="service-operational">Operational</span>
                    </div>

                    <div>
                        <span>Event registration</span>
                        <span class="service-operational">Operational</span>
                    </div>

                    <div>
                        <span>Certificates</span>
                        <span class="service-operational">Operational</span>
                    </div>
                </div>

                <div class="admin-health-footer">
                    <i class="bi bi-clock"></i>
                    Last checked: <?= $escape(date('h:i A')) ?>
                </div>

            </section>

            <!-- Access requests -->
            <section class="admin-panel">

                <div class="admin-panel-heading">
                    <div>
                        <h2>Access requests</h2>
                        <p>New organizer account requests.</p>
                    </div>

                    <span class="admin-count-icon">
                        <i class="bi bi-person-plus"></i>
                    </span>
                </div>

                <div class="admin-empty-state">
                    <span class="admin-empty-icon">
                        <i class="bi bi-inbox"></i>
                    </span>

                    <strong>No pending requests</strong>

                    <p>
                        New organizer access requests will appear here.
                    </p>
                </div>

            </section>

            <!-- Help card -->
            <section class="admin-support-card">
                <span class="admin-support-icon">
                    <i class="bi bi-headset"></i>
                </span>

                <h2>Help &amp; support</h2>

                <p>
                    Need assistance with event approvals or user management?
                    Contact the support team.
                </p>

                <a href="mailto:support@northbridge.edu">
                    Contact support
                    <i class="bi bi-arrow-up-right"></i>
                </a>
            </section>

        </aside>

    </div>

</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/main.php';
?>
