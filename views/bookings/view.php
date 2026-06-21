<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Booking.php';
require_once __DIR__ . '/../../models/Review.php';

requireLogin();
$bookingModel = new Booking();
$reviewModel  = new Review();
$id = intval($_GET['id'] ?? 0);
$booking = $bookingModel->findById($id);

if (!$booking) { flashMessage('danger','Booking not found.'); redirect(APP_URL.'/views/bookings/index.php'); }
if ($booking['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
    flashMessage('danger','Access denied.'); redirect(APP_URL.'/views/bookings/index.php');
}

$logs      = $bookingModel->getApprovalLog($id);
$review    = $reviewModel->getByBooking($id);
$canReview = $reviewModel->canReview($id, $_SESSION['user_id']);
$pageTitle = 'Booking #'.$id;

// Admin layout
if ($_SESSION['role'] === 'admin') {
    include __DIR__ . '/../../includes/header.php';
    echo '<div class="app-shell">'; include __DIR__ . '/../../includes/sidebar.php';
    echo '<div class="main-content" id="mainContent">'; include __DIR__ . '/../../includes/navbar.php';
    echo '<div class="page-content"><div class="page-header"><div class="page-header-left"><h1><i class="fas fa-calendar me-2 text-primary"></i>Booking #'.$id.'</h1></div>';
    $backUrl = APP_URL.'/views/bookings/manage.php';
    if ($booking['status']==='pending') echo '<a href="'.APP_URL.'/views/bookings/approve.php?id='.$id.'" class="btn btn-success btn-sm"><i class="fas fa-check me-1"></i>Approve/Reject</a>';
    echo '<a href="'.$backUrl.'" class="btn btn-outline-secondary btn-sm ms-2"><i class="fas fa-arrow-left me-1"></i>Back</a></div>';
    echo displayFlash();
    echo '<div class="card"><div class="card-body"><p>Booking #'.$id.' — '.sanitize($booking['facility_name']).' — '.formatDate($booking['booking_date']).'</p>';
    echo '<p>Status: '.getStatusBadge($booking['status']).'</p></div></div></div>';
    include __DIR__ . '/../../includes/footer.php'; echo '</div></div>'; exit;
}

$backUrl = APP_URL.'/views/bookings/index.php';
// Stepper logic
$statusOrder = ['pending'=>0,'approved'=>2,'rejected'=>2,'cancelled'=>2,'completed'=>3];
$curStep = $statusOrder[$booking['status']] ?? 0;
?>
<?php include __DIR__ . '/../../includes/user_header.php'; ?>
<?php include __DIR__ . '/../../includes/user_navbar.php'; ?>

<div class="u-page">
  <div class="u-banner">
    <div class="container">
      <div class="u-bc">
        <a href="<?= APP_URL ?>/views/dashboard/index.php">Home</a><span class="u-bc-sep">›</span>
        <a href="<?= $backUrl ?>">My Bookings</a><span class="u-bc-sep">›</span>
        <span>Booking #<?= $id ?></span>
      </div>
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
        <div>
          <h1 class="u-banner-title" style="margin-bottom:8px">Booking #<?= $id ?></h1>
          <?= getStatusBadge($booking['status']) ?>
        </div>
        <div style="display:flex;gap:10px;align-items:center">
          <?php if ($booking['status'] === 'pending'): ?>
          <form method="POST" action="<?= APP_URL ?>/controllers/BookingController.php?action=cancel"
                onsubmit="return confirmAction('Cancel this booking?',this)">
            <input type="hidden" name="booking_id" value="<?= $id ?>">
            <button type="submit" class="u-btn u-btn-danger u-btn-sm">
              <i class="fas fa-times"></i> Cancel Booking
            </button>
          </form>
          <?php endif; ?>
          <a href="<?= $backUrl ?>" class="u-btn u-btn-outline u-btn-sm">
            <i class="fas fa-arrow-left"></i> Back
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="u-content">
    <div class="container">
      <?= displayFlash() ?>

      <!-- Status tracker -->
      <div class="u-card mb-4" data-aos="fade-up">
        <div class="u-stepper">
          <?php
          $steps = [
            ['Submitted','paper-plane'],
            ['Under Review','search'],
            [in_array($booking['status'],['rejected','cancelled'])?ucfirst($booking['status']):'Approved', in_array($booking['status'],['rejected','cancelled'])?'times':'check'],
            ['Completed','flag'],
          ];
          foreach ($steps as $i => $step):
            $cls = '';
            if ($i < $curStep) $cls = 'done';
            elseif ($i === $curStep) $cls = 'active';
            if (in_array($booking['status'],['rejected','cancelled']) && $i===2) $cls = 'rejected';
          ?>
          <?php if ($i > 0): ?>
          <div class="u-step-line <?= $i<=$curStep?'done':'' ?>"></div>
          <?php endif; ?>
          <div class="u-step <?= $cls ?>">
            <div class="u-step-circle"><i class="fas fa-<?= $step[1] ?>"></i></div>
            <div class="u-step-label"><?= $step[0] ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-md-8" data-aos="fade-up">

          <!-- Details card -->
          <div class="u-card mb-4">
            <div class="u-card-hd"><span><i class="fas fa-info-circle"></i> Booking Details</span></div>
            <div class="u-card-body">
              <div class="row g-3">
                <div class="col-sm-6">
                  <div style="font-size:.73rem;color:var(--muted);margin-bottom:3px;text-transform:uppercase;letter-spacing:.5px">Facility</div>
                  <div style="font-weight:700"><?= sanitize($booking['facility_name']) ?></div>
                  <div style="font-size:.78rem;color:var(--muted)"><i class="fas fa-map-marker-alt me-1"></i><?= sanitize($booking['location']) ?></div>
                </div>
                <div class="col-sm-6">
                  <div style="font-size:.73rem;color:var(--muted);margin-bottom:3px;text-transform:uppercase;letter-spacing:.5px">Booked by</div>
                  <div style="font-weight:700"><?= sanitize($booking['full_name']) ?></div>
                  <div style="font-size:.78rem;color:var(--muted)"><?= sanitize($booking['email']) ?></div>
                </div>
                <div class="col-sm-4">
                  <div style="font-size:.73rem;color:var(--muted);margin-bottom:3px;text-transform:uppercase;letter-spacing:.5px">Date</div>
                  <div style="font-weight:700"><?= formatDate($booking['booking_date'],'D, d M Y') ?></div>
                </div>
                <div class="col-sm-4">
                  <div style="font-size:.73rem;color:var(--muted);margin-bottom:3px;text-transform:uppercase;letter-spacing:.5px">Time</div>
                  <div style="font-weight:700"><?= formatTime($booking['start_time']) ?> – <?= formatTime($booking['end_time']) ?></div>
                </div>
                <div class="col-sm-4">
                  <div style="font-size:.73rem;color:var(--muted);margin-bottom:3px;text-transform:uppercase;letter-spacing:.5px">Status</div>
                  <?= getStatusBadge($booking['status']) ?>
                </div>
                <div class="col-12">
                  <div style="font-size:.73rem;color:var(--muted);margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px">Purpose</div>
                  <div style="background:var(--bg);border-radius:var(--r-sm);padding:14px;font-size:.87rem;line-height:1.7;border:1px solid var(--border)">
                    <?= nl2br(sanitize($booking['purpose'])) ?>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Review section -->
          <?php if ($booking['status'] === 'completed'): ?>
          <div class="u-card">
            <div class="u-card-hd"><span><i class="fas fa-star"></i> Review</span></div>
            <div class="u-card-body">
              <?php if ($review): ?>
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
                <span style="color:var(--amber);font-size:1.3rem"><?= str_repeat('★',$review['rating']) ?><?= str_repeat('☆',5-$review['rating']) ?></span>
                <strong style="font-size:1rem"><?= $review['rating'] ?>/5</strong>
              </div>
              <?php if ($review['comment']): ?>
              <p style="color:var(--muted);font-size:.87rem;margin-bottom:8px;line-height:1.7"><?= sanitize($review['comment']) ?></p>
              <?php endif; ?>
              <div style="font-size:.72rem;color:#94a3b8"><i class="fas fa-clock me-1"></i><?= timeAgo($review['created_at']) ?></div>
              <?php elseif ($canReview && $booking['user_id'] == $_SESSION['user_id']): ?>
              <p style="color:var(--muted);font-size:.87rem;margin-bottom:16px">Share your experience to help fellow students!</p>
              <a href="<?= APP_URL ?>/views/bookings/review.php?booking_id=<?= $id ?>" class="u-btn u-btn-gold">
                <i class="fas fa-star"></i> Write a Review
              </a>
              <?php else: ?>
              <p style="color:var(--muted);font-size:.87rem;margin:0">No review submitted for this booking.</p>
              <?php endif; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <!-- Approval log -->
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="60">
          <div class="u-card">
            <div class="u-card-hd"><span><i class="fas fa-history"></i> Approval Log</span></div>
            <div class="u-card-body">
              <?php if (empty($logs)): ?>
              <p style="color:var(--muted);font-size:.85rem;text-align:center;padding:20px 0">No approval actions yet.</p>
              <?php else: ?>
              <div class="u-timeline">
                <?php foreach ($logs as $log): ?>
                <div class="u-timeline-item">
                  <div class="u-timeline-dot <?= $log['action']==='approved'?'':'rejected' ?>"></div>
                  <div class="u-timeline-box">
                    <div class="u-timeline-actor"><?= ucfirst($log['action']) ?> by <?= sanitize($log['admin_name']) ?></div>
                    <?php if ($log['note']): ?>
                    <div class="u-timeline-note"><?= sanitize($log['note']) ?></div>
                    <?php endif; ?>
                    <div class="u-timeline-time"><?= formatDateTime($log['action_time']) ?></div>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/user_footer.php'; ?>
