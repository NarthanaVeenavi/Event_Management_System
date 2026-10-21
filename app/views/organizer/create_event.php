
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

$title = 'Create Event | EventFlow';
$pageCss = 'create_event.css';

$eventController = new EventController($conn);

$old = [
    'title' => '',
    'description' => '',
    'event_date' => '',
    'start_time' => '',
    'end_time' => '',
    'venue' => ''
];

$errors = [];
$successMessage = '';

// Generate a CSRF token.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Process the form.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    foreach (array_keys($old) as $field) {
        $value = $_POST[$field] ?? '';
        $old[$field] = is_string($value) ? trim($value) : '';
    }

    $submittedToken = $_POST['csrf_token'] ?? '';

    if (
        !is_string($submittedToken) ||
        !hash_equals($_SESSION['csrf_token'], $submittedToken)
    ) {
        $errors[] = 'Your form session has expired. Please refresh the page and try again.';
    } else {
        try {
            $eventController->createEvent(
                (int) $_SESSION['user_id'],
                $old
            );

            $_SESSION['event_success'] =
                'Your event has been submitted successfully and is awaiting administrator approval.';

            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            header('Location: create_event.php');
            exit;

        } catch (InvalidArgumentException $e) {
            $errors[] = $e->getMessage();

        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }
}

// Display one-time success message.
$successMessage = $_SESSION['event_success'] ?? '';
unset($_SESSION['event_success']);

ob_start();
?>

