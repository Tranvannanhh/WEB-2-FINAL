<?php
$unread  = isLoggedIn() ? getUnreadNotificationCount($_SESSION['user_id']) : 0;
$curPage = basename($_SERVER['PHP_SELF']);
$curDir  = basename(dirname($_SERVER['PHP_SELF']));
$role    = $_SESSION['role'] ?? 'student';

$links = [
  ['href' => APP_URL.'/views/dashboard/index.php',  'label' => 'Home',        'icon' => 'home',         'dir' => 'dashboard', 'page' => 'index.php'],
  ['href' => APP_URL.'/views/facilities/index.php', 'label' => 'Facilities',  'icon' => 'building',     'dir' => 'facilities','page' => 'index.php'],
  ['href' => APP_URL.'/views/bookings/create.php',  'label' => 'Book Now',    'icon' => 'calendar-plus','dir' => 'bookings',  'page' => 'create.php'],
  ['href' => APP_URL.'/views/bookings/index.php',   'label' => 'My Bookings', 'icon' => 'list-alt',     'dir' => 'bookings',  'page' => 'index.php'],
];
?>
<nav class="u-nav" id="uNav">
  <div class="u-nav-inner">

    <!-- Brand -->
    <a href="<?= APP_URL ?>/views/dashboard/index.php" class="u-logo">
      <div class="u-logo-box"><i class="fas fa-calendar-check"></i></div>
      VNUIS&nbsp;<span class="u-logo-sub">Booking</span>
    </a>

    <!-- Desktop nav links -->
    <ul class="u-nav-links">
      <?php foreach ($links as $l):
        $active = ($curDir === $l['dir'] && $curPage === $l['page']);
      ?>
      <li>
        <a href="<?= $l['href'] ?>" class="u-nav-link <?= $active ? 'is-active' : '' ?>">
          <i class="fas fa-<?= $l['icon'] ?>"></i> <?= $l['label'] ?>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>

    <!-- Right actions -->
    <div class="u-nav-actions">

      <!-- Notification bell -->
      <div class="dropdown">
        <button class="u-nav-icon" data-bs-toggle="dropdown" id="notifBtn" title="Notifications">
          <i class="fas fa-bell"></i>
          <?php if ($unread > 0): ?>
          <span class="badge-dot" id="notifBadge"><?= $unread > 9 ? '9+' : $unread ?></span>
          <?php endif; ?>
        </button>
        <div class="dropdown-menu dropdown-menu-end u-notif-dd" style="min-width:340px;max-width:92vw">
          <div class="u-notif-dd-head">
            <span>Notifications <?php if ($unread > 0): ?><span class="badge bg-danger rounded-pill"><?= $unread ?></span><?php endif; ?></span>
            <a href="#" id="markAllRead" style="font-size:.8rem;color:var(--gold)">Mark all read</a>
          </div>
          <div id="notifList" style="max-height:320px;overflow-y:auto">
            <div class="text-center py-4 text-muted small"><i class="fas fa-spinner fa-spin"></i></div>
          </div>
          <div class="text-center border-top py-2">
            <a href="<?= APP_URL ?>/views/notifications/index.php" style="font-size:.81rem;color:var(--gold)">
              View all notifications →
            </a>
          </div>
        </div>
      </div>

      <!-- User pill -->
      <div class="dropdown">
        <button class="u-user-pill" data-bs-toggle="dropdown">
          <div class="u-avt">
            <?php if (!empty($_SESSION['avatar'])): ?>
              <img src="<?= APP_URL ?>/uploads/avatars/<?= sanitize($_SESSION['avatar']) ?>" alt="">
            <?php else: ?>
              <?= strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1)) ?>
            <?php endif; ?>
          </div>
          <span class="d-none d-sm-inline" style="max-width:110px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
            <?= sanitize(explode(' ', $_SESSION['full_name'] ?? 'User')[0]) ?>
          </span>
          <i class="fas fa-chevron-down" style="font-size:.65rem;opacity:.7"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow" style="border-radius:14px;border:1px solid var(--border);min-width:210px;padding:8px">
          <li>
            <div class="px-3 py-2 border-bottom mb-1">
              <div style="font-weight:700;font-size:.9rem"><?= sanitize($_SESSION['full_name'] ?? '') ?></div>
              <div style="font-size:.75rem;color:var(--muted)"><?= ucfirst($role) ?> · <?= sanitize($_SESSION['email'] ?? '') ?></div>
            </div>
          </li>
          <li><a class="dropdown-item rounded-2 py-2" href="<?= APP_URL ?>/views/profile/index.php">
            <i class="fas fa-user-circle me-2 text-primary"></i>My Profile</a></li>
          <li><a class="dropdown-item rounded-2 py-2" href="<?= APP_URL ?>/views/notifications/index.php">
            <i class="fas fa-bell me-2 text-warning"></i>Notifications
            <?php if ($unread > 0): ?><span class="badge bg-danger rounded-pill ms-1 float-end"><?= $unread ?></span><?php endif; ?>
          </a></li>
          <?php if ($role === 'student'): ?>
          <li><a class="dropdown-item rounded-2 py-2" href="<?= APP_URL ?>/views/reports/facility.php">
            <i class="fas fa-flag me-2 text-danger"></i>Report Issue</a></li>
          <?php endif; ?>
          <li><hr class="dropdown-divider my-1"></li>
          <li><a class="dropdown-item rounded-2 py-2 text-danger" href="<?= APP_URL ?>/controllers/AuthController.php?action=logout"
                 onclick="return confirm('Logout?')">
            <i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
        </ul>
      </div>

      <!-- Mobile hamburger -->
      <button class="u-nav-icon d-lg-none" id="mobileMenuBtn" aria-label="Menu">
        <i class="fas fa-bars"></i>
      </button>
    </div>
  </div>

  <!-- Mobile drawer -->
  <div class="u-mobile-drawer" id="mobileDrawer">
    <?php foreach ($links as $l):
      $active = ($curDir === $l['dir'] && $curPage === $l['page']);
    ?>
    <a href="<?= $l['href'] ?>" class="<?= $active ? 'is-active' : '' ?>">
      <i class="fas fa-<?= $l['icon'] ?>"></i> <?= $l['label'] ?>
    </a>
    <?php endforeach; ?>
    <a href="<?= APP_URL ?>/views/profile/index.php">
      <i class="fas fa-user-circle"></i> My Profile
    </a>
    <?php if ($role === 'student'): ?>
    <a href="<?= APP_URL ?>/views/reports/facility.php">
      <i class="fas fa-flag"></i> Report Issue
    </a>
    <?php endif; ?>
  </div>
</nav>
