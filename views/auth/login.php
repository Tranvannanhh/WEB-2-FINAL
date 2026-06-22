<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

if (isLoggedIn()) {
    redirect(APP_URL . '/index.php');
}

$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In — <?= APP_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='6' fill='%232563EB'/><text x='4' y='24' font-size='22' fill='white'>📅</text></svg>">
</head>
<body>
<div class="auth-page">
  <div class="auth-bg-shapes">
    <div class="auth-shape" style="width:400px;height:400px;top:-150px;right:-100px;"></div>
    <div class="auth-shape" style="width:250px;height:250px;bottom:-80px;left:-60px;"></div>
    <div class="auth-shape" style="width:180px;height:180px;top:40%;left:5%;opacity:.5;"></div>
  </div>

  <div class="auth-card" data-aos="fade-up">
    <!-- Logo -->
    <div class="auth-logo">
      <div class="auth-logo-icon">
        <img src="<?= APP_URL ?>/assets/img/vnuis-logo.png" alt="VNU-IS Logo">
      </div>
      <div class="auth-title"><?= APP_NAME ?></div>
      <div class="auth-subtitle">Sign in to your account to continue</div>
    </div>

    <?php if ($msg === 'logged_out'): ?>
    <div class="alert alert-info py-2 small"><i class="fas fa-info-circle me-1"></i>You have been logged out successfully.</div>
    <?php elseif ($msg === 'timeout'): ?>
    <div class="alert alert-warning py-2 small"><i class="fas fa-clock me-1"></i>Your session expired. Please sign in again.</div>
    <?php endif; ?>

    <?= displayFlash() ?>

    <form action="<?= APP_URL ?>/controllers/AuthController.php?action=login" method="POST" id="loginForm" novalidate>
      <div class="mb-3">
        <label class="form-label" for="email"><i class="fas fa-envelope me-1"></i>Email Address</label>
        <input type="email" class="form-control" id="email" name="email"
               placeholder="you@vnuis.edu.vn" required autocomplete="email">
        <div class="invalid-feedback">Please enter a valid email.</div>
      </div>

      <div class="mb-3">
        <label class="form-label" for="password"><i class="fas fa-lock me-1"></i>Password</label>
        <div class="input-group">
          <input type="password" class="form-control" id="password" name="password"
                 placeholder="Enter your password" required autocomplete="current-password">
          <button class="btn btn-outline-secondary" type="button" id="togglePwd">
            <i class="fas fa-eye"></i>
          </button>
        </div>
      </div>

      <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="form-check mb-0">
          <input class="form-check-input" type="checkbox" name="remember" id="remember">
          <label class="form-check-label small" for="remember">Remember me</label>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100 py-2 fw-600">
        <i class="fas fa-sign-in-alt me-2"></i>Sign In
      </button>
    </form>

    <div class="auth-divider">or</div>

    <div class="text-center mb-2">
      <small class="text-muted">Demo credentials:</small><br>
      <code class="small">admin@vnuis.edu.vn / admin123</code><br>
      <code class="small">student@vnuis.edu.vn / admin123</code>
    </div>

    <div class="auth-footer">
      Don't have an account? <a href="<?= APP_URL ?>/views/auth/register.php">Create one</a>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Toggle password visibility
document.getElementById('togglePwd').addEventListener('click', function () {
  const pwd = document.getElementById('password');
  const icon = this.querySelector('i');
  if (pwd.type === 'password') {
    pwd.type = 'text';
    icon.classList.replace('fa-eye', 'fa-eye-slash');
  } else {
    pwd.type = 'password';
    icon.classList.replace('fa-eye-slash', 'fa-eye');
  }
});
// Bootstrap validation
(function () {
  'use strict';
  document.getElementById('loginForm').addEventListener('submit', function (e) {
    if (!this.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
    this.classList.add('was-validated');
  });
})();
</script>
</body>
</html>
