/* Homepage: hero previews, client carousel and topic filters, case study
   panel, post cards, and the pinned filter bar with its site menu.
   Filters: one active filter at a time across clients and topics. */
(function () {
  var D = window.WOVE;
  var Cards = window.WoveCards;
  var csBySlug = {};
  D.caseStudies.forEach(function (cs) { csBySlug[cs.slug] = cs; });

  var LIMIT = 12; // homepage shows a fixed number of cards; the rest live on Our Work
  var state = { client: '*', topic: '*' };

  // Choosing a client clears the topic and the other way round.
  // Choosing the active one again clears it.
  function choose(kind, value) {
    if (kind === 'client') {
      state.client = state.client === value ? '*' : value;
      state.topic = '*';
    } else {
      state.topic = state.topic === value ? '*' : value;
      state.client = '*';
    }
  }

  /* ---------- Placeholder images ---------- */

  // Abstract SVG composition from a palette, seeded so each item is stable.
  function placeholder(palette, seed, w, h) {
    w = w || 800; h = h || 600;
    var r = seed * 9301 + 49297;
    function rand() { r = (r * 9301 + 49297) % 233280; return r / 233280; }
    var a = palette[0], b = palette[1], c = palette[2];
    var shapes = '';
    for (var i = 0; i < 3; i++) {
      var cx = Math.round(rand() * w), cy = Math.round(rand() * h);
      var rad = Math.round((0.25 + rand() * 0.35) * w);
      shapes += '<circle cx="' + cx + '" cy="' + cy + '" r="' + rad + '" fill="' + (i % 2 ? c : b) + '" opacity="0.9"/>';
    }
    var rx = Math.round(rand() * w * 0.5), ry = Math.round(rand() * h * 0.5);
    shapes += '<rect x="' + rx + '" y="' + ry + '" width="' + Math.round(w * 0.4) + '" height="' + Math.round(h * 0.5) + '" rx="24" fill="' + a + '" opacity="0.85" transform="rotate(' + Math.round(rand() * 30 - 15) + ' ' + (rx + w * 0.2) + ' ' + (ry + h * 0.25) + ')"/>';
    var svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' + w + ' ' + h + '" preserveAspectRatio="xMidYMid slice"><rect width="100%" height="100%" fill="' + a + '"/>' + shapes + '</svg>';
    return 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svg);
  }

  var GENERIC = ['#f3eeeb', '#ed8c7c', '#d5faff'];
  var images = {};
  function image(key, palette, seed, w, h) {
    return images[key] || (images[key] = placeholder(palette, seed, w, h));
  }

  /* ---------- Helpers ---------- */

  function esc(s) {
    return String(s).replace(/[&<>"]/g, function (ch) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[ch];
    });
  }

  function dateLabel(days) {
    if (days === 0) return 'Today';
    if (days === 1) return 'Yesterday';
    var d = new Date(); d.setDate(d.getDate() - days);
    if (days <= 6) return 'Last ' + d.toLocaleDateString('en-IE', { weekday: 'long' });
    return d.toLocaleDateString('en-IE', { day: 'numeric', month: 'short' });
  }

  function entriesFor(client, topic) {
    return D.entries.filter(function (e) {
      if (client !== '*' && e.caseStudy !== client) return false;
      if (topic === '*') return true;
      var p = topic.split(':');
      if (p[0] === 'service') return (e.services || []).indexOf(p[1]) !== -1;
      return (e.tags || []).indexOf(p[1]) !== -1;
    });
  }

  function entryWord(n) { return n + ' entr' + (n === 1 ? 'y' : 'ies'); }

  function topicLabel(t) {
    var p = t.split(':');
    return p[0] === 'service' ? D.services[p[1]] : D.tags[p[1]];
  }

  // Services first, then editorial tags (the site-wide tag order).
  var TOPICS = Object.keys(D.services).map(function (k) { return 'service:' + k; })
    .concat(Object.keys(D.tags).map(function (k) { return 'tag:' + k; }));

  function behaviour() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth';
  }

  /* ---------- Hero previews ---------- */

  var AVATAR_TONES = ['#ed8c7c', '#d5faff', '#e0bdff', '#f7ecd3'];
  document.querySelectorAll('[data-thumbs]').forEach(function (el) {
    el.innerHTML = D.caseStudies.slice(0, 3).map(function (cs, i) {
      return '<img class="hx-thumb" src="' + image('c' + (i + 1), cs.palette, i + 3) + '" alt="">';
    }).join('');
  });
  document.querySelectorAll('[data-avatars]').forEach(function (el) {
    el.innerHTML = D.people.slice(0, 4).map(function (p, i) {
      var initials = p.name.split(/\s+/).slice(0, 2).map(function (w) { return w.charAt(0); }).join('');
      return '<span class="hx-avatar" style="--tone:' + AVATAR_TONES[i] + '">' + esc(initials) + '</span>';
    }).join('');
  });

  /* ---------- Filters: client carousel and topic pills ---------- */

  function carouselHTML() {
    var cards = [null].concat(D.caseStudies).map(function (cs, i) {
      var slug = cs ? cs.slug : '*';
      var meta = (cs ? esc(cs.client) + ' · ' : '') + entryWord(entriesFor(slug, '*').length);
      return '<li class="client-card' + (cs ? '' : ' client-card--all') + '">' +
        '<button type="button" class="client-card__btn" data-client="' + slug + '" aria-pressed="' + (state.client === slug) + '">' +
          (cs ? '<img class="client-card__img" src="' + image('c' + i, cs.palette, i + 2) + '" alt="">' : '') +
          '<span class="client-card__logo">' + esc(cs ? cs.logo : 'All work') + '</span>' +
          '<span class="client-card__meta">' + meta + '</span>' +
        '</button>' +
      '</li>';
    }).join('');

    return '<section class="client-carousel" aria-label="Filter by client">' +
      '<ul class="client-carousel__track" role="list">' + cards + '</ul>' +
      '<div class="client-carousel__controls">' +
        '<input type="range" class="client-carousel__slider" min="0" max="1000" value="0" aria-label="Scroll clients">' +
        '<div class="client-carousel__arrows">' +
          '<button type="button" class="client-carousel__arrow" data-step="-1" aria-label="Previous clients">&larr;</button>' +
          '<button type="button" class="client-carousel__arrow" data-step="1" aria-label="Next clients">&rarr;</button>' +
        '</div>' +
      '</div>' +
    '</section>';
  }

  function pillsHTML() {
    function pill(t, label) {
      // "All" is active only when no client or topic is selected.
      var on = t === '*' ? state.topic === '*' && state.client === '*' : state.topic === t;
      return '<button type="button" class="tag-pill' + (on ? ' is-active' : '') + '" data-topic="' + t + '" aria-pressed="' + on + '">' + esc(label) + '</button>';
    }
    return '<div class="topic-pills topic-pills--ruled" role="group" aria-label="Filter by topic">' +
      pill('*', 'All') + TOPICS.map(function (t) { return pill(t, topicLabel(t)); }).join('') + '</div>';
  }

  function panelHTML(cs) {
    return '<section class="case-panel" aria-label="Case study: ' + esc(cs.client) + '">' +
      '<div class="case-panel__media"><img src="' + image('p' + cs.slug, cs.palette, 11, 900, 640) + '" alt="">' +
        '<span class="case-panel__logo">' + esc(cs.logo) + '</span></div>' +
      '<div class="case-panel__body">' +
        '<p class="case-panel__eyebrow">Case study · ' + esc(cs.client) + '</p>' +
        '<h3 class="case-panel__title">' + esc(cs.title) + '</h3>' +
        '<p class="case-panel__text">' + esc(cs.text) + '</p>' +
        '<dl class="case-panel__kpis">' + cs.kpis.map(function (k) {
          return '<div class="case-panel__kpi"><dt>' + esc(k.label) + '</dt><dd>' + esc(k.value) + '</dd></div>';
        }).join('') + '</dl>' +
        '<a href="#" class="case-panel__cta">See case study <span aria-hidden="true">&rarr;</span></a>' +
      '</div>' +
    '</section>';
  }

  var filters = document.getElementById('filters');
  var grid = document.getElementById('feed-grid');
  var countEl = document.getElementById('feed-count');
  var more = document.getElementById('feed-more');
  var savedScroll = 0;

  function bindCarousel(focusSelected) {
    var track = filters.querySelector('.client-carousel__track');
    var slider = filters.querySelector('.client-carousel__slider');
    track.scrollLeft = savedScroll;

    function maxScroll() { return track.scrollWidth - track.clientWidth; }
    function sync() {
      savedScroll = track.scrollLeft;
      var max = maxScroll();
      slider.value = max > 0 ? Math.round(track.scrollLeft / max * 1000) : 0;
      // The dark segment marks the part of the client list in view.
      var total = track.scrollWidth || 1;
      slider.style.setProperty('--from', (track.scrollLeft / total * 100) + '%');
      slider.style.setProperty('--to', ((track.scrollLeft + track.clientWidth) / total * 100) + '%');
      filters.querySelectorAll('.client-carousel__arrow').forEach(function (b) {
        var dir = +b.getAttribute('data-step');
        b.disabled = dir < 0 ? track.scrollLeft <= 2 : track.scrollLeft >= max - 2;
      });
    }
    track.addEventListener('scroll', sync, { passive: true });
    slider.addEventListener('input', function () { track.scrollLeft = slider.value / 1000 * maxScroll(); });
    sync();

    if (focusSelected) {
      var active = track.querySelector('[aria-pressed="true"]');
      var li = active && active.parentElement;
      if (li && (li.offsetLeft < track.scrollLeft || li.offsetLeft + li.offsetWidth > track.scrollLeft + track.clientWidth)) {
        // The page scrolls to the results straight after, and two smooth
        // scrolls at once can cancel each other (Safari), so this one jumps.
        track.scrollTo({ left: li.offsetLeft - track.offsetLeft, behavior: 'auto' });
      }
    }
  }
  window.addEventListener('resize', function () { bindCarousel(false); });

  // After a filter change, scroll to the top of the filtered results (case
  // study panel, or the first cards), under the pinned bar.
  function scrollToResults() {
    requestAnimationFrame(function () {
      var target = filters.querySelector('.case-panel') || grid;
      var y = target.getBoundingClientRect().top + window.scrollY - bar.offsetHeight - 24;
      window.scrollTo({ top: Math.max(0, y), behavior: behaviour() });
    });
  }

  filters.addEventListener('click', function (ev) {
    var client = ev.target.closest('.client-card__btn');
    var pill = ev.target.closest('.tag-pill');
    var arrow = ev.target.closest('.client-carousel__arrow');
    if (client) {
      choose('client', client.getAttribute('data-client'));
      render(true);
      restoreFocus(client);
      scrollToResults();
    } else if (pill) {
      choose('topic', pill.getAttribute('data-topic'));
      render(false);
      restoreFocus(pill);
      scrollToResults();
    } else if (arrow) {
      var track = filters.querySelector('.client-carousel__track');
      var card = track.querySelector('.client-card');
      var gap = parseFloat(getComputedStyle(track).columnGap) || 0;
      track.scrollBy({ left: +arrow.getAttribute('data-step') * (card.offsetWidth + gap), behavior: behaviour() });
    }
  });

  /* ---------- Cards ---------- */

  function cardTags(e) {
    var t = [];
    if (e.caseStudy) t.push('<button type="button" class="card-tags__tag" data-client="' + e.caseStudy + '">' + esc(csBySlug[e.caseStudy].client) + '</button>');
    (e.services || []).forEach(function (s) { t.push('<button type="button" class="card-tags__tag" data-topic="service:' + s + '">' + D.services[s] + '</button>'); });
    (e.tags || []).forEach(function (s) { t.push('<button type="button" class="card-tags__tag" data-topic="tag:' + s + '">' + D.tags[s] + '</button>'); });
    return t.length ? '<div class="card-tags">' + t.join('') + '</div>' : '';
  }

  var cardCtx = {
    image: function (e) {
      var i = D.entries.indexOf(e);
      var pal = e.caseStudy ? csBySlug[e.caseStudy].palette : GENERIC;
      return image('e' + i, pal, i + 20, 800, Math.round(800 / (e.ratio || 1.5)));
    },
    tags: cardTags,
    date: function (e) { return dateLabel(e.daysAgo); }
  };

  grid.addEventListener('click', function (ev) {
    var tag = ev.target.closest('.card-tags__tag');
    if (!tag) return;
    if (tag.hasAttribute('data-client')) choose('client', tag.getAttribute('data-client'));
    if (tag.hasAttribute('data-topic')) choose('topic', tag.getAttribute('data-topic'));
    render(true);
    // The tag itself is gone; move focus to the matching filter control.
    restoreFocus(tag);
    scrollToResults();
  });

  // Row spans depend on card heights, which change with width.
  if (window.ResizeObserver) new ResizeObserver(function () { Cards.layout(grid); }).observe(grid);

  function renderGrid() {
    var list = entriesFor(state.client, state.topic);
    var shown = list.slice(0, LIMIT);
    countEl.textContent = entryWord(list.length);
    grid.innerHTML = list.length
      ? Cards.masonry(shown.map(function (e) { return Cards.render(e, cardCtx); }))
      : '<p class="feed-empty">No entries match these filters yet.</p>';
    Cards.layout(grid);

    // "See all" names the active filter, like the live homepage.
    var name = state.client !== '*' ? csBySlug[state.client].client
      : state.topic !== '*' ? topicLabel(state.topic) : '';
    more.innerHTML =
      (list.length > shown.length ? '<span class="feed-more__count">Showing ' + shown.length + ' of ' + list.length + '</span>' : '') +
      '<a href="#our-work" class="feed-more__btn">See all ' + (name ? esc(name) + ' ' : '') + 'work <span aria-hidden="true">&rarr;</span></a>';
  }

  /* ---------- Pinned filter bar and site menu ----------
     Pinned to the top once the full filter controls have scrolled away,
     while the feed is on screen. Clients and topics share one row. A menu
     button at its left opens Back to top, Our work, Our people and Get in
     touch; the bar also shows (menu only) on scroll-up outside the feed. */

  var MENU_LINKS = [
    ['top', 'Back to top'], ['#our-work', 'Our work'], ['#our-people', 'Our people'], ['#contact', 'Get in touch']
  ];
  var bar = document.getElementById('filter-bar');
  var barShown = false;
  var menuOpen = false;

  function barHTML() {
    var clients = [null].concat(D.caseStudies).map(function (cs, i) {
      var slug = cs ? cs.slug : '*';
      return '<button type="button" class="bar-chip bar-chip--client" data-client="' + slug + '" aria-pressed="' + (state.client === slug) + '">' +
        (cs ? '<img class="bar-chip__thumb" src="' + image('c' + i, cs.palette, i + 2) + '" alt="">' : '') +
        esc(cs ? cs.client : 'All work') + ' <span class="bar-chip__count">' + entriesFor(slug, '*').length + '</span></button>';
    }).join('');
    var topics = TOPICS.map(function (t) {
      return '<button type="button" class="bar-chip" data-topic="' + t + '" aria-pressed="' + (state.topic === t) + '">' + esc(topicLabel(t)) + '</button>';
    }).join('');
    var menu = '<div class="filter-bar__menu" id="filter-bar-menu"' + (menuOpen ? '' : ' hidden') + '><ul role="list">' +
      MENU_LINKS.map(function (l) {
        return '<li>' + (l[0] === 'top'
          ? '<button type="button" data-top>' + l[1] + ' <span aria-hidden="true">&uarr;</span></button>'
          : '<a href="' + l[0] + '">' + l[1] + ' <span aria-hidden="true">&rarr;</span></a>') + '</li>';
      }).join('') +
    '</ul></div>';

    return '<div class="filter-bar__inner">' +
      '<button type="button" class="filter-bar__menu-btn" data-menu aria-label="Menu" aria-expanded="' + menuOpen + '" aria-controls="filter-bar-menu">' +
        '<span class="filter-bar__menu-icon" aria-hidden="true"></span></button>' +
      '<div class="filter-bar__track">' +
        '<button type="button" class="filter-bar__arrow filter-bar__arrow--prev" data-bar-step="-1" aria-label="Scroll filters left" tabindex="-1">&larr;</button>' +
        '<div class="filter-bar__scroll">' +
          '<div class="filter-bar__group" role="group" aria-label="Clients">' + clients + '</div>' +
          '<span class="filter-bar__divider" aria-hidden="true"></span>' +
          '<div class="filter-bar__group" role="group" aria-label="Topics">' + topics + '</div>' +
        '</div>' +
        '<button type="button" class="filter-bar__arrow filter-bar__arrow--next" data-bar-step="1" aria-label="Scroll filters right" tabindex="-1">&rarr;</button>' +
      '</div>' +
      '<span class="filter-bar__count">' + entryWord(entriesFor(state.client, state.topic).length) + '</span>' +
    '</div>' + menu;
  }

  function renderBar() {
    var scroller = bar.querySelector('.filter-bar__scroll');
    var left = scroller ? scroller.scrollLeft : 0;
    bar.innerHTML = barHTML();
    scroller = bar.querySelector('.filter-bar__scroll');
    scroller.scrollLeft = left;
    // Keep the active chip clear of the faded edges.
    var active = bar.querySelector('.bar-chip[aria-pressed="true"]:not([data-client="*"])');
    if (active) {
      var pad = 96;
      var l = active.offsetLeft - scroller.offsetLeft, r = l + active.offsetWidth;
      if (l < scroller.scrollLeft + pad || r > scroller.scrollLeft + scroller.clientWidth - pad) {
        scroller.scrollLeft = l - (scroller.clientWidth - active.offsetWidth) / 2;
      }
    }
    scroller.addEventListener('scroll', syncBarEdges, { passive: true });
    syncBarEdges();
  }

  // Fades and arrows show only on a side with more to scroll.
  function syncBarEdges() {
    var sc = bar.querySelector('.filter-bar__scroll');
    var track = bar.querySelector('.filter-bar__track');
    if (!sc) return;
    track.classList.toggle('has-left', sc.scrollLeft > 2);
    track.classList.toggle('has-right', sc.scrollLeft < sc.scrollWidth - sc.clientWidth - 2);
  }
  window.addEventListener('resize', syncBarEdges);

  // Bottom of the filter controls (the case study panel doesn't count).
  function controlsBottom() {
    var pills = filters.querySelector('.topic-pills');
    return pills ? pills.getBoundingClientRect().bottom : 0;
  }

  // Scroll direction, with thresholds so small movements don't flicker the bar.
  var lastY = window.scrollY, travel = 0, scrollingUp = false;
  var hero = document.querySelector('.hx');

  function updateBar() {
    var y = window.scrollY;
    var dy = y - lastY;
    lastY = y;
    if ((dy < 0) !== (travel < 0)) travel = 0;
    travel += dy;
    if (travel < -40) scrollingUp = true;
    if (travel > 12) { scrollingUp = false; if (menuOpen) toggleMenu(false); }

    var chipsH = bar.querySelector('.filter-bar__inner').offsetHeight || 56;
    var inFeed = controlsBottom() < chipsH && grid.getBoundingClientRect().bottom > chipsH + 80;
    var pastHero = hero.getBoundingClientRect().bottom < 0;
    var show = inFeed || (scrollingUp && pastHero) || menuOpen;

    bar.classList.toggle('show-chips', inFeed);
    if (show !== barShown) {
      barShown = show;
      bar.classList.toggle('is-visible', show);
      bar.inert = !show;
      syncBarEdges();
    }
  }

  function toggleMenu(open) {
    menuOpen = open;
    bar.querySelector('.filter-bar__menu').hidden = !open;
    bar.querySelector('[data-menu]').setAttribute('aria-expanded', String(open));
  }

  document.addEventListener('click', function (ev) {
    if (menuOpen && !bar.contains(ev.target)) { toggleMenu(false); updateBar(); }
  });
  document.addEventListener('keydown', function (ev) {
    if (ev.key === 'Escape' && menuOpen) { toggleMenu(false); bar.querySelector('[data-menu]').focus(); }
  });

  var barTick = false;
  window.addEventListener('scroll', function () {
    if (barTick) return;
    barTick = true;
    requestAnimationFrame(function () { barTick = false; updateBar(); });
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
      var sc = bar.querySelector('.filter-bar__scroll');
      sc.scrollBy({ left: +arrow.getAttribute('data-bar-step') * sc.clientWidth * 0.6, behavior: behaviour() });
      return;
    }
    var chip = ev.target.closest('.bar-chip');
    if (!chip) return;
    if (chip.hasAttribute('data-client')) choose('client', chip.getAttribute('data-client'));
    else choose('topic', chip.getAttribute('data-topic'));
    render(true);
    restoreFocus(chip);
    scrollToResults();
  });

  /* ---------- Render ---------- */

  // Filters re-render, which would drop keyboard focus to the page. Put it
  // back on the same control, without scrolling (the page scroll handles that).
  function restoreFocus(origin) {
    if (!origin) return;
    var sel = origin.hasAttribute('data-client')
      ? '[data-client="' + origin.getAttribute('data-client') + '"]'
      : '[data-topic="' + origin.getAttribute('data-topic') + '"]';
    // Bar chips refocus in the bar; carousel, pills and card tags in the filters.
    var scope = origin.closest('#filter-bar') ? bar : filters;
    var el = scope.querySelector(sel);
    if (el) el.focus({ preventScroll: true });
  }

  function render(focusSelected) {
    var cs = csBySlug[state.client];
    filters.innerHTML = carouselHTML() + pillsHTML() + (cs ? panelHTML(cs) : '');
    bindCarousel(focusSelected);
    renderGrid();
    renderBar();
    updateBar();
  }

  render(false);
})();
