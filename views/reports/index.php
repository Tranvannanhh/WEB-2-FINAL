<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Booking.php';
require_once __DIR__ . '/../../models/Facility.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Report.php';
require_once __DIR__ . '/../../models/Review.php';

requireAdmin();
$bookingModel  = new Booking();
$facilityModel = new Facility();
$userModel     = new User();
$reportModel   = new Report();
$reviewModel   = new Review();

$bookingModel->autoComplete();

// Stats
$totalBookings   = $bookingModel->getTotalCount();
$totalUsers      = $userModel->getTotalCount();
$totalFacilities = $facilityModel->getTotalCount();
$bookingsByStatus= $bookingModel->countByStatus();
$usersByRole     = $userModel->countByRole();
$facilityByType  = $facilityModel->countByType();
$monthlyStats    = $bookingModel->getMonthlyStats();
$reportStats     = $reportModel->countByStatus();
$recentReviews   = $reviewModel->getAll(10);

$pageTitle = 'Analytics & Reports';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<div class="app-shell">
<?php include __DIR__ . '/../../includes/sidebar.php'; ?>
<div class="main-content" id="mainContent">
<?php include __DIR__ . '/../../includes/navbar.php'; ?>
<div class="page-content">

<div class="page-header">
  <div class="page-header-left">
    <h1><i class="fas fa-chart-bar me-2 text-primary"></i>Analytics & Reports</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/views/dashboard/admin.php">Dashboard</a></li>
      <li class="breadcrumb-item active">Analytics</li>
    </ol></nav>
  </div>
  <span class="badge bg-primary px-3 py-2"><i class="fas fa-calendar me-1"></i><?= date('Y') ?></span>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
  <?php
  $kpis = [
    ['Total Bookings',  $totalBookings,                    'calendar-check','blue',  'All time'],
    ['Approved',        $bookingsByStatus['approved']??0,  'check-circle',  'green', 'Approved bookings'],
    ['Pending',         $bookingsByStatus['pending']??0,   'clock',         'orange','Awaiting review'],
    ['Open Issues',     ($reportStats['open']??0)+($reportStats['in_progress']??0),'exclamation-triangle','red','Facility reports'],
  ];
  foreach ($kpis as [$label,$val,$icon,$color,$sub]):
  ?>
  <div class="col-sm-6 col-xl-3" data-aos="fade-up">
    <div class="stat-card <?= $color ?>">
      <div class="stat-icon <?= $color ?>"><i class="fas fa-<?= $icon ?>"></i></div>
      <div><div class="stat-value"><?= number_format($val) ?></div><div class="stat-label"><?= $label ?></div><div class="stat-change"><?= $sub ?></div></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Charts Row 1 -->
<div class="row g-3 mb-4">
  <div class="col-xl-8" data-aos="fade-up">
    <div class="card h-100">
      <div class="card-header"><i class="fas fa-chart-line me-2 text-primary"></i>Monthly Bookings (<?= date('Y') ?>)</div>
      <div class="card-body"><canvas id="monthlyChart" height="100"></canvas></div>
    </div>
  </div>
  <div class="col-xl-4" data-aos="fade-up" data-aos-delay="60">
    <div class="card h-100">
      <div class="card-header"><i class="fas fa-users me-2 text-primary"></i>Users by Role</div>
      <div class="card-body d-flex align-items-center justify-content-center">
        <canvas id="usersChart" height="200"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Charts Row 2 -->
<div class="row g-3 mb-4">
  <div class="col-xl-4" data-aos="fade-up">
    <div class="card h-100">
      <div class="card-header"><i class="fas fa-chart-pie me-2 text-primary"></i>Booking Status</div>
      <div class="card-body d-flex align-items-center justify-content-center">
        <canvas id="statusChart" height="200"></canvas>
      </div>
    </div>
  </div>
  <div class="col-xl-4" data-aos="fade-up" data-aos-delay="60">
    <div class="card h-100">
      <div class="card-header"><i class="fas fa-building me-2 text-primary"></i>Facilities by Type</div>
      <div class="card-body"><canvas id="facilityChart" height="200"></canvas></div>
    </div>
  </div>
  <div class="col-xl-4" data-aos="fade-up" data-aos-delay="120">
    <div class="card h-100">
      <div class="card-header"><i class="fas fa-exclamation-triangle me-2 text-danger"></i>Issue Reports</div>
      <div class="card-body d-flex align-items-center justify-content-center">
        <canvas id="reportsChart" height="200"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Recent Reviews -->
