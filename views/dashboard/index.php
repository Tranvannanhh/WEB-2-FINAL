<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Booking.php';
require_once __DIR__ . '/../../models/Facility.php';
require_once __DIR__ . '/../../models/Notification.php';

requireLogin();
if ($_SESSION['role'] === 'admin') redirect(APP_URL . '/views/dashboard/admin.php');

$bookingModel  = new Booking();
$facilityModel = new Facility();
$bookingModel->autoComplete();

$userId         = $_SESSION['user_id'];
$myBookings     = $bookingModel->getByUser($userId);
$upcoming       = $bookingModel->getUpcomingByUser($userId, 5);

$totalMine    = count($myBookings);
$pendingMine  = count(array_filter($myBookings, fn($b) => $b['status'] === 'pending'));
$approvedMine = count(array_filter($myBookings, fn($b) => $b['status'] === 'approved'));
$completedMine= count(array_filter($myBookings, fn($b) => $b['status'] === 'completed'));

$availableFacilities = $facilityModel->getAll(null, 'available');
$firstName = sanitize(explode(' ', $_SESSION['full_name'])[0]);
$pageTitle = 'My Dashboard';
?>
<?php include __DIR__ . '/../../includes/user_header.php'; ?>
<?php include __DIR__ . '/../../includes/user_navbar.php'; ?>

