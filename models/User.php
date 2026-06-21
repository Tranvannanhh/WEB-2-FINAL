<?php
require_once __DIR__ . '/../config/db.php';

class User {
    private $pdo;

    public function __construct() {
        $this->pdo = getDB();
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([strtolower(trim($email))]);
        return $stmt->fetch();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT id, full_name, email, role, student_code, phone, avatar, is_active, created_at, updated_at FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO users (full_name, email, password, role, student_code, phone) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['full_name'],
            strtolower(trim($data['email'])),
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['role'] ?? 'student',
            $data['student_code'] ?? null,
            $data['phone'] ?? null,
        ]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data) {
        $fields = [];
        $values = [];
        $allowed = ['full_name', 'email', 'role', 'student_code', 'phone', 'avatar', 'is_active'];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "$f = ?";
                $values[] = $data[$f];
            }
        }
        if (empty($fields)) return false;
        $values[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->pdo->prepare($sql)->execute($values);
    }

    public function delete($id) {
        // Soft delete
        $stmt = $this->pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function hardDelete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAll($role = null, $search = null) {
        $sql = "SELECT id, full_name, email, role, student_code, phone, avatar, is_active, created_at FROM users WHERE 1=1";
        $params = [];
        if ($role) {
            $sql .= " AND role = ?";
            $params[] = $role;
        }
        if ($search) {
            $sql .= " AND (full_name LIKE ? OR email LIKE ? OR student_code LIKE ?)";
            $like = "%$search%";
            $params = array_merge($params, [$like, $like, $like]);
        }
        $sql .= " ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function updatePassword($id, $newPassword) {
        $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([password_hash($newPassword, PASSWORD_BCRYPT), $id]);
    }

    public function updateProfile($id, $data) {
        $stmt = $this->pdo->prepare(
            "UPDATE users SET full_name = ?, phone = ?, student_code = ?, avatar = ? WHERE id = ?"
        );
        return $stmt->execute([
            $data['full_name'],
            $data['phone'] ?? null,
            $data['student_code'] ?? null,
            $data['avatar'] ?? null,
            $id
        ]);
    }

    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM users WHERE email = ?";
        $params = [strtolower(trim($email))];
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    public function countByRole() {
        $stmt = $this->pdo->query(
            "SELECT role, COUNT(*) as cnt FROM users WHERE is_active = 1 GROUP BY role"
        );
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['role']] = $row['cnt'];
        }
        return $result;
    }

    public function getTotalCount() {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();
    }

    public function getRecentUsers($limit = 5) {
        $stmt = $this->pdo->prepare(
            "SELECT id, full_name, email, role, created_at FROM users WHERE is_active = 1 ORDER BY created_at DESC LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
