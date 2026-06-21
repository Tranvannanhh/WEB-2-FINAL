<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Booking.php';

requireLogin();
$userModel    = new User();
$bookingModel = new Booking();
$userId       = $_SESSION['user_id'];
$user         = $userModel->findById($userId);
$errors       = [];
$activeTab    = $_GET['tab'] ?? 'profile';

// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullName    = trim($_POST['full_name'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $studentCode = trim($_POST['student_code'] ?? '');
    $avatarName  = $user['avatar'];
    if (empty($fullName)) $errors[] = 'Full name is required.';
    if (!empty($_FILES['avatar']['tmp_name'])) {
        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext,['jpg','jpeg','png','gif','webp'])) $errors[] = 'Invalid image format.';
        elseif ($_FILES['avatar']['size'] > 2*1024*1024) $errors[] = 'Avatar must be under 2MB.';
        else {
            $uploadDir = __DIR__.'/../../uploads/avatars/';
            if (!is_dir($uploadDir)) mkdir($uploadDir,0755,true);
            $avatarName = 'avatar_'.$userId.'_'.time().'.'.$ext;
            move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir.$avatarName);
        }
    }
    if (empty($errors)) {
        $userModel->updateProfile($userId,['full_name'=>$fullName,'phone'=>$phone?:null,'student_code'=>$studentCode?:null,'avatar'=>$avatarName]);
        $_SESSION['full_name'] = $fullName; $_SESSION['avatar'] = $avatarName;
        $user = $userModel->findById($userId);
        flashMessage('success','Profile updated successfully.');
        redirect(APP_URL.'/views/profile/index.php?tab=profile');
    }
}

// Change password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $pdo = \getDB(); $stmt = $pdo->prepare("SELECT password FROM users WHERE id=?"); $stmt->execute([$userId]); $pw = $stmt->fetch();
    if (!password_verify($current,$pw['password'])) $errors[] = 'Current password is incorrect.';
    elseif (strlen($new)<6) $errors[] = 'New password must be at least 6 characters.';
    elseif ($new!==$confirm) $errors[] = 'Passwords do not match.';
    else {
        $userModel->updatePassword($userId,$new);
        flashMessage('success','Password changed successfully. Please log in again.');
        session_unset(); session_destroy();
        redirect(APP_URL.'/views/auth/login.php');
    }
}

$all       = $bookingModel->getByUser($userId);
$total     = count($all);
$completed = count(array_filter($all, fn($b)=>$b['status']==='completed'));
$pending   = count(array_filter($all, fn($b)=>$b['status']==='pending'));
$pageTitle = 'My Profile';

// Admin
if ($_SESSION['role'] === 'admin') {
    include __DIR__ . '/../../includes/header.php';
    echo '<div class="app-shell">'; include __DIR__ . '/../../includes/sidebar.php';
    echo '<div class="main-content" id="mainContent">'; include __DIR__ . '/../../includes/navbar.php';
    echo '<div class="page-content"><h1><i class="fas fa-user-circle me-2 text-primary"></i>My Profile</h1>'.displayFlash();
    echo '<p class="text-muted mt-3">'.sanitize($user['full_name']).' — '.ucfirst($user['role']).'</p>';
    include __DIR__ . '/../../includes/footer.php'; echo '</div></div>'; exit;
}
?>
<?php include __DIR__ . '/../../includes/user_header.php'; ?>
<?php include __DIR__ . '/../../includes/user_navbar.php'; ?>

