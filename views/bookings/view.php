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

$approvalLogs = $bookingModel->getApprovalLog($id);
$review       = $reviewModel->getByBooking($id);
$canReview    = $reviewModel->canReview($id, $_SESSION['user_id']);
$pageTitle    = 'Booking #' . $id;

// Admin: original layout
if ($_SESSION['role'] === 'admin') {
    $backUrl = APP_URL . '/views/bookings/manage.php';
    include __DIR__ . '/../../includes/header.php';
    echo '<div class="app-shell">';
    include __DIR__ . '/../../includes/sidebar.php';
    echo '<div class="main-content" id="mainContent">';
    include __DIR__ . '/../../includes/navbar.php';
    ?>
    <div class="page-content">
    <div class="page-header">
      <div class="page-header-left">
        <h1><i class="fas fa-calendar me-2 text-primary"></i>Booking #<?= $id ?></h1>
      </div>
      <div class="d-flex gap-2">
        <?php if ($booking['status'] === 'pending'): ?>
        <a href="<?= APP_URL ?>/views/bookings/approve.php?id=<?= $id ?>" class="btn btn-success btn-sm">
          <i class="fas fa-check me-1"></i>Approve/Reject
        </a>
        <?php endif; ?>
        <a href="<?= $backUrl ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
      </div>
    </div>
    <?= displayFlash() ?>
    <div class="card"><div class="card-body">
      <p>Booking #<?= $id ?> — <?= sanitize($booking['facility_name']) ?> — <?= formatDate($booking['booking_date']) ?></p>
      <p>Status: <?= getStatusBadge($booking['status']) ?></p>
    </div></div>
    </div>
    <?php
    include __DIR__ . '/../../includes/footer.php';
    echo '</div></div>';
    exit;
}

$backUrl = APP_URL . '/views/bookings/index.php';
?>
<?php include __DIR__ . '/../../includes/user_header.php'; ?>
<?php include __DIR__ . '/../../includes/user_navbar.php'; ?>

