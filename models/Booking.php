<?php
require_once __DIR__ . '/../config/db.php';

class Booking {
    private $pdo;

    public function __construct() {
        $this->pdo = getDB();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO bookings (user_id, facility_id, booking_date, start_time, end_time, purpose, status)
             VALUES (?, ?, ?, ?, ?, ?, 'pending')"
        );
        $stmt->execute([
            $data['user_id'],
            $data['facility_id'],
            $data['booking_date'],
            $data['start_time'],
            $data['end_time'],
            $data['purpose'],
        ]);
        return $this->pdo->lastInsertId();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare(
            "SELECT b.*, u.full_name, u.email, u.student_code, u.phone,
                    f.facility_name, f.facility_type, f.location, f.capacity
             FROM bookings b
             JOIN users u ON b.user_id = u.id
             JOIN facilities f ON b.facility_id = f.id
             WHERE b.id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByUser($userId, $status = null) {
        $sql = "SELECT b.*, f.facility_name, f.facility_type, f.location
                FROM bookings b
                JOIN facilities f ON b.facility_id = f.id
                WHERE b.user_id = ?";
        $params = [$userId];
        if ($status) {
            $sql .= " AND b.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY b.booking_date DESC, b.start_time DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getAll($status = null, $facilityId = null, $userId = null) {
        $sql = "SELECT b.*, u.full_name, u.email, u.student_code,
                       f.facility_name, f.facility_type, f.location
                FROM bookings b
                JOIN users u ON b.user_id = u.id
                JOIN facilities f ON b.facility_id = f.id
                WHERE 1=1";
        $params = [];
        if ($status) {
            $sql .= " AND b.status = ?";
            $params[] = $status;
        }
        if ($facilityId) {
            $sql .= " AND b.facility_id = ?";
            $params[] = $facilityId;
        }
        if ($userId) {
            $sql .= " AND b.user_id = ?";
            $params[] = $userId;
        }
        $sql .= " ORDER BY b.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function updateStatus($id, $status) {
        $stmt = $this->pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function cancel($id, $userId) {
        $stmt = $this->pdo->prepare(
            "UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status IN ('pending','approved')"
        );
        return $stmt->execute([$id, $userId]);
    }

    public function getPending() {
        return $this->getAll('pending');
    }

    public function getCompleted($userId = null) {
        return $this->getAll('completed', null, $userId);
    }

    public function checkConflict($facilityId, $date, $startTime, $endTime, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM bookings
                WHERE facility_id = ?
                  AND booking_date = ?
                  AND status IN ('approved','pending')
                  AND NOT (end_time <= ? OR start_time >= ?)";
        $params = [$facilityId, $date, $startTime, $endTime];
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    public function getTotalCount($status = null) {
        $sql = "SELECT COUNT(*) FROM bookings WHERE 1=1";
        $params = [];
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getMonthlyStats($year = null) {
        $year = $year ?? date('Y');
        $stmt = $this->pdo->prepare(
            "SELECT MONTH(booking_date) as month, COUNT(*) as total,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                    SUM(CASE WHEN status = 'pending'  THEN 1 ELSE 0 END) as pending
             FROM bookings
             WHERE YEAR(booking_date) = ?
             GROUP BY MONTH(booking_date)
             ORDER BY month ASC"
        );
        $stmt->execute([$year]);
        return $stmt->fetchAll();
    }

    public function getUpcomingByUser($userId, $limit = 5) {
        $stmt = $this->pdo->prepare(
            "SELECT b.*, f.facility_name, f.facility_type, f.location
             FROM bookings b
             JOIN facilities f ON b.facility_id = f.id
             WHERE b.user_id = ? AND b.booking_date >= CURDATE() AND b.status IN ('approved','pending')
             ORDER BY b.booking_date ASC, b.start_time ASC LIMIT ?"
        );
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    public function getApprovalLog($bookingId) {
        $stmt = $this->pdo->prepare(
            "SELECT al.*, u.full_name as admin_name FROM approval_logs al
             JOIN users u ON al.admin_id = u.id
             WHERE al.booking_id = ? ORDER BY al.action_time DESC"
        );
        $stmt->execute([$bookingId]);
        return $stmt->fetchAll();
    }

    public function logApproval($bookingId, $adminId, $action, $note = null) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO approval_logs (booking_id, admin_id, action, note) VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([$bookingId, $adminId, $action, $note]);
    }

    public function autoComplete() {
        // Mark approved bookings in the past as completed
        $stmt = $this->pdo->prepare(
            "UPDATE bookings SET status = 'completed'
             WHERE status = 'approved'
               AND (booking_date < CURDATE() OR (booking_date = CURDATE() AND end_time < CURTIME()))"
        );
        return $stmt->execute();
    }

    public function countByStatus() {
        $stmt = $this->pdo->query(
            "SELECT status, COUNT(*) as cnt FROM bookings GROUP BY status"
        );
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['status']] = $row['cnt'];
        }
        return $result;
    }

    public function getRecentByUser($userId, $limit = 5) {
        $stmt = $this->pdo->prepare(
            "SELECT b.*, f.facility_name, f.facility_type
             FROM bookings b
             JOIN facilities f ON b.facility_id = f.id
             WHERE b.user_id = ?
             ORDER BY b.created_at DESC LIMIT ?"
        );
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
}
