<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Notification.php';

requireLogin();
$notifModel = new Notification();
$userId     = $_SESSION['user_id'];

$action = $_GET['action'] ?? '';
if ($action === 'mark_read' && isset($_GET['id'])) {
    $notifModel->markRead(intval($_GET['id']), $userId);
    redirect(APP_URL.'/views/notifications/index.php');
}
if ($action === 'mark_all') {
    $notifModel->markAllRead($userId);
    flashMessage('success','All notifications marked as read.');
    redirect(APP_URL.'/views/notifications/index.php');
}
if ($action === 'delete' && isset($_GET['id'])) {
    $notifModel->delete(intval($_GET['id']), $userId);
    flashMessage('success','Notification deleted.');
    redirect(APP_URL.'/views/notifications/index.php');
}

$notifications = $notifModel->getByUser($userId, 100);
$unread        = $notifModel->getUnreadCount($userId);
$pageTitle     = 'Notifications';

// Admin
if ($_SESSION['role'] === 'admin') {
    include __DIR__ . '/../../includes/header.php';
    echo '<div class="app-shell">'; include __DIR__ . '/../../includes/sidebar.php';
    echo '<div class="main-content" id="mainContent">'; include __DIR__ . '/../../includes/navbar.php';
    echo '<div class="page-content"><h1 class="mb-3"><i class="fas fa-bell me-2 text-primary"></i>Notifications</h1>';
    echo displayFlash();
    echo '<div class="card"><div class="card-body p-0">';
    foreach ($notifications as $n) {
        $bg = $n['is_read'] ? '' : 'background:#EFF6FF';
        echo '<div class="d-flex gap-3 px-4 py-3 border-bottom" style="'.$bg.'"><div style="flex:1">';
        echo '<div class="fw-semibold small">'.sanitize($n['title']).'</div>';
        echo '<div class="text-muted small">'.sanitize($n['message']).'</div>';
        echo '<div class="text-muted" style="font-size:.72rem">'.timeAgo($n['created_at']).'</div></div>';
        echo '<div class="d-flex gap-1 align-items-center">';
        if (!$n['is_read']) echo '<a href="?action=mark_read&id='.$n['id'].'" class="btn btn-xs btn-outline-primary"><i class="fas fa-check"></i></a>';
        echo '<a href="?action=delete&id='.$n['id'].'" class="btn btn-xs btn-outline-danger" onclick="return confirm(\'Delete?\')"><i class="fas fa-trash"></i></a>';
        echo '</div></div>';
    }
    echo '</div></div>';
    include __DIR__ . '/../../includes/footer.php'; echo '</div></div>'; exit;
}
?>
<?php include __DIR__ . '/../../includes/user_header.php'; ?>
<?php include __DIR__ . '/../../includes/user_navbar.php'; ?>

<div class="u-page">
  <div class="u-banner">
    <div class="container">
      <div class="u-bc"><a href="<?= APP_URL ?>/views/dashboard/index.php">Home</a><span class="u-bc-sep">›</span><span>Notifications</span></div>
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
        <div>
          <h1 class="u-banner-title" style="margin-bottom:6px">
            Notifications
            <?php if ($unread > 0): ?>
            <span style="font-size:1rem;background:var(--red);color:#fff;border-radius:50px;padding:2px 12px;vertical-align:middle;font-family:var(--font)"><?= $unread ?></span>
            <?php endif; ?>
          </h1>
          <p class="u-banner-sub" style="margin:0">Stay up to date with your booking activity</p>
        </div>
        <?php if ($unread > 0): ?>
        <a href="?action=mark_all" onclick="return confirm('Mark all notifications as read?')" class="u-btn u-btn-outline u-btn-sm" style="color:#fff;border-color:rgba(255,255,255,.35)">
          <i class="fas fa-check-double"></i> Mark All Read
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="u-content">
    <div class="container">
      <?= displayFlash() ?>

      <?php if (empty($notifications)): ?>
      <div class="u-card"><div class="u-empty">
        <i class="fas fa-bell-slash u-empty-icon"></i>
        <h5>No notifications yet</h5>
        <p>You'll receive updates about your bookings and campus announcements here.</p>
      </div></div>
      <?php else: ?>
      <div class="u-card" data-aos="fade-up">
        <?php foreach ($notifications as $n): ?>
        <div class="u-notif-row <?= $n['is_read'] ? '' : 'unread' ?>">
          <div class="u-notif-icon"><i class="fas fa-bell"></i></div>
          <div style="flex:1;min-width:0">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px">
              <div>
                <div class="u-notif-title">
                  <?= sanitize($n['title']) ?>
                  <?php if (!$n['is_read']): ?>
                  <span class="u-new-badge">NEW</span>
                  <?php endif; ?>
                </div>
                <div class="u-notif-msg"><?= sanitize($n['message']) ?></div>
                <div class="u-notif-time">
                  <i class="fas fa-clock me-1"></i><?= timeAgo($n['created_at']) ?> · <?= formatDateTime($n['created_at'],'d M Y H:i') ?>
                </div>
              </div>
              <div style="display:flex;gap:6px;flex-shrink:0;padding-top:2px">
                <?php if (!$n['is_read']): ?>
                <a href="?action=mark_read&id=<?= $n['id'] ?>" class="u-icon-btn-sm" title="Mark as read"><i class="fas fa-check"></i></a>
                <?php endif; ?>
                <a href="?action=delete&id=<?= $n['id'] ?>" onclick="return confirm('Delete this notification?')"
                   class="u-icon-btn-sm danger" title="Delete"><i class="fas fa-trash"></i></a>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/user_footer.php'; ?>
