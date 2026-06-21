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

$facility  = $facilityModel->findById($id);
if (!$facility) { flashMessage('danger','Facility not found.'); redirect(APP_URL.'/views/facilities/index.php'); }

$equipment = $equipmentModel->getByFacility($id);
$reviews   = $reviewModel->getByFacility($id, 10);
$ratingInfo= $reviewModel->getFacilityAverageRating($id);

$pageTitle = sanitize($facility['facility_name']);

// Admin uses admin layout with full detail
if ($_SESSION['role'] === 'admin') {
    include __DIR__ . '/../../includes/header.php';
    echo '<div class="app-shell">';
    include __DIR__ . '/../../includes/sidebar.php';
    echo '<div class="main-content" id="mainContent">';
    include __DIR__ . '/../../includes/navbar.php';
    ?>
    <div class="page-content">
      <div class="page-header">
        <div class="page-header-left">
          <h1><i class="fas fa-<?= getFacilityIcon($facility['facility_type']) ?> me-2 text-primary"></i><?= sanitize($facility['facility_name']) ?></h1>
          <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= APP_URL ?>/views/dashboard/admin.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= APP_URL ?>/views/facilities/manage.php">Facilities</a></li>
            <li class="breadcrumb-item active"><?= sanitize($facility['facility_name']) ?></li>
          </ol></nav>
        </div>
        <div class="d-flex gap-2">
          <a href="<?= APP_URL ?>/views/facilities/form.php?id=<?= $id ?>" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-edit me-1"></i>Edit
          </a>
          <?php if ($facility['status'] === 'available'): ?>
          <a href="<?= APP_URL ?>/views/bookings/create.php?facility_id=<?= $id ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-calendar-plus me-1"></i>Book
          </a>
          <?php endif; ?>
          <a href="<?= APP_URL ?>/views/facilities/manage.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
          </a>
        </div>
      </div>
      <?= displayFlash() ?>

      <div class="row g-4">
        <!-- LEFT: Info + Reviews -->
        <div class="col-lg-8">

          <!-- Hero Image -->
          <?php if (!empty($facility['image_path'])): ?>
          <div class="card mb-4" style="overflow:hidden">
            <img src="<?= facilityImgSrc($facility['image_path']) ?>" alt="<?= sanitize($facility['facility_name']) ?>"
                 style="width:100%;height:280px;object-fit:cover">
          </div>
          <?php endif; ?>

          <!-- Details Card -->
          <div class="card mb-4">
            <div class="card-header"><i class="fas fa-info-circle me-2 text-primary"></i>Facility Details</div>
            <div class="card-body">
              <div class="row g-3">
                <div class="col-sm-6">
                  <div class="text-muted small mb-1">Type</div>
                  <div class="fw-semibold"><i class="fas fa-<?= getFacilityIcon($facility['facility_type']) ?> me-1 text-primary"></i><?= ucfirst(str_replace('_',' ',$facility['facility_type'])) ?></div>
                </div>
                <div class="col-sm-6">
                  <div class="text-muted small mb-1">Status</div>
                  <?= getStatusBadge($facility['status']) ?>
                </div>
                <div class="col-sm-6">
                  <div class="text-muted small mb-1">Capacity</div>
                  <div class="fw-semibold"><i class="fas fa-users me-1 text-primary"></i><?= $facility['capacity'] ?> seats</div>
                </div>
                <div class="col-sm-6">
                  <div class="text-muted small mb-1">Location</div>
                  <div class="fw-semibold"><i class="fas fa-map-marker-alt me-1 text-primary"></i><?= sanitize($facility['location']) ?></div>
                </div>
                <?php if (!empty($facility['description'])): ?>
                <div class="col-12">
                  <div class="text-muted small mb-1">Description</div>
                  <div class="small" style="line-height:1.7"><?= nl2br(sanitize($facility['description'])) ?></div>
                </div>
                <?php endif; ?>
                <?php if ($ratingInfo['total'] > 0): ?>
                <div class="col-sm-6">
                  <div class="text-muted small mb-1">Rating</div>
                  <div class="fw-semibold">
                    <span class="text-warning"><?= str_repeat('★', round($ratingInfo['avg_rating'])) ?></span>
                    <?= number_format($ratingInfo['avg_rating'],1) ?>/5
                    <span class="text-muted small">(<?= $ratingInfo['total'] ?> reviews)</span>
                  </div>
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Reviews -->
          <div class="card">
            <div class="card-header"><i class="fas fa-star me-2 text-warning"></i>Reviews (<?= count($reviews) ?>)</div>
            <div class="card-body p-0">
              <?php if (empty($reviews)): ?>
              <p class="text-center text-muted py-4 small">No reviews yet.</p>
              <?php else: ?>
              <table class="table table-hover mb-0">
                <thead><tr><th>User</th><th>Rating</th><th>Comment</th><th>Date</th></tr></thead>
                <tbody>
                <?php foreach ($reviews as $r): ?>
                <tr>
                  <td class="small fw-semibold"><?= sanitize($r['full_name']) ?></td>
                  <td><span class="text-warning"><?= str_repeat('★',$r['rating']) ?></span> <span class="small text-muted"><?= $r['rating'] ?>/5</span></td>
                  <td class="small text-muted"><?= sanitize(truncate($r['comment']??'—',60)) ?></td>
                  <td class="small text-muted"><?= timeAgo($r['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- RIGHT: Equipment -->
        <div class="col-lg-4">
          <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
              <span><i class="fas fa-tools me-2 text-primary"></i>Equipment (<?= count($equipment) ?>)</span>
              <a href="<?= APP_URL ?>/views/facilities/equipment.php?facility_id=<?= $id ?>" class="btn btn-xs btn-outline-primary">Manage</a>
            </div>
            <div class="card-body p-0">
              <?php if (empty($equipment)): ?>
              <p class="text-center text-muted py-3 small">No equipment listed.</p>
              <?php else: ?>
              <ul class="list-group list-group-flush">
                <?php foreach ($equipment as $eq): ?>
                <li class="list-group-item d-flex align-items-center justify-content-between py-2">
                  <div>
                    <div class="small fw-semibold"><?= sanitize($eq['equipment_name']) ?></div>
                    <div class="text-muted" style="font-size:.72rem">Qty: <?= $eq['quantity'] ?></div>
                  </div>
                  <?= getStatusBadge($eq['status']) ?>
                </li>
                <?php endforeach; ?>
              </ul>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php
    include __DIR__ . '/../../includes/footer.php';
    echo '</div></div>';
    exit;
}
?>
<?php include __DIR__ . '/../../includes/user_header.php'; ?>
<?php include __DIR__ . '/../../includes/user_navbar.php'; ?>

<div class="u-page">

  <!-- Facility Hero Image -->
  <div class="container" style="padding-top:30px">
    <div class="u-breadcrumb mb-3">
      <a href="<?= APP_URL ?>/views/dashboard/index.php">Home</a>
      <span class="u-breadcrumb-sep">›</span>
      <a href="<?= APP_URL ?>/views/facilities/index.php">Facilities</a>
      <span class="u-breadcrumb-sep">›</span>
      <span><?= sanitize($facility['facility_name']) ?></span>
    </div>
  </div>

  <div class="container pb-5">

    <?= displayFlash() ?>

    <div class="row g-4">

      <!-- LEFT: Main content -->
      <div class="col-lg-8" data-aos="fade-up">

        <!-- Hero image card -->
        <div class="u-card mb-4" style="overflow:hidden">
          <div class="u-facility-hero">
            <?php if (!empty($facility['image_path'])): ?>
            <img src="<?= facilityImgSrc($facility['image_path']) ?>" alt="">
            <?php else: ?>
            <div style="display:flex;align-items:center;justify-content:center;height:100%">
              <i class="fas fa-<?= getFacilityIcon($facility['facility_type']) ?>" style="font-size:6rem;color:rgba(255,255,255,.2)"></i>
            </div>
            <?php endif; ?>
            <div class="u-facility-hero-overlay"></div>
            <div class="u-facility-hero-info">
              <div>
                <h1 class="u-facility-hero-name"><?= sanitize($facility['facility_name']) ?></h1>
                <div style="color:rgba(255,255,255,.7);font-size:.85rem;margin-top:4px">
                  <i class="fas fa-map-marker-alt me-1"></i><?= sanitize($facility['location']) ?>
                </div>
              </div>
              <div class="d-flex gap-2 flex-wrap">
                <span class="u-chip u-chip-white">
                  <i class="fas fa-<?= getFacilityIcon($facility['facility_type']) ?>"></i>
                  <?= ucfirst(str_replace('_',' ',$facility['facility_type'])) ?>
                </span>
                <?php if ($facility['status'] === 'available'): ?>
                <span class="u-chip u-chip-green"><i class="fas fa-check"></i> Available</span>
                <?php else: ?>
                <span class="u-chip u-chip-amber"><?= ucfirst($facility['status']) ?></span>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Quick stats row -->
          <div class="row g-0 border-top">
            <div class="col-4 text-center py-4 border-end">
              <div style="font-size:1.5rem;font-weight:800;color:var(--u-primary)"><?= $facility['capacity'] ?></div>
              <div style="font-size:.75rem;color:var(--u-gray);margin-top:2px">Seats</div>
            </div>
            <div class="col-4 text-center py-4 border-end">
              <?php if ($ratingInfo['total'] > 0): ?>
              <div style="font-size:1.5rem;font-weight:800;color:var(--u-primary)"><?= number_format($ratingInfo['avg_rating'],1) ?></div>
              <div style="font-size:.75rem;color:var(--u-gray);margin-top:2px"><?= $ratingInfo['total'] ?> Reviews</div>
              <?php else: ?>
              <div style="font-size:1rem;font-weight:600;color:#94A3B8;padding-top:6px">No reviews</div>
              <div style="font-size:.75rem;color:var(--u-gray);margin-top:2px">Yet</div>
              <?php endif; ?>
            </div>
            <div class="col-4 text-center py-4">
              <div style="font-size:1.5rem;font-weight:800;color:var(--u-primary)"><?= count($equipment) ?></div>
              <div style="font-size:.75rem;color:var(--u-gray);margin-top:2px">Equipment</div>
            </div>
          </div>
        </div>

        <!-- Description -->
        <?php if (!empty($facility['description'])): ?>
        <div class="u-card mb-4">
          <div class="u-card-header">About This Facility</div>
          <div class="u-card-body">
            <p style="color:var(--u-gray);line-height:1.8;font-size:.9rem;margin:0">
              <?= nl2br(sanitize($facility['description'])) ?>
            </p>
          </div>
        </div>
        <?php endif; ?>

        <!-- Reviews -->
        <div class="u-card">
          <div class="u-card-header">
            <span><i class="fas fa-star"></i> Reviews & Ratings</span>
            <?php if ($ratingInfo['total'] > 0): ?>
            <span style="color:var(--u-gold);font-weight:700">
              <?= str_repeat('★', round($ratingInfo['avg_rating'])) ?><?= str_repeat('☆', 5-round($ratingInfo['avg_rating'])) ?>
              &nbsp;<?= number_format($ratingInfo['avg_rating'],1) ?>
            </span>
            <?php endif; ?>
          </div>
          <div class="u-card-body">
            <?php if (empty($reviews)): ?>
            <div class="u-empty" style="padding:32px 0">
              <i class="fas fa-star d-block"></i>
              <p>No reviews yet. Be the first to share your experience!</p>
            </div>
            <?php else: ?>
            <?php foreach ($reviews as $r): ?>
            <div class="u-review-item">
              <div class="u-review-avatar">
                <?= strtoupper(substr($r['full_name'],0,1)) ?>
              </div>
              <div style="flex:1">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px">
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

      <!-- RIGHT: Sidebar actions -->
      <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">

        <!-- Book CTA card -->
        <?php if ($facility['status'] === 'available'): ?>
        <div class="u-card mb-4" style="border:2px solid var(--u-gold)">
          <div class="u-card-body text-center py-4">
            <div style="font-size:2.5rem;color:var(--u-gold);margin-bottom:12px">
              <i class="fas fa-calendar-check"></i>
            </div>
            <h5 style="font-family:var(--u-font-serif);font-weight:700;margin-bottom:8px">Reserve This Space</h5>
            <p style="color:var(--u-gray);font-size:.85rem;margin-bottom:20px">
              This facility is available for booking right now.
            </p>
            <a href="<?= APP_URL ?>/views/bookings/create.php?facility_id=<?= $id ?>" class="u-btn u-btn-gold u-btn-lg w-100" style="justify-content:center">
              <i class="fas fa-calendar-plus"></i> Book Now
            </a>
          </div>
        </div>
        <?php else: ?>
        <div class="u-card mb-4">
          <div class="u-card-body text-center py-4">
            <i class="fas fa-tools" style="font-size:2rem;color:#94A3B8;margin-bottom:12px;display:block"></i>
            <p style="color:var(--u-gray);font-size:.85rem;margin:0">
              This facility is currently <strong><?= $facility['status'] ?></strong> and not available for booking.
            </p>
          </div>
        </div>
        <?php endif; ?>

        <!-- Equipment -->
        <div class="u-card mb-4">
          <div class="u-card-header"><span><i class="fas fa-tools"></i> Equipment</span></div>
          <?php if (empty($equipment)): ?>
          <div class="u-card-body">
            <p style="color:var(--u-gray);font-size:.84rem;text-align:center">No equipment listed.</p>
          </div>
          <?php else: ?>
          <ul style="list-style:none;margin:0;padding:0">
            <?php foreach ($equipment as $eq): ?>
            <li style="display:flex;align-items:center;justify-content:space-between;padding:11px 20px;border-bottom:1px solid var(--u-border)">
              <div>
                <div style="font-size:.85rem;font-weight:600"><?= sanitize($eq['equipment_name']) ?></div>
                <div style="font-size:.73rem;color:var(--u-gray)">Qty: <?= $eq['quantity'] ?></div>
              </div>
              <?= getStatusBadge($eq['status']) ?>
            </li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </div>

        <!-- Report issue -->
        <div class="u-card">
          <div class="u-card-body">
            <a href="<?= APP_URL ?>/views/reports/facility.php?facility_id=<?= $id ?>"
               class="u-btn w-100" style="justify-content:center;border:2px solid #EF4444;color:#EF4444;border-radius:50px;padding:9px;font-weight:600;font-size:.84rem;background:transparent;transition:all .25s">
              <i class="fas fa-exclamation-triangle me-1"></i> Report an Issue
            </a>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/user_footer.php'; ?>
<script>const APP_URL = "<?= APP_URL ?>";</script>
