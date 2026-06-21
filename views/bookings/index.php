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

// Admin: original layout
if ($_SESSION['role'] === 'admin') {
    include __DIR__ . '/../../includes/header.php';
    echo '<div class="app-shell">';
    include __DIR__ . '/../../includes/sidebar.php';
    echo '<div class="main-content" id="mainContent">';
    include __DIR__ . '/../../includes/navbar.php';
    echo '<div class="page-content">';
    echo displayFlash();
    echo '<p>Admin bookings managed from manage.php</p>';
    include __DIR__ . '/../../includes/footer.php';
    echo '</div></div>';
    exit;
}
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
        <span>My Bookings</span>
      </div>
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
        <div>
          <h1 style="font-family:var(--u-font-serif);color:#fff;font-size:2rem;margin-bottom:4px">My Bookings</h1>
          <p style="color:rgba(255,255,255,.6);font-size:.9rem;margin:0">Track all your facility reservations</p>
        </div>
        <a href="<?= APP_URL ?>/views/bookings/create.php" class="u-btn-gold" style="display:inline-flex;align-items:center;gap:6px;background:var(--u-gold);color:var(--u-primary);padding:10px 22px;border-radius:50px;font-weight:700;font-size:.88rem;border:none">
          <i class="fas fa-plus"></i> New Booking
        </a>
      </div>
    </div>
  </div>

  <div class="u-page-inner">
    <div class="container">

      <?= displayFlash() ?>

      <!-- Filter Tabs -->
      <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px" data-aos="fade-up">
        <?php $statuses = [''=> 'All','pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected','completed'=>'Completed','cancelled'=>'Cancelled']; ?>
        <?php foreach ($statuses as $s => $l): ?>
        <a href="?status=<?= $s ?>"
           style="display:inline-block;padding:7px 18px;border-radius:50px;font-size:.82rem;font-weight:600;text-decoration:none;border:2px solid;transition:all .2s;
                  <?= $status===$s ? 'background:var(--u-gold);color:var(--u-primary);border-color:var(--u-gold)' : 'background:transparent;color:#475569;border-color:#e2e8f0' ?>">
          <?= $l ?>
        </a>
        <?php endforeach; ?>
      </div>

      <div class="u-card" data-aos="fade-up">
        <?php if (empty($bookings)): ?>
        <div class="u-empty">
          <i class="fas fa-calendar-times d-block"></i>
          <h5>No bookings found</h5>
          <p>You haven't made any bookings yet.</p>
          <a href="<?= APP_URL ?>/views/bookings/create.php" class="u-btn u-btn-gold mt-3">
            <i class="fas fa-plus"></i> Book Now
          </a>
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
                <div style="font-weight:600;font-size:.88rem"><?= sanitize($b['facility_name']) ?></div>
                <div style="font-size:.73rem;color:#64748B"><i class="fas fa-map-marker-alt me-1"></i><?= sanitize($b['location']) ?></div>
              </td>
              <td style="font-size:.85rem;white-space:nowrap"><?= formatDate($b['booking_date']) ?></td>
              <td style="font-size:.85rem;white-space:nowrap"><?= formatTime($b['start_time']) ?>–<?= formatTime($b['end_time']) ?></td>
              <td style="font-size:.82rem;color:#64748B;max-width:180px"><?= sanitize(truncate($b['purpose'],50)) ?></td>
              <td><?= getStatusBadge($b['status']) ?></td>
              <td style="font-size:.78rem;color:#94A3B8;white-space:nowrap"><?= timeAgo($b['created_at']) ?></td>
              <td>
                <div style="display:flex;gap:6px">
                  <a href="<?= APP_URL ?>/views/bookings/view.php?id=<?= $b['id'] ?>"
                     style="width:32px;height:32px;border-radius:8px;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;color:var(--u-primary);transition:.2s"
                     title="View"><i class="fas fa-eye" style="font-size:.8rem"></i></a>
                  <?php if ($b['status'] === 'pending'): ?>
                  <form method="POST" action="<?= APP_URL ?>/controllers/BookingController.php?action=cancel"
                        style="display:inline"
                        onsubmit="return confirmAction('Cancel this booking?', this)">
                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                    <button type="submit"
                            style="width:32px;height:32px;border-radius:8px;border:1px solid #fee2e2;background:transparent;color:#EF4444;cursor:pointer;display:flex;align-items:center;justify-content:center"
                            title="Cancel"><i class="fas fa-times" style="font-size:.8rem"></i></button>
                  </form>
                  <?php endif; ?>
                  <?php if ($b['status'] === 'completed' && $reviewModel->canReview($b['id'], $userId)): ?>
                  <a href="<?= APP_URL ?>/views/bookings/review.php?booking_id=<?= $b['id'] ?>"
                     style="width:32px;height:32px;border-radius:8px;border:1px solid #fef3c7;background:transparent;display:flex;align-items:center;justify-content:center;color:#F59E0B"
                     title="Review"><i class="fas fa-star" style="font-size:.8rem"></i></a>
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
<script>const APP_URL = "<?= APP_URL ?>";</script>