<div class="u-page">

  <!-- ── HERO ── -->
  <section class="u-hero">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-7 u-hero-content" data-aos="fade-up">
          <div class="u-hero-badge">
            <i class="fas fa-university"></i>
            VNUIS Campus Facilities
          </div>
          <h1 class="u-hero-title">
            Welcome back,<br><span><?= $firstName ?>!</span>
          </h1>
          <p class="u-hero-sub">
            Explore and book campus rooms, labs, and meeting spaces — fast, easy, and paperless.
          </p>
          <div class="u-hero-actions">
            <a href="<?= APP_URL ?>/views/bookings/create.php" class="u-btn-gold">
              <i class="fas fa-calendar-plus"></i> New Booking
            </a>
            <a href="<?= APP_URL ?>/views/facilities/index.php" class="u-btn-outline-white">
              <i class="fas fa-building"></i> Browse Facilities
            </a>
          </div>
          <div class="u-hero-stats" data-aos="fade-up" data-aos-delay="100">
            <div class="u-hero-stat">
              <div class="u-hero-stat-val"><?= $totalMine ?></div>
              <div class="u-hero-stat-label">Total Bookings</div>
            </div>
            <div class="u-hero-stat">
              <div class="u-hero-stat-val"><?= $pendingMine ?></div>
              <div class="u-hero-stat-label">Pending</div>
            </div>
            <div class="u-hero-stat">
              <div class="u-hero-stat-val"><?= $approvedMine ?></div>
              <div class="u-hero-stat-label">Approved</div>
            </div>
            <div class="u-hero-stat">
              <div class="u-hero-stat-val"><?= $completedMine ?></div>
              <div class="u-hero-stat-label">Completed</div>
            </div>
          </div>
        </div>
        <div class="col-lg-5 d-none d-lg-flex justify-content-center" data-aos="fade-left" data-aos-delay="200">
          <div style="font-size:13rem;opacity:.15;color:#fff;line-height:1">
            <i class="fas fa-calendar-alt"></i>
          </div>
        </div>
      </div>
    </div>
  </section>

  <div class="u-page-inner">
    <div class="container">

      <?= displayFlash() ?>

      <!-- ── Upcoming Bookings ── -->
      <div class="row g-4 mb-5">
        <div class="col-12" data-aos="fade-up">
          <div class="u-card">
            <div class="u-card-header">
              <span><i class="fas fa-calendar-check"></i> Upcoming Bookings</span>
              <a href="<?= APP_URL ?>/views/bookings/index.php" class="u-btn u-btn-outline u-btn-sm">View All</a>
            </div>
            <?php if (empty($upcoming)): ?>
            <div class="u-empty">
              <i class="fas fa-calendar-times d-block"></i>
              <h5>No upcoming bookings</h5>
              <p>Ready to reserve a space?</p>
              <a href="<?= APP_URL ?>/views/bookings/create.php" class="u-btn u-btn-gold mt-3">
                <i class="fas fa-plus"></i> Book Now
              </a>
            </div>
            <?php else: ?>
            <div style="overflow-x:auto">
              <table class="u-table">
                <thead><tr>
                  <th>Facility</th><th>Date</th><th>Time</th><th>Status</th><th>Action</th>
                </tr></thead>
                <tbody>
                <?php foreach ($upcoming as $b): ?>
                <tr>
                  <td>
                    <div class="fw-semibold" style="font-size:.88rem"><?= sanitize($b['facility_name']) ?></div>
                    <div style="font-size:.74rem;color:#64748B"><i class="fas fa-map-marker-alt me-1"></i><?= sanitize($b['location']) ?></div>
                  </td>
                  <td style="font-size:.85rem"><?= formatDate($b['booking_date']) ?></td>
                  <td style="font-size:.85rem"><?= formatTime($b['start_time']) ?>–<?= formatTime($b['end_time']) ?></td>
                  <td><?= getStatusBadge($b['status']) ?></td>
                  <td>
                    <a href="<?= APP_URL ?>/views/bookings/view.php?id=<?= $b['id'] ?>" class="u-btn u-btn-outline u-btn-sm">
                      <i class="fas fa-eye"></i>
                    </a>
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

      <!-- ── Available Facilities ── -->
      <div class="mb-2" data-aos="fade-up">
        <div class="u-divider"></div>
        <h2 class="u-section-title">Available Facilities</h2>
        <p class="u-section-sub">Book a room, lab, or meeting space instantly</p>
      </div>

      <?php if (empty($availableFacilities)): ?>
      <div class="u-empty u-card">
        <i class="fas fa-building d-block"></i>
        <h5>No facilities available right now</h5>
      </div>
      <?php else: ?>
      <div class="row g-4">
        <?php foreach (array_slice($availableFacilities, 0, 8) as $i => $f): ?>
        <div class="col-xl-3 col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= ($i % 4) * 60 ?>">
          <div class="u-facility-card">
            <div class="u-facility-img">
              <?php if (!empty($f['image_path'])): ?>
                <img src="<?= APP_URL ?>/uploads/facilities/<?= sanitize($f['image_path']) ?>" alt="<?= sanitize($f['facility_name']) ?>">
              <?php endif; ?>
              <div class="u-facility-img-overlay"></div>
              <div class="u-facility-icon-bg"><i class="fas fa-<?= getFacilityIcon($f['facility_type']) ?>"></i></div>
              <span class="u-facility-type-chip">
                <i class="fas fa-<?= getFacilityIcon($f['facility_type']) ?>"></i>
                <?= ucfirst(str_replace('_',' ',$f['facility_type'])) ?>
              </span>
            </div>
            <div class="u-facility-body">
              <div class="u-facility-name"><?= sanitize($f['facility_name']) ?></div>
              <div class="u-facility-location"><i class="fas fa-map-marker-alt"></i><?= sanitize($f['location']) ?></div>
              <div class="u-facility-meta">
                <div class="u-facility-meta-item"><i class="fas fa-users"></i><?= $f['capacity'] ?> seats</div>
                <?php if ($f['avg_rating'] > 0): ?>
                <div class="u-facility-meta-item">
                  <i class="fas fa-star" style="color:#F59E0B"></i>
                  <?= number_format($f['avg_rating'],1) ?> (<?= $f['review_count'] ?>)
                </div>
                <?php endif; ?>
              </div>
              <?php if (!empty($f['description'])): ?>
              <p style="font-size:.77rem;color:#64748B;margin-bottom:14px;line-height:1.5">
                <?= sanitize(truncate($f['description'], 75)) ?>
              </p>
              <?php endif; ?>
              <div class="u-facility-footer">
                <a href="<?= APP_URL ?>/views/facilities/view.php?id=<?= $f['id'] ?>" class="u-btn u-btn-outline u-btn-sm flex-fill text-center">
                  <i class="fas fa-eye me-1"></i>Details
                </a>
                <a href="<?= APP_URL ?>/views/bookings/create.php?facility_id=<?= $f['id'] ?>" class="u-btn u-btn-gold u-btn-sm flex-fill text-center">
                  <i class="fas fa-calendar-plus me-1"></i>Book
                </a>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php if (count($availableFacilities) > 8): ?>
      <div class="text-center mt-4" data-aos="fade-up">
        <a href="<?= APP_URL ?>/views/facilities/index.php" class="u-btn u-btn-primary u-btn-lg">
          <i class="fas fa-th-large me-2"></i>Browse All <?= count($availableFacilities) ?> Facilities
        </a>
      </div>
      <?php endif; ?>
      <?php endif; ?>

    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/user_footer.php'; ?>
<script>const APP_URL = "<?= APP_URL ?>";</script>
