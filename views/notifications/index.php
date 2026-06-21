<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Notification.php';

requireLogin();

$notifModel = new Notification();
$userId     = $_SESSION['user_id'];

$action = $_GET['action'] ?? $_POST['action'] ?? '';
if ($action === 'mark_read' && isset($_GET['id'])) {
    $notifModel->markRead(intval($_GET['id']), $userId);
    redirect(APP_URL . '/views/notifications/index.php');
}
if ($action === 'mark_all') {
    $notifModel->markAllRead($userId);
    flashMessage('success', 'All notifications marked as read.');
    redirect(APP_URL . '/views/notifications/index.php');
}
if ($action === 'delete' && isset($_GET['id'])) {
    $notifModel->delete(intval($_GET['id']), $userId);
    flashMessage('success', 'Notification deleted.');
    redirect(APP_URL . '/views/notifications/index.php');
}

$notifications = $notifModel->getByUser($userId, 100);
$unread        = $notifModel->getUnreadCount($userId);
$pageTitle     = 'Notifications';

// Admin uses original layout
if ($_SESSION['role'] === 'admin') {
    include __DIR__ . '/../../includes/header.php';
    echo '<div class="app-shell">';
    include __DIR__ . '/../../includes/sidebar.php';
    echo '<div class="main-content" id="mainContent">';
    include __DIR__ . '/../../includes/navbar.php';
    echo '<div class="page-content">';
    echo '<h1 class="mb-3"><i class="fas fa-bell me-2 text-primary"></i>Notifications</h1>';
    echo displayFlash();
    // Render notifications for admin too
    if (empty($notifications)) {
        echo '<div class="card"><div class="card-body text-center py-4 text-muted">No notifications.</div></div>';
    } else {
        echo '<div class="card"><div class="card-body p-0">';
        foreach ($notifications as $n) {
            $bg = $n['is_read'] ? '' : 'background:#EFF6FF';
            echo '<div class="d-flex gap-3 px-4 py-3 border-bottom" style="'.$bg.'">';
            echo '<div style="flex:1"><div class="fw-semibold small">'.sanitize($n['title']).'</div>';
            echo '<div class="text-muted small">'.sanitize($n['message']).'</div>';
            echo '<div class="text-muted" style="font-size:.72rem">'.timeAgo($n['created_at']).'</div></div>';
            echo '<div class="d-flex gap-1">';
            if (!$n['is_read']) echo '<a href="?action=mark_read&id='.$n['id'].'" class="btn btn-xs btn-outline-primary"><i class="fas fa-check"></i></a>';
            echo '<a href="?action=delete&id='.$n['id'].'" class="btn btn-xs btn-outline-danger" onclick="return confirm(\'Delete?\')"><i class="fas fa-trash"></i></a>';
            echo '</div></div>';
        }
        echo '</div></div>';
    }
    include __DIR__ . '/../../includes/footer.php';
    echo '</div></div>';
    exit;
}
?>
<?php include __DIR__ . '/../../includes/user_header.php'; ?>
<?php include __DIR__ . '/../../includes/user_navbar.php'; ?>

<div class="u-page">

  <div style="background:var(--u-primary);padding:60px 0 36px">
    <div class="container">
      <div class="u-breadcrumb mb-2">
        <a href="<?= APP_URL ?>/views/dashboard/index.php">Home</a>
        <span class="u-breadcrumb-sep">›</span>
        <span>Notifications</span>
      </div>
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
        <div>
          <h1 style="font-family:var(--u-font-serif);color:#fff;font-size:2rem;margin-bottom:4px">
            Notifications <?php if ($unread > 0): ?><span style="font-size:1rem;background:#EF4444;color:#fff;border-radius:50px;padding:2px 10px;vertical-align:middle"><?= $unread ?></span><?php endif; ?>
          </h1>
          <p style="color:rgba(255,255,255,.6);font-size:.9rem;margin:0">Stay up to date with your booking updates</p>
        </div>
        <?php if ($unread > 0): ?>
        <a href="?action=mark_all" onclick="return confirm('Mark all as read?')"
           style="display:inline-flex;align-items:center;gap:6px;padding:9px 20px;border-radius:50px;border:2px solid rgba(255,255,255,.3);color:#fff;font-size:.84rem;font-weight:600">
          <i class="fas fa-check-double"></i> Mark All Read
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="u-page-inner">
    <div class="container">

      <?= displayFlash() ?>

      <?php if (empty($notifications)): ?>
      <div class="u-card">
        <div class="u-empty">
          <i class="fas fa-bell-slash d-block"></i>
          <h5>No notifications yet</h5>
          <p>You'll be notified about booking updates and campus announcements here.</p>
        </div>
      </div>
      <?php else: ?>
      <div class="u-card" data-aos="fade-up">
        <?php foreach ($notifications as $n): ?>
        <div class="u-notif-row <?= $n['is_read'] ? '' : 'unread' ?>">
          <div class="u-notif-dot">
            <i class="fas fa-bell"></i>
          </div>
          <div style="flex:1;min-width:0">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px">
              <div style="flex:1">
                <div style="font-size:.88rem;font-weight:<?= $n['is_read']?'500':'700' ?>;color:var(--u-primary)">
                  <?= sanitize($n['title']) ?>
                  <?php if (!$n['is_read']): ?>
                  <span style="background:var(--u-gold);color:var(--u-primary);font-size:.6rem;font-weight:700;padding:2px 7px;border-radius:50px;margin-left:6px;vertical-align:middle">NEW</span>
                  <?php endif; ?>
                </div>
                <div style="font-size:.83rem;color:var(--u-gray);margin-top:4px;line-height:1.5"><?= sanitize($n['message']) ?></div>
                <div style="font-size:.73rem;color:#94A3B8;margin-top:6px">
                  <i class="fas fa-clock me-1"></i><?= timeAgo($n['created_at']) ?> · <?= formatDateTime($n['created_at'],'d M Y H:i') ?>
                </div>
              </div>
              <div style="display:flex;gap:6px;flex-shrink:0;margin-top:2px">
                <?php if (!$n['is_read']): ?>
                <a href="?action=mark_read&id=<?= $n['id'] ?>"
                   style="width:30px;height:30px;border-radius:8px;border:1px solid var(--u-border);display:flex;align-items:center;justify-content:center;color:var(--u-primary);font-size:.75rem"
                   title="Mark as read"><i class="fas fa-check"></i></a>
                <?php endif; ?>
                <a href="?action=delete&id=<?= $n['id'] ?>"
                   onclick="return confirm('Delete this notification?')"
                   style="width:30px;height:30px;border-radius:8px;border:1px solid #fee2e2;display:flex;align-items:center;justify-content:center;color:#EF4444;font-size:.75rem"
                   title="Delete"><i class="fas fa-trash"></i></a>
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
<script>const APP_URL = "<?= APP_URL ?>";</script>
