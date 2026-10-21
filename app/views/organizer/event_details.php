
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/event_controller.php';

if (
    empty($_SESSION['user_id']) ||
    ($_SESSION['role'] ?? '') !== 'Organizer'
) {
    header('Location: /EventFlow/login.php');
    exit;
}

$eventId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$organizerId = filter_var(
    $_SESSION['user_id'],
    FILTER_VALIDATE_INT
);

if (
    !$eventId ||
    $eventId <= 0 ||
    !$organizerId ||
    $organizerId <= 0
) {
    http_response_code(404);
    exit('Event not found.');
}

$controller = new EventController($conn);

$event = $controller->getOrganizerEvent(
    $eventId,
    $organizerId
);

if (!$event) {
    http_response_code(404);
    exit('Event not found or you do not have access to it.');
}

$title = 'Event Details | EventFlow';
$pageCss = 'event_details.css';

$escape = static function ($value): string {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
};

$status = $event['status'] ?? 'Pending';
$statusClass = strtolower($status);

$eventTimestamp = strtotime($event['event_date'] ?? '');
$startTimestamp = strtotime($event['start_time'] ?? '');
$endTimestamp = strtotime($event['end_time'] ?? '');

$eventDate = $eventTimestamp !== false
    ? date('d M Y', $eventTimestamp)
    : 'Date unavailable';

$startTime = $startTimestamp !== false
    ? date('g:i A', $startTimestamp)
    : '--';

$endTime = $endTimestamp !== false
    ? date('g:i A', $endTimestamp)
    : '--';

$organizerName = trim(
    ($_SESSION['user_fname'] ?? '') . ' ' .
    ($_SESSION['user_lname'] ?? '')
);

if ($organizerName === '') {
    $organizerName = 'Event Organizer';
}

ob_start();
?>

<div class="event-details-page">

    <!-- Back navigation -->
    <a
        href="/EventFlow/app/views/organizer/events.php"
        class="details-back-link"
    >
        <i class="bi bi-arrow-left"></i>
        Back to Event Management
    </a>

    <!-- Main Event Details Box -->
    <section class="details-hero">

        <div class="details-hero-top">

            <div>
                <span class="details-eyebrow">
                    <i class="bi bi-calendar-event"></i>
                    ORGANIZER WORKSPACE
                </span>

                <h1><?= $escape($event['title'] ?? 'Untitled event') ?></h1>

                <p class="details-subtitle">
                    Event details and management
                </p>
            </div>

            <span class="details-status <?= $escape($statusClass) ?>">
                <?= $escape($status) ?>
            </span>

        </div>

        <!-- Event metadata -->
        <div class="details-meta">

            <span>
                <i class="bi bi-hash"></i>
                EVT-<?= (int) $event['event_id'] ?>
            </span>

            <span>
                <i class="bi bi-calendar3"></i>
                <?= $escape($eventDate) ?>
            </span>

            <span>
                <i class="bi bi-clock"></i>
                <?= $escape($startTime) ?>
                – <?= $escape($endTime) ?>
            </span>

            <span>
                <i class="bi bi-geo-alt"></i>
                <?= $escape($event['venue'] ?? 'Venue not specified') ?>
            </span>

        </div>

        <!-- Organizer information -->
        <div class="details-organizer">
            <i class="bi bi-person-circle"></i>
            Organizer: <?= $escape($organizerName) ?>
        </div>

        <!-- Actions: Edit button at the bottom-right -->
        <div class="details-actions">
            <div class="details-actions-left">
                <a href="/EventFlow/app/views/organizer/events.php"
                class="details-button secondary">
                    <i class="bi bi-arrow-left"></i>
                    All events
                </a>

                <a href="/EventFlow/app/views/organizer/qr-display.php?id=<?= (int) $event['event_id'] ?>"
                class="details-button primary">
                    <i class="bi bi-qr-code"></i>
                    Open QR display
                </a>
            </div>

            <div class="details-actions-right">
                <a href="/EventFlow/app/views/organizer/edit_event.php?id=<?= (int) $event['event_id'] ?>"
                class="details-button edit">
                    <i class="bi bi-pencil-square"></i>
                    Edit event
                </a>
            </div>
        </div>

    </section>

    <!-- About the Event -->
    <section class="details-info-card">

        <div class="details-card-icon">
            <i class="bi bi-card-text"></i>
        </div>

        <div>
            <h2>About this event</h2>

            <p>
                <?php
                $description = trim(
                    (string) ($event['description'] ?? '')
                );

                echo $description !== ''
                    ? nl2br($escape($description))
                    : 'No description has been added for this event.';
                ?>
            </p>
        </div>

    </section>

    <!-- Event Workspace -->
    <section class="details-section">

        <div class="details-section-heading">
            <div>
                <h2>Event workspace</h2>
                <p>
                    Manage the registrations and activities for this event.
                </p>
            </div>
        </div>

        <div class="details-workspace-grid">

            <!-- Participant Registrations -->
            <a href="#registrations" class="details-workspace-card">

                <span class="workspace-icon blue">
                    <i class="bi bi-people"></i>
                </span>

                <span class="workspace-card-text">
                    <strong>Participant registrations</strong>
                    <small>View registered participants</small>
                </span>

                <i class="bi bi-chevron-right workspace-arrow"></i>

            </a>

            <!-- Attendance Summary -->
            <a href="#attendance" class="details-workspace-card">

                <span class="workspace-icon teal">
                    <i class="bi bi-clipboard-check"></i>
                </span>

                <span class="workspace-card-text">
                    <strong>Attendance summary</strong>
                    <small>Review participant check-ins</small>
                </span>

                <i class="bi bi-chevron-right workspace-arrow"></i>

            </a>

            <!-- QR Display -->
            <a
                href="/EventFlow/app/views/organizer/qr-display.php"
                class="details-workspace-card"
            >

                <span class="workspace-icon purple">
                    <i class="bi bi-qr-code-scan"></i>
                </span>

                <span class="workspace-card-text">
                    <strong>QR display</strong>
                    <small>Open the QR attendance page</small>
                </span>

                <i class="bi bi-chevron-right workspace-arrow"></i>

            </a>

            <!-- Certificates -->
            <a href="#certificates" class="details-workspace-card">

                <span class="workspace-icon orange">
                    <i class="bi bi-award"></i>
                </span>

                <span class="workspace-card-text">
                    <strong>Certificates</strong>
                    <small>Manage event certificates</small>
                </span>

                <i class="bi bi-chevron-right workspace-arrow"></i>

            </a>

        </div>

    </section>

    <!-- Notice -->
    <div class="details-notice">
        <i class="bi bi-info-circle"></i>

        <span>
            Registration, attendance and certificate information
            will appear here when those features are connected to
            their respective database records.
        </span>
    </div>

    <!-- Registration Placeholder -->
    <div id="registrations" class="details-placeholder">
        <h2>Participant registrations</h2>
        <p>The registration list will be displayed here.</p>
    </div>

    <!-- Attendance Placeholder -->
    <div id="attendance" class="details-placeholder">
        <h2>Attendance summary</h2>
        <p>The event attendance records will be displayed here.</p>
    </div>

    <!-- Certificates Placeholder -->
    <div id="certificates" class="details-placeholder">
        <h2>Certificates</h2>
        <p>
            Certificate eligibility and generation will be managed here.
        </p>
    </div>

</div>

<?php

$content = ob_get_clean();

require_once __DIR__ . '/../layout/main.php';

?>