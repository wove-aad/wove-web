/* Feed behaviour: the homepage (site/templates/wove-mind.php) and Our work
 * (snippets/feed/work-view.php, also used by the service and tag pages).
 *
 * Filters: Projects / Thinking / News beside the feed heading (#feed-types)
 * set one active filter ("type:project", or "*" for all); ?filter= links
 * and author links on cards can also set "cs:slug", "service:x", "tag:y",
 * "sector:z" or "author:slug". Choosing the active one again clears it. Cards
 * carry data-filters; the first N matches show (N = data-limit on the grid,
 * all of them when it has none). With data-page (Our work) N starts at that
 * many and "Load more" adds the same again; choosing a filter resets it. Selecting a filter shows its context
 * panel (data-panel): the case study for a client, or the service, tag,
 * sector or person. After a change the page scrolls to the results.
 *
 * Our work: the grid's data-initial is the filter to start with, and
 * data-url is the Our work address, kept in step with ?filter=.
 *
 * Layout: a plain CSS grid in date order, rows aligned at the top
 * (home.css), so reading and tab order match what you see.
 *
 * Pinned header: the site header (#sticky-head, brand/menu-bar) slides
 * down on scroll-up past the hero. The pinned filter bar was removed on
 * 2026-10-29.
 */
(function () {
  var filters = document.getElementById('filters');
  var types = document.getElementById('feed-types');
  var heading = document.getElementById('feed');
  var grid = document.getElementById('feed-grid');
  if (!filters || !grid) return;

  var cards = [].slice.call(grid.querySelectorAll('.pc'));
  var pageSize = grid.hasAttribute('data-page') ? parseInt(grid.getAttribute('data-page'), 10) || 12 : 0;
  var limit = pageSize || (grid.hasAttribute('data-limit') ? parseInt(grid.getAttribute('data-limit'), 10) || 12 : Infinity);
  var workUrl = grid.getAttribute('data-url');
  var countEl = document.getElementById('feed-count');
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
    return [].slice.call(document.querySelectorAll('#feed-types [data-filter]'));
  }

  function labelFor(key) {
    var el = document.querySelector('#feed-types [data-filter="' + key + '"]');
    if (!el) {
      var panel = filters.querySelector('[data-panel="' + key + '"]');
      return panel ? panel.getAttribute('data-label') || '' : '';
    }
    var clone = el.cloneNode(true);
    var count = clone.querySelector('.feed-type__count');
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

  /* ---------- Masonry option (pc-grid--masonry, for review) ---------- */

  // Each card spans as many 4px rows as its height needs, plus the gap.
  function layout() {
    if (!grid.classList.contains('pc-grid--masonry')) return;
    cards.forEach(function (el) {
      if (!el.hidden) el.style.gridRowEnd = 'span ' + Math.ceil((el.offsetHeight + 48) / 4);
    });
  }
  if (grid.classList.contains('pc-grid--masonry')) {
    if (window.ResizeObserver) {
      var ro = new ResizeObserver(layout);
      ro.observe(grid);
      cards.forEach(function (el) { ro.observe(el); });
    }
    window.addEventListener('load', layout);
  }

  /* ---------- Scroll to results ---------- */

  function scrollToResults(instant) {
    requestAnimationFrame(function () {
      var panel = filters.querySelector('[data-panel]:not([hidden])');
      var target = panel || grid;
      var y = target.getBoundingClientRect().top + window.scrollY - 24;
      window.scrollTo({ top: Math.max(0, y), behavior: instant ? 'auto' : behaviour() });
    });
  }

  /* ---------- Filter clicks ---------- */

  function onFilterClick(ev) {
    if (ev.target.closest('[data-clear]')) { choose('*'); scrollToResults(); return; }
    var el = ev.target.closest('[data-filter]');
    if (!el) return;
    choose(el.getAttribute('data-filter'));
    scrollToResults();
  }
  filters.addEventListener('click', onFilterClick);
  if (types) types.addEventListener('click', onFilterClick);

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

  // Author links on cards go to the feed filtered by that person; filter in
  // place when this page has that filter.
  grid.addEventListener('click', function (ev) {
    var a = ev.target.closest('a.pc__author');
    if (!a) return;
    var key = new URL(a.href, location.href).searchParams.get('filter');
    var control = key && filters.querySelector('[data-filter="' + key + '"]');
    if (!control && !(workUrl && key && known(key))) return;
    ev.preventDefault();
    if (active !== key) choose(key);
    (control || grid).focus({ preventScroll: true });
    scrollToResults();
  });

  // Homepage intro services and the hero's "See our work" (data-jump-filter
  // "*"): filter the feed in place and jump to the cards when the feed has
  // that filter; otherwise the link goes to Our work.
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

  /* ---------- Pinned site header ---------- */

  // The site header (#sticky-head, brand/menu-bar) slides down on scroll-up
  // past the hero. Thresholds stop small movements flickering it.
  var head = document.getElementById('sticky-head');
  var headShown = false;
  var lastY = window.scrollY, travel = 0, scrollingUp = false;

  function updateBar() {
    var y = window.scrollY;
    var dy = y - lastY;
    lastY = y;
    if ((dy < 0) !== (travel < 0)) travel = 0;
    travel += dy;
    if (travel < -40) scrollingUp = true;
    if (travel > 12) scrollingUp = false;

    var pastHero = hero ? hero.getBoundingClientRect().bottom < 0 : y > 400;
    var showHead = !!head && scrollingUp && pastHero;
    if (head && showHead !== headShown) {
      headShown = showHead;
      head.classList.toggle('is-visible', showHead);
      head.inert = !showHead;
    }
  }

  var ticking = false;
  window.addEventListener('scroll', function () {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(function () { ticking = false; updateBar(); });
  }, { passive: true });

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