<div class="card" data-aos="fade-up">
  <div class="card-header"><i class="fas fa-star me-2 text-warning"></i>Recent Reviews</div>
  <div class="card-body p-0">
    <?php if (empty($recentReviews)): ?>
    <p class="text-center text-muted py-4 small">No reviews yet.</p>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover datatable mb-0">
        <thead><tr><th>User</th><th>Facility</th><th>Rating</th><th>Comment</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($recentReviews as $r): ?>
        <tr>
          <td class="small fw-semibold"><?= sanitize($r['full_name']) ?></td>
          <td class="small"><?= sanitize($r['facility_name']) ?></td>
          <td>
            <span class="text-warning"><?= str_repeat('★',$r['rating']) ?></span>
            <span class="text-muted"><?= str_repeat('☆',5-$r['rating']) ?></span>
            <span class="small ms-1"><?= $r['rating'] ?>/5</span>
          </td>
          <td class="small text-muted"><?= sanitize(truncate($r['comment']??'—',60)) ?></td>
          <td class="small text-muted"><?= timeAgo($r['created_at']) ?></td>
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

<?php
$months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
$mApproved = $mPending = $mRejected = array_fill(0,12,0);
foreach ($monthlyStats as $row) {
    $idx = $row['month']-1;
    $mApproved[$idx] = (int)$row['approved'];
    $mPending[$idx]  = (int)$row['pending'];
    $mRejected[$idx] = (int)$row['rejected'];
}
$ftLabels = $ftData = [];
foreach ($facilityByType as $t=>$c) { $ftLabels[] = ucfirst(str_replace('_',' ',$t)); $ftData[] = $c; }
?>
<script>
const APP_URL = "<?= APP_URL ?>";

new Chart(document.getElementById('monthlyChart'), {
  type: 'line',
  data: {
    labels: <?= json_encode($months) ?>,
    datasets: [
      { label: 'Approved', data: <?= json_encode($mApproved) ?>, borderColor: '#10B981', backgroundColor: 'rgba(16,185,129,.1)', tension: .4, fill: true },
      { label: 'Pending',  data: <?= json_encode($mPending) ?>,  borderColor: '#F59E0B', backgroundColor: 'rgba(245,158,11,.1)',  tension: .4, fill: true },
      { label: 'Rejected', data: <?= json_encode($mRejected) ?>, borderColor: '#EF4444', backgroundColor: 'rgba(239,68,68,.1)',   tension: .4, fill: true },
    ]
  },
  options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
});

new Chart(document.getElementById('usersChart'), {
  type: 'doughnut',
  data: {
    labels: ['Students','Lecturers','Admins'],
    datasets: [{ data: [<?= $usersByRole['student']??0 ?>, <?= $usersByRole['lecturer']??0 ?>, <?= $usersByRole['admin']??0 ?>],
      backgroundColor: ['#2563EB','#10B981','#EF4444'], borderWidth: 0 }]
  },
  options: { responsive: true, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 10 } } } }
});

new Chart(document.getElementById('statusChart'), {
  type: 'pie',
  data: {
    labels: ['Pending','Approved','Rejected','Cancelled','Completed'],
    datasets: [{ data: [<?= implode(',', [$bookingsByStatus['pending']??0, $bookingsByStatus['approved']??0, $bookingsByStatus['rejected']??0, $bookingsByStatus['cancelled']??0, $bookingsByStatus['completed']??0]) ?>],
      backgroundColor: ['#F59E0B','#10B981','#EF4444','#94A3B8','#06B6D4'], borderWidth: 0 }]
  },
  options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { boxWidth: 10 } } } }
});

new Chart(document.getElementById('facilityChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($ftLabels) ?>,
    datasets: [{ label: 'Count', data: <?= json_encode($ftData) ?>, backgroundColor: '#2563EB', borderRadius: 6 }]
  },
  options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});

new Chart(document.getElementById('reportsChart'), {
  type: 'doughnut',
  data: {
    labels: ['Open','In Progress','Resolved'],
    datasets: [{ data: [<?= ($reportStats['open']??0) ?>, <?= ($reportStats['in_progress']??0) ?>, <?= ($reportStats['resolved']??0) ?>],
      backgroundColor: ['#EF4444','#F59E0B','#10B981'], borderWidth: 0 }]
  },
  options: { responsive: true, cutout: '60%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 10 } } } }
});
</script>
