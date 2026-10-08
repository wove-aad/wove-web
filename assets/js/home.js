/* Feed behaviour: the homepage (site/templates/wove-mind.php) and Our work
 * (snippets/feed/work-view.php, also used by the service and tag pages).
 *
 * Filters: the client carousel, topic pills, the pinned bar chips and the
 * tags on cards all set one active filter ("cs:slug", "service:x",
 * "tag:y", or "*" for all). Choosing the active one again clears it. Cards
 * carry data-filters; the first N matches show (N = data-limit on the grid,
 * all of them when it has none). With data-page (Our work) N starts at that
 * many and "Load more" adds the same again; choosing a filter resets it. Selecting a filter shows its context
 * panel (data-panel): the case study for a client, or the service, tag,
 * sector or person. After a change the page scrolls to the results, under
 * the pinned bar.
 *
 * Our work: the grid's data-initial is the filter to start with, and
 * data-url is the Our work address, kept in step with ?filter=.
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
  var pageSize = grid.hasAttribute('data-page') ? parseInt(grid.getAttribute('data-page'), 10) || 12 : 0;
  var limit = pageSize || (grid.hasAttribute('data-limit') ? parseInt(grid.getAttribute('data-limit'), 10) || 12 : Infinity);
  var workUrl = grid.getAttribute('data-url');
  var countEl = document.getElementById('feed-count');
  var barCount = bar.querySelector('[data-count]');
  var empty = document.getElementById('feed-empty');
  var moreCount = document.getElementById('feed-more-count');
  var moreLink = document.getElementById('feed-more-link');
  var more = document.getElementById('feed-more');
  var loadMore = document.getElementById('feed-load-more');
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
    if (!el) {
      var panel = filters.querySelector('[data-panel="' + key + '"]');
      return panel ? panel.getAttribute('data-label') || '' : '';
    }
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

    [].forEach.call(filters.querySelectorAll('[data-panel]'), function (p) {
      p.hidden = p.getAttribute('data-panel') !== key;
    });

    var matched = 0;
    cards.forEach(function (card) {
      var match = key === '*' || (' ' + card.getAttribute('data-filters') + ' ').indexOf(' ' + key + ' ') !== -1;
      card.hidden = !match || matched >= limit;
      if (match) matched++;
    });
    var shown = Math.min(matched, limit);

    if (countEl) countEl.textContent = entryWord(matched);
    if (barCount) barCount.textContent = entryWord(matched);
    if (empty) empty.hidden = matched > 0;
    if (moreCount) {
      moreCount.hidden = matched <= shown;
      moreCount.textContent = 'Showing ' + shown + ' of ' + matched;
    }
    if (loadMore) more.hidden = matched <= shown;
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

  // Choosing the active filter again clears it. On Our work the address
  // follows, so a filtered view can be shared.
  function choose(key) {
    if (pageSize) limit = pageSize;
    apply(active === key && key !== '*' ? '*' : key);
    if (workUrl) history.replaceState(null, '', workUrl + (active === '*' ? '' : '?filter=' + encodeURIComponent(active)));
  }

  // Whether any card or panel answers to a filter key.
  function known(key) {
    if (key === '*') return true;
    if (filters.querySelector('[data-panel="' + key + '"]')) return true;
    return cards.some(function (c) { return (' ' + c.getAttribute('data-filters') + ' ').indexOf(' ' + key + ' ') !== -1; });
  }

  /* ---------- Card layout (grid row spans) ---------- */

  var ROW = 4, GAP = 24;
  function layout() {
    cards.forEach(function (el) {
      if (!el.hidden) el.style.gridRowEnd = 'span ' + Math.ceil((el.offsetHeight + GAP) / ROW);
    });
  }
  // Watch each card as well as the grid, so a card that grows (its tags
  // expanding, an image loading) takes more rows.
  if (window.ResizeObserver) {
    var ro = new ResizeObserver(layout);
    ro.observe(grid);
    cards.forEach(function (el) { ro.observe(el); });
  }
  window.addEventListener('load', layout);

  /* ---------- Scroll to results ---------- */

  function scrollToResults(instant) {
    requestAnimationFrame(function () {
      var panel = filters.querySelector('[data-panel]:not([hidden])');
      var target = panel || grid;
      var barH = bar.querySelector('.filter-bar__inner').offsetHeight || 56;
      var y = target.getBoundingClientRect().top + window.scrollY - barH - 24;
      window.scrollTo({ top: Math.max(0, y), behavior: instant ? 'auto' : behaviour() });
    });
  }

  /* ---------- Client carousel ---------- */

  var track = filters.querySelector('.client-carousel__track');
  var slider = filters.querySelector('.client-carousel__slider');
  var arrows = [].slice.call(filters.querySelectorAll('.client-carousel__arrow'));

  function maxScroll() { return track.scrollWidth - track.clientWidth; }
  var carouselControls = filters.querySelector('.client-carousel__controls');
  function syncCarousel() {
    var max = maxScroll();
    // Nothing to scroll: hide the slider and arrows.
    carouselControls.hidden = max <= 2;
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
    if (ev.target.closest('[data-clear]')) { choose('*'); scrollToResults(); return; }
    var el = ev.target.closest('[data-filter]');
    if (!el) return;
    choose(el.getAttribute('data-filter'));
    scrollToResults();
  }
  filters.addEventListener('click', onFilterClick);

  // Our work: show the next page of matches, and move focus to the first
  // new card so keyboard users carry on from there.
  if (loadMore) loadMore.addEventListener('click', function () {
    var before = cards.filter(function (c) { return !c.hidden; }).length;
    limit += pageSize;
    apply(active);
    var next = cards.filter(function (c) { return !c.hidden; })[before];
    var link = next && (next.querySelector('.pc__link') || next.querySelector('a[href]'));
    if (link) link.focus({ preventScroll: true });
  });

  // Card tags link to Our Work; filter in place when this page has that
  // filter (a control on the homepage; any matching card on Our work).
  grid.addEventListener('click', function (ev) {
    var a = ev.target.closest('a.card-tags__tag');
    if (!a) return;
    var key = new URL(a.href, location.href).searchParams.get('filter');
    var control = key && filters.querySelector('[data-filter="' + key + '"]');
    if (!control && !(workUrl && key && known(key))) return;
    ev.preventDefault();
    if (active !== key) choose(key);
    (control || grid).focus({ preventScroll: true });
    scrollToResults();
  });

  // Homepage intro services: filter the feed in place and jump to it, when
  // the feed has that filter; otherwise the link goes to Our work.
  document.addEventListener('click', function (ev) {
    var a = ev.target.closest('a[data-jump-filter]');
    if (!a) return;
    var key = a.getAttribute('data-jump-filter');
    if (!known(key)) return;
    ev.preventDefault();
    if (active !== key) choose(key);
    grid.focus({ preventScroll: true });
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
  var initial = grid.getAttribute('data-initial') || '*';
  apply(known(initial) ? initial : '*');
  updateBar();
  // Arriving with a filter (a service or tag page, or ?filter=): start at
  // its panel and results.
  if (active !== '*') window.addEventListener('load', function () { scrollToResults(true); });
})();

