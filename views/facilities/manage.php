<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Facility.php';

requireAdmin();
$facilityModel = new Facility();
$facilities = $facilityModel->getAll();
$pageTitle = 'Manage Facilities';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<div class="app-shell">
<?php include __DIR__ . '/../../includes/sidebar.php'; ?>
<div class="main-content" id="mainContent">
<?php include __DIR__ . '/../../includes/navbar.php'; ?>
<div class="page-content">

<div class="page-header">
  <div class="page-header-left">
    <h1><i class="fas fa-building me-2 text-primary"></i>Manage Facilities</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/views/dashboard/admin.php">Dashboard</a></li>
      <li class="breadcrumb-item active">Facilities</li>
    </ol></nav>
  </div>
  <a href="<?= APP_URL ?>/views/facilities/form.php" class="btn btn-primary">
    <i class="fas fa-plus me-2"></i>Add Facility
  </a>
</div>

<?= displayFlash() ?>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover datatable mb-0">
        <thead><tr>
          <th>#</th><th>Name</th><th>Type</th><th>Capacity</th><th>Location</th><th>Status</th><th>Rating</th><th>Actions</th>
        </tr></thead>
        <tbody>
        <?php foreach ($facilities as $i => $f): ?>
        <tr>
          <td class="text-muted small"><?= $i+1 ?></td>
          <td>
            <div class="fw-semibold small"><?= sanitize($f['facility_name']) ?></div>
          </td>
          <td>
            <span class="badge bg-primary-subtle text-primary" style="background:#EFF6FF!important;color:#2563EB!important">
              <i class="fas fa-<?= getFacilityIcon($f['facility_type']) ?> me-1"></i><?= ucfirst(str_replace('_',' ',$f['facility_type'])) ?>
            </span>
          </td>
          <td class="small"><?= $f['capacity'] ?></td>
          <td class="small text-muted"><?= sanitize($f['location']) ?></td>
          <td><?= getStatusBadge($f['status']) ?></td>
          <td class="small">
            <?= $f['avg_rating'] > 0 ? '<span class="text-warning">★</span> ' . number_format($f['avg_rating'],1) . ' <span class="text-muted">(' . $f['review_count'] . ')</span>' : '<span class="text-muted">—</span>' ?>
          </td>
          <td>
            <a href="<?= APP_URL ?>/views/facilities/view.php?id=<?= $f['id'] ?>" class="btn btn-icon btn-outline-info btn-sm" title="View" data-bs-toggle="tooltip"><i class="fas fa-eye"></i></a>
            <a href="<?= APP_URL ?>/views/facilities/form.php?id=<?= $f['id'] ?>" class="btn btn-icon btn-outline-primary btn-sm" title="Edit" data-bs-toggle="tooltip"><i class="fas fa-edit"></i></a>
            <a href="<?= APP_URL ?>/views/facilities/equipment.php?facility_id=<?= $f['id'] ?>" class="btn btn-icon btn-outline-secondary btn-sm" title="Equipment" data-bs-toggle="tooltip"><i class="fas fa-tools"></i></a>
            <form method="POST" action="<?= APP_URL ?>/views/facilities/delete.php" style="display:inline" onsubmit="return confirmDelete(this)">
              <input type="hidden" name="id" value="<?= $f['id'] ?>">
              <button type="submit" class="btn btn-icon btn-outline-danger btn-sm" title="Delete" data-bs-toggle="tooltip"><i class="fas fa-trash"></i></button>
            </form>
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
