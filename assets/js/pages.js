/* Inner pages without feed filters (case studies, posts, Our people,
 * contact).
 *
 * - Menu bar (snippets/brand/menu-bar.php): pins to the top on scroll-up
 *   once the page header has scrolled away, with Back to top and the site
 *   links in its menu.
 * - Card grids (.pc-grid): cards stay in date order in the DOM and pack
 *   with grid row spans, as on the homepage.
 * - Our people (#people-grid): choosing a person opens a panel under
 *   their row; see the section below.
 */
(function () {
  function reduceMotion() { return window.matchMedia('(prefers-reduced-motion: reduce)').matches; }
  function behaviour() { return reduceMotion() ? 'auto' : 'smooth'; }

  /* ---------- Menu bar ---------- */

  var bar = document.querySelector('[data-menu-bar]');
  var header = document.querySelector('.hx');
  if (bar && header) {
    var menu = bar.querySelector('.filter-bar__menu');
    var menuBtn = bar.querySelector('[data-menu]');
    var shown = false, open = false;
    var lastY = window.scrollY, travel = 0, up = false;

    var toggle = function (o) {
      open = o;
      menu.hidden = !o;
      menuBtn.setAttribute('aria-expanded', String(o));
    };
    // Scroll direction, with thresholds so small movements don't flicker the bar.
    var update = function () {
      var y = window.scrollY, dy = y - lastY;
      lastY = y;
      if ((dy < 0) !== (travel < 0)) travel = 0;
      travel += dy;
      if (travel < -40) up = true;
      if (travel > 12) { up = false; if (open) toggle(false); }
      var show = (up && header.getBoundingClientRect().bottom < 0) || open;
      if (show !== shown) {
        shown = show;
        bar.classList.toggle('is-visible', show);
        bar.inert = !show;
      }
    };
    var ticking = false;
    window.addEventListener('scroll', function () {
      if (ticking) return;
      ticking = true;
      requestAnimationFrame(function () { ticking = false; update(); });
    }, { passive: true });
    bar.addEventListener('click', function (ev) {
      if (ev.target.closest('[data-top]')) { toggle(false); up = false; window.scrollTo({ top: 0, behavior: behaviour() }); return; }
      if (ev.target.closest('[data-menu]')) { toggle(!open); return; }
      if (ev.target.closest('.filter-bar__menu a')) toggle(false);
    });
    document.addEventListener('click', function (ev) {
      if (open && !bar.contains(ev.target)) { toggle(false); update(); }
    });
    document.addEventListener('keydown', function (ev) {
      if (ev.key === 'Escape' && open) { toggle(false); menuBtn.focus(); }
    });
  }

  /* ---------- Card grids ---------- */

  var ROW = 4, GAP = 24;
  [].forEach.call(document.querySelectorAll('.pc-grid'), function (grid) {
    var layout = function () {
      [].forEach.call(grid.querySelectorAll('.pc'), function (el) {
        el.style.gridRowEnd = 'span ' + Math.ceil((el.offsetHeight + GAP) / ROW);
      });
    };
    layout();
    if (window.ResizeObserver) new ResizeObserver(layout).observe(grid);
    window.addEventListener('load', layout);
  });
})();