<div class="u-page">

  <!-- Banner -->
  <div style="background:var(--u-primary);padding:60px 0 36px">
    <div class="container">
      <div class="u-breadcrumb mb-2">
        <a href="<?= APP_URL ?>/views/dashboard/index.php">Home</a>
        <span class="u-breadcrumb-sep">›</span>
        <a href="<?= APP_URL ?>/views/bookings/index.php">My Bookings</a>
        <span class="u-breadcrumb-sep">›</span>
        <span>Booking #<?= $id ?></span>
      </div>
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
        <div>
          <h1 style="font-family:var(--u-font-serif);color:#fff;font-size:2rem;margin:0">
            Booking #<?= $id ?>
          </h1>
          <div style="margin-top:8px"><?= getStatusBadge($booking['status']) ?></div>
        </div>
        <div style="display:flex;gap:10px">
          <?php if ($booking['status'] === 'pending'): ?>
          <form method="POST" action="<?= APP_URL ?>/controllers/BookingController.php?action=cancel"
                onsubmit="return confirmAction('Cancel this booking?',this)">
            <input type="hidden" name="booking_id" value="<?= $id ?>">
            <button class="u-btn u-btn-sm" style="background:#EF4444;color:#fff;border-color:#EF4444;border-radius:50px;padding:8px 18px;font-weight:600">
              <i class="fas fa-times me-1"></i> Cancel
            </button>
          </form>
          <?php endif; ?>
          <a href="<?= $backUrl ?>"
             style="display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border-radius:50px;border:2px solid rgba(255,255,255,.3);color:#fff;font-size:.84rem;font-weight:600;transition:.2s">
            <i class="fas fa-arrow-left"></i> Back
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="u-page-inner">
    <div class="container">

      <?= displayFlash() ?>

      <!-- Status Tracker -->
      <div class="u-card mb-4" data-aos="fade-up">
        <div class="u-status-track">
          <?php
          $steps = [
            ['label'=>'Submitted','icon'=>'paper-plane'],
            ['label'=>in_array($booking['status'],['rejected','cancelled'])?ucfirst($booking['status']):'Approved','icon'=>in_array($booking['status'],['rejected','cancelled'])?'times':'check'],
          ];
          $statusOrder = ['pending'=>0,'approved'=>1,'rejected'=>1,'cancelled'=>1,'completed'=>1];
          $currentStep = $statusOrder[$booking['status']] ?? 0;
          foreach ($steps as $i => $step):
            $cls = '';
            if ($i < $currentStep) $cls = 'done';
            elseif ($i === $currentStep) $cls = 'active';
            if (($booking['status'] === 'rejected' || $booking['status'] === 'cancelled') && $i === 1) $cls = 'rejected';
          ?>
          <?php if ($i > 0): ?>
          <div class="u-step-connector <?= $i <= $currentStep ? 'done' : '' ?>"></div>
          <?php endif; ?>
          <div class="u-step <?= $cls ?>">
            <div class="u-step-circle"><i class="fas fa-<?= $step['icon'] ?>"></i></div>
            <div class="u-step-label"><?= $step['label'] ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-md-8" data-aos="fade-up">

          <!-- Booking Details -->
          <div class="u-card mb-4">
            <div class="u-card-header"><span><i class="fas fa-info-circle"></i> Booking Details</span></div>
            <div class="u-card-body">
              <div class="row g-3">
                <div class="col-sm-6">
                  <div style="font-size:.75rem;color:var(--u-gray);margin-bottom:2px">Facility</div>
                  <div style="font-weight:600"><?= sanitize($booking['facility_name']) ?></div>
                  <div style="font-size:.78rem;color:var(--u-gray)"><i class="fas fa-map-marker-alt me-1"></i><?= sanitize($booking['location']) ?></div>
                </div>
                <div class="col-sm-6">
                  <div style="font-size:.75rem;color:var(--u-gray);margin-bottom:2px">Booked by</div>
                  <div style="font-weight:600"><?= sanitize($booking['full_name']) ?></div>
                  <div style="font-size:.78rem;color:var(--u-gray)"><?= sanitize($booking['email']) ?></div>
                </div>
                <div class="col-sm-4">
                  <div style="font-size:.75rem;color:var(--u-gray);margin-bottom:2px">Date</div>
                  <div style="font-weight:600"><?= formatDate($booking['booking_date'], 'D, d M Y') ?></div>
                </div>
                <div class="col-sm-4">
                  <div style="font-size:.75rem;color:var(--u-gray);margin-bottom:2px">Time</div>
                  <div style="font-weight:600"><?= formatTime($booking['start_time']) ?> – <?= formatTime($booking['end_time']) ?></div>
                </div>
                <div class="col-sm-4">
                  <div style="font-size:.75rem;color:var(--u-gray);margin-bottom:2px">Status</div>
                  <?= getStatusBadge($booking['status']) ?>
                </div>
                <div class="col-12">
                  <div style="font-size:.75rem;color:var(--u-gray);margin-bottom:6px">Purpose</div>
                  <div style="background:var(--u-off-white);border-radius:8px;padding:14px;font-size:.86rem;line-height:1.6">
                    <?= nl2br(sanitize($booking['purpose'])) ?>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Review section -->
          <?php if ($booking['status'] === 'completed'): ?>
          <div class="u-card">
            <div class="u-card-header"><span><i class="fas fa-star"></i> Review</span></div>
            <div class="u-card-body">
              <?php if ($review): ?>
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
                <span style="color:#F59E0B;font-size:1.2rem"><?= str_repeat('★',$review['rating']) ?><?= str_repeat('☆',5-$review['rating']) ?></span>
                <strong><?= $review['rating'] ?>/5</strong>
              </div>
              <?php if ($review['comment']): ?>
              <p style="color:var(--u-gray);font-size:.86rem;margin-bottom:8px"><?= sanitize($review['comment']) ?></p>
              <?php endif; ?>
              <div style="font-size:.73rem;color:#94A3B8"><i class="fas fa-clock me-1"></i><?= timeAgo($review['created_at']) ?></div>
              <?php elseif ($canReview && $booking['user_id'] == $_SESSION['user_id']): ?>
              <p style="color:var(--u-gray);font-size:.86rem;margin-bottom:14px">Share your experience to help others.</p>
              <a href="<?= APP_URL ?>/views/bookings/review.php?booking_id=<?= $id ?>" class="u-btn u-btn-gold">
                <i class="fas fa-star"></i> Write a Review
              </a>
              <?php else: ?>
              <p style="color:var(--u-gray);font-size:.86rem">No review submitted.</p>
              <?php endif; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <!-- Approval Log -->
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="60">
          <div class="u-card">
            <div class="u-card-header"><span><i class="fas fa-history"></i> Approval Log</span></div>
            <div class="u-card-body">
              <?php if (empty($approvalLogs)): ?>
              <p style="color:var(--u-gray);font-size:.84rem;text-align:center">No approval actions yet.</p>
              <?php else: ?>
              <div style="position:relative;padding-left:24px">
                <div style="position:absolute;left:7px;top:0;bottom:0;width:2px;background:var(--u-border)"></div>
                <?php foreach ($approvalLogs as $log): ?>
                <div style="position:relative;margin-bottom:18px">
                  <div style="position:absolute;left:-20px;top:4px;width:14px;height:14px;border-radius:50%;background:<?= $log['action']==='approved'?'#10B981':'#EF4444' ?>;border:2px solid #fff;box-shadow:0 0 0 2px <?= $log['action']==='approved'?'#10B981':'#EF4444' ?>"></div>
                  <div style="background:var(--u-off-white);border-radius:8px;padding:10px 12px;border:1px solid var(--u-border)">
                    <div style="font-size:.84rem;font-weight:600"><?= ucfirst($log['action']) ?> by <?= sanitize($log['admin_name']) ?></div>
                    <?php if ($log['note']): ?>
                    <div style="font-size:.78rem;color:var(--u-gray);margin-top:3px"><?= sanitize($log['note']) ?></div>
                    <?php endif; ?>
                    <div style="font-size:.72rem;color:#94A3B8;margin-top:4px"><?= formatDateTime($log['action_time']) ?></div>
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
<script>const APP_URL = "<?= APP_URL ?>";</script>
