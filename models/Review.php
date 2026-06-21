<?php
require_once __DIR__ . '/../config/db.php';

class Review {
    private $pdo;

    public function __construct() {
        $this->pdo = getDB();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO reviews (booking_id, user_id, rating, comment) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['booking_id'],
            $data['user_id'],
            $data['rating'],
            $data['comment'] ?? null,
        ]);
        return $this->pdo->lastInsertId();
    }

    public function getByFacility($facilityId, $limit = 20) {
        $stmt = $this->pdo->prepare(
            "SELECT r.*, u.full_name, u.avatar,
                    b.booking_date, b.facility_id
             FROM reviews r
             JOIN users u ON r.user_id = u.id
             JOIN bookings b ON r.booking_id = b.id
             WHERE b.facility_id = ?
             ORDER BY r.created_at DESC
             LIMIT ?"
        );
        $stmt->execute([$facilityId, $limit]);
        return $stmt->fetchAll();
    }

    public function getByBooking($bookingId) {
        $stmt = $this->pdo->prepare(
            "SELECT r.*, u.full_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.booking_id = ? LIMIT 1"
        );
        $stmt->execute([$bookingId]);
        return $stmt->fetch();
    }

    public function canReview($bookingId, $userId) {
        // Check booking is completed and belongs to user, and no review exists yet
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM bookings
             WHERE id = ? AND user_id = ? AND status = 'completed'"
        );
        $stmt->execute([$bookingId, $userId]);
        if ($stmt->fetchColumn() == 0) return false;

        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM reviews WHERE booking_id = ?"
        );
        $stmt->execute([$bookingId]);
        return $stmt->fetchColumn() == 0;
    }

    public function getFacilityAverageRating($facilityId) {
        $stmt = $this->pdo->prepare(
            "SELECT AVG(r.rating) as avg_rating, COUNT(r.id) as total
             FROM reviews r
             JOIN bookings b ON r.booking_id = b.id
             WHERE b.facility_id = ?"
        );
        $stmt->execute([$facilityId]);
        return $stmt->fetch();
    }

    public function getAll($limit = 50) {
        $stmt = $this->pdo->prepare(
            "SELECT r.*, u.full_name, f.facility_name
             FROM reviews r
             JOIN users u ON r.user_id = u.id
             JOIN bookings b ON r.booking_id = b.id
             JOIN facilities f ON b.facility_id = f.id
             ORDER BY r.created_at DESC LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
