<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Require the user to be logged in; redirect to login if not
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect(APP_URL . '/views/auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }
    // Check session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        redirect(APP_URL . '/views/auth/login.php?msg=timeout');
    }
    $_SESSION['last_activity'] = time();
}

/**
 * Require the user to have admin role
 */
function requireAdmin() {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        redirect(APP_URL . '/views/dashboard/index.php?error=access_denied');
    }
}

/**
 * Require a specific role (or array of roles)
 */
function requireRole($roles) {
    requireLogin();
    if (is_string($roles)) {
        $roles = [$roles];
    }
    if (!in_array($_SESSION['role'], $roles)) {
        redirect(APP_URL . '/views/dashboard/index.php?error=access_denied');
    }
}

/**
 * Get the current logged-in user's data from the database
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id, full_name, email, role, student_code, phone, avatar, is_active, created_at FROM users WHERE id = ? AND is_active = 1");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Redirect helper (also works without functions.php loaded)
 */
if (!function_exists('redirect')) {
    function redirect($url) {
        header("Location: $url");
        exit();
    }
}
