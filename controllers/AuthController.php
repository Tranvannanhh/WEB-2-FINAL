<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/User.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'login':
        handleLogin();
        break;
    case 'logout':
        handleLogout();
        break;
    case 'register':
        handleRegister();
        break;
    default:
        redirect(APP_URL . '/views/auth/login.php');
}

function handleLogin() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect(APP_URL . '/views/auth/login.php');
    }

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($email) || empty($password)) {
        flashMessage('danger', 'Please enter your email and password.');
        redirect(APP_URL . '/views/auth/login.php');
    }

    $userModel = new User();
    $user = $userModel->findByEmail($email);

    if (!$user) {
        flashMessage('danger', 'Invalid email or password.');
        redirect(APP_URL . '/views/auth/login.php');
    }

    // Verify password (support plain text "admin123" in demo and bcrypt)
    $valid = false;
    if (password_verify($password, $user['password'])) {
        $valid = true;
    } elseif ($user['password'] === $password) {
        // Plain text fallback (legacy, upgrade to hash)
        $valid = true;
        $userModel->updatePassword($user['id'], $password);
    }

    if (!$valid) {
        flashMessage('danger', 'Invalid email or password.');
        redirect(APP_URL . '/views/auth/login.php');
    }

    if (!$user['is_active']) {
        flashMessage('danger', 'Your account has been disabled. Please contact admin.');
        redirect(APP_URL . '/views/auth/login.php');
    }

    // Set session
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email']     = $user['email'];
    $_SESSION['role']      = $user['role'];
    $_SESSION['avatar']    = $user['avatar'];
    $_SESSION['last_activity'] = time();

    if ($remember) {
        $token = bin2hex(random_bytes(32));
        setcookie('remember_token', $token, time() + (86400 * 30), '/', '', false, true);
    }

    flashMessage('success', 'Welcome back, ' . $user['full_name'] . '!');

    // Redirect based on role
    if ($user['role'] === 'admin') {
        redirect(APP_URL . '/views/dashboard/admin.php');
    } else {
        $redirect = $_GET['redirect'] ?? '';
        if (!empty($redirect) && strpos($redirect, APP_URL) === 0) {
            redirect($redirect);
        }
        redirect(APP_URL . '/views/dashboard/index.php');
    }
}

function handleLogout() {
    session_unset();
    session_destroy();
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }
    redirect(APP_URL . '/views/auth/login.php?msg=logged_out');
}

function handleRegister() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect(APP_URL . '/views/auth/register.php');
    }

    $fullName    = trim($_POST['full_name'] ?? '');
    $email       = strtolower(trim($_POST['email'] ?? ''));
    $password    = $_POST['password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';
    $role        = $_POST['role'] ?? 'student';
    $studentCode = trim($_POST['student_code'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');

    $errors = [];

    if (empty($fullName)) $errors[] = 'Full name is required.';
    if (strlen($fullName) > 100) $errors[] = 'Full name too long.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirmPass) $errors[] = 'Passwords do not match.';
    if (!in_array($role, ['student', 'lecturer'])) $errors[] = 'Invalid role selected.';

    if (!empty($errors)) {
        flashMessage('danger', implode(' ', $errors));
        redirect(APP_URL . '/views/auth/register.php');
    }

    $userModel = new User();

    if ($userModel->emailExists($email)) {
        flashMessage('danger', 'This email address is already registered.');
        redirect(APP_URL . '/views/auth/register.php');
    }

    $userId = $userModel->create([
        'full_name'    => $fullName,
        'email'        => $email,
        'password'     => $password,
        'role'         => $role,
        'student_code' => $studentCode ?: null,
        'phone'        => $phone ?: null,
    ]);

    if ($userId) {
        // Send welcome notification
        sendNotification($userId, 'Welcome to ' . APP_NAME, 'Your account has been created successfully. You can now book facilities.');
        flashMessage('success', 'Account created successfully! Please log in.');
        redirect(APP_URL . '/views/auth/login.php');
    } else {
        flashMessage('danger', 'Registration failed. Please try again.');
        redirect(APP_URL . '/views/auth/register.php');
    }
}
