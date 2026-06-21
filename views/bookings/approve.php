<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Booking.php';
require_once __DIR__ . '/../../models/Notification.php';

requireAdmin();
$bookingModel = new Booking();

$id = intval($_GET['id'] ?? 0);
$booking = $bookingModel->findById($id);
if (!$booking) { flashMessage('danger','Booking not found.'); redirect(APP_URL.'/views/bookings/manage.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['approve_action'] ?? '';
    $note   = trim($_POST['note'] ?? '');
    if (in_array($action, ['approved','rejected'])) {
        $bookingModel->updateStatus($id, $action);
        $bookingModel->logApproval($id, $_SESSION['user_id'], $action, $note ?: null);
        $title = $action === 'approved' ? 'Booking Approved' : 'Booking Rejected';
        $msg   = $action === 'approved'
            ? "Your booking for {$booking['facility_name']} on " . formatDate($booking['booking_date']) . " has been approved. Please arrive on time."
            : "Your booking for {$booking['facility_name']} on " . formatDate($booking['booking_date']) . " was rejected." . ($note ? " Reason: $note" : '');
        sendNotification($booking['user_id'], $title, $msg);
        flashMessage('success', "Booking has been $action.");
        redirect(APP_URL.'/views/bookings/manage.php');
    }
}

$pageTitle = 'Approve Booking #' . $id;
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<div class="app-shell">
<?php include __DIR__ . '/../../includes/sidebar.php'; ?>
<div class="main-content" id="mainContent">
<?php include __DIR__ . '/../../includes/navbar.php'; ?>
<div class="page-content">

<div class="page-header">
  <div class="page-header-left">
    <h1><i class="fas fa-gavel me-2 text-primary"></i>Review Booking #<?= $id ?></h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/views/bookings/manage.php">Bookings</a></li>
      <li class="breadcrumb-item active">Review</li>
    </ol></nav>
  </div>
</div>

<?= displayFlash() ?>

<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="card mb-3">
      <div class="card-header"><i class="fas fa-info-circle me-2 text-primary"></i>Booking Details</div>
      <div class="card-body">
        <div class="row g-2 small">
          <div class="col-sm-6"><span class="text-muted">Facility: </span><strong><?= sanitize($booking['facility_name']) ?></strong></div>
          <div class="col-sm-6"><span class="text-muted">Location: </span><?= sanitize($booking['location']) ?></div>
          <div class="col-sm-6"><span class="text-muted">Requested by: </span><strong><?= sanitize($booking['full_name']) ?></strong></div>
          <div class="col-sm-6"><span class="text-muted">Email: </span><?= sanitize($booking['email']) ?></div>
          <div class="col-sm-4"><span class="text-muted">Date: </span><?= formatDate($booking['booking_date'], 'D, d M Y') ?></div>
          <div class="col-sm-4"><span class="text-muted">Time: </span><?= formatTime($booking['start_time']) ?>–<?= formatTime($booking['end_time']) ?></div>
          <div class="col-sm-4"><span class="text-muted">Status: </span><?= getStatusBadge($booking['status']) ?></div>
          <div class="col-12">
            <span class="text-muted">Purpose: </span>
            <div class="bg-light p-2 rounded mt-1"><?= nl2br(sanitize($booking['purpose'])) ?></div>
          </div>
        </div>
      </div>
    </div>

    <?php if ($booking['status'] === 'pending'): ?>
    <div class="card">
      <div class="card-header"><i class="fas fa-gavel me-2 text-primary"></i>Admin Decision</div>
      <div class="card-body">
        <form method="POST" id="approveForm">
          <div class="mb-3">
            <label class="form-label">Admin Note (optional)</label>
            <textarea class="form-control" name="note" rows="3" placeholder="Add a note for the user…"></textarea>
          </div>
          <div class="d-flex gap-3">
            <button type="submit" name="approve_action" value="approved" class="btn btn-success flex-1"
                    onclick="return confirmAction('Approve this booking?',document.getElementById(\'approveForm\'))">
              <i class="fas fa-check me-2"></i>Approve Booking
            </button>
            <button type="submit" name="approve_action" value="rejected" class="btn btn-danger flex-1"
                    onclick="return confirmAction('Reject this booking?',document.getElementById(\'approveForm\'))">
              <i class="fas fa-times me-2"></i>Reject Booking
            </button>
          </div>
        </form>
      </div>
    </div>
    <?php else: ?>
    <div class="alert alert-info">
      <i class="fas fa-info-circle me-2"></i>This booking has already been <?= $booking['status'] ?>. No further action needed.
    </div>
    <?php endif; ?>
  </div>
</div>

</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
</div>
</div>
<script>const APP_URL = "<?= APP_URL ?>";</script>
