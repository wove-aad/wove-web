/**
 * Main menu (site/snippets/brand/header-row.php): one list open at a time,
 * closed by a click outside or Escape (focus goes back to its toggle).
 * With a mouse, a list opens on hover (2026-10-30) and closes shortly
 * after the pointer leaves; a click on its toggle then leaves it open.
 * Touch and keyboard still open it with a tap or Enter.
 */
(function () {
  var menus = Array.prototype.slice.call(document.querySelectorAll('[data-nav-menu]'));
  if (!menus.length) return;

  menus.forEach(function (menu) {
    menu.addEventListener('toggle', function () {
      if (!menu.open) return;
      menus.forEach(function (other) { if (other !== menu) other.open = false; });
    });
  });

  // Impact areas (data-menu="c" on <html>, brand/header-row.php): Impact
  // areas opens the chips in the header (in every header row at once, the
  // page header and the pinned one); × or a click outside closes them. An
  // impact area page starts open (CSS, via its current chip).
  var option = document.documentElement.getAttribute('data-menu');
  var rows = Array.prototype.slice.call(document.querySelectorAll('.hx-row'));
  var setAreas = function (on) {
    rows.forEach(function (row) {
      row.classList.toggle('is-areas', on);
      row.classList.toggle('is-closed', !on);
    });
  };
  if (option) {
    rows.forEach(function (row) {
      var impact = row.querySelector('[data-impact-menu]');
      var close = row.querySelector('[data-areas-close]');
      if (!impact || !close) return;
      impact.querySelector('summary').addEventListener('click', function (ev) {
        ev.preventDefault();
        setAreas(true);
        var chip = row.querySelector('.hx-chip[aria-current]') || row.querySelector('.hx-chip');
        if (chip) chip.focus({ preventScroll: true });
      });
      // The × closes them
      [].forEach.call(row.querySelectorAll('[data-areas-close]'), function (btn) {
        btn.addEventListener('click', function () {
          setAreas(false);
          var toggle = row.querySelector('[data-impact-menu] summary');
          if (toggle && row.offsetParent !== null) toggle.focus({ preventScroll: true });
        });
      });
      row.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape' && row.querySelector('[data-areas]').contains(document.activeElement)) close.click();
      });
    });
    // A click anywhere outside the header closes the chips (2026-10-30)
    document.addEventListener('click', function (ev) {
      if (ev.target.closest('.hx-row')) return;
      var open = rows.some(function (row) { return row.querySelector('.hx-areas') && getComputedStyle(row.querySelector('.hx-areas')).display !== 'none'; });
      if (open) setAreas(false);
    });

    // Keep the current chip in view where the row scrolls
    rows.forEach(function (row) {
      var list = row.querySelector('.hx-areas__list');
      var chip = list && list.querySelector('[aria-current]');
      if (chip && list.scrollWidth > list.clientWidth) list.scrollLeft = chip.parentElement.offsetLeft - list.offsetLeft - 24;
    });

    // Click and drag the chips sideways with a mouse (touch swipes
    // natively). A drag of more than a few pixels isn't a click on a chip.
    rows.forEach(function (row) {
      var list = row.querySelector('.hx-areas__list');
      if (!list) return;
      var drag = null, dragged = false;
      list.addEventListener('pointerdown', function (ev) {
        if (ev.pointerType !== 'mouse' || ev.button !== 0) return;
        drag = { x: ev.clientX, left: list.scrollLeft, id: ev.pointerId };
        dragged = false;
      });
      list.addEventListener('pointermove', function (ev) {
        if (!drag || ev.pointerId !== drag.id) return;
        var dx = ev.clientX - drag.x;
        if (!dragged && Math.abs(dx) > 5) {
          dragged = true;
          list.setPointerCapture(drag.id);
          list.classList.add('is-dragging');
        }
        if (dragged) list.scrollLeft = drag.left - dx;
      });
      var end = function () {
        drag = null;
        list.classList.remove('is-dragging');
      };
      list.addEventListener('pointerup', end);
      list.addEventListener('pointercancel', end);
      list.addEventListener('click', function (ev) {
        if (dragged) { ev.preventDefault(); ev.stopPropagation(); dragged = false; }
      }, true);
      list.addEventListener('dragstart', function (ev) { ev.preventDefault(); });
    });
  }

  if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
    menus.forEach(function (menu) {
      if (option && menu.hasAttribute('data-impact-menu')) return;
      var timer;
      menu.addEventListener('mouseenter', function () {
        clearTimeout(timer);
        menu.open = true;
      });
      menu.addEventListener('mouseleave', function () {
        timer = setTimeout(function () { menu.open = false; }, 200);
      });
      // A mouse click on the toggle of a hover-opened list keeps it open;
      // keyboard clicks (detail 0) still toggle
      menu.querySelector('summary').addEventListener('click', function (ev) {
        if (ev.detail > 0 && menu.open) ev.preventDefault();
      });
    });
  }

  document.addEventListener('click', function (ev) {
    menus.forEach(function (menu) {
      if (menu.open && (!menu.contains(ev.target) || ev.target.closest('a'))) menu.open = false;
    });
  });

  document.addEventListener('keydown', function (ev) {
    if (ev.key !== 'Escape') return;
    menus.forEach(function (menu) {
      if (!menu.open) return;
      menu.open = false;
      menu.querySelector('summary').focus();
    });
  });
})();
