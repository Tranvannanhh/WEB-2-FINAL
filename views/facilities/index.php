<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Facility.php';

requireLogin();

$facilityModel = new Facility();

/* ── Admin: use admin layout ── */
if ($_SESSION['role'] === 'admin') {
    $type    = $_GET['type']   ?? '';
    $search  = trim($_GET['search'] ?? '');
    $statusF = $_GET['status'] ?? '';
    $facilities = $search
        ? $facilityModel->search($search, $type ?: null)
        : $facilityModel->getAll($type ?: null, $statusF ?: null);
    $pageTitle = 'Browse Facilities';
    include __DIR__ . '/../../includes/header.php';
    echo '<div class="app-shell">';
    include __DIR__ . '/../../includes/sidebar.php';
    echo '<div class="main-content" id="mainContent">';
    include __DIR__ . '/../../includes/navbar.php';
    echo '<div class="page-content"><div class="page-header"><div class="page-header-left"><h1><i class="fas fa-building me-2 text-primary"></i>Campus Facilities</h1></div></div>';
    echo displayFlash();
    // Render admin facility grid
    echo '<div class="row g-3">';
    foreach ($facilities as $f) {
        echo '<div class="col-xl-3 col-lg-4 col-md-6"><div class="facility-card">';
        echo '<div class="facility-img"><div class="facility-icon-overlay"><i class="fas fa-'.getFacilityIcon($f['facility_type']).'"></i></div><div style="background:linear-gradient(135deg,#1E3A8A,#2563EB);width:100%;height:100%"></div>';
        echo '<span class="facility-type-badge badge bg-white text-dark" style="font-size:.68rem">'.ucfirst(str_replace('_',' ',$f['facility_type'])).'</span></div>';
        echo '<div class="facility-body"><div class="facility-name">'.sanitize($f['facility_name']).'</div>';
        echo '<div class="facility-location"><i class="fas fa-map-marker-alt me-1"></i>'.sanitize($f['location']).'</div>';
        echo '<div class="facility-footer"><a href="'.APP_URL.'/views/facilities/view.php?id='.$f['id'].'" class="btn btn-outline-primary btn-sm flex-1"><i class="fas fa-eye me-1"></i>Details</a>';
        if ($f['status'] === 'available') echo '<a href="'.APP_URL.'/views/bookings/create.php?facility_id='.$f['id'].'" class="btn btn-primary btn-sm flex-1"><i class="fas fa-calendar-plus me-1"></i>Book</a>';
        echo '</div></div></div></div>';
    }
    echo '</div></div>';
    include __DIR__ . '/../../includes/footer.php';
    echo '</div></div>';
    exit;
}

/* ── Student / Lecturer ── */
$type    = $_GET['type']   ?? '';
$search  = trim($_GET['search'] ?? '');
$statusF = $_GET['status'] ?? 'available';

$facilities = $search
    ? $facilityModel->search($search, $type ?: null)
    : $facilityModel->getAll($type ?: null, $statusF ?: null);

$pageTitle = 'Facilities';
?>
<?php include __DIR__ . '/../../includes/user_header.php'; ?>
<?php include __DIR__ . '/../../includes/user_navbar.php'; ?>

