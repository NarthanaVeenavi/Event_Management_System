
<?php
$title = 'Organizer Dashboard | EventFlow';
$pageCss = 'organizer_dashboard.css';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$organizerName = $_SESSION['fname'] ?? 'Dr. James Lee';
$organizerDepartment = 'Computer Science Dept.';

$stats = [
    [
        'label' => 'My events',
        'value' => '5',
        'note' => '2 upcoming · 1 live · 1 completed · 1 draft',
        'icon' => 'bi-calendar-event',
        'class' => 'blue'
    ],
    [
        'label' => 'Total registrations',
        'value' => '436',
        'note' => 'Across 4 published events',
        'icon' => 'bi-people',
        'class' => 'teal'
    ],
    [
        'label' => 'Recorded attendance',
        'value' => '250',
        'note' => '142 today · 108 at completed events',
        'icon' => 'bi-person-check',
        'class' => 'purple'
    ],
    [
        'label' => 'Certificates issued',
        'value' => '108',
        'note' => 'For completed events',
        'icon' => 'bi-award',
        'class' => 'orange'
    ],
];

$events = [
    [
        'name' => 'Research & Innovation Symposium',
        'id' => 'EVT-1024',
        'date' => '17 Oct 2026',
        'time' => '09:00–16:00',
        'venue' => 'University Auditorium',
        'status' => 'Approved',
        'registered' => 186,
        'capacity' => 240,
        'attended' => 142,
        'action' => 'Manage',
        'live' => true
    ],
    [
        'name' => 'AI Ethics & Responsible Innovation',
        'id' => 'EVT-1025',
        'date' => '24 Oct 2026',
        'time' => '',
        'venue' => 'Innovation Hall',
        'status' => 'Approved',
        'registered' => 96,
        'capacity' => 160,
        'attended' => null,
        'action' => 'Manage',
        'live' => false
    ],
    [
        'name' => 'Web Accessibility Essentials',
        'id' => 'EVT-1027',
        'date' => '26 Oct 2026',
        'time' => '',
        'venue' => 'Computing Lab 3',
        'status' => 'Approved',
        'registered' => 34,
        'capacity' => 50,
        'attended' => null,
        'action' => 'Manage',
        'live' => false
    ],
    [
        'name' => 'Data Visualization Workshop',
        'id' => 'EVT-1018',
        'date' => '10 Oct 2026',
        'time' => '',
        'venue' => 'Computing Lab 2',
        'status' => 'Completed',
        'registered' => 120,
        'capacity' => 120,
        'attended' => 108,
        'action' => 'View report',
        'live' => false
    ],
    [
        'name' => 'Applied Machine Learning Lab',
        'id' => 'EVT-1032',
        'date' => '07 Nov 2026',
        'time' => '',
        'venue' => 'Not yet published',
        'status' => 'Draft',
        'registered' => 0,
        'capacity' => 80,
        'attended' => null,
        'action' => 'Continue',
        'live' => false
    ],
];

$activities = [
    [
        'time' => '10:24',
        'name' => 'Maya Chen checked in',
        'detail' => 'QR validated · ATT-0872',
        'icon' => 'bi-person-check',
        'class' => 'teal'
    ],
    [
        'time' => '10:22',
        'name' => 'Noah Williams checked in',
        'detail' => 'QR validated · ATT-0871',
        'icon' => 'bi-person-check',
        'class' => 'blue'
    ],
    [
        'time' => '10:18',
        'name' => 'Session code refreshed',
        'detail' => 'AM-01 · Organizer display',
        'icon' => 'bi-arrow-clockwise',
        'class' => 'purple'
    ],
];

$escape = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

ob_start();
?>

