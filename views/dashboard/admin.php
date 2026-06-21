<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Booking.php';
require_once __DIR__ . '/../../models/Facility.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Report.php';

requireAdmin();
$bookingModel  = new Booking();
$facilityModel = new Facility();
$userModel     = new User();
$reportModel   = new Report();

$bookingModel->autoComplete();

$totalUsers     = $userModel->getTotalCount();
$totalFacilities= $facilityModel->getTotalCount();
$totalBookings  = $bookingModel->getTotalCount();
$pendingCount   = $bookingModel->getTotalCount('pending');
$bookingsByStatus = $bookingModel->countByStatus();
$facilityByType = $facilityModel->countByType();
$monthlyStats   = $bookingModel->getMonthlyStats();
$pendingBookings= $bookingModel->getPending();
$recentUsers    = $userModel->getRecentUsers(5);
$reportStats    = $reportModel->countByStatus();
$usersByRole    = $userModel->countByRole();

$pageTitle = 'Admin Dashboard';

// Build chart data trước
$weeklyStats = [];
$weekLabels  = [];
$weekApproved = [];
$weekPending  = [];
$weekRejected = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $label = date('D d/m', strtotime("-$i days"));
    $weekLabels[] = $label;
    $stmt = getDB()->prepare("SELECT
        SUM(CASE WHEN status='approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status='pending'  THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status='rejected' THEN 1 ELSE 0 END) as rejected
        FROM bookings WHERE booking_date = ?");
    $stmt->execute([$date]);
    $row = $stmt->fetch();
    $weekApproved[] = (int)($row['approved'] ?? 0);
    $weekPending[]  = (int)($row['pending']  ?? 0);
    $weekRejected[] = (int)($row['rejected'] ?? 0);
}
$statusLabels = ['Pending','Approved','Rejected','Cancelled','Completed'];
$statusData   = [
    $bookingsByStatus['pending']   ?? 0,
    $bookingsByStatus['approved']  ?? 0,
    $bookingsByStatus['rejected']  ?? 0,
    $bookingsByStatus['cancelled'] ?? 0,
    $bookingsByStatus['completed'] ?? 0,
];
$ftLabels = [];
$ftData   = [];
foreach ($facilityByType as $type => $cnt) {
    $ftLabels[] = ucfirst(str_replace('_', ' ', $type));
    $ftData[]   = $cnt;
}

$extraScripts = '<script>const APP_URL = "' . APP_URL . '";
new Chart(document.getElementById("bookingChart"), {
  type: "bar",
  data: {
    labels: ' . json_encode($weekLabels) . ',
    datasets: [
      { label: "Approved", data: ' . json_encode($weekApproved) . ', backgroundColor: "#10B981", borderRadius: 5 },
      { label: "Pending",  data: ' . json_encode($weekPending)  . ', backgroundColor: "#F59E0B", borderRadius: 5 },
      { label: "Rejected", data: ' . json_encode($weekRejected) . ', backgroundColor: "#EF4444", borderRadius: 5 },
    ]
  },
  options: { responsive: true, plugins: { legend: { position: "top" } }, scales: { x: { stacked: false }, y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});
new Chart(document.getElementById("statusChart"), {
  type: "doughnut",
  data: {
    labels: ' . json_encode($statusLabels) . ',
    datasets: [{ data: ' . json_encode($statusData) . ', backgroundColor: ["#F59E0B","#10B981","#EF4444","#94A3B8","#06B6D4"], borderWidth: 0, hoverOffset: 6 }]
  },
  options: { responsive: true, cutout: "65%", plugins: { legend: { position: "bottom", labels: { boxWidth: 10, font: { size: 11 } } } } }
});
new Chart(document.getElementById("facilityChart"), {
  type: "bar",
  data: {
    labels: ' . json_encode($ftLabels) . ',
    datasets: [{ label: "Count", data: ' . json_encode($ftData) . ', backgroundColor: "#2563EB", borderRadius: 6 }]
  },
  options: { indexAxis: "y", responsive: true, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});
</script>';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<div class="app-shell">
<?php include __DIR__ . '/../../includes/sidebar.php'; ?>
<div class="main-content" id="mainContent">
<?php include __DIR__ . '/../../includes/navbar.php'; ?>
<div class="page-content">

<!-- Page Header -->
<div class="page-header">
  <div class="page-header-left">
    <h1><i class="fas fa-tachometer-alt me-2 text-primary"></i>Admin Dashboard</h1>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item active">Dashboard</li>
      </ol>
    </nav>
  </div>
  <div>
    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#calendarModal">
      <i class="fas fa-calendar me-1"></i><?= date('D, d M Y') ?>
    </button>
  </div>
</div>

<?= displayFlash() ?>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
  <div class="col-xl-3 col-md-6" data-aos="fade-up" data-aos-delay="0">
    <div class="stat-card">
      <div class="stat-icon blue"><i class="fas fa-users"></i></div>
      <div>
        <div class="stat-value"><?= number_format($totalUsers) ?></div>
        <div class="stat-label">Total Users</div>
        <div class="stat-change up"><i class="fas fa-users me-1"></i>
          <?= ($usersByRole['student'] ?? 0) ?> students · <?= ($usersByRole['lecturer'] ?? 0) ?> lecturers
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6" data-aos="fade-up" data-aos-delay="60">
    <div class="stat-card green">
      <div class="stat-icon green"><i class="fas fa-building"></i></div>
      <div>
        <div class="stat-value"><?= number_format($totalFacilities) ?></div>
        <div class="stat-label">Total Facilities</div>
        <div class="stat-change up"><i class="fas fa-check-circle me-1"></i>Active campus resources</div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6" data-aos="fade-up" data-aos-delay="120">
    <div class="stat-card orange">
      <div class="stat-icon orange"><i class="fas fa-calendar-check"></i></div>
      <div>
        <div class="stat-value"><?= number_format($totalBookings) ?></div>
        <div class="stat-label">Total Bookings</div>
        <div class="stat-change <?= $pendingCount > 0 ? 'down' : 'up' ?>">
          <i class="fas fa-clock me-1"></i><?= $pendingCount ?> pending approval
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6" data-aos="fade-up" data-aos-delay="180">
    <div class="stat-card red">
      <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
      <div>
        <div class="stat-value"><?= ($reportStats['open'] ?? 0) + ($reportStats['in_progress'] ?? 0) ?></div>
        <div class="stat-label">Open Issues</div>
        <div class="stat-change down"><i class="fas fa-tools me-1"></i><?= $reportStats['in_progress'] ?? 0 ?> in progress</div>
      </div>
    </div>
  </div>
</div>

<!-- Charts Row -->
<div class="row g-3 mb-4">
  <div class="col-xl-8" data-aos="fade-up">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="fas fa-chart-line me-2 text-primary"></i>Weekly Booking Trends</span>
        <span class="badge bg-primary">Last 7 days</span>
      </div>
      <div class="card-body">
        <canvas id="bookingChart" height="90"></canvas>
      </div>
    </div>
  </div>
  <div class="col-xl-4" data-aos="fade-up" data-aos-delay="60">
    <div class="card h-100">
      <div class="card-header"><i class="fas fa-chart-pie me-2 text-primary"></i>Booking Status</div>
      <div class="card-body d-flex align-items-center justify-content-center">
        <canvas id="statusChart" height="180"></canvas>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-xl-4" data-aos="fade-up">
    <div class="card h-100">
      <div class="card-header"><i class="fas fa-building me-2 text-primary"></i>Facilities by Type</div>
      <div class="card-body">
        <canvas id="facilityChart" height="200"></canvas>
      </div>
    </div>
  </div>
  <div class="col-xl-8" data-aos="fade-up" data-aos-delay="60">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="fas fa-clock me-2 text-warning"></i>Pending Bookings</span>
        <a href="<?= APP_URL ?>/views/bookings/manage.php?status=pending" class="btn btn-sm btn-outline-primary">View All</a>
      </div>
      <div class="card-body p-0">
        <?php if (empty($pendingBookings)): ?>
        <div class="text-center py-5 text-muted">
          <i class="fas fa-check-circle fa-2x mb-2 text-success"></i><br>
          <span class="small">No pending bookings</span>
        </div>
        <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead><tr>
              <th>User</th><th>Facility</th><th>Date</th><th>Time</th><th>Action</th>
            </tr></thead>
            <tbody>
            <?php foreach (array_slice($pendingBookings, 0, 6) as $b): ?>
            <tr>
              <td>
                <div class="fw-semibold small"><?= sanitize($b['full_name']) ?></div>
                <div class="text-muted" style="font-size:.72rem"><?= sanitize($b['email']) ?></div>
              </td>
              <td>
                <div class="small fw-medium"><?= sanitize($b['facility_name']) ?></div>
                <div class="text-muted" style="font-size:.72rem"><?= sanitize($b['location']) ?></div>
              </td>
              <td class="small"><?= formatDate($b['booking_date']) ?></td>
              <td class="small"><?= formatTime($b['start_time']) ?>–<?= formatTime($b['end_time']) ?></td>
              <td>
                <a href="<?= APP_URL ?>/views/bookings/approve.php?id=<?= $b['id'] ?>" class="btn btn-xs btn-success me-1">
                  <i class="fas fa-check"></i>
                </a>
                <a href="<?= APP_URL ?>/views/bookings/view.php?id=<?= $b['id'] ?>" class="btn btn-xs btn-outline-primary">
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
</div>

<!-- Recent Users -->
<div class="row g-3">
  <div class="col-12" data-aos="fade-up">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="fas fa-user-plus me-2 text-primary"></i>Recently Registered Users</span>
        <a href="<?= APP_URL ?>/views/users/manage.php" class="btn btn-sm btn-outline-primary">Manage Users</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th></tr></thead>
            <tbody>
            <?php foreach ($recentUsers as $u): ?>
            <tr>
              <td class="fw-semibold small"><?= sanitize($u['full_name']) ?></td>
              <td class="small text-muted"><?= sanitize($u['email']) ?></td>
              <td><?= getRoleBadge($u['role']) ?></td>
              <td class="small text-muted"><?= formatDate($u['created_at']) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

</div><!-- page-content -->
<?php include __DIR__ . '/../../includes/footer.php'; ?>
</div><!-- main-content -->
</div><!-- app-shell -->

<?php
// Build calendar data — approved/pending bookings for current month
$calPdo = getDB();
$calStmt = $calPdo->prepare("
    SELECT b.id, b.booking_date, b.start_time, b.end_time, b.status,
           f.facility_name, u.full_name
    FROM bookings b
    JOIN facilities f ON b.facility_id = f.id
    JOIN users      u ON b.user_id     = u.id
    WHERE b.booking_date BETWEEN DATE_FORMAT(NOW(),'%Y-%m-01')
                              AND LAST_DAY(NOW())
      AND b.status IN ('approved','pending')
    ORDER BY b.booking_date, b.start_time
");
$calStmt->execute();
$calBookings = $calStmt->fetchAll(PDO::FETCH_ASSOC);

// Group by date
$calByDate = [];
foreach ($calBookings as $cb) {
    $calByDate[$cb['booking_date']][] = $cb;
}
?>

<!-- ==================== CALENDAR MODAL ==================== -->
<div class="modal fade" id="calendarModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden">
      <div class="modal-header border-0" style="background:linear-gradient(135deg,#1E3A8A,#2563EB);padding:20px 28px">
        <div>
          <h5 class="modal-title text-white fw-bold mb-0">
            <i class="fas fa-calendar-alt me-2"></i>Booking Calendar — <?= date('F Y') ?>
          </h5>
          <div class="text-white-50 small mt-1">All approved &amp; pending bookings this month</div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <?php
        $year  = (int)date('Y');
        $month = (int)date('m');
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $firstDay    = (int)date('N', mktime(0,0,0,$month,1,$year)); // 1=Mon … 7=Sun
        $dayNames    = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
        ?>
        <!-- Day headers -->
        <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:6px;margin-bottom:6px">
          <?php foreach ($dayNames as $d): ?>
          <div style="text-align:center;font-size:.72rem;font-weight:700;text-transform:uppercase;color:#64748B;padding:6px 0"><?= $d ?></div>
          <?php endforeach; ?>
        </div>
        <!-- Calendar grid -->
        <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:6px">
          <?php
          // Empty cells before first day
          for ($e = 1; $e < $firstDay; $e++):
          ?>
          <div style="min-height:80px;border-radius:8px;background:#F8FAFC"></div>
          <?php endfor; ?>

          <?php for ($day = 1; $day <= $daysInMonth; $day++):
            $dateKey  = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $isToday  = ($dateKey === date('Y-m-d'));
            $dayBookings = $calByDate[$dateKey] ?? [];
          ?>
          <div style="min-height:80px;border-radius:8px;padding:6px;
                      background:<?= $isToday ? '#EFF6FF' : '#fff' ?>;
                      border:<?= $isToday ? '2px solid #2563EB' : '1px solid #E2E8F0' ?>">
            <div style="font-size:.78rem;font-weight:<?= $isToday?'800':'600' ?>;
                        color:<?= $isToday?'#2563EB':'#334155' ?>;margin-bottom:4px">
              <?= $day ?>
              <?php if ($isToday): ?><span style="font-size:.6rem;background:#2563EB;color:#fff;border-radius:4px;padding:1px 5px;margin-left:3px">TODAY</span><?php endif; ?>
            </div>
            <?php foreach (array_slice($dayBookings, 0, 2) as $cb):
              $color = $cb['status'] === 'approved' ? '#10B981' : '#F59E0B';
              $bgCol = $cb['status'] === 'approved' ? '#ECFDF5' : '#FFFBEB';
            ?>
            <a href="<?= APP_URL ?>/views/bookings/view.php?id=<?= $cb['id'] ?>"
               data-bs-dismiss="modal"
               style="display:block;background:<?= $bgCol ?>;border-left:3px solid <?= $color ?>;
                      border-radius:4px;padding:2px 5px;margin-bottom:2px;
                      font-size:.66rem;color:#1E293B;text-decoration:none;line-height:1.3;overflow:hidden">
              <span style="font-weight:600"><?= formatTime($cb['start_time']) ?></span>
              <?= sanitize(truncate($cb['facility_name'], 14)) ?>
            </a>
            <?php endforeach; ?>
            <?php if (count($dayBookings) > 2): ?>
            <div style="font-size:.64rem;color:#64748B;margin-top:1px">+<?= count($dayBookings)-2 ?> more</div>
            <?php endif; ?>
          </div>
          <?php endfor; ?>

          <?php
          // Fill remaining cells
          $totalCells = $firstDay - 1 + $daysInMonth;
          $remaining  = (7 - ($totalCells % 7)) % 7;
          for ($r = 0; $r < $remaining; $r++):
          ?>
          <div style="min-height:80px;border-radius:8px;background:#F8FAFC"></div>
          <?php endfor; ?>
        </div>

        <!-- Legend -->
        <div class="d-flex gap-4 mt-3 justify-content-end">
          <div class="d-flex align-items-center gap-2">
            <div style="width:12px;height:12px;border-radius:3px;background:#ECFDF5;border-left:3px solid #10B981"></div>
            <span style="font-size:.78rem;color:#64748B">Approved</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <div style="width:12px;height:12px;border-radius:3px;background:#FFFBEB;border-left:3px solid #F59E0B"></div>
            <span style="font-size:.78rem;color:#64748B">Pending</span>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <a href="<?= APP_URL ?>/views/bookings/manage.php" class="btn btn-primary btn-sm">
          <i class="fas fa-list me-1"></i>Manage All Bookings
        </a>
        <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
