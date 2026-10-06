/* Homepage behaviour (site/templates/wove-mind.php).
 *
 * Filters: the client carousel, topic pills, the pinned bar chips and the
 * tags on cards all set one active filter ("cs:slug", "service:x",
 * "tag:y", or "*" for all). Choosing the active one again clears it. Cards
 * carry data-filters; the first N matches show (N = data-limit on the grid).
 * Selecting a client shows its case study panel. After a change the page
 * scrolls to the results, under the pinned bar.
 *
 * Layout: cards stay in date order in the DOM and pack with grid row spans,
 * so reading and tab order match what you see.
 *
 * Pinned bar: shows once the full filter controls have scrolled away while
 * the feed is on screen, and (menu button only) on scroll-up elsewhere past
 * the hero. The menu holds Back to top and the site links.
 */
(function () {
  var filters = document.getElementById('filters');
  var grid = document.getElementById('feed-grid');
  var bar = document.getElementById('filter-bar');
  if (!filters || !grid || !bar) return;

  var cards = [].slice.call(grid.querySelectorAll('.pc'));
  var limit = parseInt(grid.getAttribute('data-limit'), 10) || 12;
  var countEl = document.getElementById('feed-count');
  var barCount = bar.querySelector('[data-count]');
  var empty = document.getElementById('feed-empty');
  var moreCount = document.getElementById('feed-more-count');
  var moreLink = document.getElementById('feed-more-link');
  var ourWork = moreLink ? moreLink.getAttribute('href') : '/our-work';
  var hero = document.querySelector('.hx');
  var active = '*';

  function reduceMotion() { return window.matchMedia('(prefers-reduced-motion: reduce)').matches; }
  function behaviour() { return reduceMotion() ? 'auto' : 'smooth'; }
  function entryWord(n) { return n + ' entr' + (n === 1 ? 'y' : 'ies'); }

  // Every control that sets a filter
  function controls() {
    return [].slice.call(document.querySelectorAll('#filters [data-filter], #filter-bar [data-filter]'));
  }

  function labelFor(key) {
    var el = document.querySelector('#filter-bar [data-filter="' + key + '"]');
    if (!el) return '';
    var clone = el.cloneNode(true);
    var count = clone.querySelector('.bar-chip__count');
    if (count) count.remove();
    return clone.textContent.trim();
  }

  /* ---------- Apply a filter ---------- */

  function apply(key) {
    active = key;

    controls().forEach(function (el) {
      var on = el.getAttribute('data-filter') === key;
      el.setAttribute('aria-pressed', String(on));
      el.classList.toggle('is-active', on);
    });

    [].forEach.call(filters.querySelectorAll('.case-panel'), function (p) {
      p.hidden = p.getAttribute('data-panel') !== key;
    });

    var matched = 0;
    cards.forEach(function (card) {
      var match = key === '*' || (' ' + card.getAttribute('data-filters') + ' ').indexOf(' ' + key + ' ') !== -1;
      card.hidden = !match || matched >= limit;
      if (match) matched++;
    });
    var shown = Math.min(matched, limit);

    countEl.textContent = entryWord(matched);
    if (barCount) barCount.textContent = entryWord(matched);
    if (empty) empty.hidden = matched > 0;
    if (moreCount) {
      moreCount.hidden = matched <= shown;
      moreCount.textContent = 'Showing ' + shown + ' of ' + matched;
    }
    if (moreLink) {
      var name = key === '*' ? '' : labelFor(key);
      moreLink.href = key === '*' ? ourWork : ourWork + '?filter=' + encodeURIComponent(key);
      moreLink.innerHTML = 'See all ' + (name ? escapeHTML(name) + ' ' : '') + 'work <span aria-hidden="true">&rarr;</span>';
    }

    layout();
    revealInCarousel();
    revealInBar();
  }

  function escapeHTML(s) {
    return s.replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; });
  }

  // Choosing the active filter again clears it.
  function choose(key) {
    apply(active === key && key !== '*' ? '*' : key);
  }

  /* ---------- Card layout (grid row spans) ---------- */

  var ROW = 4, GAP = 24;
  function layout() {
    cards.forEach(function (el) {
      if (!el.hidden) el.style.gridRowEnd = 'span ' + Math.ceil((el.offsetHeight + GAP) / ROW);
    });
  }
  if (window.ResizeObserver) new ResizeObserver(layout).observe(grid);
  window.addEventListener('load', layout);

  /* ---------- Scroll to results ---------- */

  function scrollToResults() {
    requestAnimationFrame(function () {
      var panel = filters.querySelector('.case-panel:not([hidden])');
      var target = panel || grid;
      var barH = bar.querySelector('.filter-bar__inner').offsetHeight || 56;
      var y = target.getBoundingClientRect().top + window.scrollY - barH - 24;
      window.scrollTo({ top: Math.max(0, y), behavior: behaviour() });
    });
  }

  /* ---------- Client carousel ---------- */

  var track = filters.querySelector('.client-carousel__track');
  var slider = filters.querySelector('.client-carousel__slider');
  var arrows = [].slice.call(filters.querySelectorAll('.client-carousel__arrow'));

  function maxScroll() { return track.scrollWidth - track.clientWidth; }
  function syncCarousel() {
    var max = maxScroll();
    slider.value = max > 0 ? Math.round(track.scrollLeft / max * 1000) : 0;
    // The dark segment marks the part of the client list in view.
    var total = track.scrollWidth || 1;
    slider.style.setProperty('--from', (track.scrollLeft / total * 100) + '%');
    slider.style.setProperty('--to', ((track.scrollLeft + track.clientWidth) / total * 100) + '%');
    arrows.forEach(function (b) {
      var dir = +b.getAttribute('data-step');
      b.disabled = dir < 0 ? track.scrollLeft <= 2 : track.scrollLeft >= max - 2;
    });
  }
  function revealInCarousel() {
    var btn = track.querySelector('[aria-pressed="true"]');
    var li = btn && btn.parentElement;
    if (li && (li.offsetLeft < track.scrollLeft || li.offsetLeft + li.offsetWidth > track.scrollLeft + track.clientWidth)) {
      // The page also scrolls; two smooth scrolls at once can cancel (Safari), so this one jumps.
      track.scrollTo({ left: li.offsetLeft - track.offsetLeft, behavior: 'auto' });
    }
  }
  track.addEventListener('scroll', syncCarousel, { passive: true });
  window.addEventListener('resize', syncCarousel);
  slider.addEventListener('input', function () { track.scrollLeft = slider.value / 1000 * maxScroll(); });
  arrows.forEach(function (b) {
    b.addEventListener('click', function () {
      var card = track.querySelector('.client-card');
      var gap = parseFloat(getComputedStyle(track).columnGap) || 0;
      track.scrollBy({ left: +b.getAttribute('data-step') * (card.offsetWidth + gap), behavior: behaviour() });
    });
  });
  syncCarousel();

  /* ---------- Filter clicks ---------- */

  function onFilterClick(ev) {
    var el = ev.target.closest('[data-filter]');
    if (!el) return;
    choose(el.getAttribute('data-filter'));
    scrollToResults();
  }
  filters.addEventListener('click', onFilterClick);

  // Card tags link to Our Work; filter in place when the homepage has that filter.
  grid.addEventListener('click', function (ev) {
    var a = ev.target.closest('a.card-tags__tag');
    if (!a) return;
    var key = new URL(a.href, location.href).searchParams.get('filter');
    var control = key && filters.querySelector('[data-filter="' + key + '"]');
    if (!control) return;
    ev.preventDefault();
    if (active !== key) apply(key);
    control.focus({ preventScroll: true });
    scrollToResults();
  });

  /* ---------- Pinned bar ---------- */

  var scroller = bar.querySelector('.filter-bar__scroll');
  var barTrack = bar.querySelector('.filter-bar__track');
  var menu = bar.querySelector('.filter-bar__menu');
  var menuBtn = bar.querySelector('[data-menu]');
  var pills = filters.querySelector('.topic-pills');
  var barShown = false, menuOpen = false;

  function syncBarEdges() {
    barTrack.classList.toggle('has-left', scroller.scrollLeft > 2);
    barTrack.classList.toggle('has-right', scroller.scrollLeft < scroller.scrollWidth - scroller.clientWidth - 2);
  }
  scroller.addEventListener('scroll', syncBarEdges, { passive: true });
  window.addEventListener('resize', syncBarEdges);

  // Keep the active chip clear of the faded edges.
  function revealInBar() {
    var chip = bar.querySelector('.bar-chip[aria-pressed="true"]:not([data-filter="*"])');
    if (!chip) return;
    var pad = 96;
    var l = chip.offsetLeft - scroller.offsetLeft, r = l + chip.offsetWidth;
    if (l < scroller.scrollLeft + pad || r > scroller.scrollLeft + scroller.clientWidth - pad) {
      scroller.scrollLeft = l - (scroller.clientWidth - chip.offsetWidth) / 2;
    }
    syncBarEdges();
  }

  function toggleMenu(open) {
    menuOpen = open;
    menu.hidden = !open;
    menuBtn.setAttribute('aria-expanded', String(open));
  }

  // Scroll direction, with thresholds so small movements don't flicker the bar.
  var lastY = window.scrollY, travel = 0, scrollingUp = false;

  function updateBar() {
    var y = window.scrollY;
    var dy = y - lastY;
    lastY = y;
    if ((dy < 0) !== (travel < 0)) travel = 0;
    travel += dy;
    if (travel < -40) scrollingUp = true;
    if (travel > 12) { scrollingUp = false; if (menuOpen) toggleMenu(false); }

    var chipsH = bar.querySelector('.filter-bar__inner').offsetHeight || 56;
    var inFeed = pills.getBoundingClientRect().bottom < chipsH && grid.getBoundingClientRect().bottom > chipsH + 80;
    var pastHero = hero ? hero.getBoundingClientRect().bottom < 0 : y > 400;
    var show = inFeed || (scrollingUp && pastHero) || menuOpen;

    bar.classList.toggle('show-chips', inFeed);
    if (show !== barShown) {
      barShown = show;
      bar.classList.toggle('is-visible', show);
      bar.inert = !show;
      syncBarEdges();
    }
  }

  var ticking = false;
  window.addEventListener('scroll', function () {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(function () { ticking = false; updateBar(); });
  }, { passive: true });

  bar.addEventListener('click', function (ev) {
    if (ev.target.closest('[data-top]')) {
      toggleMenu(false);
      scrollingUp = false;
      window.scrollTo({ top: 0, behavior: behaviour() });
      return;
    }
    if (ev.target.closest('[data-menu]')) { toggleMenu(!menuOpen); return; }
    if (ev.target.closest('.filter-bar__menu a')) { toggleMenu(false); return; }
    var arrow = ev.target.closest('[data-bar-step]');
    if (arrow) {
      scroller.scrollBy({ left: +arrow.getAttribute('data-bar-step') * scroller.clientWidth * 0.6, behavior: behaviour() });
      return;
    }
    onFilterClick(ev);
  });

  document.addEventListener('click', function (ev) {
    if (menuOpen && !bar.contains(ev.target)) { toggleMenu(false); updateBar(); }
  });
  document.addEventListener('keydown', function (ev) {
    if (ev.key === 'Escape' && menuOpen) { toggleMenu(false); menuBtn.focus(); }
  });

  /* ---------- Start ---------- */

  document.documentElement.classList.add('home-js');
  apply('*');
  updateBar();
})();
