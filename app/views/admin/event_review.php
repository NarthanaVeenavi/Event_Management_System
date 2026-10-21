
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

$eventId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$eventId || $eventId < 1) {
    http_response_code(400);
    exit('Invalid event ID.');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$controller = new EventController($conn);
$errorMessage = '';

$escape = static function ($value): string {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? '';

    if (
        !is_string($submittedToken)
        || !hash_equals($_SESSION['csrf_token'], $submittedToken)
    ) {
        $errorMessage = 'Your session token is invalid. Please refresh the page and try again.';
    } else {
        try {
            $controller->reviewEvent(
                $eventId,
                (int) $_SESSION['user_id'],
                [
                    'decision' => $_POST['decision'] ?? '',
                    'review_note' => $_POST['review_note'] ?? '',
                    'rejection_reason' => $_POST['rejection_reason'] ?? ''
                ]
            );

            header(
                'Location: /EventFlow/app/views/admin/event-review.php?id='
                . $eventId . '&success=1'
            );
            exit;
        } catch (InvalidArgumentException | RuntimeException $exception) {
            $errorMessage = $exception->getMessage();
        } catch (Throwable $exception) {
            error_log('Event review failed: ' . $exception->getMessage());
            $errorMessage = 'Unable to save the decision. Please try again.';
        }
    }
}

$event = $controller->getEventForReview((int) $eventId);

if (!$event) {
    http_response_code(404);
    exit('Event not found.');
}

$title = 'Review Event | EventFlow';
$pageCss = 'event_review.css';

if ($adminName === '') {
    $adminName = 'System Administrator';
}

$eventStatus = $event['status'] ?? 'Pending';
$isPending = $eventStatus === 'Pending';

$organizerName = trim(
    ($event['organizer_fname'] ?? '') . ' ' .
    ($event['organizer_lname'] ?? '')
);

$eventDate = !empty($event['event_date'])
    ? date('d M Y', strtotime($event['event_date']))
    : 'Not specified';

$startTime = !empty($event['start_time'])
    ? date('H:i', strtotime($event['start_time']))
    : '—';

$endTime = !empty($event['end_time'])
    ? date('H:i', strtotime($event['end_time']))
    : '—';

$submittedAt = !empty($event['created_at'])
    ? date('d M Y, H:i', strtotime($event['created_at']))
    : '—';

$registrationStart = !empty($event['registration_start'])
    ? date('d M Y, H:i', strtotime($event['registration_start']))
    : 'Not specified';

$registrationEnd = !empty($event['registration_end'])
    ? date('d M Y, H:i', strtotime($event['registration_end']))
    : 'Not specified';

$organizerActive = ($event['organizer_status'] ?? '') === 'Active';

$scheduleComplete =
    !empty($event['event_date'])
    && !empty($event['start_time'])
    && !empty($event['end_time'])
    && !empty($event['venue']);

$registrationDatesValid =
    !empty($event['registration_start'])
    && !empty($event['registration_end'])
    && strtotime($event['registration_start'])
        <= strtotime($event['registration_end']);

ob_start();
?>

<div class="event-review-page">

    <div class="review-breadcrumb">
        <a href="/EventFlow/app/views/admin/event_approvals.php">
            <i class="bi bi-arrow-left"></i>
            Back to approval queue
        </a>
    </div>

    <div class="review-page-heading">
        <div>
            <h1>Review event submission</h1>
            <p>Verify the event information before making it available to participants.</p>
        </div>

        <span class="review-status <?= strtolower($escape($eventStatus)) ?>">
            <span class="review-status-dot"></span>
            <?= $escape($eventStatus === 'Pending' ? 'Pending approval' : $eventStatus) ?>
        </span>
    </div>

    <?php if (isset($_GET['success']) && $_GET['success'] === '1'): ?>
        <div class="review-alert success">
            <i class="bi bi-check-circle-fill"></i>
            Your decision has been saved successfully.
        </div>
    <?php endif; ?>

    <?php if ($errorMessage !== ''): ?>
        <div class="review-alert error">
            <i class="bi bi-exclamation-circle-fill"></i>
            <?= $escape($errorMessage) ?>
        </div>
    <?php endif; ?>

    <div class="review-layout">

        <main class="review-main">

            <section class="review-card event-overview-card">

                <div class="event-overview-top">
                    <div class="event-overview-icon">
                        <i class="bi bi-calendar-event"></i>
                    </div>

                    <div class="event-overview-title">
                        <span class="event-reference">
                            EVT-<?= (int) $event['event_id'] ?>
                            · Submitted <?= $escape($submittedAt) ?>
                        </span>

                        <h2><?= $escape($event['title']) ?></h2>

                        <p>
                            Organizer:
                            <strong><?= $escape($organizerName ?: 'Unknown organizer') ?></strong>
                        </p>
                    </div>
                </div>

                <div class="event-availability-notice">
                    <i class="bi bi-lock-fill"></i>
                    <div>
                        <strong>
                            <?= $isPending ? 'Not publicly available' : 'Current publication status' ?>
                        </strong>
                        <p>
                            <?= $isPending
                                ? 'This event cannot accept registrations until approved.'
                                : 'The event currently has a ' . $escape(strtolower($eventStatus)) . ' status.' ?>
                        </p>
                    </div>
                </div>

                <div class="review-event-facts">

                    <div class="review-fact">
                        <span>Organizer</span>
                        <strong><?= $escape($organizerName ?: '—') ?></strong>
                    </div>

                    <div class="review-fact">
                        <span>Department</span>
                        <strong><?= $escape($event['organizer_department'] ?? '—') ?></strong>
                    </div>

                    <div class="review-fact">
                        <span>Event ID</span>
                        <strong>EVT-<?= (int) $event['event_id'] ?></strong>
                    </div>

                </div>

                <div class="review-description">
                    <h3>About this event</h3>
                    <p><?= nl2br($escape($event['description'] ?: 'No event description provided.')) ?></p>
                </div>

                <div class="review-event-facts">

                    <div class="review-fact">
                        <span><i class="bi bi-calendar3"></i> Date &amp; time</span>
                        <strong><?= $escape($eventDate) ?></strong>
                        <small><?= $escape($startTime) ?>–<?= $escape($endTime) ?></small>
                    </div>

                    <div class="review-fact">
                        <span><i class="bi bi-geo-alt"></i> Venue</span>
                        <strong><?= $escape($event['venue']) ?></strong>
                    </div>

                    <div class="review-fact">
                        <span><i class="bi bi-clock"></i> Submitted</span>
                        <strong><?= $escape($submittedAt) ?></strong>
                    </div>

                </div>

                <div class="review-registration-dates">
                    <div>
                        <span>Registration opens</span>
                        <strong><?= $escape($registrationStart) ?></strong>
                    </div>
                    <div>
                        <span>Registration closes</span>
                        <strong><?= $escape($registrationEnd) ?></strong>
                    </div>
                </div>

            </section>

            <section class="review-card">
                <div class="review-section-heading">
                    <div class="review-section-icon">
                        <i class="bi bi-clipboard2-check"></i>
                    </div>
                    <div>
                        <h2>Review checklist</h2>
                        <p>Verify the submission before making your decision.</p>
                    </div>
                </div>

                <div class="review-checklist">

                    <div class="review-check-item">
                        <i class="bi <?= $organizerActive ? 'bi-check-circle-fill check-good' : 'bi-x-circle-fill check-bad' ?>"></i>
                        <div>
                            <strong>Organizer account is active</strong>
                            <small><?= $organizerActive ? 'Organizer account is active.' : 'Verify the organizer account before approval.' ?></small>
                        </div>
                    </div>

                    <div class="review-check-item">
                        <i class="bi <?= $scheduleComplete ? 'bi-check-circle-fill check-good' : 'bi-x-circle-fill check-bad' ?>"></i>
                        <div>
                            <strong>Venue and schedule are complete</strong>
                            <small><?= $scheduleComplete ? 'Required venue and event times are provided.' : 'One or more schedule details are missing.' ?></small>
                        </div>
                    </div>

                    <div class="review-check-item">
                        <i class="bi <?= $registrationDatesValid ? 'bi-check-circle-fill check-good' : 'bi-exclamation-circle-fill check-warning' ?>"></i>
                        <div>
                            <strong>Registration dates are valid</strong>
                            <small><?= $registrationDatesValid ? 'Registration opening and closing dates are present and ordered.' : 'Registration dates are missing or need verification.' ?></small>
                        </div>
                    </div>

                </div>
            </section>

            <section class="review-card">
                <div class="review-section-heading">
                    <div class="review-section-icon">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <h2>Review history</h2>
                        <p>Recorded decision and submission details.</p>
                    </div>
                </div>

                <div class="review-history-item">
                    <span class="review-history-marker"></span>
                    <div>
                        <strong>Submitted for review by <?= $escape($organizerName ?: 'the organizer') ?></strong>
                        <p><?= $escape($submittedAt) ?> · Event submitted</p>
                    </div>
                </div>

                <?php if (!empty($event['reviewed_at'])): ?>
                    <div class="review-history-item">
                        <span class="review-history-marker decision-marker"></span>
                        <div>
                            <strong>
                                <?= $escape($eventStatus) ?> by administrator
                                <?= !empty($event['reviewed_by']) ? '#' . (int) $event['reviewed_by'] : '' ?>
                            </strong>
                            <p><?= $escape(date('d M Y, H:i', strtotime($event['reviewed_at']))) ?></p>

                            <?php if (!empty($event['review_note'])): ?>
                                <p>Review note: <?= $escape($event['review_note']) ?></p>
                            <?php endif; ?>

                            <?php if (!empty($event['rejection_reason'])): ?>
                                <p>Rejection reason: <?= $escape($event['rejection_reason']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="review-history-item">
                        <span class="review-history-marker pending-marker"></span>
                        <div>
                            <strong>Awaiting admin decision</strong>
                            <p>No prior decisions recorded.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </section>

        </main>

        <aside class="review-sidebar">

            <section class="review-card decision-card">

                <div class="decision-card-heading">
                    <div class="review-section-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <h2>Your decision</h2>
                        <p>Event approval</p>
                    </div>
                </div>

                <?php if ($isPending): ?>

                    <form method="POST"
                          action="/EventFlow/app/views/admin/event-review.php?id=<?= (int) $event['event_id'] ?>"
                          class="review-decision-form">

                        <input type="hidden"
                               name="csrf_token"
                               value="<?= $escape($_SESSION['csrf_token']) ?>">

                        <label for="review_note">Review note <span>(optional)</span></label>
                        <textarea id="review_note"
                                  name="review_note"
                                  rows="4"
                                  maxlength="5000"
                                  placeholder="Add a note for the organizer…"><?= $escape($_POST['review_note'] ?? '') ?></textarea>

                        <div class="review-field-divider"></div>

                        <label for="rejection_reason">Rejection reason</label>
                        <textarea id="rejection_reason"
                                  name="rejection_reason"
                                  rows="4"
                                  maxlength="5000"
                                  placeholder="Explain what needs to be corrected…"><?= $escape($_POST['rejection_reason'] ?? '') ?></textarea>
                        <small class="review-field-help">
                            Required only when rejecting. Sent to the organizer.
                        </small>

                        <button type="submit"
                                name="decision"
                                value="Approved"
                                class="decision-button approve">
                            <i class="bi bi-check-circle"></i>
                            Approve &amp; publish
                        </button>

                        <button type="submit"
                                name="decision"
                                value="Rejected"
                                class="decision-button reject">
                            <i class="bi bi-x-circle"></i>
                            Reject submission
                        </button>

                    </form>

                <?php else: ?>

                    <div class="decision-completed">
                        <i class="bi bi-check-circle-fill"></i>
                        <h3>Decision recorded</h3>
                        <p>This event has already been <?= $escape(strtolower($eventStatus)) ?>.</p>
                    </div>

                <?php endif; ?>

            </section>

            <section class="review-card proposal-card">
                <div class="review-section-heading">
                    <div class="review-section-icon">
                        <i class="bi bi-file-earmark-pdf"></i>
                    </div>
                    <div>
                        <h2>Event proposal</h2>
                        <p>Supporting documentation</p>
                    </div>
                </div>

                <div class="proposal-unavailable">
                    <i class="bi bi-info-circle"></i>
                    <p>
                        Proposal documents will appear here once a proposal
                        file path is stored in the database.
                    </p>
                </div>
            </section>

            <div class="review-sidebar-note">
                <i class="bi bi-info-circle"></i>
                <p>
                    Approval publishes the event. Registration availability
                    should follow the configured registration dates.
                    Rejection records the reason for the organizer.
                </p>
            </div>

        </aside>

    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/main.php';
?>
