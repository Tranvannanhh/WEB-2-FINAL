<?php
// Determine active page for sidebar highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));
?>
<!-- ==================== SIDEBAR ==================== -->
<nav id="sidebar" class="sidebar">
    <!-- Brand -->
    <div class="sidebar-brand">
        <a href="<?= APP_URL ?>/index.php" class="brand-link">
            <div class="brand-icon">
                <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="28" height="28" rx="6" fill="#2563EB"/>
                    <path d="M7 10h14M7 14h14M7 18h10" stroke="white" stroke-width="2" stroke-linecap="round"/>
                    <rect x="9" y="6" width="3" height="4" rx="1" fill="white"/>
                    <rect x="16" y="6" width="3" height="4" rx="1" fill="white"/>
                </svg>
            </div>
            <span class="brand-text">VNUIS Booking</span>
        </a>
        <button id="sidebarToggle" class="sidebar-toggle-btn" title="Toggle sidebar">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- User Info -->
    <div class="sidebar-user">
        <div class="user-avatar-sm">
            <?php if (!empty($_SESSION['avatar'])): ?>
                <img src="<?= APP_URL ?>/uploads/avatars/<?= sanitize($_SESSION['avatar']) ?>" alt="Avatar">
            <?php else: ?>
                <div class="avatar-initials">
                    <?= strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1)) ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="user-info">
            <span class="user-name"><?= sanitize($_SESSION['full_name'] ?? 'User') ?></span>
            <span class="user-role"><?= ucfirst($_SESSION['role'] ?? '') ?></span>
        </div>
    </div>

    <!-- Nav Menu -->
    <ul class="sidebar-menu">

        <?php if ($_SESSION['role'] === 'admin'): ?>
        <!-- ADMIN MENU -->
        <li class="menu-header"><span>Main</span></li>

        <li class="<?= $currentDir === 'dashboard' && $currentPage === 'admin.php' ? 'active' : '' ?>">
            <a href="<?= APP_URL ?>/views/dashboard/admin.php">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="menu-header"><span>Management</span></li>

        <li class="<?= $currentDir === 'users' ? 'active' : '' ?>">
            <a href="<?= APP_URL ?>/views/users/manage.php">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
        </li>

        <li class="<?= $currentDir === 'facilities' && in_array($currentPage, ['manage.php','form.php']) ? 'active' : '' ?>">
            <a href="<?= APP_URL ?>/views/facilities/manage.php">
                <i class="fas fa-building"></i>
                <span>Facilities</span>
            </a>
        </li>

        <li class="<?= $currentDir === 'bookings' && $currentPage === 'manage.php' ? 'active' : '' ?>">
            <a href="<?= APP_URL ?>/views/bookings/manage.php">
                <i class="fas fa-calendar-check"></i>
                <span>Bookings</span>
            </a>
        </li>

        <li class="menu-header"><span>Reports & Tools</span></li>

        <li class="<?= $currentDir === 'reports' && $currentPage === 'index.php' ? 'active' : '' ?>">
            <a href="<?= APP_URL ?>/views/reports/index.php">
                <i class="fas fa-chart-bar"></i>
                <span>Analytics</span>
            </a>
        </li>

        <li class="<?= $currentDir === 'reports' && $currentPage === 'facility.php' ? 'active' : '' ?>">
            <a href="<?= APP_URL ?>/views/reports/facility.php">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Issue Reports</span>
            </a>
        </li>

        <?php else: ?>
        <!-- STUDENT / LECTURER MENU -->
        <li class="menu-header"><span>Main</span></li>

        <li class="<?= $currentDir === 'dashboard' && $currentPage === 'index.php' ? 'active' : '' ?>">
            <a href="<?= APP_URL ?>/views/dashboard/index.php">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="menu-header"><span>Facilities</span></li>

        <li class="<?= $currentDir === 'facilities' && $currentPage === 'index.php' ? 'active' : '' ?>">
            <a href="<?= APP_URL ?>/views/facilities/index.php">
                <i class="fas fa-building"></i>
                <span>Browse Facilities</span>
            </a>
        </li>

        <li class="menu-header"><span>Bookings</span></li>

        <li class="<?= $currentDir === 'bookings' && $currentPage === 'create.php' ? 'active' : '' ?>">
            <a href="<?= APP_URL ?>/views/bookings/create.php">
                <i class="fas fa-plus-circle"></i>
                <span>New Booking</span>
            </a>
        </li>

        <li class="<?= $currentDir === 'bookings' && $currentPage === 'index.php' ? 'active' : '' ?>">
            <a href="<?= APP_URL ?>/views/bookings/index.php">
                <i class="fas fa-list"></i>
                <span>My Bookings</span>
            </a>
        </li>

        <li class="menu-header"><span>Account</span></li>

        <li class="<?= $currentDir === 'notifications' ? 'active' : '' ?>">
            <a href="<?= APP_URL ?>/views/notifications/index.php">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
                <?php
                $unread = isLoggedIn() ? getUnreadNotificationCount($_SESSION['user_id']) : 0;
                if ($unread > 0): ?>
                <span class="badge bg-danger rounded-pill ms-auto"><?= $unread ?></span>
                <?php endif; ?>
            </a>
        </li>

        <?php if ($_SESSION['role'] === 'student'): ?>
        <li class="<?= $currentDir === 'reports' && $currentPage === 'facility.php' ? 'active' : '' ?>">
            <a href="<?= APP_URL ?>/views/reports/facility.php">
                <i class="fas fa-flag"></i>
                <span>Report Issue</span>
            </a>
        </li>
        <?php endif; ?>

        <?php endif; ?>

        <!-- Shared -->
        <li class="menu-header"><span>Account</span></li>

        <li class="<?= $currentDir === 'profile' ? 'active' : '' ?>">
            <a href="<?= APP_URL ?>/views/profile/index.php">
                <i class="fas fa-user-circle"></i>
                <span>My Profile</span>
            </a>
        </li>

        <li>
            <a href="<?= APP_URL ?>/controllers/AuthController.php?action=logout"
               onclick="return confirm('Are you sure you want to logout?')">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</nav>
