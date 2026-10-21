<?php

class Event
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Save a new event submitted by an organizer.
     * New events always begin with Pending status.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO events
                (
                    organizer_id,
                    title,
                    description,
                    event_date,
                    start_time,
                    end_time,
                    venue,
                    status
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            'issssss',
            $data['organizer_id'],
            $data['title'],
            $data['description'],
            $data['event_date'],
            $data['start_time'],
            $data['end_time'],
            $data['venue']
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }

    /**
     * Retrieve events created by a specific organizer.
     */
    public function getByOrganizer(int $organizerId): array
    {
        $sql = "SELECT
                    event_id,
                    title,
                    description,
                    event_date,
                    start_time,
                    end_time,
                    venue,
                    status,
                    created_at
                FROM events
                WHERE organizer_id = ?
                ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $organizerId);
        $stmt->execute();

        $result = $stmt->get_result();
        $events = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $events;
    }

    public function getByIdForOrganizer(
        int $eventId,
        int $organizerId
    ): ?array {
        $sql = "SELECT *
                FROM events
                WHERE event_id = ?
                AND organizer_id = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $eventId, $organizerId);
        $stmt->execute();

        $result = $stmt->get_result();
        $event = $result->fetch_assoc();

        $stmt->close();

        return $event ?: null;
    }

    /**
     * Retrieve all events awaiting admin approval.
     */
    public function getPendingForApproval(): array
    {
        $sql = "SELECT
                    e.*,
                    u.fname AS organizer_fname,
                    u.lname AS organizer_lname,
                    u.department AS organizer_department,
                    u.email AS organizer_email,
                    u.status AS organizer_status
                FROM events e
                INNER JOIN users u
                    ON e.organizer_id = u.user_id
                WHERE e.status = 'Pending'
                ORDER BY e.created_at ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $result = $stmt->get_result();
        $events = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $events;
    }

    /**
     * Retrieve an event and its organizer details for admin review.
     */
    public function getForAdminReview(int $eventId): ?array
    {
        $sql = "SELECT
                    e.*,
                    u.fname AS organizer_fname,
                    u.lname AS organizer_lname,
                    u.department AS organizer_department,
                    u.email AS organizer_email,
                    u.status AS organizer_status
                FROM events e
                INNER JOIN users u
                    ON e.organizer_id = u.user_id
                WHERE e.event_id = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $eventId);
        $stmt->execute();

        $result = $stmt->get_result();
        $event = $result->fetch_assoc();

        $stmt->close();

        return $event ?: null;
    }

    /**
     * Approve or reject a pending event.
     *
     * The status check prevents an already-reviewed event
     * from being reviewed again through this method.
     */
    public function saveReview(
        int $eventId,
        int $reviewerId,
        string $decision,
        ?string $reviewNote,
        ?string $rejectionReason
    ): bool {
        if (!in_array($decision, ['Approved', 'Rejected'], true)) {
            throw new InvalidArgumentException(
                'Invalid event review decision.'
            );
        }

        if (
            $decision === 'Rejected'
            && trim((string) $rejectionReason) === ''
        ) {
            throw new InvalidArgumentException(
                'A rejection reason is required.'
            );
        }

        $sql = "UPDATE events
                SET status = ?,
                    review_note = ?,
                    rejection_reason = ?,
                    reviewed_by = ?,
                    reviewed_at = NOW()
                WHERE event_id = ?
                  AND status = 'Pending'";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "sssii",
            $decision,
            $reviewNote,
            $rejectionReason,
            $reviewerId,
            $eventId
        );

        $stmt->execute();

        $updated = $stmt->affected_rows === 1;

        $stmt->close();

        if (!$updated) {
            throw new RuntimeException(
                'The event was not updated. It may not exist or may have already been reviewed.'
            );
        }

        return true;
    }
}
