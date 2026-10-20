<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/event_controller.php';

// Allow only authenticated organizers.
if (
    empty($_SESSION['user_id']) ||
    ($_SESSION['role'] ?? '') !== 'Organizer'
) {
    header('Location: /EventFlow/login.php');
    exit;
}

$title = 'Create Event | EventFlow';
$pageCss = 'create_event.css';

// Initialize the controller using the existing database connection.
$eventController = new EventController($conn);

// Form values are preserved if validation fails.
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

// Create a CSRF token for form security.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Process form submission through the controller.
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

            // Store the success message for the next request.
            $_SESSION['event_success'] =
                'Your event has been submitted successfully and is awaiting administrator approval.';

            // Rotate the CSRF token after successful submission.
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            // Prevent duplicate submissions when refreshing.
            header('Location: create-event.php');
            exit;

        } catch (InvalidArgumentException $e) {
            $errors[] = $e->getMessage();

        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }
}

// Read and clear the one-time success message.
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
                Share an event with your university community.
                Complete the details below to submit it for approval.
            </p>
        </div>
    </div>

    <!-- Success message -->
    <?php if ($successMessage !== ''): ?>
        <div class="create-event-alert success" role="status">
            <i class="bi bi-check-circle-fill"></i>

            <?= htmlspecialchars(
                $successMessage,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </div>
    <?php endif; ?>

    <!-- Validation and error messages -->
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

    <!-- Create event form -->
    <form
        method="POST"
        action="create-event.php"
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
                    <p>
                        Enter the main details of your event.
                    </p>
                </div>
            </div>

            <div class="create-event-fields">

                <!-- Event title -->
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

                <!-- Description -->
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
                        Include relevant information to help participants
                        understand the event.
                    </small>
                </div>

                <!-- Venue -->
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
                    <p>
                        Choose when the event will take place.
                    </p>
                </div>
            </div>

            <div class="create-event-fields three-columns">

                <!-- Event date -->
                <div class="create-event-field">
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

                <!-- Start time -->
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

                <!-- End time -->
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

        <!-- Approval information -->
        <div class="create-event-note">
            <i class="bi bi-info-circle"></i>

            <div>
                <strong>What happens next?</strong>

                <p>
                    After submission, your event will be saved with
                    Pending status. An administrator must approve it
                    before it becomes available to participants.
                </p>
            </div>
        </div>

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

<?php

$content = ob_get_clean();

require_once __DIR__ . '/../layout/main.php';

?>