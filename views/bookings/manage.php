<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Booking.php';

requireAdmin();
$bookingModel = new Booking();
$bookingModel->autoComplete();

$status     = $_GET['status'] ?? '';
$facilityId = intval($_GET['facility_id'] ?? 0);
$bookings   = $bookingModel->getAll($status ?: null, $facilityId ?: null);
$counts     = $bookingModel->countByStatus();
$pageTitle  = 'Manage Bookings';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<div class="app-shell">
<?php include __DIR__ . '/../../includes/sidebar.php'; ?>
<div class="main-content" id="mainContent">
<?php include __DIR__ . '/../../includes/navbar.php'; ?>
<div class="page-content">

<div class="page-header">
  <div class="page-header-left">
    <h1><i class="fas fa-calendar-check me-2 text-primary"></i>Manage Bookings</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/views/dashboard/admin.php">Dashboard</a></li>
      <li class="breadcrumb-item active">Bookings</li>
    </ol></nav>
  </div>
</div>

<?= displayFlash() ?>

<!-- Stat Cards -->
<div class="row g-2 mb-3">
  <?php
  $allCount = array_sum($counts);
  $cardDefs = [
    ['All',       '',           $allCount,                   'blue',   'calendar-check'],
    ['Pending',   'pending',    $counts['pending']??0,       'orange', 'clock'],
    ['Approved',  'approved',   $counts['approved']??0,      'teal',   'check-circle'],
    ['Completed', 'completed',  $counts['completed']??0,     'orange', 'flag'],
    ['Rejected',  'rejected',   $counts['rejected']??0,      'red',    'times-circle'],
    ['Cancelled', 'cancelled',  $counts['cancelled']??0,     'gray',   'ban'],
  ];
  foreach ($cardDefs as [$label,$s,$cnt,$color,$icon]): ?>
  <div class="col-sm-6 col-xl">
    <a href="?status=<?= $s ?>" style="text-decoration:none">
    <div class="stat-card <?= $color ?> <?= $status===$s?'border border-2 border-primary':'' ?>" style="cursor:pointer;transition:transform .15s" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
      <div class="stat-icon <?= $color ?>"><i class="fas fa-<?= $icon ?>"></i></div>
      <div><div class="stat-value"><?= $cnt ?></div><div class="stat-label"><?= $label ?></div></div>
    </div>
    </a>
  </div>
  <?php endforeach; ?>
</div>

<!-- Status Filter Tabs -->
<div class="d-flex gap-2 mb-3 flex-wrap">
  <?php
  $allCount = array_sum($counts);
  $statuses = ['' => ['label'=>'All','count'=>$allCount],
               'pending'   => ['label'=>'Pending','count'=>$counts['pending']??0],
               'approved'  => ['label'=>'Approved','count'=>$counts['approved']??0],
               'completed' => ['label'=>'Completed','count'=>$counts['completed']??0],
               'rejected'  => ['label'=>'Rejected','count'=>$counts['rejected']??0],
               'cancelled' => ['label'=>'Cancelled','count'=>$counts['cancelled']??0]];
  foreach ($statuses as $s => $info): ?>
  <a href="?status=<?= $s ?>" class="btn btn-sm <?= $status===$s?'btn-primary':'btn-outline-secondary' ?>">
    <?= $info['label'] ?>
    <span class="badge bg-white text-dark ms-1" style="font-size:.65rem"><?= $info['count'] ?></span>
  </a>
  <?php endforeach; ?>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover datatable mb-0">
        <thead><tr>
          <th>#</th><th>User</th><th>Facility</th><th>Date</th><th>Time</th><th>Status</th><th>Submitted</th><th>Actions</th>
        </tr></thead>
        <tbody>
        <?php foreach ($bookings as $b): ?>
        <tr>
          <td class="small text-muted"><?= $b['id'] ?></td>
          <td>
            <div class="fw-semibold small"><?= sanitize($b['full_name']) ?></div>
            <div class="text-muted" style="font-size:.72rem"><?= sanitize($b['email']) ?></div>
          </td>
          <td>
            <div class="fw-medium small"><?= sanitize($b['facility_name']) ?></div>
            <div class="text-muted" style="font-size:.72rem"><?= sanitize($b['location']) ?></div>
          </td>
          <td class="small"><?= formatDate($b['booking_date'], 'd M Y') ?></td>
          <td class="small"><?= formatTime($b['start_time']) ?>–<?= formatTime($b['end_time']) ?></td>
          <td><?= getStatusBadge($b['status']) ?></td>
          <td class="small text-muted"><?= timeAgo($b['created_at']) ?></td>
          <td>
            <a href="<?= APP_URL ?>/views/bookings/view.php?id=<?= $b['id'] ?>" class="btn btn-icon btn-outline-primary btn-sm" title="View"><i class="fas fa-eye"></i></a>
            <?php if ($b['status'] === 'pending'): ?>
            <a href="<?= APP_URL ?>/views/bookings/approve.php?id=<?= $b['id'] ?>" class="btn btn-icon btn-outline-success btn-sm" title="Approve/Reject"><i class="fas fa-gavel"></i></a>
            <?php endif; ?>
            <?php if (in_array($b['status'], ['pending','approved'])): ?>
            <form method="POST" action="<?= APP_URL ?>/controllers/BookingController.php?action=cancel" style="display:inline"
                  onsubmit="return confirmAction('Cancel this booking?',this)">
              <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
              <button class="btn btn-icon btn-outline-danger btn-sm" title="Cancel"><i class="fas fa-ban"></i></button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
</div>
</div>
<script>const APP_URL = "<?= APP_URL ?>";</script>
