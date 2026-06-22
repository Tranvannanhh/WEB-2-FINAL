<?php
define('APP_NAME',      'VNUIS Campus Booking');
define('APP_URL',       'http://localhost/finalweb/WEB-2-FINAL');
define('APP_VERSION',   '1.0.0');
define('UPLOAD_PATH',   __DIR__ . '/../uploads/');
define('UPLOAD_URL',    APP_URL . '/uploads/');
define('SESSION_TIMEOUT', 3600);

date_default_timezone_set('Asia/Ho_Chi_Minh');

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
