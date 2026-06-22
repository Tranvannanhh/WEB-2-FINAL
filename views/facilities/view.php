<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Facility.php';
require_once __DIR__ . '/../../models/Equipment.php';
require_once __DIR__ . '/../../models/Review.php';

requireLogin();
$id = intval($_GET['id'] ?? 0);
$facilityModel  = new Facility();
$equipmentModel = new Equipment();
$reviewModel    = new Review();

$facility = $facilityModel->findById($id);
if (!$facility) { flashMessage('danger','Facility not found.'); redirect(APP_URL.'/views/facilities/index.php'); }

$equipment  = $equipmentModel->getByFacility($id);
$reviews    = $reviewModel->getByFacility($id, 10);
$ratingInfo = $reviewModel->getFacilityAverageRating($id);
$pageTitle  = sanitize($facility['facility_name']);

// Admin: minimal layout
if ($_SESSION['role'] === 'admin') {
    include __DIR__ . '/../../includes/header.php';
    echo '<div class="app-shell">';
    include __DIR__ . '/../../includes/sidebar.php';
    echo '<div class="main-content" id="mainContent">';
    include __DIR__ . '/../../includes/navbar.php';
    echo '<div class="page-content">';
    echo '<div class="page-header"><div class="page-header-left"><h1>'.sanitize($facility['facility_name']).'</h1></div>';
    if ($facility['status'] === 'available') echo '<a href="'.APP_URL.'/views/bookings/create.php?facility_id='.$id.'" class="btn btn-primary"><i class="fas fa-calendar-plus me-2"></i>Book</a>';
    echo '</div>'.displayFlash().'<p class="text-muted">'.sanitize($facility['location']).' · '.sanitize($facility['facility_type']).' · '.$facility['capacity'].' seats</p>';
    include __DIR__ . '/../../includes/footer.php';
    echo '</div></div>';
    exit;
}
?>
<?php include __DIR__ . '/../../includes/user_header.php'; ?>
<?php include __DIR__ . '/../../includes/user_navbar.php'; ?>

