<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Facility.php';

requireAdmin();
$facilityModel = new Facility();
$id = intval($_GET['id'] ?? 0);
$facility = $id ? $facilityModel->findById($id) : null;
$isEdit = (bool)$facility;

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'facility_name' => trim($_POST['facility_name'] ?? ''),
        'facility_type' => $_POST['facility_type'] ?? '',
        'capacity'      => intval($_POST['capacity'] ?? 1),
        'location'      => trim($_POST['location'] ?? ''),
        'status'        => $_POST['status'] ?? 'available',
        'description'   => trim($_POST['description'] ?? ''),
        'image_path'    => $facility['image_path'] ?? null,
    ];

    // Handle image upload
    if (!empty($_FILES['image']['tmp_name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
            $uploadDir = __DIR__ . '/../../uploads/facilities/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $filename = 'facility_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                $data['image_path'] = $filename;
            }
        }
    }

    $errors = [];
    if (empty($data['facility_name'])) $errors[] = 'Facility name is required.';
    if (empty($data['facility_type'])) $errors[] = 'Type is required.';
    if ($data['capacity'] < 1)        $errors[] = 'Capacity must be at least 1.';
    if (empty($data['location']))      $errors[] = 'Location is required.';

    if (!empty($errors)) {
        flashMessage('danger', implode(' ', $errors));
    } else {
        if ($isEdit) {
            $facilityModel->update($id, $data);
            flashMessage('success', 'Facility updated successfully.');
        } else {
            $facilityModel->create($data);
            flashMessage('success', 'Facility created successfully.');
        }
        redirect(APP_URL . '/views/facilities/manage.php');
    }
}

$pageTitle = $isEdit ? 'Edit Facility' : 'Add Facility';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<div class="app-shell">
<?php include __DIR__ . '/../../includes/sidebar.php'; ?>
<div class="main-content" id="mainContent">
<?php include __DIR__ . '/../../includes/navbar.php'; ?>
<div class="page-content">

<div class="page-header">
  <div class="page-header-left">
    <h1><i class="fas fa-<?= $isEdit?'edit':'plus-circle' ?> me-2 text-primary"></i><?= $isEdit?'Edit':'Add' ?> Facility</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/views/facilities/manage.php">Facilities</a></li>
      <li class="breadcrumb-item active"><?= $isEdit?'Edit':'Add' ?></li>
    </ol></nav>
  </div>
</div>

<?= displayFlash() ?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header"><i class="fas fa-building me-2 text-primary"></i>Facility Information</div>
      <div class="card-body">
        <form method="POST" enctype="multipart/form-data" id="facilityForm" novalidate>
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label">Facility Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="facility_name" value="<?= sanitize($facility['facility_name'] ?? '') ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Type <span class="text-danger">*</span></label>
              <select class="form-select" name="facility_type" required>
                <option value="">Select type</option>
                <?php foreach (['classroom'=>'Classroom','lab'=>'Laboratory','meeting_room'=>'Meeting Room','auditorium'=>'Auditorium','equipment'=>'Equipment'] as $v=>$l): ?>
                <option value="<?= $v ?>" <?= ($facility['facility_type']??'')===$v?'selected':'' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Capacity <span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="capacity" value="<?= $facility['capacity'] ?? 1 ?>" min="1" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Status</label>
              <select class="form-select" name="status">
                <option value="available"   <?= ($facility['status']??'')==='available'  ?'selected':'' ?>>Available</option>
                <option value="maintenance" <?= ($facility['status']??'')==='maintenance'?'selected':'' ?>>Maintenance</option>
                <option value="inactive"    <?= ($facility['status']??'')==='inactive'   ?'selected':'' ?>>Inactive</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Location <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="location" value="<?= sanitize($facility['location'] ?? '') ?>" required>
            </div>
            <div class="col-12">
              <label class="form-label">Description</label>
              <textarea class="form-control" name="description" rows="3"><?= sanitize($facility['description'] ?? '') ?></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Facility Image</label>
              <?php if (!empty($facility['image_path'])): ?>
              <div class="mb-2">
                <img src="<?= facilityImgSrc($facility['image_path']) ?>" height="80" class="rounded border">
              </div>
              <?php endif; ?>
              <input type="file" class="form-control" name="image" accept="image/*">
              <div class="form-text">JPG, PNG, WEBP — max 5MB</div>
            </div>
          </div>
          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i><?= $isEdit?'Update':'Create' ?> Facility</button>
            <a href="<?= APP_URL ?>/views/facilities/manage.php" class="btn btn-outline-secondary"><i class="fas fa-times me-2"></i>Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
</div>
</div>
<script>const APP_URL = "<?= APP_URL ?>";</script>
