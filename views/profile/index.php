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

$errors    = [];
$activeTab = $_GET['tab'] ?? 'profile';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullName    = trim($_POST['full_name'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $studentCode = trim($_POST['student_code'] ?? '');
    $avatarName  = $user['avatar'];
    if (empty($fullName)) $errors[] = 'Full name is required.';
    if (!empty($_FILES['avatar']['tmp_name'])) {
        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            $errors[] = 'Invalid image format.';
        } elseif ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Avatar must be under 2MB.';
        } else {
            $uploadDir = __DIR__ . '/../../uploads/avatars/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $avatarName = 'avatar_' . $userId . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $avatarName);
        }
    }
    if (empty($errors)) {
        $userModel->updateProfile($userId, ['full_name'=>$fullName,'phone'=>$phone?:null,'student_code'=>$studentCode?:null,'avatar'=>$avatarName]);
        $_SESSION['full_name'] = $fullName;
        $_SESSION['avatar']    = $avatarName;
        $user = $userModel->findById($userId);
        flashMessage('success', 'Profile updated successfully.');
        redirect(APP_URL . '/views/profile/index.php?tab=profile');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $pdo     = \getDB();
    $stmt    = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $pwRow   = $stmt->fetch();
    if (!password_verify($current, $pwRow['password'])) $errors[] = 'Current password is incorrect.';
    elseif (strlen($new) < 6)  $errors[] = 'New password must be at least 6 characters.';
    elseif ($new !== $confirm) $errors[] = 'Passwords do not match.';
    else {
        $userModel->updatePassword($userId, $new);
        flashMessage('success', 'Password changed. Please log in again.');
        session_unset(); session_destroy();
        redirect(APP_URL . '/views/auth/login.php');
    }
}

$myBookings    = $bookingModel->getByUser($userId);
$totalBookings = count($myBookings);
$completedCnt  = count(array_filter($myBookings, fn($b) => $b['status'] === 'completed'));
$pendingCnt    = count(array_filter($myBookings, fn($b) => $b['status'] === 'pending'));
$pageTitle     = 'My Profile';

