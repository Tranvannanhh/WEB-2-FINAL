<?php
$unreadCount = isLoggedIn() ? getUnreadNotificationCount($_SESSION['user_id']) : 0;
?>
<!-- ==================== TOP NAVBAR ==================== -->
<nav class="top-navbar navbar navbar-expand-lg">
    <div class="container-fluid px-3">
        <!-- Mobile toggle -->
        <button class="navbar-toggler border-0 me-2" id="mobileSidebarToggle" type="button">
            <i class="fas fa-bars text-white"></i>
        </button>

        <!-- Page title -->
        <span class="navbar-brand mb-0 text-white fw-semibold d-none d-md-block">
            <?= isset($pageTitle) ? sanitize($pageTitle) : APP_NAME ?>
        </span>

        <div class="ms-auto d-flex align-items-center gap-2">

            <!-- Search trigger -->
            <button class="btn btn-sm btn-outline-light d-none d-md-flex align-items-center gap-1"
                    id="globalSearchBtn" data-bs-toggle="modal" data-bs-target="#globalSearchModal">
                <i class="fas fa-search"></i>
                <span class="d-none d-lg-inline small">Search...</span>
            </button>

            <!-- Notifications -->
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-light position-relative" data-bs-toggle="dropdown" id="notifDropdownBtn">
                    <i class="fas fa-bell"></i>
                    <?php if ($unreadCount > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notifBadge">
                        <?= $unreadCount > 9 ? '9+' : $unreadCount ?>
                    </span>
                    <?php endif; ?>
                </button>
                <div class="dropdown-menu dropdown-menu-end notification-dropdown shadow" style="min-width:340px">
                    <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                        <strong class="small">Notifications</strong>
                        <a href="<?= APP_URL ?>/api/notifications.php?action=mark_all_read" class="text-primary small" id="markAllRead">Mark all read</a>
                    </div>
                    <div id="notifList" style="max-height:320px;overflow-y:auto;">
                        <div class="text-center py-3 text-muted small">
                            <i class="fas fa-spinner fa-spin"></i> Loading...
                        </div>
                    </div>
                    <div class="text-center border-top py-2">
                        <a href="<?= APP_URL ?>/views/notifications/index.php" class="small text-primary">View all notifications</a>
                    </div>
                </div>
            </div>

            <!-- User dropdown -->
            <div class="dropdown">
                <button class="btn btn-sm d-flex align-items-center gap-2 user-nav-btn" data-bs-toggle="dropdown">
                    <div class="nav-avatar">
                        <?php if (!empty($_SESSION['avatar'])): ?>
                            <img src="<?= APP_URL ?>/uploads/avatars/<?= sanitize($_SESSION['avatar']) ?>" alt="Avatar">
                        <?php else: ?>
                            <?= strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <span class="d-none d-md-inline text-white small fw-medium"><?= sanitize(explode(' ', $_SESSION['full_name'] ?? 'User')[0]) ?></span>
                    <i class="fas fa-chevron-down text-white-50 small"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li>
                        <div class="dropdown-item-text">
                            <div class="fw-semibold"><?= sanitize($_SESSION['full_name'] ?? '') ?></div>
                            <div class="text-muted small"><?= sanitize($_SESSION['email'] ?? '') ?></div>
                            <?= getRoleBadge($_SESSION['role'] ?? 'student') ?>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= APP_URL ?>/views/profile/index.php">
                        <i class="fas fa-user-circle me-2 text-primary"></i>My Profile</a></li>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li><a class="dropdown-item" href="<?= APP_URL ?>/views/dashboard/admin.php">
                        <i class="fas fa-tachometer-alt me-2 text-primary"></i>Admin Dashboard</a></li>
                    <?php endif; ?>
                    <li><a class="dropdown-item" href="<?= APP_URL ?>/views/notifications/index.php">
                        <i class="fas fa-bell me-2 text-primary"></i>Notifications
                        <?php if ($unreadCount > 0): ?>
                        <span class="badge bg-danger rounded-pill ms-1"><?= $unreadCount ?></span>
                        <?php endif; ?>
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= APP_URL ?>/controllers/AuthController.php?action=logout"
                           onclick="return confirm('Are you sure you want to logout?')">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<!-- ==================== GLOBAL SEARCH MODAL ==================== -->
<div class="modal fade" id="globalSearchModal" tabindex="-1" aria-labelledby="globalSearchLabel">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden">
      <div class="modal-header border-0 pb-0 px-4 pt-4">
        <div class="input-group">
          <span class="input-group-text bg-white border-end-0">
            <i class="fas fa-search text-primary"></i>
          </span>
          <input type="text" id="globalSearchInput" class="form-control border-start-0 border-end-0 ps-0 fs-5 shadow-none"
                 placeholder="Search bookings, facilities, users…" autocomplete="off">
          <button class="btn btn-outline-secondary border-start-0" data-bs-dismiss="modal">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
      <div class="modal-body px-4 pt-2 pb-4" style="min-height:260px;max-height:460px;overflow-y:auto">
        <div id="searchHint" class="text-center py-5 text-muted">
          <i class="fas fa-search fa-2x mb-3 opacity-25"></i>
          <p class="small">Start typing to search across bookings, facilities and users…</p>
        </div>
        <div id="searchResults" class="d-none"></div>
        <div id="searchLoading" class="text-center py-5 d-none">
          <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  const modal  = document.getElementById('globalSearchModal');
  const input  = document.getElementById('globalSearchInput');
  const hint   = document.getElementById('searchHint');
  const results= document.getElementById('searchResults');
  const loader = document.getElementById('searchLoading');
  let timer;

  // Focus input when modal opens
  modal.addEventListener('shown.bs.modal', () => { input.value=''; input.focus(); showHint(); });

  // Keyboard shortcut Ctrl+K / Cmd+K
  document.addEventListener('keydown', e => {
    if ((e.ctrlKey||e.metaKey) && e.key==='k') {
      e.preventDefault();
      bootstrap.Modal.getOrCreateInstance(modal).show();
    }
  });

  input.addEventListener('input', function(){
    clearTimeout(timer);
    const q = this.value.trim();
    if (q.length < 2) { showHint(); return; }
    showLoader();
    timer = setTimeout(() => doSearch(q), 320);
  });

  function showHint()   { hint.classList.remove('d-none'); results.classList.add('d-none'); loader.classList.add('d-none'); }
  function showLoader() { hint.classList.add('d-none');    results.classList.add('d-none'); loader.classList.remove('d-none'); }
  function showResults(){ hint.classList.add('d-none');    results.classList.remove('d-none'); loader.classList.add('d-none'); }

  function doSearch(q) {
    fetch(APP_URL + '/api/search.php?q=' + encodeURIComponent(q))
      .then(r => r.json())
      .then(data => renderResults(data, q))
      .catch(() => {
        results.innerHTML = '<p class="text-center text-muted py-4 small">Search failed. Please try again.</p>';
        showResults();
      });
  }

  function esc(s){ const d=document.createElement('div');d.appendChild(document.createTextNode(s));return d.innerHTML; }

  function renderResults(data, q) {
    let html = '';
    const total = (data.bookings||[]).length + (data.facilities||[]).length + (data.users||[]).length;

    if (total === 0) {
      html = '<div class="text-center py-5 text-muted"><i class="fas fa-search fa-2x mb-3 opacity-25"></i><p class="small">No results for "<strong>' + esc(q) + '</strong>"</p></div>';
      results.innerHTML = html; showResults(); return;
    }

    if ((data.bookings||[]).length) {
      html += '<div class="mb-3"><div class="text-uppercase fw-bold small text-muted mb-2 px-1" style="letter-spacing:.7px;font-size:.7rem">Bookings</div>';
      data.bookings.forEach(b => {
        html += `<a href="${APP_URL}/views/bookings/view.php?id=${b.id}" class="search-result-item d-flex align-items-center gap-3 px-3 py-2 rounded mb-1 text-decoration-none" data-bs-dismiss="modal">
          <div class="search-result-icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-calendar-check"></i></div>
          <div class="flex-1 min-w-0">
            <div class="fw-semibold small text-dark text-truncate">${esc(b.facility_name)} — ${esc(b.full_name)}</div>
            <div class="text-muted" style="font-size:.74rem">${esc(b.booking_date)} · ${esc(b.status)}</div>
          </div>
          <span class="badge bg-${b.status==='approved'?'success':b.status==='pending'?'warning text-dark':'secondary'} small">${esc(b.status)}</span>
        </a>`;
      });
      html += '</div>';
    }

    if ((data.facilities||[]).length) {
      html += '<div class="mb-3"><div class="text-uppercase fw-bold small text-muted mb-2 px-1" style="letter-spacing:.7px;font-size:.7rem">Facilities</div>';
      data.facilities.forEach(f => {
        html += `<a href="${APP_URL}/views/facilities/view.php?id=${f.id}" class="search-result-item d-flex align-items-center gap-3 px-3 py-2 rounded mb-1 text-decoration-none" data-bs-dismiss="modal">
          <div class="search-result-icon bg-success bg-opacity-10 text-success"><i class="fas fa-building"></i></div>
          <div class="flex-1 min-w-0">
            <div class="fw-semibold small text-dark text-truncate">${esc(f.facility_name)}</div>
            <div class="text-muted" style="font-size:.74rem">${esc(f.location)} · ${esc(f.facility_type)}</div>
          </div>
          <span class="badge bg-${f.status==='available'?'success':'warning text-dark'} small">${esc(f.status)}</span>
        </a>`;
      });
      html += '</div>';
    }

    if ((data.users||[]).length) {
      html += '<div class="mb-3"><div class="text-uppercase fw-bold small text-muted mb-2 px-1" style="letter-spacing:.7px;font-size:.7rem">Users</div>';
      data.users.forEach(u => {
        html += `<a href="${APP_URL}/views/users/manage.php" class="search-result-item d-flex align-items-center gap-3 px-3 py-2 rounded mb-1 text-decoration-none" data-bs-dismiss="modal">
          <div class="search-result-icon bg-info bg-opacity-10 text-info" style="font-weight:700;font-size:.9rem">${esc(u.full_name.charAt(0).toUpperCase())}</div>
          <div class="flex-1 min-w-0">
            <div class="fw-semibold small text-dark text-truncate">${esc(u.full_name)}</div>
            <div class="text-muted" style="font-size:.74rem">${esc(u.email)}</div>
          </div>
          <span class="badge bg-${u.role==='admin'?'danger':u.role==='lecturer'?'primary':'info'} small">${esc(u.role)}</span>
        </a>`;
      });
      html += '</div>';
    }

    html += '<div class="text-center text-muted mt-2" style="font-size:.74rem">' + total + ' result' + (total!==1?'s':'') + ' for "<strong>' + esc(q) + '</strong>"</div>';
    results.innerHTML = html;
    showResults();
  }
})();
</script>
<style>
.search-result-icon { width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.85rem; }
.search-result-item { transition:background .15s; }
.search-result-item:hover { background:#F1F5F9; }
</style>
