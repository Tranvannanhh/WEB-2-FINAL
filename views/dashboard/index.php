<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Booking.php';
require_once __DIR__ . '/../../models/Facility.php';

requireLogin();
if ($_SESSION['role'] === 'admin') redirect(APP_URL . '/views/dashboard/admin.php');

$bookingModel = new Booking();
$facilityModel = new Facility();
$bookingModel->autoComplete();

$userId    = $_SESSION['user_id'];
$all       = $bookingModel->getByUser($userId);
$upcoming  = $bookingModel->getUpcomingByUser($userId, 5);
$total     = count($all);
$pending   = count(array_filter($all, fn($b) => $b['status'] === 'pending'));
$approved  = count(array_filter($all, fn($b) => $b['status'] === 'approved'));
$completed = count(array_filter($all, fn($b) => $b['status'] === 'completed'));

$facilities = $facilityModel->getAll(null, 'available');
$firstName  = sanitize(explode(' ', $_SESSION['full_name'])[0]);
$role       = ucfirst($_SESSION['role'] ?? 'student');
$pageTitle  = 'Dashboard';
?>
<?php include __DIR__ . '/../../includes/user_header.php'; ?>
<?php include __DIR__ . '/../../includes/user_navbar.php'; ?>

<div class="u-page">

  <!-- ── HERO ── -->
  <section class="u-hero">
    <div class="u-hero-deco"><i class="fas fa-calendar-check"></i></div>
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-8" data-aos="fade-up">
          <div class="u-hero-eyebrow"><i class="fas fa-university"></i> VNUIS Campus — <?= $role ?></div>
          <h1 class="u-hero-title">Welcome back, <em><?= $firstName ?></em>!</h1>
          <p class="u-hero-desc">Book campus rooms, labs, and meeting spaces in seconds — fast, paperless, and simple.</p>
          <div class="u-hero-cta">
            <a href="<?= APP_URL ?>/views/bookings/create.php" class="btn-hero-primary">
              <i class="fas fa-calendar-plus"></i> New Booking
            </a>
            <a href="<?= APP_URL ?>/views/facilities/index.php" class="btn-hero-ghost">
              <i class="fas fa-building"></i> Browse Facilities
            </a>
          </div>
          <div class="u-hero-stats" data-aos="fade-up" data-aos-delay="120">
            <div class="u-hero-stat"><div class="u-hero-stat-val"><?= $total ?></div><div class="u-hero-stat-lbl">Total Bookings</div></div>
            <div class="u-hero-stat"><div class="u-hero-stat-val"><?= $pending ?></div><div class="u-hero-stat-lbl">Pending</div></div>
            <div class="u-hero-stat"><div class="u-hero-stat-val"><?= $approved ?></div><div class="u-hero-stat-lbl">Approved</div></div>
            <div class="u-hero-stat"><div class="u-hero-stat-val"><?= $completed ?></div><div class="u-hero-stat-lbl">Completed</div></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <div class="u-content">
    <div class="container">

      <?= displayFlash() ?>

      <!-- ── Upcoming bookings ── -->
      <div class="mb-5" data-aos="fade-up">
        <div class="u-card">
          <div class="u-card-hd">
            <span><i class="fas fa-calendar-check"></i> Upcoming Bookings</span>
            <a href="<?= APP_URL ?>/views/bookings/index.php" class="u-btn u-btn-outline u-btn-sm">View All</a>
          </div>
          <?php if (empty($upcoming)): ?>
          <div class="u-empty">
            <i class="fas fa-calendar-times u-empty-icon"></i>
            <h5>No upcoming bookings</h5>
            <p>Ready to reserve a space on campus?</p>
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
                  <div style="font-weight:700;font-size:.88rem"><?= sanitize($b['facility_name']) ?></div>
                  <div style="font-size:.73rem;color:var(--muted)"><i class="fas fa-map-marker-alt me-1"></i><?= sanitize($b['location']) ?></div>
                </td>
                <td><?= formatDate($b['booking_date']) ?></td>
                <td style="white-space:nowrap"><?= formatTime($b['start_time']) ?> – <?= formatTime($b['end_time']) ?></td>
                <td><?= getStatusBadge($b['status']) ?></td>
                <td>
                  <a href="<?= APP_URL ?>/views/bookings/view.php?id=<?= $b['id'] ?>"
                     class="u-icon-btn-sm" title="View"><i class="fas fa-eye"></i></a>
                </td>
              </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- ── Available Facilities ── -->
      <?php if (!empty($facilities)): ?>
      <div class="u-section-hd" data-aos="fade-up">
        <div class="u-section-hd-line"></div>
        <h2>Available Facilities</h2>
        <p>Browse and book the perfect space for your needs</p>
      </div>
      <div class="row g-4">
        <?php foreach (array_slice($facilities, 0, 8) as $i => $f): ?>
        <div class="col-xl-3 col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= ($i%4)*60 ?>">
          <div class="u-fcard">
            <div class="u-fcard-img">
              <?php if (!empty($f['image_path'])): ?>
              <img src="<?= getFacilityImageUrl($f['image_path']) ?>" alt="<?= sanitize($f['facility_name']) ?>">
              <?php endif; ?>
              <div class="u-fcard-img-overlay"></div>
              <div class="u-fcard-icon-bg"><i class="fas fa-<?= getFacilityIcon($f['facility_type']) ?>"></i></div>
              <span class="u-fcard-type"><i class="fas fa-<?= getFacilityIcon($f['facility_type']) ?>"></i> <?= ucfirst(str_replace('_',' ',$f['facility_type'])) ?></span>
            </div>
            <div class="u-fcard-body">
              <div class="u-fcard-name"><?= sanitize($f['facility_name']) ?></div>
              <div class="u-fcard-loc"><i class="fas fa-map-marker-alt" style="color:var(--gold)"></i><?= sanitize($f['location']) ?></div>
              <div class="u-fcard-meta">
                <div class="u-fcard-meta-item"><i class="fas fa-users"></i><?= $f['capacity'] ?> seats</div>
                <?php if ($f['avg_rating'] > 0): ?>
                <div class="u-fcard-meta-item"><i class="fas fa-star" style="color:var(--amber)"></i><?= number_format($f['avg_rating'],1) ?> (<?= $f['review_count'] ?>)</div>
                <?php endif; ?>
              </div>
              <?php if (!empty($f['description'])): ?>
              <div class="u-fcard-desc"><?= sanitize(truncate($f['description'],75)) ?></div>
              <?php endif; ?>
              <div class="u-fcard-foot">
                <a href="<?= APP_URL ?>/views/facilities/view.php?id=<?= $f['id'] ?>" class="u-btn u-btn-outline u-btn-sm flex-fill justify-content-center">
                  <i class="fas fa-eye"></i> Details
                </a>
                <a href="<?= APP_URL ?>/views/bookings/create.php?facility_id=<?= $f['id'] ?>" class="u-btn u-btn-gold u-btn-sm flex-fill justify-content-center">
                  <i class="fas fa-calendar-plus"></i> Book
                </a>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php if (count($facilities) > 8): ?>
      <div class="text-center mt-4" data-aos="fade-up">
        <a href="<?= APP_URL ?>/views/facilities/index.php" class="u-btn u-btn-primary u-btn-lg">
          <i class="fas fa-th-large me-1"></i> Browse All <?= count($facilities) ?> Facilities
        </a>
      </div>
      <?php endif; ?>
      <?php endif; ?>

    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/user_footer.php'; ?>