// Admin: original layout
if ($_SESSION['role'] === 'admin') {
    include __DIR__ . '/../../includes/header.php';
    echo '<div class="app-shell">';
    include __DIR__ . '/../../includes/sidebar.php';
    echo '<div class="main-content" id="mainContent">';
    include __DIR__ . '/../../includes/navbar.php';
    echo '<div class="page-content"><h1><i class="fas fa-user-circle me-2 text-primary"></i>My Profile</h1>';
    echo displayFlash();
    echo '<p class="text-muted mt-3">Name: '.sanitize($user['full_name']).' — '.ucfirst($user['role']).'</p>';
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
        <span>My Profile</span>
      </div>
      <h1 style="font-family:var(--u-font-serif);color:#fff;font-size:2rem;margin:0">My Profile</h1>
    </div>
  </div>

  <div class="u-page-inner">
    <div class="container">

      <?= displayFlash() ?>

      <?php if (!empty($errors)): ?>
      <div class="u-alert u-alert-danger mb-4">
        <i class="fas fa-times-circle"></i>
        <ul style="margin:0;padding-left:16px">
          <?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <div class="row g-4">

        <!-- LEFT: Avatar + Stats -->
        <div class="col-lg-3" data-aos="fade-up">
          <div class="u-card text-center mb-4" style="padding:32px 20px">
            <div style="position:relative;display:inline-block;margin-bottom:16px">
              <?php if (!empty($user['avatar'])): ?>
              <img src="<?= APP_URL ?>/uploads/avatars/<?= sanitize($user['avatar']) ?>"
                   id="avatarPreview"
                   style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid var(--u-gold)">
              <?php else: ?>
              <div id="avatarPreview"
                   style="width:90px;height:90px;border-radius:50%;background:var(--u-primary);color:var(--u-gold);font-size:2rem;font-weight:700;display:flex;align-items:center;justify-content:center;border:3px solid var(--u-gold);margin:0 auto">
                <?= strtoupper(substr($user['full_name'],0,1)) ?>
              </div>
              <?php endif; ?>
            </div>
            <div style="font-weight:700;font-size:1rem;font-family:var(--u-font-serif)"><?= sanitize($user['full_name']) ?></div>
            <div style="margin:6px 0"><?= getRoleBadge($user['role']) ?></div>
            <div style="font-size:.8rem;color:var(--u-gray)"><?= sanitize($user['email']) ?></div>
            <?php if (!empty($user['student_code'])): ?>
            <div style="font-size:.78rem;color:var(--u-gray);margin-top:4px"><i class="fas fa-id-card me-1"></i><?= sanitize($user['student_code']) ?></div>
            <?php endif; ?>
            <div style="font-size:.76rem;color:#94A3B8;margin-top:4px"><i class="fas fa-calendar me-1"></i>Joined <?= formatDate($user['created_at'],'M Y') ?></div>
          </div>

          <div class="u-card">
            <div class="u-card-header"><span><i class="fas fa-chart-bar"></i> My Stats</span></div>
            <?php $statRows = [
              ['label'=>'Total Bookings','val'=>$totalBookings,'color'=>'var(--u-primary)'],
              ['label'=>'Completed','val'=>$completedCnt,'color'=>'#10B981'],
              ['label'=>'Pending','val'=>$pendingCnt,'color'=>'#F59E0B'],
            ]; foreach ($statRows as $sr): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:11px 20px;border-bottom:1px solid var(--u-border)">
              <span style="font-size:.84rem;color:var(--u-gray)"><?= $sr['label'] ?></span>
              <span style="font-weight:700;color:<?= $sr['color'] ?>"><?= $sr['val'] ?></span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- RIGHT: Tabs -->
        <div class="col-lg-9" data-aos="fade-up" data-aos-delay="60">
          <div class="u-card">
            <!-- Tab nav -->
            <div style="border-bottom:1px solid var(--u-border);padding:0 22px">
              <div style="display:flex;gap:0">
                <a href="?tab=profile"
                   style="display:inline-block;padding:16px 20px;font-size:.88rem;font-weight:600;border-bottom:3px solid <?= $activeTab==='profile'?'var(--u-gold)':'transparent' ?>;color:<?= $activeTab==='profile'?'var(--u-primary)':'var(--u-gray)' ?>;margin-bottom:-1px">
                  <i class="fas fa-user me-1"></i> Edit Profile
                </a>
                <a href="?tab=password"
                   style="display:inline-block;padding:16px 20px;font-size:.88rem;font-weight:600;border-bottom:3px solid <?= $activeTab==='password'?'var(--u-gold)':'transparent' ?>;color:<?= $activeTab==='password'?'var(--u-primary)':'var(--u-gray)' ?>;margin-bottom:-1px">
                  <i class="fas fa-lock me-1"></i> Change Password
                </a>
              </div>
            </div>

            <div class="u-card-body">
              <?php if ($activeTab === 'profile'): ?>
              <form method="POST" enctype="multipart/form-data" novalidate>
                <div class="row g-3">
                  <!-- Avatar -->
                  <div class="col-12 text-center mb-2">
                    <div style="position:relative;display:inline-block">
                      <?php if (!empty($user['avatar'])): ?>
                      <img src="<?= APP_URL ?>/uploads/avatars/<?= sanitize($user['avatar']) ?>"
                           id="avatarPreview2"
                           style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid var(--u-gold)">
                      <?php else: ?>
                      <div id="avatarPreview2"
                           style="width:90px;height:90px;border-radius:50%;background:var(--u-primary);color:var(--u-gold);font-size:2rem;font-weight:700;display:flex;align-items:center;justify-content:center;border:3px solid var(--u-gold);margin:0 auto">
                        <?= strtoupper(substr($user['full_name'],0,1)) ?>
                      </div>
                      <?php endif; ?>
                      <label for="avatarInput2"
                             style="position:absolute;bottom:0;right:0;width:28px;height:28px;background:var(--u-gold);border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;border:2px solid #fff">
                        <i class="fas fa-camera" style="font-size:.65rem;color:var(--u-primary)"></i>
                      </label>
                    </div>
                    <input type="file" id="avatarInput2" name="avatar" accept="image/*" class="d-none"
                           onchange="previewAvatar(this,'avatarPreview2')">
                    <div style="font-size:.76rem;color:var(--u-gray);margin-top:6px">Click camera to change photo</div>
                  </div>

                  <div class="col-md-6">
                    <label class="u-form-label">Full Name <span style="color:#EF4444">*</span></label>
                    <input type="text" class="u-form-control" name="full_name"
                           value="<?= sanitize($user['full_name']) ?>" required>
                  </div>
                  <div class="col-md-6">
                    <label class="u-form-label">Email Address</label>
                    <input type="email" class="u-form-control" value="<?= sanitize($user['email']) ?>" disabled
                           style="background:var(--u-off-white);cursor:not-allowed">
                    <div style="font-size:.75rem;color:var(--u-gray);margin-top:4px">Cannot be changed. Contact admin.</div>
                  </div>
                  <div class="col-md-6">
                    <label class="u-form-label">Phone Number</label>
                    <input type="tel" class="u-form-control" name="phone"
                           value="<?= sanitize($user['phone'] ?? '') ?>" placeholder="e.g. 0901234567">
                  </div>
                  <div class="col-md-6">
                    <label class="u-form-label">Student / Staff Code</label>
                    <input type="text" class="u-form-control" name="student_code"
                           value="<?= sanitize($user['student_code'] ?? '') ?>" placeholder="e.g. SV2021001">
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
                    <label class="u-form-label">Current Password <span style="color:#EF4444">*</span></label>
                    <input type="password" class="u-form-control" name="current_password" required autocomplete="current-password">
                  </div>
                  <div class="col-12">
                    <label class="u-form-label">New Password <span style="color:#EF4444">*</span></label>
                    <input type="password" class="u-form-control" name="new_password" required minlength="6" autocomplete="new-password">
                    <div style="font-size:.75rem;color:var(--u-gray);margin-top:4px">Minimum 6 characters.</div>
                  </div>
                  <div class="col-12">
                    <label class="u-form-label">Confirm New Password <span style="color:#EF4444">*</span></label>
                    <input type="password" class="u-form-control" name="confirm_password" required autocomplete="new-password">
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
const APP_URL = "<?= APP_URL ?>";
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
      img.src = e.target.result;
      img.id  = previewId;
      img.style.cssText = 'width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid var(--u-gold)';
      img.alt = 'Avatar';
      el.replaceWith(img);
    }
  };
  reader.readAsDataURL(file);
}
</script>
