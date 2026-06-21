    <!-- ==================== FOOTER ==================== -->
    <footer class="app-footer mt-auto py-3 border-top bg-white">
        <div class="container-fluid px-4">
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-2">
                <span class="text-muted small">
                    &copy; <?= date('Y') ?> <strong><?= APP_NAME ?></strong> &mdash; Version <?= APP_VERSION ?>
                </span>
                <span class="text-muted small">
                    Built with <i class="fas fa-heart text-danger"></i> for VNUIS Campus
                </span>
            </div>
        </div>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- AOS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Chart.js -->
    <script src="<?= APP_URL ?>/assets/js/chart.umd.min.js"></script>

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <!-- Custom JS -->
    <script src="<?= APP_URL ?>/assets/js/main.js"></script>

    <?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>
