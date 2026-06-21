<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Facility.php';
require_once __DIR__ . '/../models/Report.php';

requireAdmin();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'approve_booking':
        handleApproveBooking('approved');
        break;
    case 'reject_booking':
        handleApproveBooking('rejected');
        break;
    case 'complete_booking':
        handleCompleteBooking();
        break;
    case 'update_report':
        handleUpdateReport();
        break;
    default:
        redirect(APP_URL . '/views/dashboard/admin.php');
}

function handleApproveBooking($decision) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect(APP_URL . '/views/bookings/manage.php');
    }

    $bookingId = intval($_POST['booking_id'] ?? 0);
    $note      = trim($_POST['note'] ?? '');

    if (!$bookingId) {
        flashMessage('danger', 'Invalid booking.');
        redirect(APP_URL . '/views/bookings/manage.php');
    }

    $bookingModel = new Booking();
    $booking = $bookingModel->findById($bookingId);

    if (!$booking) {
        flashMessage('danger', 'Booking not found.');
        redirect(APP_URL . '/views/bookings/manage.php');
    }

    if ($booking['status'] !== 'pending') {
        flashMessage('warning', 'This booking is no longer pending.');
        redirect(APP_URL . '/views/bookings/view.php?id=' . $bookingId);
    }

    $bookingModel->updateStatus($bookingId, $decision);
    $bookingModel->logApproval($bookingId, $_SESSION['user_id'], $decision, $note ?: null);

    $title = $decision === 'approved' ? 'Booking Approved ✓' : 'Booking Rejected';
    $msg   = $decision === 'approved'
        ? "Your booking for {$booking['facility_name']} on " . formatDate($booking['booking_date']) . " has been approved. Please arrive on time.|" . APP_URL . "/views/bookings/view.php?id={$bookingId}"
        : "Your booking for {$booking['facility_name']} on " . formatDate($booking['booking_date']) . " was rejected." . ($note ? " Reason: $note" : '') . "|" . APP_URL . "/views/bookings/view.php?id={$bookingId}";

    sendNotification($booking['user_id'], $title, $msg);

    flashMessage('success', "Booking has been $decision successfully.");
    redirect(APP_URL . '/views/bookings/manage.php');
}

function handleCompleteBooking() {
    $bookingId = intval($_POST['booking_id'] ?? 0);
    $bookingModel = new Booking();
    $booking = $bookingModel->findById($bookingId);
    if ($booking && $booking['status'] === 'approved') {
        $bookingModel->updateStatus($bookingId, 'completed');
        sendNotification($booking['user_id'], 'Booking Completed', "Your booking for {$booking['facility_name']} has been marked as completed. You can now leave a review!");
        flashMessage('success', 'Booking marked as completed.');
    }
    redirect(APP_URL . '/views/bookings/manage.php');
}

function handleUpdateReport() {
    $reportId  = intval($_POST['report_id'] ?? 0);
    $status    = $_POST['report_status'] ?? '';
    $note      = trim($_POST['admin_note'] ?? '');

    if (!in_array($status, ['open','in_progress','resolved'])) {
        flashMessage('danger', 'Invalid status.');
        redirect(APP_URL . '/views/reports/facility.php');
    }

    $reportModel = new Report();
    $reportModel->updateStatus($reportId, $status, $note ?: null);
    flashMessage('success', 'Report updated.');
    redirect(APP_URL . '/views/reports/facility.php');
}