<div class="u-page">
  <div style="padding-top:28px">
    <div class="container">
      <div class="u-bc">
        <a href="<?= APP_URL ?>/views/dashboard/index.php">Home</a><span class="u-bc-sep" style="color:#94a3b8">›</span>
        <a href="<?= APP_URL ?>/views/facilities/index.php">Facilities</a><span class="u-bc-sep" style="color:#94a3b8">›</span>
        <span style="color:var(--muted)"><?= sanitize($facility['facility_name']) ?></span>
      </div>
    </div>
  </div>

  <div class="u-content" style="padding-top:20px">
    <div class="container">
      <?= displayFlash() ?>

      <div class="row g-4">

        <!-- LEFT -->
        <div class="col-lg-8" data-aos="fade-up">
          <div class="u-card mb-4" style="overflow:hidden">
            <div class="u-facility-hero">
              <?php if (!empty($facility['image_path'])): ?>
              <img src="<?= getFacilityImageUrl($facility['image_path']) ?>" alt="">
              <?php else: ?>
              <div style="display:flex;align-items:center;justify-content:center;height:100%;font-size:6rem;color:rgba(255,255,255,.15)">
                <i class="fas fa-<?= getFacilityIcon($facility['facility_type']) ?>"></i>
              </div>
              <?php endif; ?>
              <div class="u-facility-hero-overlay"></div>
              <div class="u-facility-hero-info">
                <div>
                  <div class="u-facility-hero-name"><?= sanitize($facility['facility_name']) ?></div>
                  <div style="color:rgba(255,255,255,.65);font-size:.84rem;margin-top:4px">
                    <i class="fas fa-map-marker-alt me-1"></i><?= sanitize($facility['location']) ?>
                  </div>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap">
                  <span class="u-chip u-chip-white"><i class="fas fa-<?= getFacilityIcon($facility['facility_type']) ?>"></i><?= ucfirst(str_replace('_',' ',$facility['facility_type'])) ?></span>
                  <?php if ($facility['status'] === 'available'): ?>
                  <span class="u-chip u-chip-green"><i class="fas fa-check"></i>Available</span>
                  <?php else: ?>
                  <span class="u-chip u-chip-amber"><?= ucfirst($facility['status']) ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <!-- Stats row -->
            <div class="row g-0" style="border-top:1px solid var(--border)">
              <div class="col-4 text-center py-4" style="border-right:1px solid var(--border)">
                <div style="font-size:1.6rem;font-weight:800;color:var(--text)"><?= $facility['capacity'] ?></div>
                <div style="font-size:.75rem;color:var(--muted);margin-top:2px">Seats</div>
              </div>
              <div class="col-4 text-center py-4" style="border-right:1px solid var(--border)">
                <?php if ($ratingInfo['total'] > 0): ?>
                <div style="font-size:1.6rem;font-weight:800;color:var(--text)"><?= number_format($ratingInfo['avg_rating'],1) ?></div>
                <div style="font-size:.75rem;color:var(--muted);margin-top:2px"><?= $ratingInfo['total'] ?> Reviews</div>
                <?php else: ?>
                <div style="font-size:.9rem;font-weight:600;color:#94a3b8;padding-top:8px">—</div>
                <div style="font-size:.75rem;color:var(--muted);margin-top:2px">No reviews</div>
                <?php endif; ?>
              </div>
              <div class="col-4 text-center py-4">
                <div style="font-size:1.6rem;font-weight:800;color:var(--text)"><?= count($equipment) ?></div>
                <div style="font-size:.75rem;color:var(--muted);margin-top:2px">Equipment</div>
              </div>
            </div>
          </div>

          <?php if (!empty($facility['description'])): ?>
          <div class="u-card mb-4">
            <div class="u-card-hd">About This Facility</div>
            <div class="u-card-body">
              <p style="color:var(--muted);line-height:1.8;font-size:.9rem;margin:0"><?= nl2br(sanitize($facility['description'])) ?></p>
            </div>
          </div>
          <?php endif; ?>

          <!-- Reviews -->
          <div class="u-card">
            <div class="u-card-hd">
              <span><i class="fas fa-star"></i> Reviews & Ratings</span>
              <?php if ($ratingInfo['total'] > 0): ?>
              <span style="color:var(--amber);font-weight:700">
                <?= str_repeat('★', round($ratingInfo['avg_rating'])) ?><?= str_repeat('☆', 5-round($ratingInfo['avg_rating'])) ?>
                &nbsp;<?= number_format($ratingInfo['avg_rating'],1) ?>
              </span>
              <?php endif; ?>
            </div>
            <div class="u-card-body">
              <?php if (empty($reviews)): ?>
              <div class="u-empty" style="padding:32px 0">
                <i class="fas fa-star u-empty-icon" style="font-size:2rem"></i>
                <p>No reviews yet. Be the first to share your experience!</p>
              </div>
              <?php else: ?>
              <?php foreach ($reviews as $r): ?>
              <div class="u-review">
                <div class="u-review-avt"><?= strtoupper(substr($r['full_name'],0,1)) ?></div>
                <div style="flex:1">
                  <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                    <span class="u-review-name"><?= sanitize($r['full_name']) ?></span>
                    <span class="u-review-stars"><?= str_repeat('★',$r['rating']) ?><?= str_repeat('☆',5-$r['rating']) ?></span>
                  </div>
                  <?php if (!empty($r['comment'])): ?>
                  <p class="u-review-text"><?= sanitize($r['comment']) ?></p>
                  <?php endif; ?>
                  <div class="u-review-time"><i class="fas fa-clock me-1"></i><?= timeAgo($r['created_at']) ?></div>
                </div>
              </div>
              <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- RIGHT sidebar -->
        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="80">
          <?php if ($facility['status'] === 'available'): ?>
          <div class="u-card mb-4" style="border:2px solid var(--gold)">
            <div class="u-card-body text-center" style="padding:28px 22px">
              <div style="font-size:2.8rem;color:var(--gold);margin-bottom:14px"><i class="fas fa-calendar-check"></i></div>
              <h5 style="font-weight:800;margin-bottom:8px">Reserve This Space</h5>
              <p style="color:var(--muted);font-size:.85rem;margin-bottom:20px">This facility is available for booking right now.</p>
              <a href="<?= APP_URL ?>/views/bookings/create.php?facility_id=<?= $id ?>" class="u-btn u-btn-gold u-btn-lg u-btn-block">
                <i class="fas fa-calendar-plus"></i> Book Now
              </a>
            </div>
          </div>
          <?php else: ?>
          <div class="u-card mb-4">
            <div class="u-card-body text-center" style="padding:28px">
              <i class="fas fa-tools" style="font-size:2rem;color:#94a3b8;display:block;margin-bottom:12px"></i>
              <p style="color:var(--muted);font-size:.86rem;margin:0">
                Currently <strong><?= $facility['status'] ?></strong> — not available for booking.
              </p>
            </div>
          </div>
          <?php endif; ?>

          <!-- Equipment -->
          <div class="u-card mb-4">
            <div class="u-card-hd"><span><i class="fas fa-tools"></i> Equipment</span></div>
            <?php if (empty($equipment)): ?>
            <div class="u-card-body"><p style="color:var(--muted);font-size:.84rem;text-align:center;margin:0">No equipment listed.</p></div>
            <?php else: ?>
            <ul style="list-style:none;margin:0;padding:0">
              <?php foreach ($equipment as $eq): ?>
              <li style="display:flex;align-items:center;justify-content:space-between;padding:10px 18px;border-bottom:1px solid var(--border)">
                <div>
                  <div style="font-size:.85rem;font-weight:700"><?= sanitize($eq['equipment_name']) ?></div>
                  <div style="font-size:.73rem;color:var(--muted)">Qty: <?= $eq['quantity'] ?></div>
                </div>
                <?= getStatusBadge($eq['status']) ?>
              </li>
              <?php endforeach; ?>
            </ul>
            <?php endif; ?>
          </div>

          <!-- Report -->
          <div class="u-card">
            <div class="u-card-body">
              <a href="<?= APP_URL ?>/views/reports/facility.php?facility_id=<?= $id ?>" class="u-btn u-btn-danger u-btn-block">
                <i class="fas fa-exclamation-triangle"></i> Report an Issue
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/user_footer.php'; ?>