/* Blue hero options (?hero=a|b|c): as the hero scrolls away its blue fades
 * into the feed's grey (--p), and in option b the squiggle draws with the
 * scroll (--draw). */
(function () {
  var hero = document.querySelector('.hx--blue');
  if (!hero) return;
  var drawn = hero.getAttribute('data-hero') === 'b';
  var ticking = false;
  // On phones the squiggle fills its space and crops at the sides, so it
  // stays a good size.
  var svg = hero.querySelector('.hx-squiggle svg');
  var narrow = window.matchMedia('(max-width: 600px)');
  function fit() {
    svg.setAttribute('preserveAspectRatio', narrow.matches ? svg.getAttribute('data-narrow') : 'xMidYMid meet');
  }
  fit();
  if (narrow.addEventListener) narrow.addEventListener('change', fit);
  function update() {
    ticking = false;
    var h = hero.offsetHeight || 1;
    var y = window.scrollY;
    // Fade from 35% to 90% of the hero scrolled, when the text is mostly gone.
    hero.style.setProperty('--p', Math.min(1, Math.max(0, (y / h - 0.35) / 0.55)).toFixed(3));
    if (drawn) hero.style.setProperty('--draw', Math.min(1, 0.55 + y / (h * 0.6)).toFixed(3));
  }
  window.addEventListener('scroll', function () {
    if (!ticking) { ticking = true; requestAnimationFrame(update); }
  }, { passive: true });
  window.addEventListener('resize', update);
  update();
})();
