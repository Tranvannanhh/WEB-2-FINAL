<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

/**
 * Sanitize output for HTML display
 */
function sanitize($value) {
    return htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
}

/**
 * Format date to a readable string
 */
function formatDate($date, $format = 'd M Y') {
    if (empty($date)) return '-';
    $dt = new DateTime($date);
    return $dt->format($format);
}

/**
 * Format time to H:i
 */
function formatTime($time) {
    if (empty($time)) return '-';
    $dt = new DateTime($time);
    return $dt->format('H:i');
}

/**
 * Send a notification to a user
 */
function sendNotification($userId, $title, $message) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
        return $stmt->execute([$userId, $title, $message]);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Return a Bootstrap badge HTML for a given status
 */
function getStatusBadge($status) {
    $map = [
        'pending'     => ['bg-warning text-dark', 'clock',       'Pending'],
        'approved'    => ['bg-success',            'check-circle','Approved'],
        'rejected'    => ['bg-danger',             'times-circle','Rejected'],
        'cancelled'   => ['bg-secondary',          'ban',         'Cancelled'],
        'completed'   => ['bg-info',               'flag',        'Completed'],
        'available'   => ['bg-success',            'check',       'Available'],
        'maintenance' => ['bg-warning text-dark',  'tools',       'Maintenance'],
        'inactive'    => ['bg-secondary',          'minus-circle','Inactive'],
        'open'        => ['bg-danger',             'exclamation', 'Open'],
        'in_progress' => ['bg-warning text-dark',  'spinner',     'In Progress'],
        'resolved'    => ['bg-success',            'check',       'Resolved'],
        'good'        => ['bg-success',            'check',       'Good'],
        'damaged'     => ['bg-danger',             'times',       'Damaged'],
        'missing'     => ['bg-secondary',          'question',    'Missing'],
    ];
    $s = strtolower($status);
    if (isset($map[$s])) {
        [$cls, $icon, $label] = $map[$s];
        return '<span class="badge ' . $cls . '"><i class="fas fa-' . $icon . ' me-1"></i>' . $label . '</span>';
    }
    return '<span class="badge bg-secondary">' . sanitize($status) . '</span>';
}

/**
 * Redirect to URL
 */
if (!function_exists('redirect')) {
    function redirect($url) {
        header("Location: $url");
        exit();
    }
}

/**
 * Store a flash message in session
 */
function flashMessage($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Retrieve and clear the flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Display flash message as Bootstrap alert HTML
 */
function displayFlash() {
    $flash = getFlashMessage();
    if (!$flash) return '';
    $icons = ['success' => 'check-circle', 'danger' => 'times-circle', 'warning' => 'exclamation-triangle', 'info' => 'info-circle'];
    $icon = $icons[$flash['type']] ?? 'info-circle';
    return '<div class="alert alert-' . sanitize($flash['type']) . ' alert-dismissible fade show" role="alert">
        <i class="fas fa-' . $icon . ' me-2"></i>' . sanitize($flash['message']) . '
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';
}

/**
 * Generate CSRF token
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Paginate query results
 */
function paginate($totalItems, $currentPage, $perPage = 10) {
    $totalPages = (int)ceil($totalItems / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    return ['total' => $totalItems, 'perPage' => $perPage, 'currentPage' => $currentPage, 'totalPages' => $totalPages, 'offset' => $offset];
}

/**
 * Format file size
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
}

/**
 * Check if user can review a booking
 */
function canReview($bookingId, $userId) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE booking_id = ? AND user_id = ?");
    $stmt->execute([$bookingId, $userId]);
    return $stmt->fetchColumn() == 0;
}

/**
 * Get unread notification count for current user
 */
function getUnreadNotificationCount($userId) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}

/**
 * Get facility image src — supports external URLs and local uploads
 */
function facilityImgSrc($imagePath) {
    if (empty($imagePath)) return '';
    if (strpos($imagePath, 'http://') === 0 || strpos($imagePath, 'https://') === 0) {
        return $imagePath;
    }
    return APP_URL . '/uploads/facilities/' . $imagePath;
}

/**
 * Truncate text to specified length
 */
function truncate($text, $length = 100) {
    $text = strip_tags($text);
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

/**
 * Get role badge HTML
 */
function getRoleBadge($role) {
    $map = [
        'admin'    => 'bg-danger',
        'lecturer' => 'bg-primary',
        'student'  => 'bg-info',
    ];
    $cls = $map[$role] ?? 'bg-secondary';
    return '<span class="badge ' . $cls . '">' . ucfirst(sanitize($role)) . '</span>';
}

/**
 * Get facility type icon
 */
function getFacilityIcon($type) {
    $icons = [
        'classroom'    => 'chalkboard',
        'lab'          => 'flask',
        'meeting_room' => 'users',
        'auditorium'   => 'theater-masks',
        'equipment'    => 'tools',
    ];
    return $icons[$type] ?? 'building';
}

/**
 * Format datetime for display
 */
function formatDateTime($datetime, $format = 'd M Y H:i') {
    if (empty($datetime)) return '-';
    return (new DateTime($datetime))->format($format);
}

/**
 * Time ago helper
 */
function timeAgo($datetime) {
    $now  = new DateTime();
    $ago  = new DateTime($datetime);
    $diff = $now->diff($ago);
    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'just now';
}
