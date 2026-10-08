/* Inner pages without feed filters (case studies, posts, Our people,
 * contact).
 *
 * - Bar (snippets/brand/menu-bar.php): pins to the top on scroll-up
 *   once the page header has scrolled away, with a back-to-top button and
 *   the logo.
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
    var shown = false;
    var lastY = window.scrollY, travel = 0, up = false;

    // Scroll direction, with thresholds so small movements don't flicker the bar.
    var update = function () {
      var y = window.scrollY, dy = y - lastY;
      lastY = y;
      if ((dy < 0) !== (travel < 0)) travel = 0;
      travel += dy;
      if (travel < -40) up = true;
      if (travel > 12) up = false;
      var show = up && header.getBoundingClientRect().bottom < 0;
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
      if (ev.target.closest('[data-top]')) { up = false; window.scrollTo({ top: 0, behavior: behaviour() }); }
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
    // Watch each card as well as the grid, so a card that grows (its tags
    // expanding, an image loading) takes more rows.
    if (window.ResizeObserver) {
      var ro = new ResizeObserver(layout);
      ro.observe(grid);
      [].forEach.call(grid.querySelectorAll('.pc'), function (el) { ro.observe(el); });
    }
    window.addEventListener('load', layout);
  });
})();

/* ---------- Our people ----------
 * Choosing a person opens a panel under their row, holding the details
 * that are rendered inside their card. One is open at a time; choosing it
 * again, Close or Escape shuts it and returns focus to the card. The other
 * cards step back. The panel moves when the number of columns changes.
 * /our-people#{slug} opens that person on load. */
(function () {
  var grid = document.getElementById('people-grid');
  if (!grid) return;
  var open = null, panel = null;

  function behaviour() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth';
  }

  // The last card in the open card's row
  function lastInRow(card) {
    var top = card.offsetTop, last = card, el = card.nextElementSibling;
    while (el) {
      if (el.classList.contains('person-card')) {
        if (el.offsetTop !== top) break;
        last = el;
      }
      el = el.nextElementSibling;
    }
    return last;
  }

  function close(focus) {
    if (!open) return;
    var card = document.getElementById(open);
    card.appendChild(panel.firstElementChild);   // details back into the card
    panel.remove();
    panel = null;
    grid.classList.remove('has-open');
    var btn = card.querySelector('.person-card__btn');
    btn.setAttribute('aria-expanded', 'false');
    if (focus) btn.focus();
    open = null;
    history.replaceState(null, '', location.pathname + location.search);
  }

  function show(slug, scroll) {
    var card = document.getElementById(slug);
    if (!card || !card.classList.contains('person-card')) return;
    close(false);
    open = slug;
    panel = document.createElement('li');
    panel.className = 'person-detail';
    panel.appendChild(card.querySelector('.person-card__details'));
    lastInRow(card).insertAdjacentElement('afterend', panel);
    card.querySelector('.person-card__btn').setAttribute('aria-expanded', 'true');
    grid.classList.add('has-open');
    history.replaceState(null, '', '#' + slug);
    if (scroll) {
      // Bring the card and its panel into view together; the panel wins
      // when both don't fit.
      var r = panel.getBoundingClientRect(), c = card.getBoundingClientRect();
      if (r.bottom > window.innerHeight || c.top < 0) {
        var y = Math.min(c.top - 16, r.bottom - window.innerHeight + 16);
        window.scrollBy({ top: y, behavior: behaviour() });
      }
    }
  }

  window.addEventListener('resize', function () {
    if (!open) return;
    var after = lastInRow(document.getElementById(open));
    if (after.nextElementSibling !== panel) after.insertAdjacentElement('afterend', panel);
  });

  grid.addEventListener('click', function (ev) {
    var btn = ev.target.closest('.person-card__btn');
    if (btn) {
      var slug = btn.getAttribute('data-person');
      if (open === slug) close(true); else show(slug, true);
      return;
    }
    if (ev.target.closest('.person-detail__close')) close(true);
  });
  document.addEventListener('keydown', function (ev) {
    if (ev.key === 'Escape' && open) close(true);
  });

  var hash = decodeURIComponent(location.hash.slice(1));
  if (hash && document.getElementById(hash)) {
    show(hash, false);
    requestAnimationFrame(function () { document.getElementById(hash).scrollIntoView({ block: 'start' }); });
  }
})();
