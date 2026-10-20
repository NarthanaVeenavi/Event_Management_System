<?php
$title = 'Participant Dashboard | EventFlow';
$pageCss = 'dashboard.css';

// Start the session if it hasn't already been started.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure the user is logged in as a participant.
if (
    !isset($_SESSION['user_id']) ||
    ($_SESSION['role'] ?? '') !== 'Participant'
) {
    header('Location: /EventFlow/login.php');
    exit;
}

// Get the logged-in participant's details.
$firstName = $_SESSION['user_fname'] ?? '';
$lastName  = $_SESSION['user_lname'] ?? '';

$participant = [
    'name' => $_SESSION['user_name'] ?? 'Participant',
    'initials' => strtoupper(
        mb_substr($firstName, 0, 1, 'UTF-8') .
        mb_substr($lastName, 0, 1, 'UTF-8')
    ),
    'department' => $_SESSION['department'] ?? '',
    'student_id' => $_SESSION['student_id'] ?? ''
];

// Dashboard statistics.
$stats = [
    [
        'label' => 'Upcoming events',
        'value' => '2',
        'icon' => 'bi-calendar-event',
        'color' => 'blue'
    ],
    [
        'label' => 'Events attended',
        'value' => '4',
        'icon' => 'bi-check2-circle',
        'color' => 'teal'
    ],
    [
        'label' => 'Certificates earned',
        'value' => '1',
        'icon' => 'bi-award',
        'color' => 'purple'
    ],
    [
        'label' => 'My registrations',
        'value' => '3 of 4',
        'icon' => 'bi-ticket-perforated',
        'color' => 'orange'
    ]
];

// Sample registrations for dashboard UI development.
$registrations = [
    [
        'title' => 'AI Ethics & Responsible Innovation',
        'meta' => 'EVT-1025 · Seminar',
        'date' => '24 Oct',
        'time' => '10:00 AM',
        'status' => 'Registered'
    ],
    [
        'title' => 'Campus Leadership Forum',
        'meta' => 'EVT-1026 · Forum',
        'date' => '30 Oct',
        'time' => '09:00 AM',
        'status' => 'Registered'
    ],
    [
        'title' => 'Data Visualization Workshop',
        'meta' => 'EVT-1018 · Workshop',
        'date' => '10 Oct',
        'time' => '01:00 PM',
        'status' => 'Attended'
    ]
];

// Start capturing the dashboard content.
// main.php will render the shared sidebar and topbar.
ob_start();
?>

<!-- Welcome Section -->
<section class="welcome-section">

    <div>
        <div class="welcome-eyebrow">
            <span class="welcome-dot"></span>
            YOUR CAMPUS, YOUR JOURNEY
        </div>

        <h1>
            <?php
            date_default_timezone_set('Asia/Colombo');
            $hour = (int) date('G');

            if ($hour >= 1 && $hour < 12) {
                $greeting = 'Good Morning';
            } elseif ($hour >= 12 && $hour < 15) {
                $greeting = 'Good Afternoon';
            } else {
                $greeting = 'Good Evening';
            }
            ?>

            <?= htmlspecialchars($greeting, ENT_QUOTES, 'UTF-8') ?>,
            <?= htmlspecialchars($participant['name'], ENT_QUOTES, 'UTF-8') ?>
        </h1>

        <p>
            Here's what's happening in your campus community.
        </p>
    </div>

</section>

