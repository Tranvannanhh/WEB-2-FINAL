<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/User.php';

requireAdmin();
$userModel = new User();

// Handle toggle active / delete
$action = $_POST['action'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = intval($_POST['user_id'] ?? 0);
    if ($action === 'toggle_active') {
        $target = $userModel->findById($uid);
        if ($target) {
            $userModel->update($uid, ['is_active' => $target['is_active'] ? 0 : 1]);
            flashMessage('success', 'User status updated.');
        }
    } elseif ($action === 'delete') {
        if ($uid !== (int)$_SESSION['user_id']) {
            $userModel->hardDelete($uid);
            flashMessage('success', 'User deleted.');
        } else {
            flashMessage('danger', 'You cannot delete your own account.');
        }
    } elseif ($action === 'change_role') {
        $newRole = $_POST['role'] ?? '';
        if (in_array($newRole, ['student','lecturer','admin'])) {
            $userModel->update($uid, ['role' => $newRole]);
            flashMessage('success', 'Role updated.');
        }
    }
    redirect(APP_URL . '/views/users/manage.php');
}

$roleFilter   = $_GET['role']   ?? '';
$searchFilter = trim($_GET['search'] ?? '');
$users = $userModel->getAll($roleFilter ?: null, $searchFilter ?: null);
$counts = $userModel->countByRole();
$pageTitle = 'Manage Users';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<div class="app-shell">
<?php include __DIR__ . '/../../includes/sidebar.php'; ?>
<div class="main-content" id="mainContent">
<?php include __DIR__ . '/../../includes/navbar.php'; ?>
<div class="page-content">

<div class="page-header">
  <div class="page-header-left">
    <h1><i class="fas fa-users me-2 text-primary"></i>Manage Users</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/views/dashboard/admin.php">Dashboard</a></li>
      <li class="breadcrumb-item active">Users</li>
    </ol></nav>
  </div>
</div>

<?= displayFlash() ?>

<!-- Stats Row -->
<div class="row g-2 mb-3">
  <?php foreach ([['All','',array_sum($counts),'blue','users'],['Students','student',$counts['student']??0,'teal','user-graduate'],['Lecturers','lecturer',$counts['lecturer']??0,'orange','chalkboard-teacher'],['Admins','admin',$counts['admin']??0,'red','user-shield']] as [$label,$role,$cnt,$color,$icon]): ?>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card <?= $color ?>">
      <div class="stat-icon <?= $color ?>"><i class="fas fa-<?= $icon ?>"></i></div>
      <div><div class="stat-value"><?= $cnt ?></div><div class="stat-label"><?= $label ?></div></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Filter Bar -->
<div class="search-bar mb-3">
  <form method="GET">
    <div class="row g-2 align-items-end">
      <div class="col-md-5">
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-search"></i></span>
          <input type="text" class="form-control" name="search" value="<?= sanitize($searchFilter) ?>" placeholder="Search name, email, student code…">
        </div>
      </div>
      <div class="col-md-3">
        <select class="form-select" name="role">
          <option value="">All Roles</option>
          <option value="student"  <?= $roleFilter==='student' ?'selected':'' ?>>Student</option>
          <option value="lecturer" <?= $roleFilter==='lecturer'?'selected':'' ?>>Lecturer</option>
          <option value="admin"    <?= $roleFilter==='admin'   ?'selected':'' ?>>Admin</option>
        </select>
      </div>
      <div class="col-md-2 d-flex gap-2">
        <button class="btn btn-primary flex-1"><i class="fas fa-search"></i></button>
        <a href="?" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
      </div>
    </div>
  </form>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover datatable mb-0">
        <thead><tr>
          <th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Code</th><th>Status</th><th>Joined</th><th>Actions</th>
        </tr></thead>
        <tbody>
        <?php foreach ($users as $i => $u): ?>
        <tr>
          <td class="small text-muted"><?= $i+1 ?></td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="nav-avatar flex-shrink-0" style="width:34px;height:34px;border-radius:50%;background:#EFF6FF;color:#2563EB;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem">
                <?php if (!empty($u['avatar'])): ?>
                <img src="<?= APP_URL ?>/uploads/avatars/<?= sanitize($u['avatar']) ?>" style="width:34px;height:34px;border-radius:50%;object-fit:cover" alt="">
                <?php else: ?>
                <?= strtoupper(substr($u['full_name'],0,1)) ?>
                <?php endif; ?>
              </div>
              <span class="fw-semibold small"><?= sanitize($u['full_name']) ?></span>
            </div>
          </td>
          <td class="small text-muted"><?= sanitize($u['email']) ?></td>
          <td><?= getRoleBadge($u['role']) ?></td>
          <td class="small text-muted"><?= sanitize($u['student_code'] ?? '—') ?></td>
          <td>
            <?php if ($u['is_active']): ?>
            <span class="badge bg-success"><i class="fas fa-check me-1"></i>Active</span>
            <?php else: ?>
            <span class="badge bg-secondary"><i class="fas fa-ban me-1"></i>Inactive</span>
            <?php endif; ?>
          </td>
          <td class="small text-muted"><?= formatDate($u['created_at'], 'd M Y') ?></td>
          <td>
            <?php if ($u['id'] !== (int)$_SESSION['user_id']): ?>
            <!-- Toggle active -->
            <form method="POST" style="display:inline">
              <input type="hidden" name="action" value="toggle_active">
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <button class="btn btn-icon btn-outline-<?= $u['is_active']?'warning':'success' ?> btn-sm"
                      title="<?= $u['is_active']?'Deactivate':'Activate' ?>" data-bs-toggle="tooltip"
                      onclick="return confirm('<?= $u['is_active']?'Deactivate':'Activate' ?> this user?')">
                <i class="fas fa-<?= $u['is_active']?'ban':'check' ?>"></i>
              </button>
            </form>
            <!-- Change Role -->
            <button class="btn btn-icon btn-outline-primary btn-sm" title="Change Role"
                    data-bs-toggle="modal" data-bs-target="#roleModal"
                    data-uid="<?= $u['id'] ?>" data-name="<?= sanitize($u['full_name']) ?>" data-role="<?= $u['role'] ?>">
              <i class="fas fa-user-tag"></i>
            </button>
            <!-- Delete -->
            <form method="POST" style="display:inline" onsubmit="return confirmDelete(this)">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <button class="btn btn-icon btn-outline-danger btn-sm" title="Delete"><i class="fas fa-trash"></i></button>
            </form>
            <?php else: ?>
            <span class="text-muted small">You</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Role Modal -->
<div class="modal fade" id="roleModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Change Role</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <input type="hidden" name="action" value="change_role">
        <input type="hidden" name="user_id" id="roleUserId">
        <div class="modal-body">
          <p class="small text-muted mb-2">User: <strong id="roleUserName"></strong></p>
          <select class="form-select" name="role" id="roleSelect">
            <option value="student">Student</option>
            <option value="lecturer">Lecturer</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary btn-sm" type="submit">Update Role</button>
        </div>
      </form>
    </div>
  </div>
</div>

</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
</div>
</div>
<script>
const APP_URL = "<?= APP_URL ?>";
document.getElementById('roleModal').addEventListener('show.bs.modal', function(e) {
  const btn = e.relatedTarget;
  document.getElementById('roleUserId').value   = btn.dataset.uid;
  document.getElementById('roleUserName').textContent = btn.dataset.name;
  document.getElementById('roleSelect').value   = btn.dataset.role;
});
</script>
