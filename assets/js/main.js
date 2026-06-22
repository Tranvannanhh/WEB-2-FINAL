/* ==============================================
   VNUIS Campus Booking System – Main JS
   ============================================== */

$(function () {

  /* ── AOS Init ── */
  if (typeof AOS !== 'undefined') {
    AOS.init({ duration: 500, once: true, offset: 40 });
  }

  /* ── Sidebar Toggle ── */
  const $sidebar   = $('#sidebar');
  const $mainContent = $('.main-content');
  const $overlay   = $('<div class="sidebar-overlay" id="sidebarOverlay"></div>').appendTo('body');

  $('#sidebarToggle').on('click', function () {
    if ($(window).width() >= 992) {
      $sidebar.toggleClass('collapsed');
      $mainContent.toggleClass('expanded');
      localStorage.setItem('sidebarCollapsed', $sidebar.hasClass('collapsed') ? '1' : '0');
    }
  });

  $('#mobileSidebarToggle').on('click', function () {
    $sidebar.toggleClass('mobile-open');
    $overlay.toggleClass('active');
  });

  $overlay.on('click', function () {
    $sidebar.removeClass('mobile-open');
    $overlay.removeClass('active');
  });

  // Restore sidebar state
  if (localStorage.getItem('sidebarCollapsed') === '1' && $(window).width() >= 992) {
    $sidebar.addClass('collapsed');
    $mainContent.addClass('expanded');
  }

  /* ── Notification Dropdown (AJAX) ── */
  $('#notifDropdownBtn').on('click', function () {
    loadNotifications();
  });

  function loadNotifications() {
    $.get(APP_URL + '/api/notifications.php?action=get_recent', function (data) {
      const $list = $('#notifList');
      $list.empty();
      if (!data.notifications || data.notifications.length === 0) {
        $list.html('<div class="text-center py-4 text-muted small"><i class="fas fa-bell-slash mb-2 d-block fs-4"></i>No notifications</div>');
        return;
      }
      data.notifications.forEach(function (n) {
        const unreadCls = n.is_read == 0 ? 'unread' : '';
        // parse optional link from message (format: "text|url")
        const parts = n.message.split('|');
        const msgText = parts[0];
        const msgLink = parts.length > 1 ? parts[1].trim() : '';
        $list.append(`
          <div class="notif-item ${unreadCls}" data-id="${n.id}" data-link="${msgLink}" style="cursor:pointer">
            <div class="notif-icon"><i class="fas fa-bell"></i></div>
            <div class="flex-1">
              <div class="notif-title">${escHtml(n.title)}</div>
              <div class="notif-msg">${escHtml(msgText.substring(0, 80))}${msgText.length > 80 ? '…' : ''}</div>
              <div class="notif-time"><i class="fas fa-clock me-1"></i>${n.time_ago}</div>
            </div>
          </div>`);
      });
      // Update badge
      if (data.unread_count > 0) {
        $('#notifBadge').text(data.unread_count > 9 ? '9+' : data.unread_count).show();
      } else {
        $('#notifBadge').hide();
      }
    }, 'json').fail(function () {
      $('#notifList').html('<div class="text-center py-3 text-muted small">Failed to load notifications.</div>');
    });
  }

  /* Mark all read */
  $('#markAllRead').on('click', function (e) {
    e.preventDefault();
    $.post(APP_URL + '/api/notifications.php', { action: 'mark_all_read' }, function () {
      $('#notifBadge').hide();
      $('#notifList .notif-item').removeClass('unread');
    });
  });

  /* Individual mark read on click */
  $(document).on('click', '.notif-item', function (e) {
    e.stopPropagation();
    const id   = $(this).data('id');
    const link = $(this).data('link');
    $(this).removeClass('unread');
    $.post(APP_URL + '/api/notifications.php', { action: 'mark_read', id: id });
    if (link && link !== '') {
      setTimeout(function() {
        window.location.href = link;
      }, 100);
    }
  });


  /* ── SweetAlert confirm helpers ── */
  window.confirmAction = function (msg, formOrUrl) {
    Swal.fire({
      title: 'Confirm Action',
      text: msg,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#2563EB',
      cancelButtonColor: '#64748B',
      confirmButtonText: 'Yes, proceed',
      cancelButtonText: 'Cancel',
    }).then(function (res) {
      if (res.isConfirmed) {
        if (typeof formOrUrl === 'string') {
          window.location.href = formOrUrl;
        } else if (formOrUrl && formOrUrl.submit) {
          formOrUrl.submit();
        }
      }
    });
    return false;
  };

  window.confirmDelete = function (form) {
    Swal.fire({
      title: 'Delete Record',
      text: 'This action cannot be undone.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#EF4444',
      cancelButtonColor: '#64748B',
      confirmButtonText: 'Delete',
    }).then(function (res) {
      if (res.isConfirmed) form.submit();
    });
    return false;
  };

  /* ── Flash message auto-dismiss ── */
  setTimeout(function () {
    $('.alert-dismissible.fade.show').alert('close');
  }, 5000);

  /* ── DataTables default init ── */
  if ($.fn.DataTable) {
    $('.datatable').each(function () {
      if (!$.fn.DataTable.isDataTable(this)) {
        $(this).DataTable({
          responsive: true,
          pageLength: 15,
          lengthMenu: [10, 15, 25, 50],
          dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
               "<'row'<'col-12'tr>>" +
               "<'row'<'col-sm-5'i><'col-sm-7'p>>",
          language: {
            search: '',
            searchPlaceholder: 'Search…',
            lengthMenu: 'Show _MENU_',
          },
        });
      }
    });
  }

  /* ── Booking conflict check (AJAX) ── */
  function checkBookingConflict() {
    const facilityId = $('#facility_id').val();
    const date       = $('#booking_date').val();
    const startTime  = $('#start_time').val();
    const endTime    = $('#end_time').val();

    if (!facilityId || !date || !startTime || !endTime) return;
    if (startTime >= endTime) return;

    $.post(APP_URL + '/controllers/BookingController.php', {
      action: 'check_conflict',
      facility_id: facilityId,
      booking_date: date,
      start_time: startTime,
      end_time: endTime,
    }, function (data) {
      const $warn = $('#conflictWarning');
      if (data.conflict) {
        $warn.removeClass('d-none').text('⚠ This time slot is already booked. Please choose a different time.');
        $('#submitBookingBtn').prop('disabled', true);
      } else {
        $warn.addClass('d-none');
        $('#submitBookingBtn').prop('disabled', false);
      }
    }, 'json');
  }

  $('#facility_id, #booking_date, #start_time, #end_time').on('change', checkBookingConflict);

  /* ── Avatar preview ── */
  $('#avatarInput').on('change', function () {
    const file = this.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function (e) {
        $('#avatarPreview').attr('src', e.target.result);
      };
      reader.readAsDataURL(file);
    }
  });

  /* ── Utility: escape HTML ── */
  window.escHtml = function (str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
  };

  /* ── Tooltips ── */
  $('[data-bs-toggle="tooltip"]').each(function () {
    new bootstrap.Tooltip(this);
  });

  /* ── Popovers ── */
  $('[data-bs-toggle="popover"]').each(function () {
    new bootstrap.Popover(this);
  });

});
