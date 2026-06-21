/* ==============================================
   VNUIS Campus Booking — User Theme JS
   ============================================== */

$(function () {

  /* ── AOS Init ── */
  if (typeof AOS !== 'undefined') {
    AOS.init({ duration: 600, once: true, offset: 50 });
  }

  /* ── Navbar scroll effect ── */
  $(window).on('scroll', function () {
    if ($(this).scrollTop() > 10) {
      $('#uNavbar').addClass('scrolled');
    } else {
      $('#uNavbar').removeClass('scrolled');
    }
  });

  /* ── Mobile menu toggle ── */
  $('#mobileMenuBtn').on('click', function () {
    $('#mobileMenu').toggleClass('open');
  });

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
        const cls = n.is_read == 0 ? 'unread' : '';
        $list.append(`
          <div class="u-notif-item ${cls}" data-id="${n.id}" style="cursor:pointer">
            <div style="width:36px;height:36px;border-radius:50%;background:${n.is_read==0?'#c9a84c':'#f1f5f9'};color:${n.is_read==0?'#1a1a2e':'#64748B'};display:flex;align-items:center;justify-content:center;flex-shrink:0">
              <i class="fas fa-bell" style="font-size:.8rem"></i>
            </div>
            <div style="flex:1;min-width:0">
              <div style="font-size:.8rem;font-weight:600;color:#1a1a2e">${escHtml(n.title)}</div>
              <div style="font-size:.73rem;color:#64748B;margin-top:2px">${escHtml(n.message.substring(0,80))}${n.message.length>80?'…':''}</div>
              <div style="font-size:.68rem;color:#94A3B8;margin-top:3px">${n.time_ago}</div>
            </div>
          </div>`);
      });
      if (data.unread_count > 0) {
        $('#notifBadge').text(data.unread_count > 9 ? '9+' : data.unread_count).show();
      } else {
        $('#notifBadge').hide();
      }
    }, 'json').fail(function () {
      $('#notifList').html('<div class="text-center py-3 text-muted small">Failed to load.</div>');
    });
  }

  $('#markAllRead').on('click', function (e) {
    e.preventDefault();
    $.post(APP_URL + '/api/notifications.php', { action: 'mark_all_read' }, function () {
      $('#notifBadge').hide();
      $('#notifList .u-notif-item').removeClass('unread');
    });
  });

  $(document).on('click', '.u-notif-item', function () {
    const id = $(this).data('id');
    $(this).removeClass('unread');
    $.post(APP_URL + '/api/notifications.php', { action: 'mark_read', id: id });
  });

  /* ── SweetAlert helpers ── */
  window.confirmAction = function (msg, formOrUrl) {
    Swal.fire({
      title: 'Confirm Action',
      text: msg,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#c9a84c',
      cancelButtonColor: '#64748B',
      confirmButtonText: 'Yes, proceed',
      cancelButtonText: 'Cancel',
    }).then(function (res) {
      if (res.isConfirmed) {
        if (typeof formOrUrl === 'string') window.location.href = formOrUrl;
        else if (formOrUrl && formOrUrl.submit) formOrUrl.submit();
      }
    });
    return false;
  };

  window.confirmDelete = function (form) {
    Swal.fire({
      title: 'Delete',
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

  /* ── Flash auto-dismiss ── */
  setTimeout(function () {
    $('.alert-dismissible.fade.show').alert('close');
  }, 5000);

  /* ── DataTables ── */
  if ($.fn.DataTable) {
    $('.u-datatable').each(function () {
      if (!$.fn.DataTable.isDataTable(this)) {
        $(this).DataTable({
          responsive: true,
          pageLength: 15,
          lengthMenu: [10, 15, 25, 50],
          dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
               "<'row'<'col-12'tr>>" +
               "<'row'<'col-sm-5'i><'col-sm-7'p>>",
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
      action: 'check_conflict', facility_id: fid,
      booking_date: dt, start_time: st, end_time: et,
    }, function (data) {
      const $w = $('#conflictWarning');
      if (data.conflict) {
        $w.removeClass('d-none').text('⚠ This time slot is already booked. Please choose a different time.');
        $('#submitBookingBtn').prop('disabled', true);
      } else {
        $w.addClass('d-none');
        $('#submitBookingBtn').prop('disabled', false);
      }
    }, 'json');
  }
  $('#facility_id, #booking_date, #start_time, #end_time').on('change', checkConflict);

  /* ── Utility ── */
  window.escHtml = function (str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
  };

  /* ── Tooltips & Popovers ── */
  $('[data-bs-toggle="tooltip"]').each(function () { new bootstrap.Tooltip(this); });
  $('[data-bs-toggle="popover"]').each(function () { new bootstrap.Popover(this); });

});
