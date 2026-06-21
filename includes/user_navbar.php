<?php
$unreadCount = isLoggedIn() ? getUnreadNotificationCount($_SESSION['user_id']) : 0;
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));

$navLinks = [
    ['href' => APP_URL . '/views/dashboard/index.php',  'label' => 'Home',       'dir' => 'dashboard',  'page' => 'index.php'],
    ['href' => APP_URL . '/views/facilities/index.php', 'label' => 'Facilities', 'dir' => 'facilities', 'page' => 'index.php'],
    ['href' => APP_URL . '/views/bookings/create.php',  'label' => 'Book Now',   'dir' => 'bookings',   'page' => 'create.php'],
    ['href' => APP_URL . '/views/bookings/index.php',   'label' => 'My Bookings','dir' => 'bookings',   'page' => 'index.php'],
];
?>
<!-- ====== USER NAVBAR ====== -->
<header class="u-navbar" id="uNavbar">
    <div class="container-fluid px-4">
        <nav class="d-flex align-items-center justify-content-between" style="height:70px">

            <!-- Brand -->
            <a href="<?= APP_URL ?>/views/dashboard/index.php" class="u-brand">
                <div class="u-brand-icon">
                    <svg width="26" height="26" viewBox="0 0 28 28" fill="none">
                        <rect width="28" height="28" rx="6" fill="white" fill-opacity="0.15"/>
                        <path d="M7 10h14M7 14h14M7 18h10" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        <rect x="9" y="6" width="3" height="4" rx="1" fill="white"/>
                        <rect x="16" y="6" width="3" height="4" rx="1" fill="white"/>
                    </svg>
                </div>
                <span class="u-brand-text">VNUIS <span>Booking</span></span>
            </a>

            <!-- Desktop Nav Links -->
            <ul class="u-nav-links d-none d-lg-flex">
                <?php foreach ($navLinks as $link):
                    $isActive = ($currentDir === $link['dir'] && $currentPage === $link['page']);
                ?>
                <li>
                    <a href="<?= $link['href'] ?>" class="u-nav-link <?= $isActive ? 'active' : '' ?>">
                        <?= $link['label'] ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>

            <!-- Right Actions -->
            <div class="d-flex align-items-center gap-3">

                <!-- Notifications -->
                <div class="dropdown">
                    <button class="u-icon-btn position-relative" data-bs-toggle="dropdown" id="notifDropdownBtn">
                        <i class="fas fa-bell"></i>
                        <?php if ($unreadCount > 0): ?>
                        <span class="u-badge" id="notifBadge"><?= $unreadCount > 9 ? '9+' : $unreadCount ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end u-notif-dropdown shadow-lg" style="min-width:340px">
                        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                            <strong class="small">Notifications</strong>
                            <a href="#" class="text-muted small" id="markAllRead">Mark all read</a>
                        </div>
                        <div id="notifList" style="max-height:320px;overflow-y:auto">
                            <div class="text-center py-3 text-muted small">
                                <i class="fas fa-spinner fa-spin"></i> Loading…
                            </div>
                        </div>
                        <div class="text-center border-top py-2">
                            <a href="<?= APP_URL ?>/views/notifications/index.php" class="small" style="color:var(--u-primary)">View all</a>
                        </div>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="dropdown">
                    <button class="u-user-btn d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                        <div class="u-avatar">
                            <?php if (!empty($_SESSION['avatar'])): ?>
                                <img src="<?= APP_URL ?>/uploads/avatars/<?= sanitize($_SESSION['avatar']) ?>" alt="Avatar">
                            <?php else: ?>
                                <?= strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <span class="d-none d-md-inline"><?= sanitize(explode(' ', $_SESSION['full_name'] ?? 'User')[0]) ?></span>
                        <i class="fas fa-chevron-down small opacity-75"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow" style="border-radius:12px;border:1px solid #eee;min-width:200px">
                        <li>
                            <div class="px-3 py-2 border-bottom">
                                <div class="fw-semibold small"><?= sanitize($_SESSION['full_name'] ?? '') ?></div>
                                <div class="text-muted" style="font-size:.75rem"><?= ucfirst($_SESSION['role'] ?? '') ?></div>
                            </div>
                        </li>
                        <li><a class="dropdown-item py-2" href="<?= APP_URL ?>/views/profile/index.php">
                            <i class="fas fa-user-circle me-2" style="color:var(--u-primary)"></i>My Profile</a></li>
                        <li><a class="dropdown-item py-2" href="<?= APP_URL ?>/views/notifications/index.php">
                            <i class="fas fa-bell me-2" style="color:var(--u-primary)"></i>Notifications
                            <?php if ($unreadCount > 0): ?><span class="badge bg-danger rounded-pill ms-1"><?= $unreadCount ?></span><?php endif; ?>
                        </a></li>
                        <?php if ($_SESSION['role'] === 'student'): ?>
                        <li><a class="dropdown-item py-2" href="<?= APP_URL ?>/views/reports/facility.php">
                            <i class="fas fa-flag me-2 text-warning"></i>Report Issue</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 text-danger" href="<?= APP_URL ?>/controllers/AuthController.php?action=logout"
                               onclick="return confirm('Are you sure you want to logout?')">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>

                <!-- Mobile Hamburger -->
                <button class="u-icon-btn d-lg-none" id="mobileMenuBtn">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </nav>
    </div>

    <!-- Mobile Menu -->
    <div class="u-mobile-menu d-lg-none" id="mobileMenu">
        <ul>
            <?php foreach ($navLinks as $link):
                $isActive = ($currentDir === $link['dir'] && $currentPage === $link['page']);
            ?>
            <li><a href="<?= $link['href'] ?>" class="<?= $isActive ? 'active' : '' ?>"><?= $link['label'] ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
</header>
