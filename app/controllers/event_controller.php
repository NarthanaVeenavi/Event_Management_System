
<?php

require_once __DIR__ . '/../models/event.php';

class EventController
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
        $this->eventModel = new Event($conn);
    }

    /**
     * Validate and create an event.
     *
     * @throws InvalidArgumentException For invalid form data.
     * @throws RuntimeException If saving fails.
     */
    public function createEvent(int $organizerId, array $data): void
    {
        if ($organizerId <= 0) {
            throw new InvalidArgumentException(
                'Please sign in with an organizer account.'
            );
        }

        $requiredFields = [
            'title',
            'description',
            'event_date',
            'start_time',
            'end_time',
            'venue'
        ];

        foreach ($requiredFields as $field) {
            if (
                !isset($data[$field]) ||
                !is_string($data[$field])
            ) {
                throw new InvalidArgumentException(
                    'Please provide valid event information.'
                );
            }
        }

        $title = trim($data['title']);
        $description = trim($data['description']);
        $eventDate = trim($data['event_date']);
        $startTime = trim($data['start_time']);
        $endTime = trim($data['end_time']);
        $venue = trim($data['venue']);

        if ($title === '' || mb_strlen($title) > 200) {
            throw new InvalidArgumentException(
                'Enter an event title of up to 200 characters.'
            );
        }

        if (mb_strlen($description) > 10000) {
            throw new InvalidArgumentException(
                'The description must not exceed 10,000 characters.'
            );
        }

        if ($venue === '' || mb_strlen($venue) > 255) {
            throw new InvalidArgumentException(
                'Enter a venue of up to 255 characters.'
            );
        }

        // Validate the date format and reject dates in the past.
        $dateObject = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $eventDate
        );

        $dateErrors = DateTimeImmutable::getLastErrors();

        if (
            !$dateObject ||
            $dateObject->format('Y-m-d') !== $eventDate ||
            ($dateErrors !== false &&
                ($dateErrors['warning_count'] > 0 ||
                 $dateErrors['error_count'] > 0))
        ) {
            throw new InvalidArgumentException(
                'Select a valid event date.'
            );
        }

        if ($eventDate < date('Y-m-d')) {
            throw new InvalidArgumentException(
                'The event date cannot be in the past.'
            );
        }

        // HTML time inputs normally submit HH:MM.
        $timePattern = '/^(?:[01]\d|2[0-3]):[0-5]\d$/';

        if (
            !preg_match($timePattern, $startTime) ||
            !preg_match($timePattern, $endTime)
        ) {
            throw new InvalidArgumentException(
                'Select valid start and end times.'
            );
        }

        if ($endTime <= $startTime) {
            throw new InvalidArgumentException(
                'The end time must be later than the start time.'
            );
        }

        $eventModel = new Event($this->conn);

        try {
            $created = $eventModel->create([
                'organizer_id' => $organizerId,
                'title' => $title,
                'description' => $description,
                'event_date' => $eventDate,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'venue' => $venue
            ]);
        } catch (mysqli_sql_exception $e) {
            error_log('Event creation database error: ' . $e->getMessage());

            throw new RuntimeException(
                'Unable to save the event. Please try again.'
            );
        }

        if (!$created) {
            error_log('Event creation failed: ' . $this->conn->error);

            throw new RuntimeException(
                'Unable to save the event. Please try again.'
            );
        }
    }

    /**
     * Retrieve events belonging to an organizer.
     */
    public function getOrganizerEvents(int $organizerId): array
    {
        if ($organizerId <= 0) {
            throw new InvalidArgumentException(
                'A valid organizer ID is required.'
            );
        }

        $eventModel = new Event($this->conn);

        return $eventModel->getByOrganizer($organizerId);
    }

    public function getOrganizerEvent(
        int $eventId,
        int $organizerId
    ): ?array {
        if ($eventId <= 0 || $organizerId <= 0) {
            throw new InvalidArgumentException(
                'A valid event and organizer ID are required.'
            );
        }

        return $this->eventModel->getByIdForOrganizer(
            $eventId,
            $organizerId
        );
    }

    /**
     * Retrieve events waiting for admin approval.
     */
    public function getPendingEvents(): array
    {
        return $this->eventModel->getPendingForApproval();
    }

    /**
     * Retrieve event details for the admin review page.
     */
    public function getEventForReview(int $eventId): ?array
    {
        if ($eventId <= 0) {
            throw new InvalidArgumentException(
                'Invalid event ID.'
            );
        }

        return $this->eventModel->getForAdminReview($eventId);
    }

    /**
     * Approve or reject an event.
     */
    public function reviewEvent(
        int $eventId,
        int $adminId,
        array $data
    ): bool {
        if ($eventId <= 0 || $adminId <= 0) {
            throw new InvalidArgumentException(
                'Invalid event or administrator ID.'
            );
        }

        $decision = $data['decision'] ?? '';

        if (!in_array($decision, ['Approved', 'Rejected'], true)) {
            throw new InvalidArgumentException(
                'Please select a valid decision.'
            );
        }

        $reviewNote = trim((string) ($data['review_note'] ?? ''));
        $rejectionReason = trim(
            (string) ($data['rejection_reason'] ?? '')
        );

        if ($decision === 'Rejected' && $rejectionReason === '') {
            throw new InvalidArgumentException(
                'Please provide a reason for rejecting the event.'
            );
        }

        return $this->eventModel->saveReview(
            $eventId,
            $adminId,
            $decision,
            $reviewNote !== '' ? $reviewNote : null,
            $decision === 'Rejected' ? $rejectionReason : null
        );
    }
}
