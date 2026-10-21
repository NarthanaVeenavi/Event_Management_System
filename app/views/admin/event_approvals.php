
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (
    !isset($_SESSION['user_id'], $_SESSION['role'])
    || $_SESSION['role'] !== 'Admin'
) {
    header('Location: /EventFlow/login.php');
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/event_controller.php';

$controller = new EventController($conn);
$events = $controller->getPendingEvents();

$title = 'Event Approvals | EventFlow';
$pageCss = 'event_approvals.css';

$escape = static function ($value): string {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
};

$adminName = trim(
    ($_SESSION['fname'] ?? 'System') . ' ' .
    ($_SESSION['lname'] ?? 'Administrator')
);

if ($adminName === '') {
    $adminName = 'System Administrator';
}

ob_start();
?>

<div class="approval-page">

    <div class="approval-heading">
        <div>
            <h1>Event approvals</h1>
            <p>Review event submissions before they become available to participants.</p>
        </div>

        <a href="/EventFlow/app/views/admin/admin_dashboard.php"
           class="approval-back-link">
            <i class="bi bi-arrow-left"></i>
            Back to dashboard
        </a>
    </div>

    <div class="approval-stats">
        <div class="approval-stat-card">
            <div class="approval-stat-icon pending">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div>
                <span>Awaiting review</span>
                <strong><?= count($events) ?></strong>
                <small>Events pending approval</small>
            </div>
        </div>

        <div class="approval-stat-card">
            <div class="approval-stat-icon review">
                <i class="bi bi-shield-check"></i>
            </div>
            <div>
                <span>Review process</span>
                <strong>Admin</strong>
                <small>Verify before publishing</small>
            </div>
        </div>
    </div>

    <section class="approval-panel">

        <div class="approval-panel-heading">
            <div>
                <h2>Pending submissions</h2>
                <p>Review each event and record your decision.</p>
            </div>

            <span class="approval-count">
                <?= count($events) ?> pending
            </span>
        </div>

        <?php if (empty($events)): ?>

            <div class="approval-empty">
                <div class="approval-empty-icon">
                    <i class="bi bi-check2-circle"></i>
                </div>
                <h3>You're all caught up</h3>
                <p>There are no events waiting for approval right now.</p>
            </div>

        <?php else: ?>

            <div class="approval-table-wrap">
                <table class="approval-table">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Organizer</th>
                            <th>Event date</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($events as $event): ?>
                            <?php
                            $organizerName = trim(
                                ($event['organizer_fname'] ?? '') . ' ' .
                                ($event['organizer_lname'] ?? '')
                            );

                            $eventDate = !empty($event['event_date'])
                                ? date('d M Y', strtotime($event['event_date']))
                                : 'Not specified';

                            $submittedAt = !empty($event['created_at'])
                                ? date('d M Y, H:i', strtotime($event['created_at']))
                                : '—';
                            ?>

                            <tr>
                                <td>
                                    <div class="approval-event-cell">
                                        <span class="approval-event-id">
                                            EVT-<?= (int) $event['event_id'] ?>
                                        </span>
                                        <strong><?= $escape($event['title']) ?></strong>
                                        <small><?= $escape($event['venue']) ?></small>
                                    </div>
                                </td>

                                <td>
                                    <div class="approval-organizer">
                                        <strong>
                                            <?= $escape($organizerName ?: 'Unknown organizer') ?>
                                        </strong>
                                        <small>
                                            <?= $escape($event['organizer_department'] ?? '—') ?>
                                        </small>
                                    </div>
                                </td>

                                <td><?= $escape($eventDate) ?></td>

                                <td><?= $escape($submittedAt) ?></td>

                                <td>
                                    <span class="approval-status pending">
                                        <span></span>
                                        Pending
                                    </span>
                                </td>

                                <td>
                                    <a class="approval-review-button"
                                       href="/EventFlow/app/views/admin/event_review.php?id=<?= (int) $event['event_id'] ?>">
                                        Review
                                        <i class="bi bi-arrow-up-right"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>

    </section>

    <div class="approval-info">
        <i class="bi bi-info-circle"></i>
        <p>
            Approved events become available according to their registration
            dates. Rejected events remain unavailable until the organizer
            makes the necessary corrections and resubmits them.
        </p>
    </div>

</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/main.php';
?>