<?php
require_once __DIR__ . '/../config/db.php';

class Equipment {
    private $pdo;

    public function __construct() {
        $this->pdo = getDB();
    }

    public function getByFacility($facilityId) {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM equipment WHERE facility_id = ? ORDER BY equipment_name ASC"
        );
        $stmt->execute([$facilityId]);
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM equipment WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO equipment (facility_id, equipment_name, quantity, status) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['facility_id'],
            $data['equipment_name'],
            $data['quantity'] ?? 1,
            $data['status'] ?? 'good',
        ]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare(
            "UPDATE equipment SET equipment_name = ?, quantity = ?, status = ? WHERE id = ?"
        );
        return $stmt->execute([
            $data['equipment_name'],
            $data['quantity'],
            $data['status'],
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM equipment WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAll() {
        $stmt = $this->pdo->query(
            "SELECT e.*, f.facility_name FROM equipment e JOIN facilities f ON e.facility_id = f.id ORDER BY f.facility_name, e.equipment_name"
        );
        return $stmt->fetchAll();
    }
}
