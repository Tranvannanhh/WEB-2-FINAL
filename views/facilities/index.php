<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Facility.php';

requireLogin();

// Admin uses the admin layout
if ($_SESSION['role'] === 'admin') {
    $facilityModel = new Facility();
    $type   = $_GET['type']   ?? '';
    $search = trim($_GET['search'] ?? '');
    $statusF= $_GET['status'] ?? '';
    if ($search) $facilities = $facilityModel->search($search, $type ?: null);
    else         $facilities = $facilityModel->getAll($type ?: null, $statusF ?: null);
    $pageTitle = 'Browse Facilities';
    include __DIR__ . '/../../includes/header.php';
    echo '<div class="app-shell">';
    include __DIR__ . '/../../includes/sidebar.php';
    echo '<div class="main-content" id="mainContent">';
    include __DIR__ . '/../../includes/navbar.php';
    // original admin content below:
    ?>
    <div class="page-content">
    <div class="page-header">
      <div class="page-header-left">
        <h1><i class="fas fa-building me-2 text-primary"></i>Campus Facilities</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="<?= APP_URL ?>/views/dashboard/admin.php">Dashboard</a></li>
          <li class="breadcrumb-item active">Facilities</li>
        </ol></nav>
      </div>
    </div>
    <?php
    include __DIR__ . '/../../includes/footer.php';
    echo '</div></div>';
    exit;
}

// ── STUDENT / LECTURER VIEW ──
$facilityModel = new Facility();
$type    = $_GET['type']    ?? '';
$search  = trim($_GET['search'] ?? '');
$statusF = $_GET['status']  ?? 'available';

if ($search) $facilities = $facilityModel->search($search, $type ?: null);
else         $facilities = $facilityModel->getAll($type ?: null, $statusF ?: null);

$pageTitle = 'Browse Facilities';
?>
<?php include __DIR__ . '/../../includes/user_header.php'; ?>
<?php include __DIR__ . '/../../includes/user_navbar.php'; ?>

<div class="u-page">

  <!-- Page Banner -->
  <div style="background:var(--u-primary);padding:60px 0 40px">
    <div class="container" data-aos="fade-up">
      <div class="u-breadcrumb mb-2">
        <a href="<?= APP_URL ?>/views/dashboard/index.php">Home</a>
        <span class="u-breadcrumb-sep">›</span>
        <span>Facilities</span>
      </div>
      <h1 style="font-family:var(--u-font-serif);color:#fff;font-size:2rem;margin-bottom:8px">
        Campus Facilities
      </h1>
      <p style="color:rgba(255,255,255,.65);font-size:.9rem">
        Find and book the perfect space for your academic needs
      </p>
    </div>
  </div>

  <div class="u-page-inner">
    <div class="container">

      <?= displayFlash() ?>

      <p style="font-size:.83rem;color:var(--u-gray);margin-bottom:20px">
        Showing <strong><?= count($facilities) ?></strong> facilit<?= count($facilities)===1?'y':'ies' ?>
      </p>

      <?php if (empty($facilities)): ?>
      <div class="u-card">
        <div class="u-empty">
          <i class="fas fa-building d-block"></i>
          <h5>No facilities found</h5>
          <p>Try adjusting your filters.</p>
          <a href="?" class="u-btn u-btn-outline mt-3">Clear Filters</a>
        </div>
      </div>
      <?php else: ?>
      <div class="row g-4">
        <?php foreach ($facilities as $i => $f): ?>
        <div class="col-xl-3 col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= ($i%4)*50 ?>">
          <div class="u-facility-card">
            <div class="u-facility-img">
              <?php if (!empty($f['image_path'])): ?>
                <img src="<?= facilityImgSrc($f['image_path']) ?>" alt="<?= sanitize($f['facility_name']) ?>">
              <?php endif; ?>
              <div class="u-facility-img-overlay"></div>
              <div class="u-facility-icon-bg"><i class="fas fa-<?= getFacilityIcon($f['facility_type']) ?>"></i></div>
              <span class="u-facility-type-chip">
                <i class="fas fa-<?= getFacilityIcon($f['facility_type']) ?>"></i>
                <?= ucfirst(str_replace('_',' ',$f['facility_type'])) ?>
              </span>
              <?php if ($f['status'] !== 'available'): ?>
              <span class="u-facility-status-chip u-chip-amber"><?= ucfirst($f['status']) ?></span>
              <?php endif; ?>
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
                <?= sanitize(truncate($f['description'],75)) ?>
              </p>
              <?php endif; ?>
              <div class="u-facility-footer">
                <a href="<?= APP_URL ?>/views/facilities/view.php?id=<?= $f['id'] ?>" class="u-btn u-btn-outline u-btn-sm flex-fill text-center">
                  <i class="fas fa-eye me-1"></i>Details
                </a>
                <?php if ($f['status'] === 'available'): ?>
                <a href="<?= APP_URL ?>/views/bookings/create.php?facility_id=<?= $f['id'] ?>" class="u-btn u-btn-gold u-btn-sm flex-fill text-center">
                  <i class="fas fa-calendar-plus me-1"></i>Book
                </a>
                <?php else: ?>
                <button class="u-btn u-btn-sm flex-fill" disabled style="background:#f1f5f9;color:#94a3b8;border-color:#e2e8f0;cursor:not-allowed">
                  Unavailable
                </button>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/user_footer.php'; ?>
<script>const APP_URL = "<?= APP_URL ?>";</script>
