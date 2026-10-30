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

  // Menu options for review (?menu=a|b|c, brand/header-row.php): Impact
  // areas opens the chips in the header (in every header row at once, the
  // page header and the pinned one); the close button shuts them. An
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
      close.addEventListener('click', function () {
        setAreas(false);
        var toggle = row.querySelector('[data-impact-menu] summary');
        if (toggle && row.offsetParent !== null) toggle.focus({ preventScroll: true });
      });
      row.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape' && row.querySelector('[data-areas]').contains(document.activeElement)) close.click();
      });
    });
    // Keep the current chip in view where the row scrolls
    rows.forEach(function (row) {
      var list = row.querySelector('.hx-areas__list');
      var chip = list && list.querySelector('[aria-current]');
      if (chip && list.scrollWidth > list.clientWidth) list.scrollLeft = chip.parentElement.offsetLeft - list.offsetLeft - 24;
    });

    // A switcher for comparing the options
    var panel = document.createElement('aside');
    panel.className = 'feed-options menu-options';
    panel.setAttribute('aria-label', 'Menu options');
    var here = location.pathname;
    panel.innerHTML = '<p class="feed-options__title">Menu options</p><div class="feed-options__group"><span class="feed-options__label">Impact areas</span>'
      + [['a', 'A Replace'], ['b', 'B Row below'], ['c', 'C Joined'], ['off', 'Dropdown']].map(function (o) {
        return '<a href="' + here + '?menu=' + o[0] + '"' + (o[0] === option ? ' aria-current="true"' : '') + '>' + o[1] + '</a>';
      }).join('') + '</div>';
    document.body.appendChild(panel);
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
