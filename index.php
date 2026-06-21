<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    if ($_SESSION['role'] === 'admin') {
        redirect(APP_URL . '/views/dashboard/admin.php');
    } else {
        redirect(APP_URL . '/views/dashboard/index.php');
    }
} else {
    redirect(APP_URL . '/views/auth/login.php');
}
