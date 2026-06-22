<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

if (isLoggedIn()) redirect(APP_URL . '/index.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Account — <?= APP_NAME ?></title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-bg-shapes">
    <div class="auth-shape" style="width:400px;height:400px;top:-150px;right:-100px;"></div>
    <div class="auth-shape" style="width:250px;height:250px;bottom:-80px;left:-60px;"></div>
  </div>

  <div class="auth-card" style="max-width:520px">
    <div class="auth-logo">
      <div class="auth-logo-icon" style="background:none;box-shadow:none;border-radius:0">
        <img src="<?= APP_URL ?>/assets/is.png" alt="VNUIS Logo" style="width:90px;height:90px;object-fit:contain">
      </div>
      <div class="auth-title">Create Account</div>
      <div class="auth-subtitle">Join <?= APP_NAME ?> to book campus facilities</div>
    </div>

    <?= displayFlash() ?>

    <form action="<?= APP_URL ?>/controllers/AuthController.php?action=register" method="POST" id="regForm" novalidate>
      <div class="row g-3">
        <div class="col-12">
          <label class="form-label"><i class="fas fa-user me-1"></i>Full Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="full_name" placeholder="Nguyen Van A" required minlength="2" maxlength="100">
          <div class="invalid-feedback">Full name is required.</div>
        </div>
        <div class="col-12">
          <label class="form-label"><i class="fas fa-envelope me-1"></i>Email Address <span class="text-danger">*</span></label>
          <input type="email" class="form-control" name="email" placeholder="you@vnuis.edu.vn" required>
          <div class="invalid-feedback">Please enter a valid email.</div>
        </div>
        <div class="col-md-6">
          <label class="form-label"><i class="fas fa-lock me-1"></i>Password <span class="text-danger">*</span></label>
          <input type="password" class="form-control" id="regPwd" name="password" placeholder="Min. 6 characters" required minlength="6">
          <div class="invalid-feedback">Minimum 6 characters.</div>
        </div>
        <div class="col-md-6">
          <label class="form-label"><i class="fas fa-lock me-1"></i>Confirm Password <span class="text-danger">*</span></label>
          <input type="password" class="form-control" id="regPwdConfirm" name="confirm_password" placeholder="Repeat password" required>
          <div class="invalid-feedback">Passwords must match.</div>
        </div>
        <div class="col-md-6">
          <label class="form-label"><i class="fas fa-user-tag me-1"></i>Role <span class="text-danger">*</span></label>
          <select class="form-select" name="role" required>
            <option value="student">Student</option>
            <option value="lecturer">Lecturer</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label"><i class="fas fa-id-card me-1"></i>Student/Staff Code</label>
          <input type="text" class="form-control" name="student_code" placeholder="e.g. SV2021001">
        </div>
        <div class="col-12">
          <label class="form-label"><i class="fas fa-phone me-1"></i>Phone Number</label>
          <input type="tel" class="form-control" name="phone" placeholder="e.g. 0901234567">
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary w-100 py-2">
            <i class="fas fa-user-plus me-2"></i>Create Account
          </button>
        </div>
      </div>
    </form>

    <div class="auth-footer">
      Already have an account? <a href="<?= APP_URL ?>/views/auth/login.php">Sign in</a>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
  'use strict';
  const form = document.getElementById('regForm');
  const pwd  = document.getElementById('regPwd');
  const conf = document.getElementById('regPwdConfirm');
  form.addEventListener('submit', function (e) {
    if (pwd.value !== conf.value) {
      conf.setCustomValidity('Passwords do not match');
    } else {
      conf.setCustomValidity('');
    }
    if (!this.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
    this.classList.add('was-validated');
  });
  conf.addEventListener('input', function () {
    this.setCustomValidity(this.value !== pwd.value ? 'Passwords do not match' : '');
  });
})();
</script>
</body>
</html>
