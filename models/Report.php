<?php
require_once __DIR__ . '/../config/db.php';

class Report {
    private $pdo;

    public function __construct() {
        $this->pdo = getDB();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO reports (facility_id, user_id, issue_description) VALUES (?, ?, ?)"
        );
        $stmt->execute([
            $data['facility_id'],
            $data['user_id'],
            $data['issue_description'],
        ]);
        return $this->pdo->lastInsertId();
    }

    public function getAll($status = null) {
        $sql = "SELECT r.*, u.full_name, u.email, f.facility_name, f.location
                FROM reports r
                JOIN users u ON r.user_id = u.id
                JOIN facilities f ON r.facility_id = f.id
                WHERE 1=1";
        $params = [];
        if ($status) {
            $sql .= " AND r.report_status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY r.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare(
            "SELECT r.*, u.full_name, u.email, f.facility_name, f.location
             FROM reports r
             JOIN users u ON r.user_id = u.id
             JOIN facilities f ON r.facility_id = f.id
             WHERE r.id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateStatus($id, $status, $adminNote = null) {
        $stmt = $this->pdo->prepare(
            "UPDATE reports SET report_status = ?, admin_note = ? WHERE id = ?"
        );
        return $stmt->execute([$status, $adminNote, $id]);
    }

    public function getByUser($userId) {
        $stmt = $this->pdo->prepare(
            "SELECT r.*, f.facility_name FROM reports r
             JOIN facilities f ON r.facility_id = f.id
             WHERE r.user_id = ? ORDER BY r.created_at DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function countByStatus() {
        $stmt = $this->pdo->query(
            "SELECT report_status, COUNT(*) as cnt FROM reports GROUP BY report_status"
        );
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['report_status']] = $row['cnt'];
        }
        return $result;
    }

    public function getTotalCount() {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM reports")->fetchColumn();
    }
}