<!-- Statistics Section -->
<section class="stats-grid">

    <?php foreach ($stats as $stat): ?>

        <article class="stat-card">

            <div class="stat-top">

                <span class="stat-label">
                    <?= htmlspecialchars(
                        $stat['label'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </span>

                <span class="stat-icon <?= htmlspecialchars(
                    $stat['color'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>">
                    <i class="bi <?= htmlspecialchars(
                        $stat['icon'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"></i>
                </span>

            </div>

            <div class="stat-bottom">
                <strong>
                    <?= htmlspecialchars(
                        $stat['value'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </strong>
            </div>

            <div class="stat-footnote">

                <?php if ($stat['label'] === 'Upcoming events'): ?>

                    <i class="bi bi-calendar2-week"></i>
                    2 upcoming · 1 live · 1 completed

                <?php elseif ($stat['label'] === 'Events attended'): ?>

                    <i class="bi bi-check-circle"></i>
                    Including today's check-in

                <?php elseif ($stat['label'] === 'Certificates earned'): ?>

                    <i class="bi bi-patch-check"></i>
                    Ready to download

                <?php else: ?>

                    <i class="bi bi-arrow-right-circle"></i>
                    Next: AI Ethics · 24 Oct

                <?php endif; ?>

            </div>

        </article>

    <?php endforeach; ?>

</section>

<!-- Attendance and Certificate Section -->
<section class="feature-grid">

    <!-- Attendance Panel -->
    <article class="panel attendance-panel" id="attendance">

        <div class="panel-heading">

            <div>
                <span class="section-eyebrow">HAPPENING TODAY</span>
                <h2>My attendance</h2>
            </div>

            <span class="live-badge">
                <span></span>
                Live event
            </span>

        </div>

        <div class="event-highlight">

            <div class="event-date-block">
                <span>OCT</span>
                <strong>17</strong>
                <span>SATURDAY</span>
            </div>

            <div class="event-highlight-info">

                <span class="event-category">SYMPOSIUM</span>

                <h3>Research &amp; Innovation Symposium</h3>

                <p>
                    <i class="bi bi-geo-alt"></i>
                    University Auditorium
                </p>

                <p>
                    <i class="bi bi-clock"></i>
                    09:00 AM – 04:00 PM
                </p>

                <span class="event-id">
                    EVT-1024 · Computer Science Department
                </span>

            </div>

        </div>

        <div class="checkin-success">

            <div class="checkin-icon">
                <i class="bi bi-check-lg"></i>
            </div>

            <div>
                <strong>Check-in recorded</strong>
                <p>You checked in successfully at 10:24 AM.</p>
            </div>

            <span class="checkin-time">10:24 AM</span>

        </div>

        <div class="quick-actions">

            <span>Quick actions</span>

            <div class="quick-action-buttons">

                <a href="#attendance" class="quick-action primary-action">
                    <i class="bi bi-qr-code-scan"></i>
                    Scan event QR
                </a>

                <a href="#attendance" class="quick-action">
                    <i class="bi bi-clock-history"></i>
                    View attendance
                </a>

            </div>

        </div>

    </article>

    <!-- Certificate Panel -->
    <article class="panel certificate-panel" id="certificates">

        <div class="certificate-top">

            <span class="certificate-icon">
                <i class="bi bi-award"></i>
            </span>

            <span class="certificate-status">
                <span></span>
                AVAILABLE
            </span>

        </div>

        <span class="section-eyebrow">YOUR NEXT MILESTONE</span>

        <h2>A learning journey worth sharing</h2>

        <p class="certificate-description">
            Your Data Visualization certificate is ready.
        </p>

        <div class="certificate-preview">

            <div class="certificate-watermark">
                <i class="bi bi-patch-check"></i>
            </div>

            <span>NORTHBRIDGE UNIVERSITY</span>

            <i class="bi bi-award"></i>

            <strong>Certificate of Achievement</strong>

            <small>DATA VISUALIZATION WORKSHOP</small>

        </div>

        <button
            type="button"
            class="download-certificate"
            onclick="alert('Certificate download will be available after certificate generation is implemented.')"
        >
            <i class="bi bi-download"></i>
            Download certificate
        </button>
        <br/>       
        <p class="certificate-note">
            Certificate eligibility is reviewed after the event.
        </p>

    </article>

</section>

<!-- Registrations and Announcements Section -->
<section class="bottom-grid">

    <!-- Registrations Panel -->
    <article class="panel registrations-panel" id="registrations">

        <div class="panel-heading registrations-heading">

            <div>
                <span class="section-eyebrow">YOUR SCHEDULE</span>
                <h2>My registrations</h2>
            </div>

            <a href="#registrations" class="view-all">
                View all
                <i class="bi bi-arrow-right"></i>
            </a>

        </div>

        <div class="registration-table-wrap">

            <table class="registration-table">

                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Schedule</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($registrations as $registration): ?>

                        <tr>

                            <td>

                                <div class="registration-event">

                                    <span class="registration-event-icon">
                                        <i class="bi bi-calendar-event"></i>
                                    </span>

                                    <div>

                                        <strong>
                                            <?= htmlspecialchars(
                                                $registration['title'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </strong>

                                        <small>
                                            <?= htmlspecialchars(
                                                $registration['meta'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </small>

                                    </div>

                                </div>

                            </td>

                            <td>

                                <div class="schedule-date">
                                    <?= htmlspecialchars(
                                        $registration['date'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </div>

                                <small class="schedule-time">
                                    <?= htmlspecialchars(
                                        $registration['time'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </small>

                            </td>

                            <td>

                                <span class="registration-status <?= $registration['status'] === 'Attended' ? 'attended' : 'registered' ?>">

                                    <span></span>

                                    <?= htmlspecialchars(
                                        $registration['status'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </span>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

        <div class="table-pagination">

            <span>Showing 3 of 4 registrations</span>

            <div>
                <button type="button" disabled>Previous</button>
                <button type="button" class="page-number">1</button>
                <button type="button">Next</button>
            </div>

        </div>

    </article>

    <!-- Announcements Panel -->
    <article class="panel announcements-panel" id="announcements">

        <div class="panel-heading">

            <div>
                <span class="section-eyebrow">CAMPUS UPDATES</span>
                <h2>Announcements</h2>
            </div>

            <span class="announcement-icon">
                <i class="bi bi-megaphone"></i>
            </span>

        </div>

        <div class="announcement-empty">

            <div class="announcement-empty-icon">
                <i class="bi bi-check2-circle"></i>
            </div>

            <strong>You're all caught up</strong>

            <p>New event updates will appear here.</p>

        </div>

        <a
            href="mailto:support@northbridge.edu"
            class="announcement-help"
        >
            <i class="bi bi-question-circle"></i>
            Need help with an event?
            <i class="bi bi-arrow-up-right"></i>
        </a>

    </article>

</section>

<!-- Dashboard Footer -->
<footer class="dashboard-footer">

    <span>
        © 2026 EventFlow · Academic operations, simplified.
    </span>

    <span>
        Made for the Northbridge University community
        <i class="bi bi-heart-fill"></i>
    </span>

</footer>

<?php
// Capture the dashboard content.
$content = ob_get_clean();

// Render the shared layout.
// main.php includes the sidebar and topbar exactly once.
require_once __DIR__ . '/../layout/main.php';
?>