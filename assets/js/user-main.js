/* ============================================================
   VNUIS Campus Booking — User JS v2
   ============================================================ */
'use strict';

$(function () {

  /* ── AOS ── */
  if (typeof AOS !== 'undefined') AOS.init({ duration: 550, once: true, offset: 40 });

  /* ── Navbar scroll ── */
  const $nav = $('#uNav');
  $(window).on('scroll.nav', function () {
    $nav.toggleClass('scrolled', $(this).scrollTop() > 8);
  });

  /* ── Mobile drawer ── */
  $('#mobileMenuBtn').on('click', function () {
    const $d = $('#mobileDrawer');
    $d.toggleClass('open');
    $(this).find('i').toggleClass('fa-bars fa-times');
  });
  // Close on outside click
  $(document).on('click', function (e) {
    if (!$(e.target).closest('#uNav').length) {
      $('#mobileDrawer').removeClass('open');
      $('#mobileMenuBtn i').removeClass('fa-times').addClass('fa-bars');
    }
  });

  /* ── Notification dropdown ── */
  $('#notifBtn').on('click', loadNotifications);

  function loadNotifications() {
    $.get(APP_URL + '/api/notifications.php?action=get_recent', function (data) {
      const $list = $('#notifList');
      $list.empty();
      if (!data.notifications || !data.notifications.length) {
        $list.html('<div class="text-center py-5 text-muted small"><i class="fas fa-bell-slash d-block fs-3 mb-2 opacity-25"></i>No notifications yet</div>');
        return;
      }
      data.notifications.forEach(function (n) {
        const unread = n.is_read == 0;
        $list.append(`
          <div class="u-notif-dd-item${unread?' unread':''}" data-id="${n.id}">
            <div class="u-notif-dd-dot"><i class="fas fa-bell" style="font-size:.75rem"></i></div>
            <div style="flex:1;min-width:0">
              <div class="u-notif-dd-title">${escHtml(n.title)}</div>
              <div class="u-notif-dd-msg">${escHtml(n.message.substring(0,90))}${n.message.length>90?'…':''}</div>
              <div class="u-notif-dd-time"><i class="fas fa-clock me-1"></i>${n.time_ago}</div>
            </div>
          </div>`);
      });
      const cnt = data.unread_count || 0;
      if (cnt > 0) {
        $('#notifBadge').text(cnt > 9 ? '9+' : cnt).show();
      } else {
        $('#notifBadge').hide();
      }
    }, 'json').fail(function () {
      $('#notifList').html('<div class="text-center py-3 text-muted small">Could not load notifications.</div>');
    });
  }

  $('#markAllRead').on('click', function (e) {
    e.preventDefault();
    $.post(APP_URL + '/api/notifications.php', { action: 'mark_all_read' }, function () {
      $('#notifBadge').hide();
      $('#notifList .u-notif-dd-item').removeClass('unread');
    });
  });

  $(document).on('click', '.u-notif-dd-item', function () {
    const id = $(this).data('id');
    $(this).removeClass('unread');
    $.post(APP_URL + '/api/notifications.php', { action: 'mark_read', id: id });
  });

  /* ── SweetAlert confirm helpers ── */
  window.confirmAction = function (msg, formOrUrl) {
    Swal.fire({
      title: 'Confirm', text: msg, icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#d4a017', cancelButtonColor: '#64748b',
      confirmButtonText: 'Yes, proceed', cancelButtonText: 'Cancel',
    }).then(res => {
      if (res.isConfirmed) {
        if (typeof formOrUrl === 'string') window.location.href = formOrUrl;
        else if (formOrUrl && formOrUrl.submit) formOrUrl.submit();
      }
    });
    return false;
  };

  window.confirmDelete = function (form) {
    Swal.fire({
      title: 'Delete?', text: 'This cannot be undone.', icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444', cancelButtonColor: '#64748b',
      confirmButtonText: 'Delete',
    }).then(res => { if (res.isConfirmed) form.submit(); });
    return false;
  };

  /* ── Flash auto-dismiss ── */
  setTimeout(() => { $('.alert-dismissible').alert('close'); }, 5500);

  /* ── DataTables ── */
  if ($.fn.DataTable) {
    $('.u-datatable').each(function () {
      if (!$.fn.DataTable.isDataTable(this)) {
        $(this).DataTable({
          responsive: true, pageLength: 15, lengthMenu: [10,15,25,50],
          dom: "<'row align-items-center mb-2'<'col-sm-6'l><'col-sm-6 text-end'f>>" +
               "<'row'<'col-12'tr>>" +
               "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
          language: { search: '', searchPlaceholder: 'Search…', lengthMenu: 'Show _MENU_' },
        });
      }
    });
  }

  /* ── Booking conflict check ── */
  function checkConflict() {
    const fid = $('#facility_id').val();
    const dt  = $('#booking_date').val();
    const st  = $('#start_time').val();
    const et  = $('#end_time').val();
    if (!fid || !dt || !st || !et || st >= et) return;
    $.post(APP_URL + '/controllers/BookingController.php', {
      action: 'check_conflict', facility_id: fid, booking_date: dt, start_time: st, end_time: et,
    }, function (data) {
      const $w = $('#conflictWarning');
      if (data.conflict) {
        $w.removeClass('d-none').html('<i class="fas fa-exclamation-triangle me-2"></i>This time slot is already booked. Choose a different time.');
        $('#submitBookingBtn').prop('disabled', true).addClass('u-btn:disabled');
      } else {
        $w.addClass('d-none');
        $('#submitBookingBtn').prop('disabled', false);
      }
    }, 'json');
  }
  $('#facility_id,#booking_date,#start_time,#end_time').on('change', checkConflict);

  /* ── Utility: escape HTML ── */
  window.escHtml = function (s) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(s));
    return d.innerHTML;
  };

  /* ── Bootstrap tooltips ── */
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

});
