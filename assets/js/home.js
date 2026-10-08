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
    syncMore();
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

  // Click and drag with a mouse (touch screens swipe natively). A drag of
  // more than a few pixels doesn't count as a click on the card under it.
  var drag = null, dragged = false;
  track.addEventListener('pointerdown', function (ev) {
    if (ev.pointerType !== 'mouse' || ev.button !== 0) return;
    drag = { x: ev.clientX, left: track.scrollLeft, id: ev.pointerId };
    dragged = false;
  });
  track.addEventListener('pointermove', function (ev) {
    if (!drag || ev.pointerId !== drag.id) return;
    var dx = ev.clientX - drag.x;
    if (!dragged && Math.abs(dx) > 5) {
      dragged = true;
      track.setPointerCapture(drag.id);
      track.classList.add('is-dragging');
    }
    if (dragged) track.scrollLeft = drag.left - dx;
  });
  function endDrag() {
    if (!drag) return;
    drag = null;
    if (!dragged) return;
    track.classList.remove('is-dragging');
    // Settle on the nearest card, as the snap would
    var card = track.querySelector('.client-card');
    var step = card.offsetWidth + (parseFloat(getComputedStyle(track).columnGap) || 0);
    track.scrollTo({ left: Math.round(track.scrollLeft / step) * step, behavior: behaviour() });
  }
  track.addEventListener('pointerup', endDrag);
  track.addEventListener('pointercancel', endDrag);
  track.addEventListener('click', function (ev) {
    if (dragged) { ev.preventDefault(); ev.stopPropagation(); dragged = false; }
  }, true);
  track.addEventListener('dragstart', function (ev) { ev.preventDefault(); });

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
    var a = ev.target.closest('a.card-tags__tag, a.pc__author');
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
  // "*"): filter the feed in place and jump to the cards, under the pinned
  // bar, when the feed has that filter; otherwise the link goes to Our work.
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

  var menu = bar.querySelector('.filter-bar__menu');
  var menuBtn = bar.querySelector('[data-menu]');
  var morePanel = bar.querySelector('.filter-more');
  var moreBtn = bar.querySelector('[data-more]');
  var moreLabel = bar.querySelector('[data-more-label]');
  var pills = filters.querySelector('.topic-pills');
  var barShown = false, menuOpen = false, moreOpen = false;

  // When the active filter is one of the More panel's, the More button
  // carries its name and the pressed look (choosing it again in the panel
  // clears it, as with the chips).
  function syncMore() {
    // Narrow screens: keep the active chip in view in the swipe row
    var row = bar.querySelector('.filter-bar__chips');
    var chip = row && row.querySelector('[aria-pressed="true"]:not([data-filter="*"])');
    if (chip && chip.offsetParent && row.scrollWidth > row.clientWidth) {
      var l = chip.offsetLeft - row.offsetLeft;
      if (l < row.scrollLeft || l + chip.offsetWidth > row.scrollLeft + row.clientWidth) row.scrollLeft = l - 16;
    }
    if (!moreBtn) return;
    var item = morePanel.querySelector('[data-filter="' + active + '"]');
    moreLabel.textContent = item ? item.querySelector('.filter-more__name').textContent : 'More';
    moreBtn.classList.toggle('is-active', !!item);
  }

  function toggleMenu(open) {
    if (!menu) return;
    menuOpen = open;
    menu.hidden = !open;
    menuBtn.setAttribute('aria-expanded', String(open));
    if (open) toggleMore(false);
  }
  function toggleMore(open) {
    if (!moreBtn) return;
    moreOpen = open;
    morePanel.hidden = !open;
    moreBtn.setAttribute('aria-expanded', String(open));
    if (open && menuOpen) toggleMenu(false);
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
    if (travel > 12) { scrollingUp = false; if (menuOpen) toggleMenu(false); if (moreOpen) toggleMore(false); }

    var chipsH = bar.querySelector('.filter-bar__inner').offsetHeight || 56;
    var inFeed = pills.getBoundingClientRect().bottom < chipsH && grid.getBoundingClientRect().bottom > chipsH + 80;
    var pastHero = hero ? hero.getBoundingClientRect().bottom < 0 : y > 400;
    var show = inFeed || (scrollingUp && pastHero) || menuOpen || moreOpen;

    bar.classList.toggle('show-chips', inFeed);
    if (show !== barShown) {
      barShown = show;
      bar.classList.toggle('is-visible', show);
      bar.inert = !show;
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
    if (ev.target.closest('[data-more]')) { toggleMore(!moreOpen); return; }
    if (ev.target.closest('.filter-more [data-filter]')) toggleMore(false);
    onFilterClick(ev);
  });

  document.addEventListener('click', function (ev) {
    if ((menuOpen || moreOpen) && !bar.contains(ev.target)) { toggleMenu(false); toggleMore(false); updateBar(); }
  });
  document.addEventListener('keydown', function (ev) {
    if (ev.key === 'Escape' && menuOpen) { toggleMenu(false); menuBtn.focus(); }
    if (ev.key === 'Escape' && moreOpen) { toggleMore(false); moreBtn.focus(); }
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

/* Homepage: section paging between the hero, the intro and Our work.
 * In that top stretch a scroll gesture (wheel, trackpad, swipe or
 * Page Down / arrow / space) carries straight on to the next section in a
 * quick ease, as if your own scroll were lengthened; scrolling up from the
 * top of Our work goes back to the intro. Inside the feed and below it,
 * scrolling is the browser's own. Trackpad and touch momentum after a
 * move is absorbed so it doesn't overshoot. Off with reduced motion.
 * Earlier tries: CSS scroll-snap "proximity" (pulled back deliberate
 * scrolls) and an ease-in after scrolling stopped (felt late). */
(function () {
  var hero = document.querySelector('.hx--home');
  var intro = document.querySelector('.hx-intro');
  var feed = document.getElementById('feed');
  if (!hero || !intro || !feed) return;
  var still = window.matchMedia('(prefers-reduced-motion: reduce)');
  var DURATION = 650;
  var moving = false, lockUntil = 0, lastWheel = 0;

  // Section tops; when the intro is taller than the screen (phones) it
  // gets a second stop so its end shows before Our work
  function stops() {
    var y = window.scrollY, vh = window.innerHeight;
    var r = intro.getBoundingClientRect(), introTop = r.top + y;
    var list = [0, introTop];
    if (r.height > vh + 8) list.push(introTop + r.height - vh);
    list.push(feed.getBoundingClientRect().top + y);
    return list.map(Math.round);
  }
  // Where a gesture in this direction should go, or null to scroll natively
  function target(dir) {
    var y = window.scrollY, s = stops(), feedTop = s[s.length - 1];
    if (dir > 0 && y < feedTop - 4) return s.filter(function (v) { return v > y + 4; })[0];
    if (dir < 0 && y > 0 && y <= feedTop + 4) return s.filter(function (v) { return v < y - 4; }).pop();
    return null;
  }
  function ease(t) { return 1 - Math.pow(1 - t, 4); }
  function go(to) {
    var from = window.scrollY, start = performance.now();
    moving = true;
    (function step(now) {
      var t = Math.min(1, (now - start) / DURATION);
      window.scrollTo({ top: from + (to - from) * ease(t), behavior: 'instant' });
      if (t < 1) requestAnimationFrame(step);
      else { moving = false; lockUntil = performance.now() + 450; }
    })(start);
  }
  function busy() {
    var bar = document.getElementById('filter-bar');
    return still.matches || (bar && bar.querySelector('[aria-expanded="true"]'));
  }

  // Wheel and trackpad
  window.addEventListener('wheel', function (ev) {
    if (busy() || ev.ctrlKey || Math.abs(ev.deltaX) > Math.abs(ev.deltaY)) return;
    var now = performance.now(), gap = now - lastWheel;
    lastWheel = now;
    // Swallow the rest of a gesture (and its momentum) after a move
    if (moving || (now < lockUntil && gap < 120)) { ev.preventDefault(); if (!moving) lockUntil = now + 200; return; }
    var to = target(ev.deltaY > 0 ? 1 : -1);
    if (to == null) return;
    ev.preventDefault();
    go(to);
  }, { passive: false });

  // Touch: a swipe in the top stretch moves a whole section
  var touchY = null, touchTo = null;
  window.addEventListener('touchstart', function (ev) {
    touchY = ev.touches.length === 1 ? ev.touches[0].clientY : null;
    touchTo = undefined;
  }, { passive: true });
  window.addEventListener('touchmove', function (ev) {
    if (touchY === null || busy()) return;
    var dy = touchY - ev.touches[0].clientY;
    if (moving) { ev.preventDefault(); return; }
    if (touchTo === undefined && Math.abs(dy) > 6) touchTo = target(dy > 0 ? 1 : -1);
    if (touchTo != null) ev.preventDefault();
  }, { passive: false });
  window.addEventListener('touchend', function () {
    if (touchTo != null && !moving) go(touchTo);
    touchY = null;
  }, { passive: true });

  // Keyboard
  window.addEventListener('keydown', function (ev) {
    if (busy() || moving || ev.altKey || ev.ctrlKey || ev.metaKey) return;
    if (ev.target.closest && ev.target.closest('input, textarea, select, [contenteditable]')) return;
    var dir = { PageDown: 1, ArrowDown: 1, ' ': ev.shiftKey ? -1 : 1, PageUp: -1, ArrowUp: -1 }[ev.key];
    if (!dir) return;
    var to = target(dir);
    if (to == null) return;
    ev.preventDefault();
    go(to);
  });
})();