<div class="org-dashboard">

    <!-- Page heading -->
    <div class="org-page-heading">
        <div>
            <div class="org-university">
                <i class="bi bi-mortarboard"></i>
                Northbridge University
            </div>
        </div>
    </div>

    <!-- Welcome -->
    <section class="org-welcome">
        <div class="org-welcome-copy">
            <span class="org-eyebrow">
                <i class="bi bi-grid"></i>
                WORKSPACE
            </span>

            <h1>Organizer overview</h1>
            <p class="org-welcome-subtitle">Organizer portal</p>

            <p class="org-welcome-description">
                Manage your events, welcome participants, and track every check-in.
            </p>

            <div class="org-welcome-actions">
                <a href="create_event.php" class="org-btn-primary">
                    <i class="bi bi-plus-lg"></i>
                    Create event
                </a>

                <a href="#my-events" class="org-btn-light">
                    <i class="bi bi-calendar-week"></i>
                    Manage your events
                </a>
            </div>
        </div>

        <div class="org-welcome-visual" aria-hidden="true">
            <div class="org-visual-ring org-ring-one"></div>
            <div class="org-visual-ring org-ring-two"></div>
            <div class="org-visual-calendar">
                <div class="org-calendar-top">
                    <span></span><span></span>
                </div>
                <i class="bi bi-calendar2-check"></i>
                <div class="org-calendar-lines">
                    <span></span><span></span><span></span>
                    <span></span><span></span><span></span>
                </div>
            </div>
            <div class="org-visual-check">
                <i class="bi bi-check-lg"></i>
            </div>
        </div>
    </section>

    <!-- Statistics -->
    <section class="org-stats-grid">
        <?php foreach ($stats as $stat): ?>
            <article class="org-stat-card">
                <div class="org-stat-heading">
                    <span class="org-stat-label">
                        <?= $escape($stat['label']) ?>
                    </span>
                    <span class="org-stat-icon <?= $escape($stat['class']) ?>">
                        <i class="bi <?= $escape($stat['icon']) ?>"></i>
                    </span>
                </div>

                <div class="org-stat-value">
                    <?= $escape($stat['value']) ?>
                </div>

                <p class="org-stat-note">
                    <?= $escape($stat['note']) ?>
                </p>
            </article>
        <?php endforeach; ?>
    </section>

    <!-- Live event and activity -->
    <div class="org-live-grid">

        <section class="org-live-card">
            <div class="org-live-header">
                <div class="org-live-title">
                    <span class="org-live-indicator"></span>
                    <span>LIVE EVENT · EVT-1024</span>
                </div>
                <span class="org-checkin-open">
                    <span></span> Check-in open
                </span>
            </div>

            <div class="org-live-body">
                <div class="org-live-event-icon">
                    <i class="bi bi-qr-code-scan"></i>
                </div>

                <div class="org-live-event-content">
                    <span class="org-section-kicker">EVENT MANAGEMENT</span>
                    <h2>Research &amp; Innovation Symposium</h2>

                    <div class="org-live-meta">
                        <span><i class="bi bi-calendar3"></i> 17 Oct 2026</span>
                        <span><i class="bi bi-clock"></i> 09:00–16:00</span>
                    </div>

                    <div class="org-live-venue">
                        <i class="bi bi-geo-alt"></i>
                        University Auditorium
                    </div>
                </div>
            </div>

            <div class="org-attendance-summary">
                <div class="org-attendance-numbers">
                    <strong>142 <span>/ 186 checked in</span></strong>
                    <span>76.3% attendance</span>
                </div>

                <div class="org-attendance-track">
                    <div class="org-attendance-progress" style="width: 76.3%"></div>
                </div>

                <p>44 registrations pending check-in</p>
            </div>

            <div class="org-live-actions">
                <a href="qr-display.php" class="org-btn-primary">
                    <i class="bi bi-qr-code"></i>
                    Open QR display
                </a>

                <a href="manage-event.php?id=EVT-1024" class="org-btn-outline">
                    Manage event
                    <i class="bi bi-arrow-up-right"></i>
                </a>
            </div>
        </section>

        <section class="org-activity-card">
            <div class="org-card-heading">
                <div>
                    <h2>Today's activity</h2>
                    <p>Latest check-ins and event updates.</p>
                </div>
                <span class="org-activity-count">3</span>
            </div>

            <div class="org-activity-list">
                <?php foreach ($activities as $activity): ?>
                    <article class="org-activity-item">
                        <div class="org-activity-icon <?= $escape($activity['class']) ?>">
                            <i class="bi <?= $escape($activity['icon']) ?>"></i>
                        </div>

                        <div class="org-activity-content">
                            <div class="org-activity-top">
                                <h3><?= $escape($activity['name']) ?></h3>
                                <time><?= $escape($activity['time']) ?></time>
                            </div>
                            <p><?= $escape($activity['detail']) ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <a href="attendance.php?event=EVT-1024" class="org-activity-footer">
                View attendance details
                <i class="bi bi-arrow-right"></i>
            </a>
        </section>
    </div>

    <!-- My events -->
    <section class="org-events-card" id="my-events">
        <div class="org-card-heading org-events-heading">
            <div>
                <h2>My events</h2>
                <p>Manage events, registrations, and attendance.</p>
            </div>

            <a href="events.php" class="org-view-all">
                Manage all events
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="org-table-scroll">
            <table class="org-events-table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Schedule</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Attended</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td>
                                <div class="org-table-event">
                                    <span class="org-table-event-icon">
                                        <i class="bi bi-calendar-event"></i>
                                    </span>
                                    <div>
                                        <strong><?= $escape($event['name']) ?></strong>
                                        <span><?= $escape($event['id']) ?>
                                            · <?= $escape($event['venue']) ?></span>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="org-schedule-date">
                                    <?= $escape($event['date']) ?>
                                </span>
                                <?php if ($event['time'] !== ''): ?>
                                    <span class="org-schedule-time">
                                        <?= $escape($event['time']) ?>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <span class="org-status <?= strtolower($escape($event['status'])) ?>">
                                    <span></span>
                                    <?= $escape($event['status']) ?>
                                </span>
                            </td>

                            <td>
                                <span class="org-registered">
                                    <?= $escape($event['registered']) ?>
                                    <span>/ <?= $escape($event['capacity']) ?></span>
                                </span>
                            </td>

                            <td>
                                <span class="org-attended">
                                    <?= $event['attended'] === null ? '—' : $escape($event['attended']) ?>
                                </span>
                            </td>

                            <td>
                                <a class="org-table-action"
                                   href="<?= $event['status'] === 'Completed'
                                       ? 'event-report.php?id=' . rawurlencode($event['id'])
                                       : ($event['status'] === 'Draft'
                                           ? 'edit-event.php?id=' . rawurlencode($event['id'])
                                           : 'manage-event.php?id=' . rawurlencode($event['id'])) ?>">
                                    <?= $escape($event['action']) ?>
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/main.php';
?>
