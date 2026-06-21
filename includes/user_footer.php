<!-- ====== USER FOOTER ====== -->
<footer class="u-footer">
    <div class="container">
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="u-footer-brand mb-3">
                    <svg width="24" height="24" viewBox="0 0 28 28" fill="none" class="me-2">
                        <rect width="28" height="28" rx="6" fill="var(--u-gold)"/>
                        <path d="M7 10h14M7 14h14M7 18h10" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        <rect x="9" y="6" width="3" height="4" rx="1" fill="white"/>
                        <rect x="16" y="6" width="3" height="4" rx="1" fill="white"/>
                    </svg>
                    VNUIS Booking
                </div>
                <p style="color:#94A3B8;font-size:.85rem;line-height:1.7">
                    The official campus facility booking platform for VNUIS students and lecturers.
                </p>
            </div>
            <div class="col-md-2">
                <h6 class="u-footer-heading">Navigation</h6>
                <ul class="u-footer-links">
                    <li><a href="<?= APP_URL ?>/views/dashboard/index.php">Home</a></li>
                    <li><a href="<?= APP_URL ?>/views/facilities/index.php">Facilities</a></li>
                    <li><a href="<?= APP_URL ?>/views/bookings/create.php">Book Now</a></li>
                    <li><a href="<?= APP_URL ?>/views/bookings/index.php">My Bookings</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h6 class="u-footer-heading">Account</h6>
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
                <h6 class="u-footer-heading">Campus Hours</h6>
                <ul class="u-footer-links">
                    <li><span style="color:#94A3B8">Mon – Fri</span> &nbsp; 07:00 – 21:00</li>
                    <li><span style="color:#94A3B8">Saturday</span> &nbsp; 08:00 – 17:00</li>
                    <li><span style="color:#94A3B8">Sunday</span> &nbsp; Closed</li>
                </ul>
            </div>
        </div>
        <div class="u-footer-bottom">
            <span>&copy; <?= date('Y') ?> <?= APP_NAME ?> &mdash; v<?= APP_VERSION ?></span>
            <span>Built with <i class="fas fa-heart" style="color:var(--u-gold)"></i> for VNUIS Campus</span>
        </div>
    </div>
</footer>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap 5.3 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- AOS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<!-- User Theme JS -->
<script src="<?= APP_URL ?>/assets/js/user-main.js"></script>

<?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>