<div class="create-event-page">

    <!-- Page heading -->
    <div class="create-event-heading">
        <div>
            <a href="organizer_dashboard.php" class="create-event-back">
                <i class="bi bi-arrow-left"></i>
                Back to dashboard
            </a>

            <h1>Create an event</h1>

            <p>
                Organize your next university event by providing
                the details below.
            </p>
        </div>
    </div>

    <!-- Success message -->
    <?php if ($successMessage !== ''): ?>
        <div class="create-event-alert success" role="status">
            <i class="bi bi-check-circle-fill"></i>

            <span>
                <?= htmlspecialchars(
                    $successMessage,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </span>
        </div>
    <?php endif; ?>

    <!-- Validation errors -->
    <?php if (!empty($errors)): ?>
        <div class="create-event-alert error" role="alert">
            <div>
                <i class="bi bi-exclamation-circle-fill"></i>
                <strong>Please check the following:</strong>
            </div>

            <ul>
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


    <!-- Two-column layout -->
    <div class="create-event-layout">

        <!-- LEFT SIDE: Event form -->
        <div class="create-event-main">

            <form
                method="POST"
                action="create_event.php"
                class="create-event-form"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars(
                        $_SESSION['csrf_token'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >

                <!-- Event information -->
                <section class="create-event-card">

                    <div class="create-event-card-heading">
                        <span class="create-event-icon">
                            <i class="bi bi-calendar-event"></i>
                        </span>

                        <div>
                            <h2>Event information</h2>
                            <p>Enter the main details of your event.</p>
                        </div>
                    </div>

                    <div class="create-event-fields">

                        <div class="create-event-field full-width">
                            <label for="title">
                                Event title <span>*</span>
                            </label>

                            <input
                                type="text"
                                id="title"
                                name="title"
                                maxlength="200"
                                placeholder="e.g. Annual Technology Conference"
                                value="<?= htmlspecialchars(
                                    $old['title'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                required
                            >
                        </div>

                        <div class="create-event-field full-width">
                            <label for="description">
                                Event description
                            </label>

                            <textarea
                                id="description"
                                name="description"
                                rows="5"
                                maxlength="10000"
                                placeholder="Describe the event, its purpose and what participants can expect..."
                            ><?= htmlspecialchars(
                                $old['description'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?></textarea>

                            <small>
                                Provide a clear description to help
                                participants understand your event.
                            </small>
                        </div>

                        <div class="create-event-field full-width">
                            <label for="venue">
                                Venue <span>*</span>
                            </label>

                            <div class="create-event-input-icon">
                                <i class="bi bi-geo-alt"></i>

                                <input
                                    type="text"
                                    id="venue"
                                    name="venue"
                                    maxlength="255"
                                    placeholder="e.g. Main Auditorium, Northbridge University"
                                    value="<?= htmlspecialchars(
                                        $old['venue'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    required
                                >
                            </div>
                        </div>

                    </div>
                </section>


                <!-- Date and time -->
                <section class="create-event-card">

                    <div class="create-event-card-heading">
                        <span class="create-event-icon teal">
                            <i class="bi bi-clock"></i>
                        </span>

                        <div>
                            <h2>Date and time</h2>
                            <p>Set the schedule for your event.</p>
                        </div>
                    </div>

                    <div class="create-event-fields">

                        <div class="create-event-field full-width">
                            <label for="event_date">
                                Event date <span>*</span>
                            </label>

                            <input
                                type="date"
                                id="event_date"
                                name="event_date"
                                min="<?= date('Y-m-d') ?>"
                                value="<?= htmlspecialchars(
                                    $old['event_date'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                required
                            >
                        </div>

                        <div class="create-event-field">
                            <label for="start_time">
                                Start time <span>*</span>
                            </label>

                            <input
                                type="time"
                                id="start_time"
                                name="start_time"
                                value="<?= htmlspecialchars(
                                    $old['start_time'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                required
                            >
                        </div>

                        <div class="create-event-field">
                            <label for="end_time">
                                End time <span>*</span>
                            </label>

                            <input
                                type="time"
                                id="end_time"
                                name="end_time"
                                value="<?= htmlspecialchars(
                                    $old['end_time'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                required
                            >
                        </div>

                    </div>
                </section>


                <!-- Form actions -->
                <div class="create-event-actions">

                    <a
                        href="organizer_dashboard.php"
                        class="create-event-cancel"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="create-event-submit"
                    >
                        <i class="bi bi-send"></i>
                        Submit for approval
                    </button>

                </div>

            </form>
        </div>


        <!-- RIGHT SIDE: Guidelines panel -->
        <aside class="create-event-sidebar">

            <section class="guidelines-card">

                <div class="guidelines-heading">
                    <span class="guidelines-heading-icon">
                        <i class="bi bi-info-circle"></i>
                    </span>

                    <div>
                        <h2>Before you submit</h2>
                        <p>Keep these important points in mind.</p>
                    </div>
                </div>


                <!-- Checklist -->
                <div class="guidelines-section">

                    <h3>
                        <i class="bi bi-check2-circle"></i>
                        Submission checklist
                    </h3>

                    <ul class="guidelines-checklist">
                        <li>Confirm the venue is available.</li>
                        <li>Check event and registration dates.</li>
                        <li>Provide a clear event description.</li>
                        <li>Review participant capacity.</li>
                    </ul>

                </div>


                <!-- Attendance and certificates -->
                <div class="guidelines-section">

                    <h3>
                        <i class="bi bi-qr-code-scan"></i>
                        Attendance &amp; certificates
                    </h3>

                    <div class="guidelines-detail">
                        <span class="guidelines-label">
                            Attendance method
                        </span>

                        <p>
                            <i class="bi bi-qr-code"></i>
                            Session QR validation
                        </p>
                    </div>

                    <div class="guidelines-detail">
                        <span class="guidelines-label">
                            Certificate rule
                        </span>

                        <p>
                            <i class="bi bi-award"></i>
                            Recorded attendance + completed event
                        </p>
                    </div>

                    <div class="guidelines-help">
                        <i class="bi bi-lightbulb"></i>

                        <p>
                            Generate certificates from Event Management
                            after the event ends.
                        </p>
                    </div>

                </div>


                <!-- Approval information -->
                <div class="guidelines-approval">

                    <div class="guidelines-approval-heading">
                        <i class="bi bi-shield-check"></i>
                        <h3>Approval required</h3>
                    </div>

                    <p>
                        Draft and pending events are private.
                        Registration opens only after admin approval
                        and the configured opening date.
                    </p>

                </div>

            </section>


            <!-- Helpful note -->
            <div class="guidelines-footer">
                <i class="bi bi-lightbulb"></i>

                <p>
                    <strong>Tip</strong>
                    <br>
                    Review your event details carefully before
                    submitting them for approval.
                </p>
            </div>

        </aside>

    </div>

</div>

<?php

$content = ob_get_clean();

require_once __DIR__ . '/../layout/main.php';

?>