<div class="u-page">
  <div class="u-banner">
    <div class="container">
      <div class="u-bc"><a href="<?= APP_URL ?>/views/dashboard/index.php">Home</a><span class="u-bc-sep">›</span><span>My Profile</span></div>
      <h1 class="u-banner-title">My Profile</h1>
    </div>
  </div>

  <div class="u-content">
    <div class="container">
      <?= displayFlash() ?>

      <?php if (!empty($errors)): ?>
      <div class="u-alert u-alert-danger">
        <i class="fas fa-times-circle"></i>
        <ul style="margin:0;padding-left:16px"><?php foreach($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul>
      </div>
      <?php endif; ?>

      <div class="row g-4">

        <!-- LEFT: card -->
        <div class="col-lg-3" data-aos="fade-up">
          <div class="u-card mb-4" style="padding:28px 20px;text-align:center">
            <div style="margin-bottom:16px">
              <?php if (!empty($user['avatar'])): ?>
              <img src="<?= APP_URL ?>/uploads/avatars/<?= sanitize($user['avatar']) ?>"
                   class="u-profile-avt" style="margin:0 auto">
              <?php else: ?>
              <div class="u-profile-avt-init" style="margin:0 auto">
                <?= strtoupper(substr($user['full_name'],0,1)) ?>
              </div>
              <?php endif; ?>
            </div>
            <div style="font-weight:800;font-size:1rem;margin-bottom:6px"><?= sanitize($user['full_name']) ?></div>
            <div style="margin-bottom:8px"><?= getRoleBadge($user['role']) ?></div>
            <div style="font-size:.8rem;color:var(--muted)"><?= sanitize($user['email']) ?></div>
            <?php if (!empty($user['student_code'])): ?>
            <div style="font-size:.78rem;color:var(--muted);margin-top:4px"><i class="fas fa-id-card me-1"></i><?= sanitize($user['student_code']) ?></div>
            <?php endif; ?>
            <div style="font-size:.76rem;color:#94a3b8;margin-top:4px"><i class="fas fa-calendar me-1"></i>Joined <?= formatDate($user['created_at'],'M Y') ?></div>
          </div>

          <div class="u-card">
            <div class="u-card-hd"><span><i class="fas fa-chart-bar"></i> My Stats</span></div>
            <?php $stats=[['Total Bookings',$total,'var(--p)'],['Completed',$completed,'var(--green)'],['Pending',$pending,'var(--amber)']];
            foreach($stats as [$lbl,$val,$col]): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:11px 18px;border-bottom:1px solid var(--border)">
              <span style="font-size:.84rem;color:var(--muted)"><?= $lbl ?></span>
              <span style="font-weight:800;color:<?= $col ?>"><?= $val ?></span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- RIGHT: tabs -->
        <div class="col-lg-9" data-aos="fade-up" data-aos-delay="60">
          <div class="u-card">
            <div class="u-tabs">
              <a href="?tab=profile" class="u-tab <?= $activeTab==='profile'?'is-active':'' ?>">
                <i class="fas fa-user me-1"></i> Edit Profile
              </a>
              <a href="?tab=password" class="u-tab <?= $activeTab==='password'?'is-active':'' ?>">
                <i class="fas fa-lock me-1"></i> Change Password
              </a>
            </div>
            <div class="u-card-body">

              <?php if ($activeTab === 'profile'): ?>
              <form method="POST" enctype="multipart/form-data" novalidate>
                <div class="row g-3">
                  <div class="col-12 text-center mb-2">
                    <div class="u-profile-avt-wrap" style="display:inline-block">
                      <?php if (!empty($user['avatar'])): ?>
                      <img src="<?= APP_URL ?>/uploads/avatars/<?= sanitize($user['avatar']) ?>" class="u-profile-avt" id="avatarPreview">
                      <?php else: ?>
                      <div class="u-profile-avt-init" id="avatarPreview"><?= strtoupper(substr($user['full_name'],0,1)) ?></div>
                      <?php endif; ?>
                      <label for="avatarInput" class="u-profile-avt-btn" title="Change photo">
                        <i class="fas fa-camera"></i>
                      </label>
                    </div>
                    <input type="file" id="avatarInput" name="avatar" accept="image/*" class="d-none"
                           onchange="previewAvatar(this,'avatarPreview')">
                    <div style="font-size:.75rem;color:var(--muted);margin-top:6px">Click camera icon to change photo</div>
                  </div>
                  <div class="col-md-6">
                    <label class="u-label">Full Name <span class="req">*</span></label>
                    <input type="text" class="u-input" name="full_name" value="<?= sanitize($user['full_name']) ?>" required>
                  </div>
                  <div class="col-md-6">
                    <label class="u-label">Email Address</label>
                    <input type="email" class="u-input" value="<?= sanitize($user['email']) ?>" disabled style="background:var(--bg);cursor:not-allowed">
                    <div class="u-form-hint">Cannot be changed. Contact admin.</div>
                  </div>
                  <div class="col-md-6">
                    <label class="u-label">Phone Number</label>
                    <input type="tel" class="u-input" name="phone" value="<?= sanitize($user['phone'] ?? '') ?>" placeholder="e.g. 0901234567">
                  </div>
                  <div class="col-md-6">
                    <label class="u-label">Student / Staff Code</label>
                    <input type="text" class="u-input" name="student_code" value="<?= sanitize($user['student_code'] ?? '') ?>" placeholder="e.g. SV2021001">
                  </div>
                  <div class="col-12 mt-2">
                    <button type="submit" name="update_profile" value="1" class="u-btn u-btn-gold u-btn-lg">
                      <i class="fas fa-save"></i> Save Changes
                    </button>
                  </div>
                </div>
              </form>

              <?php else: ?>
              <form method="POST" novalidate>
                <div class="row g-3" style="max-width:440px">
                  <div class="col-12">
                    <label class="u-label">Current Password <span class="req">*</span></label>
                    <input type="password" class="u-input" name="current_password" required autocomplete="current-password">
                  </div>
                  <div class="col-12">
                    <label class="u-label">New Password <span class="req">*</span></label>
                    <input type="password" class="u-input" name="new_password" required minlength="6" autocomplete="new-password">
                    <div class="u-form-hint">Minimum 6 characters.</div>
                  </div>
                  <div class="col-12">
                    <label class="u-label">Confirm New Password <span class="req">*</span></label>
                    <input type="password" class="u-input" name="confirm_password" required autocomplete="new-password">
                  </div>
                  <div class="col-12 mt-2">
                    <button type="submit" name="change_password" value="1" class="u-btn u-btn-gold u-btn-lg">
                      <i class="fas fa-key"></i> Change Password
                    </button>
                  </div>
                </div>
              </form>
              <?php endif; ?>

            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/user_footer.php'; ?>
<script>
function previewAvatar(input, previewId) {
  const file = input.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    let el = document.getElementById(previewId);
    if (el && el.tagName === 'IMG') {
      el.src = e.target.result;
    } else if (el) {
      const img = document.createElement('img');
      img.src = e.target.result; img.id = previewId;
      img.className = 'u-profile-avt'; img.style.margin = '0 auto';
      el.replaceWith(img);
    }
  };
  reader.readAsDataURL(file);
}
</script>
