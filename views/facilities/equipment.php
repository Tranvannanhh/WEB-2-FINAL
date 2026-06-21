<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Facility.php';
require_once __DIR__ . '/../../models/Equipment.php';

requireAdmin();
$facilityId = intval($_GET['facility_id'] ?? 0);
$facilityModel  = new Facility();
$equipmentModel = new Equipment();
$facility = $facilityModel->findById($facilityId);
if (!$facility) { flashMessage('danger','Facility not found.'); redirect(APP_URL.'/views/facilities/manage.php'); }

// Handle actions
$action = $_POST['action'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $equipmentModel->create([
            'facility_id'    => $facilityId,
            'equipment_name' => trim($_POST['equipment_name'] ?? ''),
            'quantity'       => intval($_POST['quantity'] ?? 1),
            'status'         => $_POST['status'] ?? 'good',
        ]);
        flashMessage('success', 'Equipment added.');
    } elseif ($action === 'update') {
        $eqId = intval($_POST['eq_id'] ?? 0);
        $equipmentModel->update($eqId, [
            'equipment_name' => trim($_POST['equipment_name'] ?? ''),
            'quantity'       => intval($_POST['quantity'] ?? 1),
            'status'         => $_POST['status'] ?? 'good',
        ]);
        flashMessage('success', 'Equipment updated.');
    } elseif ($action === 'delete') {
        $eqId = intval($_POST['eq_id'] ?? 0);
        $equipmentModel->delete($eqId);
        flashMessage('success', 'Equipment removed.');
    }
    redirect(APP_URL . '/views/facilities/equipment.php?facility_id=' . $facilityId);
}

$equipment = $equipmentModel->getByFacility($facilityId);
$pageTitle = 'Equipment — ' . $facility['facility_name'];
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<div class="app-shell">
<?php include __DIR__ . '/../../includes/sidebar.php'; ?>
<div class="main-content" id="mainContent">
<?php include __DIR__ . '/../../includes/navbar.php'; ?>
<div class="page-content">

<div class="page-header">
  <div class="page-header-left">
    <h1><i class="fas fa-tools me-2 text-primary"></i>Equipment — <?= sanitize($facility['facility_name']) ?></h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/views/facilities/manage.php">Facilities</a></li>
      <li class="breadcrumb-item active">Equipment</li>
    </ol></nav>
  </div>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEquipModal">
    <i class="fas fa-plus me-2"></i>Add Equipment
  </button>
</div>

<?= displayFlash() ?>

<div class="card">
  <div class="card-body p-0">
    <table class="table datatable mb-0">
      <thead><tr><th>#</th><th>Name</th><th>Quantity</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($equipment as $i => $eq): ?>
      <tr>
        <td class="small text-muted"><?= $i+1 ?></td>
        <td class="fw-semibold small"><?= sanitize($eq['equipment_name']) ?></td>
        <td class="small"><?= $eq['quantity'] ?></td>
        <td><?= getStatusBadge($eq['status']) ?></td>
        <td>
          <button class="btn btn-icon btn-outline-primary btn-sm edit-eq-btn"
                  data-id="<?= $eq['id'] ?>"
                  data-name="<?= sanitize($eq['equipment_name']) ?>"
                  data-qty="<?= $eq['quantity'] ?>"
                  data-status="<?= $eq['status'] ?>"
                  data-bs-toggle="modal" data-bs-target="#editEquipModal"
                  title="Edit">
            <i class="fas fa-edit"></i>
          </button>
          <form method="POST" style="display:inline" onsubmit="return confirmDelete(this)">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="eq_id" value="<?= $eq['id'] ?>">
            <button class="btn btn-icon btn-outline-danger btn-sm"><i class="fas fa-trash"></i></button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($equipment)): ?>
      <tr><td colspan="5" class="text-center text-muted py-4">No equipment added yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addEquipModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Add Equipment</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <form method="POST">
        <input type="hidden" name="action" value="add">
        <div class="modal-body row g-3">
          <div class="col-12"><label class="form-label">Name</label><input type="text" class="form-control" name="equipment_name" required></div>
          <div class="col-6"><label class="form-label">Quantity</label><input type="number" class="form-control" name="quantity" value="1" min="1" required></div>
          <div class="col-6"><label class="form-label">Status</label>
            <select class="form-select" name="status">
              <option value="good">Good</option><option value="damaged">Damaged</option><option value="missing">Missing</option>
            </select>
          </div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Add</button></div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editEquipModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Edit Equipment</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <form method="POST">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="eq_id" id="editEqId">
        <div class="modal-body row g-3">
          <div class="col-12"><label class="form-label">Name</label><input type="text" class="form-control" name="equipment_name" id="editEqName" required></div>
          <div class="col-6"><label class="form-label">Quantity</label><input type="number" class="form-control" name="quantity" id="editEqQty" min="1" required></div>
          <div class="col-6"><label class="form-label">Status</label>
            <select class="form-select" name="status" id="editEqStatus">
              <option value="good">Good</option><option value="damaged">Damaged</option><option value="missing">Missing</option>
            </select>
          </div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Update</button></div>
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
document.querySelectorAll('.edit-eq-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    document.getElementById('editEqId').value   = this.dataset.id;
    document.getElementById('editEqName').value = this.dataset.name;
    document.getElementById('editEqQty').value  = this.dataset.qty;
    document.getElementById('editEqStatus').value = this.dataset.status;
  });
});
</script>
