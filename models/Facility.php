<?php
require_once __DIR__ . '/../config/db.php';

class Facility {
    private $pdo;

    public function __construct() {
        $this->pdo = getDB();
    }

    public function getAll($type = null, $status = null) {
        $sql = "SELECT f.*, 
                    (SELECT COUNT(*) FROM bookings b WHERE b.facility_id = f.id AND b.status IN ('approved','pending')) AS active_bookings,
                    COALESCE((SELECT AVG(r.rating) FROM reviews r JOIN bookings b ON r.booking_id = b.id WHERE b.facility_id = f.id), 0) AS avg_rating,
                    (SELECT COUNT(*) FROM reviews r JOIN bookings b ON r.booking_id = b.id WHERE b.facility_id = f.id) AS review_count
                FROM facilities f WHERE 1=1";
        $params = [];
        if ($type) {
            $sql .= " AND f.facility_type = ?";
            $params[] = $type;
        }
        if ($status) {
            $sql .= " AND f.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY f.facility_name ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare(
            "SELECT f.*,
                COALESCE((SELECT AVG(r.rating) FROM reviews r JOIN bookings b ON r.booking_id = b.id WHERE b.facility_id = f.id), 0) AS avg_rating,
                (SELECT COUNT(*) FROM reviews r JOIN bookings b ON r.booking_id = b.id WHERE b.facility_id = f.id) AS review_count
             FROM facilities f WHERE f.id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO facilities (facility_name, facility_type, capacity, location, status, description, image_path)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['facility_name'],
            $data['facility_type'],
            $data['capacity'],
            $data['location'],
            $data['status'] ?? 'available',
            $data['description'] ?? null,
            $data['image_path'] ?? null,
        ]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare(
            "UPDATE facilities SET facility_name=?, facility_type=?, capacity=?, location=?, status=?, description=?, image_path=? WHERE id=?"
        );
        return $stmt->execute([
            $data['facility_name'],
            $data['facility_type'],
            $data['capacity'],
            $data['location'],
            $data['status'],
            $data['description'] ?? null,
            $data['image_path'] ?? null,
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM facilities WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAvailable($date = null, $startTime = null, $endTime = null) {
        $sql = "SELECT * FROM facilities WHERE status = 'available'";
        $params = [];
        if ($date && $startTime && $endTime) {
            $sql .= " AND id NOT IN (
                SELECT facility_id FROM bookings
                WHERE booking_date = ?
                  AND status IN ('approved','pending')
                  AND NOT (end_time <= ? OR start_time >= ?)
            )";
            $params = [$date, $startTime, $endTime];
        }
        $sql .= " ORDER BY facility_name ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function search($keyword, $type = null) {
        $sql = "SELECT * FROM facilities WHERE status = 'available' AND (facility_name LIKE ? OR location LIKE ? OR description LIKE ?)";
        $like = "%$keyword%";
        $params = [$like, $like, $like];
        if ($type) {
            $sql .= " AND facility_type = ?";
            $params[] = $type;
        }
        $sql .= " ORDER BY facility_name ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function checkConflict($facilityId, $date, $startTime, $endTime, $excludeBookingId = null) {
        $sql = "SELECT COUNT(*) FROM bookings
                WHERE facility_id = ?
                  AND booking_date = ?
                  AND status IN ('approved','pending')
                  AND NOT (end_time <= ? OR start_time >= ?)";
        $params = [$facilityId, $date, $startTime, $endTime];
        if ($excludeBookingId) {
            $sql .= " AND id != ?";
            $params[] = $excludeBookingId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    public function getTotalCount() {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM facilities")->fetchColumn();
    }

    public function countByType() {
        $stmt = $this->pdo->query(
            "SELECT facility_type, COUNT(*) as cnt FROM facilities GROUP BY facility_type"
        );
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['facility_type']] = $row['cnt'];
        }
        return $result;
    }

    public function getImages($facilityId) {
        $stmt = $this->pdo->prepare("SELECT * FROM facility_images WHERE facility_id = ? ORDER BY is_primary DESC");
        $stmt->execute([$facilityId]);
        return $stmt->fetchAll();
    }

    public function addImage($facilityId, $imagePath, $isPrimary = false) {
        if ($isPrimary) {
            $this->pdo->prepare("UPDATE facility_images SET is_primary = 0 WHERE facility_id = ?")->execute([$facilityId]);
        }
        $stmt = $this->pdo->prepare("INSERT INTO facility_images (facility_id, image_path, is_primary) VALUES (?, ?, ?)");
        return $stmt->execute([$facilityId, $imagePath, $isPrimary ? 1 : 0]);
    }
}
