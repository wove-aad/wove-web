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

  if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
    menus.forEach(function (menu) {
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
