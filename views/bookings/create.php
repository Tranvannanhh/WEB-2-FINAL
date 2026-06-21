<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Facility.php';

requireLogin();
$facilityModel = new Facility();
$facilities    = $facilityModel->getAll(null, 'available');
$preSelected   = intval($_GET['facility_id'] ?? 0);
$pageTitle     = 'New Booking';

// Admin uses the original layout
if ($_SESSION['role'] === 'admin') {
    include __DIR__ . '/../../includes/header.php';
    echo '<div class="app-shell">';
    include __DIR__ . '/../../includes/sidebar.php';
    echo '<div class="main-content" id="mainContent">';
    include __DIR__ . '/../../includes/navbar.php';
    echo '<div class="page-content">';
    echo displayFlash();
    echo '<p class="text-muted">Admins manage bookings from the bookings panel.</p>';
    include __DIR__ . '/../../includes/footer.php';
    echo '</div></div>';
    exit;
}
?>
<?php include __DIR__ . '/../../includes/user_header.php'; ?>
<?php include __DIR__ . '/../../includes/user_navbar.php'; ?>

<div class="u-page">

  <!-- Banner -->
  <div style="background:var(--u-primary);padding:60px 0 36px">
    <div class="container">
      <div class="u-breadcrumb mb-2">
        <a href="<?= APP_URL ?>/views/dashboard/index.php">Home</a>
        <span class="u-breadcrumb-sep">›</span>
        <a href="<?= APP_URL ?>/views/bookings/index.php">My Bookings</a>
        <span class="u-breadcrumb-sep">›</span>
        <span>New Booking</span>
      </div>
      <h1 style="font-family:var(--u-font-serif);color:#fff;font-size:2rem;margin-bottom:6px">
        New Booking Request
      </h1>
      <p style="color:rgba(255,255,255,.6);font-size:.9rem">Fill in the details to reserve a facility</p>
    </div>
  </div>

  <div class="u-page-inner">
    <div class="container">

      <?= displayFlash() ?>

      <div class="row g-4">

        <!-- FORM -->
        <div class="col-lg-8" data-aos="fade-up">
          <div class="u-card">
            <div class="u-card-header">
              <span><i class="fas fa-calendar-plus"></i> Booking Details</span>
            </div>
            <div class="u-card-body">
              <form action="<?= APP_URL ?>/controllers/BookingController.php?action=create"
                    method="POST" id="bookingForm" novalidate>

                <!-- Facility -->
                <div class="mb-4">
                  <label class="u-form-label">Facility <span style="color:#EF4444">*</span></label>
                  <select class="u-form-control" name="facility_id" id="facility_id"
                          required style="appearance:auto">
                    <option value="">— Select a facility —</option>
                    <?php foreach ($facilities as $f): ?>
                    <option value="<?= $f['id'] ?>"
                            <?= $preSelected === $f['id'] ? 'selected' : '' ?>
                            data-capacity="<?= $f['capacity'] ?>"
                            data-location="<?= sanitize($f['location']) ?>">
                      <?= sanitize($f['facility_name']) ?> — <?= sanitize($f['location']) ?> (<?= $f['capacity'] ?> seats)
                    </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <!-- Facility Info Preview -->
                <div id="facilityInfo" class="u-alert u-alert-info d-none mb-4" style="display:none!important">
                  <i class="fas fa-info-circle"></i>
                  <span id="facilityInfoText"></span>
                </div>

                <!-- Date & Time -->
                <div class="row g-3 mb-4">
                  <div class="col-md-4">
                    <label class="u-form-label">Booking Date <span style="color:#EF4444">*</span></label>
                    <input type="date" class="u-form-control" name="booking_date"
                           id="booking_date" min="<?= date('Y-m-d') ?>" required>
                  </div>
                  <div class="col-md-4">
                    <label class="u-form-label">Start Time <span style="color:#EF4444">*</span></label>
                    <input type="time" class="u-form-control" name="start_time" id="start_time" required>
                  </div>
                  <div class="col-md-4">
                    <label class="u-form-label">End Time <span style="color:#EF4444">*</span></label>
                    <input type="time" class="u-form-control" name="end_time" id="end_time" required>
                  </div>
                </div>

                <!-- Conflict Warning -->
                <div id="conflictWarning" class="u-alert u-alert-danger d-none mb-4"></div>

                <!-- Purpose -->
                <div class="mb-4">
                  <label class="u-form-label">Purpose / Description <span style="color:#EF4444">*</span></label>
                  <textarea class="u-form-control" name="purpose" rows="4"
                            placeholder="Describe the purpose of your booking (e.g., Study group, Department meeting…)"
                            required minlength="10" style="resize:vertical;min-height:100px"></textarea>
                </div>

                <!-- Note -->
                <div class="u-alert u-alert-warning mb-4">
                  <i class="fas fa-info-circle"></i>
                  <div>
                    <strong>Note:</strong> Bookings require admin approval. You will be notified once reviewed.
                    Only <strong>pending</strong> bookings can be cancelled.
                  </div>
                </div>

                <div class="d-flex gap-3">
                  <button type="submit" class="u-btn u-btn-gold u-btn-lg" id="submitBookingBtn">
                    <i class="fas fa-paper-plane"></i> Submit Request
                  </button>
                  <a href="<?= APP_URL ?>/views/bookings/index.php"
                     class="u-btn u-btn-outline u-btn-lg">Cancel</a>
                </div>

              </form>
            </div>
          </div>
        </div>

        <!-- GUIDELINES Sidebar -->
        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="80">
          <div class="u-card mb-4">
            <div class="u-card-header"><span><i class="fas fa-lightbulb"></i> Booking Guidelines</span></div>
            <div class="u-card-body">
              <ul style="list-style:none;padding:0;margin:0">
                <?php $tips = [
                  ['icon'=>'check','color'=>'#10B981','text'=>'Book at least 1 day in advance.'],
                  ['icon'=>'bell','color'=>'#2563EB','text'=>'You\'ll be notified once approved.'],
                  ['icon'=>'times','color'=>'#EF4444','text'=>'Cancelled bookings cannot be restored.'],
                  ['icon'=>'broom','color'=>'#F59E0B','text'=>'Leave the facility clean after use.'],
                  ['icon'=>'flag','color'=>'#6366F1','text'=>'Report any issues immediately.'],
                ]; foreach ($tips as $tip): ?>
                <li style="display:flex;align-items:flex-start;gap:10px;margin-bottom:12px">
                  <div style="width:26px;height:26px;border-radius:50%;background:<?= $tip['color'] ?>20;color:<?= $tip['color'] ?>;display:flex;align-items:center;justify-content:center;font-size:.72rem;flex-shrink:0">
                    <i class="fas fa-<?= $tip['icon'] ?>"></i>
                  </div>
                  <span style="font-size:.83rem;color:var(--u-gray);line-height:1.5"><?= $tip['text'] ?></span>
                </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>

          <!-- Workflow steps -->
          <div class="u-card">
            <div class="u-card-header"><span><i class="fas fa-route"></i> Booking Workflow</span></div>
            <div class="u-card-body">
              <?php $steps = ['Submit Request','Admin Review','Approved / Rejected','Completed → Review'];
              foreach ($steps as $i => $step): ?>
              <div style="display:flex;align-items:center;gap:12px;margin-bottom:<?= $i<3?'16':'0' ?>px">
                <div style="width:32px;height:32px;border-radius:50%;background:<?= $i===0?'var(--u-gold)':'var(--u-border)' ?>;color:<?= $i===0?'var(--u-primary)':'#94A3B8' ?>;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.78rem;flex-shrink:0">
                  <?= $i+1 ?>
                </div>
                <span style="font-size:.83rem;color:<?= $i===0?'var(--u-primary)':'var(--u-gray)' ?>;font-weight:<?= $i===0?'600':'400' ?>">
                  <?= $step ?>
                </span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/user_footer.php'; ?>
<script>
const APP_URL = "<?= APP_URL ?>";
document.getElementById('facility_id').addEventListener('change', function () {
  const opt = this.options[this.selectedIndex];
  const info = document.getElementById('facilityInfo');
  const txt  = document.getElementById('facilityInfoText');
  if (this.value) {
    txt.innerHTML = '<strong>' + escHtml(opt.text) + '</strong>';
    info.style.removeProperty('display');
    info.classList.remove('d-none');
  } else {
    info.classList.add('d-none');
  }
});
document.getElementById('bookingForm').addEventListener('submit', function (e) {
  const st = document.getElementById('start_time').value;
  const et = document.getElementById('end_time').value;
  if (st && et && st >= et) {
    alert('End time must be after start time.');
    e.preventDefault();
  }
});
function escHtml(s){const d=document.createElement('div');d.appendChild(document.createTextNode(s));return d.innerHTML;}
</script>