<div class="u-page">

  <div class="u-banner">
    <div class="container">
      <div class="u-bc"><a href="<?= APP_URL ?>/views/dashboard/index.php">Home</a><span class="u-bc-sep">›</span><span>Facilities</span></div>
      <h1 class="u-banner-title">Campus Facilities</h1>
      <p class="u-banner-sub">Find and reserve the perfect space for your academic work</p>
    </div>
  </div>

  <div class="u-content">
    <div class="container">

      <!-- Search / Filter -->
      <div class="u-search-box" data-aos="fade-up">
        <form method="GET">
          <div class="row g-2 align-items-end">
            <div class="col-md-4">
              <label class="u-label">Search</label>
              <div class="u-input-group">
                <i class="fas fa-search u-prefix"></i>
                <input type="text" class="u-input" name="search" value="<?= sanitize($search) ?>" placeholder="Facility name, location…">
              </div>
            </div>
            <div class="col-md-3">
              <label class="u-label">Type</label>
              <select class="u-select" name="type">
                <option value="">All Types</option>
                <option value="classroom"    <?= $type==='classroom'    ?'selected':'' ?>>Classroom</option>
                <option value="lab"          <?= $type==='lab'          ?'selected':'' ?>>Laboratory</option>
                <option value="meeting_room" <?= $type==='meeting_room' ?'selected':'' ?>>Meeting Room</option>
                <option value="auditorium"   <?= $type==='auditorium'   ?'selected':'' ?>>Auditorium</option>
                <option value="equipment"    <?= $type==='equipment'    ?'selected':'' ?>>Equipment</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="u-label">Status</label>
              <select class="u-select" name="status">
                <option value="">All</option>
                <option value="available"   <?= $statusF==='available'   ?'selected':'' ?>>Available</option>
                <option value="maintenance" <?= $statusF==='maintenance' ?'selected':'' ?>>Maintenance</option>
              </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
              <button type="submit" class="u-btn u-btn-gold flex-fill justify-content-center">
                <i class="fas fa-search"></i> Search
              </button>
              <a href="?" class="u-btn u-btn-outline"><i class="fas fa-times"></i></a>
            </div>
          </div>
        </form>
      </div>

      <?= displayFlash() ?>

      <p style="font-size:.82rem;color:var(--muted);margin-bottom:18px">
        Showing <strong><?= count($facilities) ?></strong> facilit<?= count($facilities)===1?'y':'ies' ?>
        <?= $search ? ' for "<strong>'.sanitize($search).'</strong>"' : '' ?>
      </p>

      <?php if (empty($facilities)): ?>
      <div class="u-card"><div class="u-empty">
        <i class="fas fa-building u-empty-icon"></i>
        <h5>No facilities found</h5>
        <p>Try adjusting your search filters.</p>
        <a href="?" class="u-btn u-btn-outline mt-3">Clear Filters</a>
      </div></div>
      <?php else: ?>
      <div class="row g-4">
        <?php foreach ($facilities as $i => $f): ?>
        <div class="col-xl-3 col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= ($i%4)*50 ?>">
          <div class="u-fcard">
            <div class="u-fcard-img">
              <?php if (!empty($f['image_path'])): ?>
              <img src="<?= APP_URL ?>/uploads/facilities/<?= sanitize($f['image_path']) ?>" alt="">
              <?php endif; ?>
              <div class="u-fcard-img-overlay"></div>
              <div class="u-fcard-icon-bg"><i class="fas fa-<?= getFacilityIcon($f['facility_type']) ?>"></i></div>
              <span class="u-fcard-type"><i class="fas fa-<?= getFacilityIcon($f['facility_type']) ?>"></i><?= ucfirst(str_replace('_',' ',$f['facility_type'])) ?></span>
              <?php if ($f['status'] !== 'available'): ?>
              <span class="u-fcard-status maintenance"><?= ucfirst($f['status']) ?></span>
              <?php else: ?>
              <span class="u-fcard-status available">Available</span>
              <?php endif; ?>
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
                <?php if ($f['status'] === 'available'): ?>
                <a href="<?= APP_URL ?>/views/bookings/create.php?facility_id=<?= $f['id'] ?>" class="u-btn u-btn-gold u-btn-sm flex-fill justify-content-center">
                  <i class="fas fa-calendar-plus"></i> Book
                </a>
                <?php else: ?>
                <button disabled class="u-btn u-btn-sm flex-fill justify-content-center" style="background:#f1f5f9;color:#94a3b8;border-color:#e2e8f0;cursor:not-allowed">Unavailable</button>
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
