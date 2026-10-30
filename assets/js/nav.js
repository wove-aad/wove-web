/**
 * Main menu (site/snippets/brand/header-row.php): one list open at a time,
 * closed by a click outside or Escape (focus goes back to its toggle).
 * With a mouse, a list opens on hover (2026-10-30) and closes shortly
 * after the pointer leaves; a click on its toggle then leaves it open.
 * Touch and keyboard still open it with a tap or Enter. Services and
 * impact areas open their chips in the header, on hover too.
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

  // Services and impact areas (data-menu="c" on <html>,
  // brand/header-row.php): each opens its chips in the header (in every
  // header row at once, the page header and the pinned one), named by
  // data-open on the row; × or a click outside closes them (data-open
  // empty). A service or impact area page starts with its own open (CSS,
  // via its current chip, while data-open isn't set).
  var option = document.documentElement.getAttribute('data-menu');
  var rows = Array.prototype.slice.call(document.querySelectorAll('.hx-row'));
  var still = window.matchMedia('(prefers-reduced-motion: reduce)');
  // Where the menu item sits within its bar (--from-l, --from-r), so the
  // bar grows out of the item and shrinks back into it (2026-10-30). The
  // item is measured with the menu briefly shown (.is-measuring), within
  // one task, so nothing flashes.
  var fromItem = function (row, bar) {
    var toggle = row.querySelector('[data-bar-menu="' + bar.getAttribute('data-areas') + '"] summary');
    if (!toggle) return;
    row.classList.add('is-measuring');
    var t = toggle.getBoundingClientRect();
    row.classList.remove('is-measuring');
    var b = bar.getBoundingClientRect();
    if (!b.width || !t.width) return;
    bar.style.setProperty('--from-l', Math.max(0, t.left - b.left) + 'px');
    bar.style.setProperty('--from-r', Math.max(0, b.right - t.right) + 'px');
  };
  var setBar = function (id) {
    rows.forEach(function (row) { row.setAttribute('data-open', id); });
    if (!id || still.matches) return;
    rows.forEach(function (row) {
      var bar = row.querySelector('[data-areas="' + id + '"]');
      if (!bar) return;
      fromItem(row, bar);
      // Restart the grow with the measured start
      bar.style.animation = 'none';
      void bar.offsetWidth;
      bar.style.animation = '';
    });
  };
  var openBar = function (row) {
    var open = row.getAttribute('data-open');
    if (open === null) {
      var cur = row.querySelector('.hx-areas .hx-chip[aria-current]');
      open = cur ? cur.closest('[data-areas]').getAttribute('data-areas') : '';
    }
    return open && row.querySelector('[data-areas="' + open + '"]');
  };
  // Closing: the open bars shrink back into their menu item
  // (.is-closing, CSS), then the menu fades in. Without motion it closes
  // at once.
  var closing = false;
  var closeBars = function () {
    var bars = rows.map(openBar).filter(Boolean);
    if (!bars.length || still.matches) return setBar('');
    if (closing) return;
    closing = true;
    bars.forEach(function (bar) {
      fromItem(bar.closest('.hx-row'), bar);
      bar.classList.add('is-closing');
    });
    setTimeout(function () {
      bars.forEach(function (bar) { bar.classList.remove('is-closing'); });
      setBar('');
      closing = false;
    }, 260);
  };
  if (option) {
    rows.forEach(function (row) {
      [].forEach.call(row.querySelectorAll('[data-bar-menu]'), function (menu) {
        var id = menu.getAttribute('data-bar-menu');
        menu.querySelector('summary').addEventListener('click', function (ev) {
          ev.preventDefault();
          setBar(id);
          var bar = row.querySelector('[data-areas="' + id + '"]');
          var chip = bar && (bar.querySelector('.hx-chip[aria-current]') || bar.querySelector('.hx-chip'));
          if (chip) chip.focus({ preventScroll: true });
        });
      });
      // The × closes them
      [].forEach.call(row.querySelectorAll('[data-areas-close]'), function (btn) {
        btn.addEventListener('click', function () {
          var id = btn.closest('[data-areas]').getAttribute('data-areas');
          closeBars();
          var toggle = row.querySelector('[data-bar-menu="' + id + '"] summary');
          if (toggle && row.offsetParent !== null) toggle.focus({ preventScroll: true });
        });
      });
      row.addEventListener('keydown', function (ev) {
        var bar = openBar(row);
        if (ev.key === 'Escape' && bar && bar.contains(document.activeElement)) bar.querySelector('[data-areas-close]').click();
      });
    });
    // A click anywhere outside the header closes the chips (2026-10-30)
    document.addEventListener('click', function (ev) {
      if (ev.target.closest('.hx-row')) return;
      if (rows.some(openBar)) closeBars();
    });

    // Keep the current chip in view where the row scrolls
    rows.forEach(function (row) {
      [].forEach.call(row.querySelectorAll('.hx-areas__list'), function (list) {
        var chip = list.querySelector('[aria-current]');
        if (chip && list.scrollWidth > list.clientWidth) list.scrollLeft = chip.parentElement.offsetLeft - list.offsetLeft - 24;
      });
    });

    // Click and drag the chips sideways with a mouse (touch swipes
    // natively). A drag of more than a few pixels isn't a click on a chip.
    [].forEach.call(document.querySelectorAll('.hx-areas__list'), function (list) {
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
    // Services and impact areas also open their chips on hover
    // (2026-10-30), after a short pause so passing over doesn't open them.
    // Opened this way, they close again shortly after the pointer leaves
    // the header row; opened by a click, or open on page load, they stay.
    if (option) {
      rows.forEach(function (row) {
        var enter, leave, hovered = false;
        [].forEach.call(row.querySelectorAll('[data-bar-menu]'), function (menu) {
          var summary = menu.querySelector('summary');
          summary.addEventListener('mouseenter', function () {
            clearTimeout(enter);
            enter = setTimeout(function () {
              hovered = true;
              setBar(menu.getAttribute('data-bar-menu'));
            }, 120);
          });
          summary.addEventListener('mouseleave', function () { clearTimeout(enter); });
          summary.addEventListener('click', function () { hovered = false; });
        });
        row.addEventListener('mouseenter', function () { clearTimeout(leave); });
        row.addEventListener('mouseleave', function () {
          if (!hovered) return;
          leave = setTimeout(function () {
            hovered = false;
            if (openBar(row) && !(row.matches(':focus-within'))) closeBars();
          }, 400);
        });
      });
    }
    menus.forEach(function (menu) {
      if (option && menu.hasAttribute('data-bar-menu')) return;
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
