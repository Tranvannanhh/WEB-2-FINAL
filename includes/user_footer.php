<footer class="u-footer">
  <div class="container">
    <div class="row g-4 mb-2">
      <div class="col-md-4">
        <div class="u-footer-logo">
          <div style="width:36px;height:36px;border-radius:10px;background:var(--gold);display:flex;align-items:center;justify-content:center">
            <i class="fas fa-calendar-check" style="color:var(--p-darker)"></i>
          </div>
          VNUIS Booking
        </div>
        <p class="u-footer-desc">The official campus facility booking platform for VNUIS students and lecturers.</p>
      </div>
      <div class="col-6 col-md-2">
        <div class="u-footer-heading">Navigate</div>
        <ul class="u-footer-links">
          <li><a href="<?= APP_URL ?>/views/dashboard/index.php">Home</a></li>
          <li><a href="<?= APP_URL ?>/views/facilities/index.php">Facilities</a></li>
          <li><a href="<?= APP_URL ?>/views/bookings/create.php">Book Now</a></li>
          <li><a href="<?= APP_URL ?>/views/bookings/index.php">My Bookings</a></li>
        </ul>
      </div>
      <div class="col-6 col-md-3">
        <div class="u-footer-heading">Account</div>
        <ul class="u-footer-links">
          <li><a href="<?= APP_URL ?>/views/profile/index.php">My Profile</a></li>
          <li><a href="<?= APP_URL ?>/views/notifications/index.php">Notifications</a></li>
          <?php if (($_SESSION['role'] ?? '') === 'student'): ?>
          <li><a href="<?= APP_URL ?>/views/reports/facility.php">Report Issue</a></li>
          <?php endif; ?>
          <li><a href="<?= APP_URL ?>/controllers/AuthController.php?action=logout">Logout</a></li>
        </ul>
      </div>
      <div class="col-md-3">
        <div class="u-footer-heading">Campus Hours</div>
        <ul class="u-footer-links">
          <li>Mon – Fri &nbsp;<strong style="color:rgba(255,255,255,.7)">07:00 – 21:00</strong></li>
          <li>Saturday &nbsp;<strong style="color:rgba(255,255,255,.7)">08:00 – 17:00</strong></li>
          <li>Sunday &nbsp;<strong style="color:rgba(255,255,255,.4)">Closed</strong></li>
        </ul>
      </div>
    </div>
    <div class="u-footer-bottom">
      <span>&copy; <?= date('Y') ?> <?= APP_NAME ?> — v<?= APP_VERSION ?></span>
      <span>Made with <i class="fas fa-heart" style="color:var(--gold)"></i> for VNUIS</span>
    </div>
  </div>
</footer>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/user-main.js"></script>
<?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>
