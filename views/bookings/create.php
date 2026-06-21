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

if ($_SESSION['role'] === 'admin') {
    include __DIR__ . '/../../includes/header.php';
    echo '<div class="app-shell">'; include __DIR__ . '/../../includes/sidebar.php';
    echo '<div class="main-content" id="mainContent">'; include __DIR__ . '/../../includes/navbar.php';
    echo '<div class="page-content">'.displayFlash().'<p class="text-muted">Admins can create bookings from the bookings panel.</p>';
    include __DIR__ . '/../../includes/footer.php'; echo '</div></div>'; exit;
}
?>
<?php include __DIR__ . '/../../includes/user_header.php'; ?>
<?php include __DIR__ . '/../../includes/user_navbar.php'; ?>

<div class="u-page">
  <div class="u-banner">
    <div class="container">
      <div class="u-bc">
        <a href="<?= APP_URL ?>/views/dashboard/index.php">Home</a><span class="u-bc-sep">›</span>
        <a href="<?= APP_URL ?>/views/bookings/index.php">My Bookings</a><span class="u-bc-sep">›</span>
        <span>New Booking</span>
      </div>
      <h1 class="u-banner-title">New Booking Request</h1>
      <p class="u-banner-sub">Fill in the details below to reserve a facility</p>
    </div>
  </div>

  <div class="u-content">
    <div class="container">
      <?= displayFlash() ?>
      <div class="row g-4">

        <!-- FORM -->
        <div class="col-lg-8" data-aos="fade-up">
          <div class="u-card">
            <div class="u-card-hd"><span><i class="fas fa-calendar-plus"></i> Booking Details</span></div>
            <div class="u-card-body">
              <form action="<?= APP_URL ?>/controllers/BookingController.php?action=create"
                    method="POST" id="bookingForm" novalidate>

                <div class="u-field">
                  <label class="u-label">Facility <span class="req">*</span></label>
                  <select class="u-select" name="facility_id" id="facility_id" required>
                    <option value="">— Select a facility —</option>
                    <?php foreach ($facilities as $f): ?>
                    <option value="<?= $f['id'] ?>" <?= $preSelected===$f['id']?'selected':'' ?>
                            data-capacity="<?= $f['capacity'] ?>" data-location="<?= sanitize($f['location']) ?>">
                      <?= sanitize($f['facility_name']) ?> — <?= sanitize($f['location']) ?> (<?= $f['capacity'] ?> seats)
                    </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div id="facilityPreview" class="u-alert u-alert-info d-none">
                  <i class="fas fa-info-circle"></i>
                  <span id="facilityPreviewText"></span>
                </div>

                <div class="row g-3 mb-4">
                  <div class="col-md-4">
                    <label class="u-label">Date <span class="req">*</span></label>
                    <input type="date" class="u-input" name="booking_date" id="booking_date"
                           min="<?= date('Y-m-d') ?>" required>
                  </div>
                  <div class="col-md-4">
                    <label class="u-label">Start Time <span class="req">*</span></label>
                    <input type="time" class="u-input" name="start_time" id="start_time" required>
                  </div>
                  <div class="col-md-4">
                    <label class="u-label">End Time <span class="req">*</span></label>
                    <input type="time" class="u-input" name="end_time" id="end_time" required>
                  </div>
                </div>

                <div id="conflictWarning" class="u-alert u-alert-danger d-none"></div>

                <div class="u-field">
                  <label class="u-label">Purpose / Description <span class="req">*</span></label>
                  <textarea class="u-textarea" name="purpose" rows="4"
                            placeholder="Describe the purpose of your booking (e.g., Study group, Thesis meeting…)"
                            required minlength="10"></textarea>
                  <div class="u-form-hint">Minimum 10 characters.</div>
                </div>

                <div class="u-alert u-alert-warning">
                  <i class="fas fa-info-circle"></i>
                  <div><strong>Note:</strong> Bookings require admin approval. You'll be notified once reviewed. Only <strong>pending</strong> bookings can be cancelled.</div>
                </div>

                <div style="display:flex;gap:12px;margin-top:20px">
                  <button type="submit" class="u-btn u-btn-gold u-btn-lg" id="submitBookingBtn">
                    <i class="fas fa-paper-plane"></i> Submit Request
                  </button>
                  <a href="<?= APP_URL ?>/views/bookings/index.php" class="u-btn u-btn-outline u-btn-lg">
                    Cancel
                  </a>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- SIDEBAR -->
        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="80">
          <div class="u-card mb-4">
            <div class="u-card-hd"><span><i class="fas fa-lightbulb"></i> Guidelines</span></div>
            <div class="u-card-body">
              <?php $tips = [
                ['check','#10b981','Book at least 1 day in advance.'],
                ['bell','#1d4ed8','You\'ll be notified once approved.'],
                ['times','#ef4444','Cancelled bookings cannot be restored.'],
                ['broom','#f59e0b','Leave the facility clean after use.'],
                ['flag','#6366f1','Report any issues immediately.'],
              ]; foreach ($tips as [$icon,$color,$text]): ?>
              <div style="display:flex;align-items:flex-start;gap:10px;margin-bottom:13px">
                <div style="width:26px;height:26px;border-radius:50%;background:<?= $color ?>20;color:<?= $color ?>;display:flex;align-items:center;justify-content:center;font-size:.7rem;flex-shrink:0">
                  <i class="fas fa-<?= $icon ?>"></i>
                </div>
                <span style="font-size:.83rem;color:var(--muted);line-height:1.5"><?= $text ?></span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="u-card">
            <div class="u-card-hd"><span><i class="fas fa-route"></i> Workflow</span></div>
            <div class="u-card-body">
              <?php $steps = ['Submit Request','Admin Review','Approved / Rejected','Completed → Review'];
              foreach ($steps as $i => $s): ?>
              <div style="display:flex;align-items:center;gap:12px;margin-bottom:<?= $i<3?'14':'0' ?>px">
                <div style="width:30px;height:30px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.75rem;
                  background:<?= $i===0?'var(--gold)':'var(--border)' ?>;color:<?= $i===0?'var(--p-darker)':'#94a3b8' ?>">
                  <?= $i+1 ?>
                </div>
                <span style="font-size:.83rem;color:<?= $i===0?'var(--text)':'var(--muted)' ?>;font-weight:<?= $i===0?'700':'400' ?>"><?= $s ?></span>
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
document.getElementById('facility_id').addEventListener('change', function () {
  const opt = this.options[this.selectedIndex];
  const box = document.getElementById('facilityPreview');
  const txt = document.getElementById('facilityPreviewText');
  if (this.value) {
    txt.innerHTML = '<strong>' + escHtml(opt.text) + '</strong>';
    box.classList.remove('d-none');
  } else { box.classList.add('d-none'); }
});
document.getElementById('bookingForm').addEventListener('submit', function (e) {
  const st = document.getElementById('start_time').value;
  const et = document.getElementById('end_time').value;
  if (st && et && st >= et) {
    Swal.fire({ icon:'warning', title:'Time Error', text:'End time must be after start time.' });
    e.preventDefault();
  }
});
function escHtml(s) { const d=document.createElement('div'); d.appendChild(document.createTextNode(s)); return d.innerHTML; }
</script>
