<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Booking.php';
require_once __DIR__ . '/../../models/Review.php';

requireLogin();
$bookingModel = new Booking();
$reviewModel  = new Review();
$bookingModel->autoComplete();

$userId   = $_SESSION['user_id'];
$status   = $_GET['status'] ?? '';
$bookings = $bookingModel->getByUser($userId, $status ?: null);
$pageTitle = 'My Bookings';

if ($_SESSION['role'] === 'admin') {
    include __DIR__ . '/../../includes/header.php';
    echo '<div class="app-shell">'; include __DIR__ . '/../../includes/sidebar.php';
    echo '<div class="main-content" id="mainContent">'; include __DIR__ . '/../../includes/navbar.php';
    echo '<div class="page-content">'.displayFlash().'<p>Admin bookings managed from the bookings manage page.</p>';
    include __DIR__ . '/../../includes/footer.php'; echo '</div></div>'; exit;
}
?>
<?php include __DIR__ . '/../../includes/user_header.php'; ?>
<?php include __DIR__ . '/../../includes/user_navbar.php'; ?>

<div class="u-page">
  <div class="u-banner">
    <div class="container">
      <div class="u-bc"><a href="<?= APP_URL ?>/views/dashboard/index.php">Home</a><span class="u-bc-sep">›</span><span>My Bookings</span></div>
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
        <div>
          <h1 class="u-banner-title" style="margin-bottom:4px">My Bookings</h1>
          <p class="u-banner-sub" style="margin:0">Track all your campus facility reservations</p>
        </div>
        <a href="<?= APP_URL ?>/views/bookings/create.php" class="btn-hero-primary" style="padding:11px 24px">
          <i class="fas fa-plus"></i> New Booking
        </a>
      </div>
    </div>
  </div>

  <div class="u-content">
    <div class="container">
      <?= displayFlash() ?>

      <!-- Filter tabs -->
      <div class="u-filter-tabs">
        <?php $tabs = [''=>'All','pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected','completed'=>'Completed','cancelled'=>'Cancelled']; ?>
        <?php foreach ($tabs as $s => $l): ?>
        <a href="?status=<?= $s ?>" class="u-filter-tab <?= $status===$s?'is-active':'' ?>"><?= $l ?></a>
        <?php endforeach; ?>
      </div>

      <div class="u-card" data-aos="fade-up">
        <?php if (empty($bookings)): ?>
        <div class="u-empty">
          <i class="fas fa-calendar-times u-empty-icon"></i>
          <h5>No bookings found</h5>
          <p>You haven't made any bookings yet.</p>
          <a href="<?= APP_URL ?>/views/bookings/create.php" class="u-btn u-btn-gold mt-3"><i class="fas fa-plus"></i> Book Now</a>
        </div>
        <?php else: ?>
        <div style="overflow-x:auto">
          <table class="u-table u-datatable">
            <thead><tr>
              <th>Facility</th><th>Date</th><th>Time</th><th>Purpose</th><th>Status</th><th>Created</th><th></th>
            </tr></thead>
            <tbody>
            <?php foreach ($bookings as $b): ?>
            <tr>
              <td>
                <div style="font-weight:700;font-size:.88rem"><?= sanitize($b['facility_name']) ?></div>
                <div style="font-size:.73rem;color:var(--muted)"><i class="fas fa-map-marker-alt me-1"></i><?= sanitize($b['location']) ?></div>
              </td>
              <td style="white-space:nowrap"><?= formatDate($b['booking_date']) ?></td>
              <td style="white-space:nowrap"><?= formatTime($b['start_time']) ?>–<?= formatTime($b['end_time']) ?></td>
              <td style="font-size:.82rem;color:var(--muted);max-width:170px"><?= sanitize(truncate($b['purpose'],48)) ?></td>
              <td><?= getStatusBadge($b['status']) ?></td>
              <td style="font-size:.77rem;color:#94a3b8;white-space:nowrap"><?= timeAgo($b['created_at']) ?></td>
              <td>
                <div style="display:flex;gap:5px">
                  <a href="<?= APP_URL ?>/views/bookings/view.php?id=<?= $b['id'] ?>"
                     class="u-icon-btn-sm" title="View Detail"><i class="fas fa-eye"></i></a>
                  <?php if ($b['status'] === 'pending'): ?>
                  <form method="POST" action="<?= APP_URL ?>/controllers/BookingController.php?action=cancel"
                        style="display:inline" onsubmit="return confirmAction('Cancel this booking?', this)">
                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                    <button type="submit" class="u-icon-btn-sm danger" title="Cancel Booking">
                      <i class="fas fa-times"></i>
                    </button>
                  </form>
                  <?php endif; ?>
                  <?php if ($b['status'] === 'completed' && $reviewModel->canReview($b['id'], $userId)): ?>
                  <a href="<?= APP_URL ?>/views/bookings/review.php?booking_id=<?= $b['id'] ?>"
                     class="u-icon-btn-sm warning" title="Write Review"><i class="fas fa-star"></i></a>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/user_footer.php'; ?>
