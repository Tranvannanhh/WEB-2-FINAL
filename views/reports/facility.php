<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Report.php';
require_once __DIR__ . '/../../models/Facility.php';

requireLogin();
$reportModel   = new Report();
$facilityModel = new Facility();
$userId        = $_SESSION['user_id'];
$isAdmin       = ($_SESSION['role'] === 'admin');

/* ── Handle Submit (students/lecturers) ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_report'])) {
    $facilityId = intval($_POST['facility_id'] ?? 0);
    $desc       = trim($_POST['issue_description'] ?? '');
    if (!$facilityId)        flashMessage('danger', 'Please select a facility.');
    elseif (strlen($desc) < 10) flashMessage('danger', 'Please describe the issue (min 10 characters).');
    else {
        $reportModel->create(['facility_id' => $facilityId, 'user_id' => $userId, 'issue_description' => $desc]);
        sendNotification($userId, 'Issue Reported', 'Your issue report has been submitted. Our team will review it shortly.');
        // Notify admins
        require_once __DIR__ . '/../../models/User.php';
        $admins = (new \User())->getAll('admin');
        $fname  = $facilityModel->findById($facilityId)['facility_name'] ?? 'Unknown';
        foreach ($admins as $a) {
            sendNotification($a['id'], 'New Issue Report', "A new issue was reported for $fname by {$_SESSION['full_name']}.");
        }
        flashMessage('success', 'Issue report submitted successfully.');
        redirect(APP_URL . '/views/reports/facility.php');
    }
}

/* ── Handle Admin Status Update ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    requireAdmin();
    $rid    = intval($_POST['report_id'] ?? 0);
    $status = $_POST['report_status'] ?? '';
    $note   = trim($_POST['admin_note'] ?? '');
    if (in_array($status, ['open','in_progress','resolved'])) {
        $reportModel->updateStatus($rid, $status, $note ?: null);
        flashMessage('success', 'Report status updated.');
    }
    redirect(APP_URL . '/views/reports/facility.php');
}

// Load data
$facilities = $facilityModel->getAll();
$preSelected = intval($_GET['facility_id'] ?? 0);

if ($isAdmin) {
    $statusFilter = $_GET['status'] ?? '';
    $reports = $reportModel->getAll($statusFilter ?: null);
} else {
    $reports = $reportModel->getByUser($userId);
}

$pageTitle = $isAdmin ? 'Issue Reports' : 'Report an Issue';

// ── ADMIN: original layout ──
if ($isAdmin) {
    include __DIR__ . '/../../includes/header.php';
    echo '<div class="app-shell">';
    include __DIR__ . '/../../includes/sidebar.php';
    echo '<div class="main-content" id="mainContent">';
    include __DIR__ . '/../../includes/navbar.php';
    ?>
    <div class="page-content">
    <div class="page-header">
      <div class="page-header-left">
        <h1><i class="fas fa-exclamation-triangle me-2 text-danger"></i>Issue Reports</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="<?= APP_URL ?>/views/dashboard/admin.php">Dashboard</a></li>
          <li class="breadcrumb-item active">Issue Reports</li>
        </ol></nav>
      </div>
    </div>
    <?= displayFlash() ?>
    <?php
    $statusFilter = $_GET['status'] ?? '';
    $rCounts = $reportModel->countByStatus();
    ?>
    <div class="d-flex gap-2 mb-3 flex-wrap">
      <?php foreach (['' => 'All', 'open' => 'Open', 'in_progress' => 'In Progress', 'resolved' => 'Resolved'] as $s => $l): ?>
      <a href="?status=<?= $s ?>" class="btn btn-sm <?= $statusFilter===$s?'btn-primary':'btn-outline-secondary' ?>">
        <?= $l ?> <span class="badge bg-white text-dark ms-1"><?= $s===''?array_sum($rCounts):($rCounts[$s]??0) ?></span>
      </a>
      <?php endforeach; ?>
    </div>
    <div class="card">
      <div class="card-body p-0">
        <?php if (empty($reports)): ?>
        <div class="text-center py-5 text-muted"><small>No reports found.</small></div>
        <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover datatable mb-0">
            <thead><tr><th>#</th><th>Reported By</th><th>Facility</th><th>Issue</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($reports as $i => $r): ?>
            <tr>
              <td class="small text-muted"><?= $i+1 ?></td>
              <td><div class="fw-semibold small"><?= sanitize($r['full_name']) ?></div><div class="text-muted" style="font-size:.72rem"><?= sanitize($r['email']) ?></div></td>
              <td><div class="fw-medium small"><?= sanitize($r['facility_name']) ?></div><div class="text-muted" style="font-size:.72rem"><?= sanitize($r['location']) ?></div></td>
              <td class="small"><?= sanitize(truncate($r['issue_description'],70)) ?></td>
              <td><?= getStatusBadge($r['report_status']) ?></td>
              <td class="small text-muted"><?= timeAgo($r['created_at']) ?></td>
              <td>
                <button class="btn btn-xs btn-outline-primary update-report-btn"
                        data-bs-toggle="modal" data-bs-target="#updateReportModal"
                        data-id="<?= $r['id'] ?>" data-status="<?= $r['report_status'] ?>"
                        data-note="<?= sanitize($r['admin_note'] ?? '') ?>">
                  <i class="fas fa-edit"></i> Update
                </button>
              </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <!-- Update Report Modal -->
    <div class="modal fade" id="updateReportModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header"><h5 class="modal-title">Update Report Status</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
          <form method="POST">
            <input type="hidden" name="report_id" id="reportId">
            <div class="modal-body row g-3">
              <div class="col-12">
                <label class="form-label">Status</label>
                <select class="form-select" name="report_status" id="reportStatus">
                  <option value="open">Open</option>
                  <option value="in_progress">In Progress</option>
                  <option value="resolved">Resolved</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label">Admin Note</label>
                <textarea class="form-control" name="admin_note" id="reportNote" rows="3"></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button class="btn btn-primary" name="update_status" value="1" type="submit">Update</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    </div>
    <?php
    include __DIR__ . '/../../includes/footer.php';
    echo '</div></div>';
    ?>
    <script>
    const APP_URL = "<?= APP_URL ?>";
    document.querySelectorAll('.update-report-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        document.getElementById('reportId').value     = this.dataset.id;
        document.getElementById('reportStatus').value = this.dataset.status;
        document.getElementById('reportNote').value   = this.dataset.note;
      });
    });
    </script>
    <?php
    exit;
}

// ── STUDENT / LECTURER: user theme layout ──
?>
<?php include __DIR__ . '/../../includes/user_header.php'; ?>
<?php include __DIR__ . '/../../includes/user_navbar.php'; ?>

<div class="u-page">

  <div style="background:var(--u-primary);padding:60px 0 36px">
    <div class="container">
      <div class="u-breadcrumb mb-2">
        <a href="<?= APP_URL ?>/views/dashboard/index.php">Home</a>
        <span class="u-breadcrumb-sep">›</span>
        <span>Report an Issue</span>
      </div>
      <h1 style="font-family:var(--u-font-serif);color:#fff;font-size:2rem;margin-bottom:4px">Report an Issue</h1>
      <p style="color:rgba(255,255,255,.6);font-size:.9rem;margin:0">Help us keep campus facilities in top condition</p>
    </div>
  </div>

  <div class="u-page-inner">
    <div class="container">

      <?= displayFlash() ?>

      <div class="row g-4">
        <!-- Submit Form -->
        <div class="col-lg-5" data-aos="fade-up">
          <div class="u-card">
            <div class="u-card-header"><span><i class="fas fa-flag" style="color:#EF4444"></i> Submit Issue Report</span></div>
            <div class="u-card-body">
              <form method="POST" novalidate>
                <div class="mb-4">
                  <label class="u-form-label">Facility <span style="color:#EF4444">*</span></label>
                  <select class="u-form-control" name="facility_id" required style="appearance:auto">
                    <option value="">— Select facility —</option>
                    <?php foreach ($facilities as $f): ?>
                    <option value="<?= $f['id'] ?>" <?= $preSelected===$f['id']?'selected':'' ?>>
                      <?= sanitize($f['facility_name']) ?>
                    </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="mb-4">
                  <label class="u-form-label">Describe the Issue <span style="color:#EF4444">*</span></label>
                  <textarea class="u-form-control" name="issue_description" rows="5"
                            placeholder="Equipment malfunction, damage, safety concern…"
                            required minlength="10" style="resize:vertical;min-height:110px"></textarea>
                </div>
                <div class="u-alert u-alert-info mb-4">
                  <i class="fas fa-info-circle"></i>
                  Reports are reviewed by our facilities team and addressed promptly.
                </div>
                <button type="submit" name="submit_report" value="1"
                        class="u-btn u-btn-lg w-100" style="justify-content:center;background:#EF4444;color:#fff;border-color:#EF4444;border-radius:50px">
                  <i class="fas fa-paper-plane"></i> Submit Report
                </button>
              </form>
            </div>
          </div>
        </div>

        <!-- My Reports -->
        <div class="col-lg-7" data-aos="fade-up" data-aos-delay="60">
          <div class="u-card">
            <div class="u-card-header"><span><i class="fas fa-list"></i> My Submitted Reports</span></div>
            <?php if (empty($reports)): ?>
            <div class="u-empty">
              <i class="fas fa-clipboard-check d-block"></i>
              <p>No reports submitted yet.</p>
            </div>
            <?php else: ?>
            <div style="overflow-x:auto">
              <table class="u-table">
                <thead><tr>
                  <th>#</th><th>Facility</th><th>Issue</th><th>Status</th><th>Date</th>
                </tr></thead>
                <tbody>
                <?php foreach ($reports as $i => $r): ?>
                <tr>
                  <td style="color:#94A3B8;font-size:.82rem"><?= $i+1 ?></td>
                  <td>
                    <div style="font-weight:600;font-size:.86rem"><?= sanitize($r['facility_name']) ?></div>
                    <div style="font-size:.73rem;color:var(--u-gray)"><?= sanitize($r['location']) ?></div>
                  </td>
                  <td style="font-size:.83rem;color:var(--u-gray);max-width:180px"><?= sanitize(truncate($r['issue_description'],60)) ?></td>
                  <td><?= getStatusBadge($r['report_status']) ?></td>
                  <td style="font-size:.78rem;color:#94A3B8;white-space:nowrap"><?= timeAgo($r['created_at']) ?></td>
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
  </div>
</div>

<?php include __DIR__ . '/../../includes/user_footer.php'; ?>
<script>const APP_URL = "<?= APP_URL ?>";</script>
