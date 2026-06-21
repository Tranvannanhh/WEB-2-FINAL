<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Notification.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']); exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$userId = $_SESSION['user_id'];
$notifModel = new Notification();

switch ($action) {
    case 'get_recent':
        $items = $notifModel->getRecentForDropdown($userId, 8);
        $unread = $notifModel->getUnreadCount($userId);
        $result = [];
        foreach ($items as $n) {
            $n['time_ago'] = timeAgo($n['created_at']);
            $result[] = $n;
        }
        echo json_encode(['notifications' => $result, 'unread_count' => $unread]);
        break;

    case 'mark_read':
        $id = intval($_POST['id'] ?? 0);
        $notifModel->markRead($id, $userId);
        echo json_encode(['success' => true]);
        break;

    case 'mark_all_read':
        $notifModel->markAllRead($userId);
        echo json_encode(['success' => true]);
        break;

    case 'delete':
        $id = intval($_POST['id'] ?? 0);
        $notifModel->delete($id, $userId);
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}
