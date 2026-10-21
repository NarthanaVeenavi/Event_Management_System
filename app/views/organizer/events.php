
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/event_controller.php';

// Allow only logged-in organizers.
if (
    empty($_SESSION['user_id']) ||
    ($_SESSION['role'] ?? '') !== 'Organizer'
) {
    header('Location: /EventFlow/login.php');
    exit;
}

$title = 'Event Management | EventFlow';
$pageCss = 'events.css';

$organizerId = filter_var(
    $_SESSION['user_id'],
    FILTER_VALIDATE_INT
);

$events = [];
$loadError = '';

$escape = static function ($value): string {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
};

if ($organizerId === false || $organizerId <= 0) {
    header('Location: /EventFlow/login.php');
    exit;
}

try {
    $eventController = new EventController($conn);

    // Retrieve only events belonging to the logged-in organizer.
    $events = $eventController->getOrganizerEvents($organizerId);

} catch (Throwable $e) {
    error_log('Organizer event management error: ' . $e->getMessage());

    $loadError = 'Unable to load your events right now. Please try again later.';
}

// Event status styles.
$statusClasses = [
    'Pending'   => 'pending',
    'Approved'  => 'approved',
    'Rejected'  => 'rejected',
    'Cancelled' => 'cancelled'
];

// Calculate event status counts.
$totalEvents = count($events);
$approvedEvents = 0;
$rejectedEvents = 0;

foreach ($events as $event) {
    $status = $event['status'] ?? 'Pending';

    if ($status === 'Approved') {
        $approvedEvents++;
    } elseif ($status === 'Rejected') {
        $rejectedEvents++;
    }
}

ob_start();
?>

<div class="events-page">

    <!-- Page heading -->
    <div class="events-heading">

        <div class="events-heading-text">

            <span class="events-eyebrow">
                <i class="bi bi-calendar2-check"></i>
                ORGANIZER WORKSPACE
            </span>

            <h1>Event Management</h1>

            <p>
                View and keep track of all the events you have created.
            </p>

        </div>

        <a
            href="/EventFlow/app/views/organizer/create_event.php"
            class="events-create-button"
        >
            <i class="bi bi-plus-lg"></i>
            Create event
        </a>

    </div>

    <!-- Summary Cards -->
    <div class="events-summary-grid">

        <!-- Total Events -->
        <div class="events-summary">

            <div class="events-summary-icon total-icon">
                <i class="bi bi-calendar-event"></i>
            </div>

            <div class="events-summary-content">

                <span class="events-summary-label">
                    Total Events
                </span>

                <strong class="events-summary-value">
                    <?= $totalEvents ?>
                </strong>

                <span class="events-summary-note">
                    Events created by you
                </span>

            </div>

        </div>

        <!-- Approved Events -->
        <div class="events-summary">

            <div class="events-summary-icon approved-icon">
                <i class="bi bi-calendar2-check"></i>
            </div>

            <div class="events-summary-content">

                <span class="events-summary-label">
                    Approved Events
                </span>

                <strong class="events-summary-value">
                    <?= $approvedEvents ?>
                </strong>

                <span class="events-summary-note">
                    Approved by administrator
                </span>

            </div>

        </div>

        <!-- Rejected Events -->
        <div class="events-summary">

            <div class="events-summary-icon rejected-icon">
                <i class="bi bi-calendar2-x"></i>
            </div>

            <div class="events-summary-content">

                <span class="events-summary-label">
                    Rejected Events
                </span>

                <strong class="events-summary-value">
                    <?= $rejectedEvents ?>
                </strong>

                <span class="events-summary-note">
                    Events not approved
                </span>

            </div>

        </div>

    </div>

    <!-- Error message -->
    <?php if ($loadError !== ''): ?>

        <div class="events-alert error" role="alert">
            <i class="bi bi-exclamation-circle-fill"></i>
            <?= $escape($loadError) ?>
        </div>

    <?php else: ?>

        <!-- Events section -->
        <section class="events-section">

            <div class="events-section-heading">

                <div>
                    <h2>Your events</h2>

                    <p>
                        Review your event details and approval status.
                    </p>
                </div>

                <span class="events-count">
                    <?= $totalEvents ?>
                    <?= $totalEvents === 1 ? 'event' : 'events' ?>
                </span>

            </div>

            <?php if (empty($events)): ?>

                <!-- Empty state -->
                <div class="events-empty">

                    <div class="events-empty-icon">
                        <i class="bi bi-calendar2-plus"></i>
                    </div>

                    <h3>No events yet</h3>

                    <p>
                        You haven't created any events. Create your first
                        event to get started.
                    </p>

                    <a
                        href="/EventFlow/app/views/organizer/create_event.php"
                        class="events-empty-button"
                    >
                        <i class="bi bi-plus-lg"></i>
                        Create your first event
                    </a>

                </div>

            <?php else: ?>

                <!-- Event cards -->
                <div class="events-list">

                    <?php foreach ($events as $event): ?>

                        <?php
                        $status = $event['status'] ?? 'Pending';

                        $statusClass = $statusClasses[$status]
                            ?? 'pending';

                        $eventTimestamp = strtotime(
                            $event['event_date'] ?? ''
                        );

                        $startTimestamp = strtotime(
                            $event['start_time'] ?? ''
                        );

                        $endTimestamp = strtotime(
                            $event['end_time'] ?? ''
                        );

                        $eventDate = $eventTimestamp !== false
                            ? date('d M Y', $eventTimestamp)
                            : 'Date unavailable';

                        $startTime = $startTimestamp !== false
                            ? date('g:i A', $startTimestamp)
                            : '--';

                        $endTime = $endTimestamp !== false
                            ? date('g:i A', $endTimestamp)
                            : '--';

                        $eventId = (int) ($event['event_id'] ?? 0);

                        $description = trim(
                            (string) ($event['description'] ?? '')
                        );

                        $venue = $event['venue'] ?? 'Venue not specified';
                        ?>

                        <a
                            href="/EventFlow/app/views/organizer/event_details.php?id=<?= $eventId ?>"
                            class="event-item-link"
                        >

                            <article class="event-item">

                                <div class="event-item-main">

                                    <div class="event-date-icon">
                                        <i class="bi bi-calendar-event"></i>
                                    </div>

                                    <div class="event-item-details">

                                        <div class="event-item-title-row">

                                            <h3>
                                                <?= $escape($event['title'] ?? 'Untitled event') ?>
                                            </h3>

                                            <span class="event-status <?= $escape($statusClass) ?>">

                                                <span class="event-status-dot"></span>

                                                <?= $escape($status) ?>

                                            </span>

                                        </div>

                                        <p class="event-description">
                                            <?= $description !== ''
                                                ? $escape($description)
                                                : 'No description provided.' ?>
                                        </p>

                                        <div class="event-metadata">

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
                                                <?= $escape($venue) ?>
                                            </span>

                                        </div>

                                    </div>

                                </div>

                                <div class="event-item-footer">

                                    <span class="event-id">
                                        Event #<?= $eventId ?>
                                    </span>

                                    <span class="event-created">

                                        <i class="bi bi-shield-check"></i>

                                        <?= $status === 'Approved'
                                            ? 'Approved by administrator'
                                            : ($status === 'Rejected'
                                                ? 'Not approved'
                                                : ($status === 'Cancelled'
                                                    ? 'Event cancelled'
                                                    : 'Awaiting approval')) ?>

                                    </span>

                                </div>

                            </article>

                        </a>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </section>

    <?php endif; ?>

</div>

<?php

$content = ob_get_clean();

require_once __DIR__ . '/../layout/main.php';

?>