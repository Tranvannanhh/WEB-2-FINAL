<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Review.php';

requireAdmin();
$reviewModel = new Review();

$ratingFilter = $_GET['rating'] ?? '';
$searchFilter = trim($_GET['search'] ?? '');

// Get all reviews then filter
$allReviews = $reviewModel->getAll(200);

// Count by rating
$countByRating = [1=>0, 2=>0, 3=>0, 4=>0, 5=>0];
$totalReviews  = count($allReviews);
$sumRating     = 0;
foreach ($allReviews as $r) {
    $countByRating[(int)$r['rating']]++;
    $sumRating += $r['rating'];
}
$avgRating = $totalReviews > 0 ? round($sumRating / $totalReviews, 1) : 0;

// Apply filters
$reviews = array_filter($allReviews, function($r) use ($ratingFilter, $searchFilter) {
    if ($ratingFilter !== '' && (int)$r['rating'] !== (int)$ratingFilter) return false;
    if ($searchFilter !== '' &&
        stripos($r['full_name'], $searchFilter) === false &&
        stripos($r['facility_name'], $searchFilter) === false &&
        stripos($r['comment'] ?? '', $searchFilter) === false) return false;
    return true;
});

$pageTitle = 'Feedbacks';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<div class="app-shell">
<?php include __DIR__ . '/../../includes/sidebar.php'; ?>
<div class="main-content" id="mainContent">
<?php include __DIR__ . '/../../includes/navbar.php'; ?>
<div class="page-content">

<div class="page-header">
  <div class="page-header-left">
    <h1><i class="fas fa-star me-2 text-warning"></i>Feedbacks</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/views/dashboard/admin.php">Dashboard</a></li>
      <li class="breadcrumb-item active">Feedbacks</li>
    </ol></nav>
  </div>
</div>

<?= displayFlash() ?>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
  <div class="col-xl-3 col-md-6" data-aos="fade-up">
    <div class="stat-card blue">
      <div class="stat-icon blue"><i class="fas fa-comments"></i></div>
      <div>
        <div class="stat-value"><?= $totalReviews ?></div>
        <div class="stat-label">Total Reviews</div>
        <div class="stat-change up">All facilities</div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6" data-aos="fade-up" data-aos-delay="60">
    <div class="stat-card green">
      <div class="stat-icon green"><i class="fas fa-star"></i></div>
      <div>
        <div class="stat-value"><?= $avgRating ?></div>
        <div class="stat-label">Average Rating</div>
        <div class="stat-change up">Out of 5.0</div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6" data-aos="fade-up" data-aos-delay="120">
    <div class="stat-card orange">
      <div class="stat-icon orange"><i class="fas fa-thumbs-up"></i></div>
      <div>
        <div class="stat-value"><?= ($countByRating[4] + $countByRating[5]) ?></div>
        <div class="stat-label">Positive (4–5★)</div>
        <div class="stat-change up">High ratings</div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6" data-aos="fade-up" data-aos-delay="180">
    <div class="stat-card red">
      <div class="stat-icon red"><i class="fas fa-thumbs-down"></i></div>
      <div>
        <div class="stat-value"><?= ($countByRating[1] + $countByRating[2]) ?></div>
        <div class="stat-label">Negative (1–2★)</div>
        <div class="stat-change down">Needs attention</div>
      </div>
    </div>
  </div>
</div>

<!-- Rating Filter + Search -->
<div class="card mb-3" data-aos="fade-up">
  <div class="card-body py-3">
    <form method="GET">
      <div class="row g-2 align-items-end">
        <div class="col-md-5">
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" class="form-control" name="search"
                   value="<?= sanitize($searchFilter) ?>"
                   placeholder="Search user, facility, comment…">
          </div>
        </div>
        <div class="col-md-4">
          <div class="d-flex gap-2 flex-wrap">
            <a href="?" class="btn btn-sm <?= $ratingFilter===''?'btn-warning':'btn-outline-secondary' ?>">All</a>
            <?php for ($s = 5; $s >= 1; $s--): ?>
            <a href="?rating=<?= $s ?><?= $searchFilter ? '&search='.urlencode($searchFilter) : '' ?>"
               class="btn btn-sm <?= (string)$ratingFilter===(string)$s?'btn-warning':'btn-outline-secondary' ?>">
              <?= str_repeat('★', $s) ?>
              <span class="ms-1 text-muted" style="font-size:.7rem">(<?= $countByRating[$s] ?>)</span>
            </a>
            <?php endfor; ?>
          </div>
        </div>
        <div class="col-md-3 d-flex gap-2">
          <button class="btn btn-primary flex-fill" type="submit"><i class="fas fa-search me-1"></i>Search</button>
          <a href="?" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Reviews Table -->
<div class="card" data-aos="fade-up">
  <div class="card-header d-flex align-items-center justify-content-between">
    <span><i class="fas fa-list me-2 text-warning"></i>Reviews
      <span class="badge bg-secondary ms-2"><?= count($reviews) ?></span>
    </span>
  </div>
  <div class="card-body p-0">
    <?php if (empty($reviews)): ?>
    <div class="text-center py-5 text-muted">
      <i class="fas fa-star fa-2x mb-2 text-secondary"></i><br>
      <span class="small">No reviews found.</span>
    </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover datatable mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>User</th>
            <th>Facility</th>
            <th>Rating</th>
            <th>Comment</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
        <?php $i = 1; foreach ($reviews as $r): ?>
        <tr>
          <td class="small text-muted"><?= $i++ ?></td>
          <td class="small fw-semibold"><?= sanitize($r['full_name']) ?></td>
          <td class="small"><?= sanitize($r['facility_name']) ?></td>
          <td>
            <span class="text-warning fw-bold" style="letter-spacing:1px">
              <?= str_repeat('★', (int)$r['rating']) ?><?= str_repeat('☆', 5 - (int)$r['rating']) ?>
            </span>
            <span class="small text-muted ms-1"><?= $r['rating'] ?>/5</span>
          </td>
          <td class="small text-muted" style="max-width:280px">
            <?= sanitize(truncate($r['comment'] ?? '—', 80)) ?>
          </td>
          <td class="small text-muted white-space:nowrap"><?= timeAgo($r['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
</div>
</div>
<script>const APP_URL = "<?= APP_URL ?>";</script>
