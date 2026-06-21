<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Facility.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/User.php';

requireLogin();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'create':
        handleCreate();
        break;
    case 'cancel':
        handleCancel();
        break;
    case 'view':
        redirect(APP_URL . '/views/bookings/view.php?id=' . intval($_GET['id'] ?? 0));
        break;
    case 'check_conflict':
        handleCheckConflict();
        break;
    default:
        redirect(APP_URL . '/views/bookings/index.php');
}

function handleCreate() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect(APP_URL . '/views/bookings/create.php');
    }

    $userId     = $_SESSION['user_id'];
    $facilityId = intval($_POST['facility_id'] ?? 0);
    $date       = $_POST['booking_date'] ?? '';
    $startTime  = $_POST['start_time'] ?? '';
    $endTime    = $_POST['end_time'] ?? '';
    $purpose    = trim($_POST['purpose'] ?? '');

    $errors = [];

    if (!$facilityId) $errors[] = 'Please select a facility.';
    if (empty($date)) $errors[] = 'Booking date is required.';
    if (empty($startTime)) $errors[] = 'Start time is required.';
    if (empty($endTime)) $errors[] = 'End time is required.';
    if (empty($purpose)) $errors[] = 'Purpose is required.';

    if (!empty($date) && $date < date('Y-m-d')) {
        $errors[] = 'Booking date cannot be in the past.';
    }

    if (!empty($startTime) && !empty($endTime) && $startTime >= $endTime) {
        $errors[] = 'End time must be after start time.';
    }

    if (!empty($errors)) {
        flashMessage('danger', implode(' ', $errors));
        redirect(APP_URL . '/views/bookings/create.php?facility_id=' . $facilityId);
    }

    $facilityModel = new Facility();
    $facility = $facilityModel->findById($facilityId);
    if (!$facility || $facility['status'] !== 'available') {
        flashMessage('danger', 'Selected facility is not available.');
        redirect(APP_URL . '/views/bookings/create.php');
    }

    $bookingModel = new Booking();

    if ($bookingModel->checkConflict($facilityId, $date, $startTime, $endTime)) {
        flashMessage('danger', 'This facility is already booked for the selected time slot. Please choose a different time.');
        redirect(APP_URL . '/views/bookings/create.php?facility_id=' . $facilityId);
    }

    $bookingId = $bookingModel->create([
        'user_id'      => $userId,
        'facility_id'  => $facilityId,
        'booking_date' => $date,
        'start_time'   => $startTime,
        'end_time'     => $endTime,
        'purpose'      => $purpose,
    ]);

    if ($bookingId) {
        // Notify user
        sendNotification($userId, 'Booking Submitted', 'Your booking for ' . $facility['facility_name'] . ' on ' . formatDate($date) . ' has been submitted and is awaiting approval.|' . APP_URL . '/views/bookings/view.php?id=' . $bookingId);

        // Notify all admins
        $userModel = new User();
        $admins = $userModel->getAll('admin');
        foreach ($admins as $admin) {
            sendNotification($admin['id'], 'New Booking Request', 'New booking from ' . $_SESSION['full_name'] . ' for ' . $facility['facility_name'] . ' on ' . formatDate($date) . '.');
        }

        flashMessage('success', 'Booking submitted successfully! You will be notified when it is approved.');
        redirect(APP_URL . '/views/bookings/view.php?id=' . $bookingId);
    } else {
        flashMessage('danger', 'Failed to create booking. Please try again.');
        redirect(APP_URL . '/views/bookings/create.php');
    }
}

function handleCancel() {
    $bookingId = intval($_POST['booking_id'] ?? $_GET['id'] ?? 0);
    $userId = $_SESSION['user_id'];

    if (!$bookingId) {
        flashMessage('danger', 'Invalid booking.');
        redirect(APP_URL . '/views/bookings/index.php');
    }

    $bookingModel = new Booking();
    $booking = $bookingModel->findById($bookingId);

    if (!$booking) {
        flashMessage('danger', 'Booking not found.');
        redirect(APP_URL . '/views/bookings/index.php');
    }

    if ($booking['user_id'] != $userId && $_SESSION['role'] !== 'admin') {
        flashMessage('danger', 'You are not authorized to cancel this booking.');
        redirect(APP_URL . '/views/bookings/index.php');
    }

    if (!in_array($booking['status'], ['pending', 'approved'])) {
        flashMessage('danger', 'This booking cannot be cancelled.');
        redirect(APP_URL . '/views/bookings/view.php?id=' . $bookingId);
    }

    $success = $bookingModel->updateStatus($bookingId, 'cancelled');

    if ($success) {
        sendNotification($booking['user_id'], 'Booking Cancelled', 'Your booking for ' . $booking['facility_name'] . ' on ' . formatDate($booking['booking_date']) . ' has been cancelled.');
        flashMessage('success', 'Booking has been cancelled.');
    } else {
        flashMessage('danger', 'Failed to cancel booking.');
    }

    redirect(APP_URL . '/views/bookings/index.php');
}

function handleCheckConflict() {
    header('Content-Type: application/json');

    $facilityId = intval($_POST['facility_id'] ?? 0);
    $date       = $_POST['booking_date'] ?? '';
    $startTime  = $_POST['start_time'] ?? '';
    $endTime    = $_POST['end_time'] ?? '';
    $excludeId  = intval($_POST['exclude_id'] ?? 0);

    if (!$facilityId || !$date || !$startTime || !$endTime) {
        echo json_encode(['conflict' => false, 'error' => 'Missing parameters']);
        exit;
    }

    $bookingModel = new Booking();
    $hasConflict = $bookingModel->checkConflict($facilityId, $date, $startTime, $endTime, $excludeId ?: null);

    echo json_encode(['conflict' => $hasConflict]);
    exit;
}
