<?php
require_once __DIR__ . '/../config/db.php';

class Notification {
    private $pdo;

    public function __construct() {
        $this->pdo = getDB();
    }

    public function create($userId, $title, $message) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)"
        );
        $stmt->execute([$userId, $title, $message]);
        return $this->pdo->lastInsertId();
    }

    public function getByUser($userId, $limit = 50, $onlyUnread = false) {
        $sql = "SELECT * FROM notifications WHERE user_id = ?";
        $params = [$userId];
        if ($onlyUnread) {
            $sql .= " AND is_read = 0";
        }
        $sql .= " ORDER BY created_at DESC";
        if ($limit) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function markRead($id, $userId) {
        $stmt = $this->pdo->prepare(
            "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?"
        );
        return $stmt->execute([$id, $userId]);
    }

    public function markAllRead($userId) {
        $stmt = $this->pdo->prepare(
            "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0"
        );
        return $stmt->execute([$userId]);
    }

    public function getUnreadCount($userId) {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0"
        );
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM notifications WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function delete($id, $userId) {
        $stmt = $this->pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $userId]);
    }

    public function getRecentForDropdown($userId, $limit = 8) {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?"
        );
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
}
