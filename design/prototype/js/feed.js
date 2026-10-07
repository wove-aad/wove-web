/* Feed pages (homepage and Our work): hero previews, client carousel and
   topic filters, case study panel, post cards, and the pinned filter bar
   with its site menu. One active filter at a time across clients, topics
   and people.
   Options on <body>:
   - data-limit="12": show a fixed number of cards (homepage);
   - data-case-cards: case studies appear as cards in the grid (Our work);
   - data-url-state: read and write the filter in the URL (Our work). */
(function () {
  var D = window.WOVE;
  var Cards = window.WoveCards;
  var U = window.WoveUI;
  var esc = U.esc, image = U.image, entryWord = U.entryWord, behaviour = U.behaviour;
  var csBySlug = U.csBySlug;

  // Options sit on <body>, or on the wrapper in the artifact build.
  var body = document.querySelector('[class*="tpl-"]') || document.body;
  var LIMIT = body.hasAttribute('data-limit') ? +body.getAttribute('data-limit') : Infinity;
  var CASE_CARDS = body.hasAttribute('data-case-cards');
  var URL_STATE = body.hasAttribute('data-url-state');

  var state = { client: '*', topic: '*', author: '*' };

  // Choosing one filter clears the others. Choosing the active one again clears it.
  function choose(kind, value) {
    var next = state[kind] === value ? '*' : value;
    state = { client: '*', topic: '*', author: '*' };
    state[kind] = next;
    if (URL_STATE) {
      var q = next === '*' ? '' : '?' + kind + '=' + encodeURIComponent(next);
      history.replaceState(null, '', location.pathname + q);
    }
  }

  if (URL_STATE) {
    ['client', 'topic', 'author'].forEach(function (k) {
      var v = U.param(k);
      if (v && (k !== 'client' || csBySlug[v]) && (k !== 'author' || U.personBySlug[v])) state[k] = v;
    });
  }

  /* ---------- Helpers ---------- */

  function matchesTopic(services, tags, topic) {
    var p = topic.split(':');
    if (p[0] === 'service') return (services || []).indexOf(p[1]) !== -1;
    return (tags || []).indexOf(p[1]) !== -1;
  }

  function entriesFor(client, topic, author) {
    return D.entries.filter(function (e) {
      if (client !== '*' && e.caseStudy !== client) return false;
      if (author && author !== '*' && (!e.author || U.personBySlug[author].name !== e.author)) return false;
      return topic === '*' || matchesTopic(e.services, e.tags, topic);
    });
  }

  // Grid items: posts, plus case studies on Our work. A selected client's
  // case study is in the panel above, so it isn't repeated as a card.
  function itemsFor() {
    var items = entriesFor(state.client, state.topic, state.author);
    if (CASE_CARDS && state.client === '*' && state.author === '*') {
      items = items.concat(D.caseStudies.filter(function (cs) {
        return state.topic === '*' || matchesTopic(cs.services, cs.tags, state.topic);
      }));
      items.sort(function (a, b) { return a.daysAgo - b.daysAgo; });
    }
    return items;
  }

  function topicLabel(t) {
    var p = t.split(':');
    return p[0] === 'service' ? D.services[p[1]] : D.tags[p[1]];
  }

  // Services first, then editorial tags (the site-wide tag order).
  var TOPICS = Object.keys(D.services).map(function (k) { return 'service:' + k; })
    .concat(Object.keys(D.tags).map(function (k) { return 'tag:' + k; }));

  /* ---------- Hero previews ---------- */

  document.querySelectorAll('[data-thumbs]').forEach(function (el) {
    el.innerHTML = D.caseStudies.slice(0, 3).map(function (cs) {
      return '<img class="hx-thumb" src="' + U.caseImage(cs) + '" alt="">';
    }).join('');
  });
  document.querySelectorAll('[data-avatars]').forEach(function (el) {
    el.innerHTML = D.people.slice(0, 4).map(function (p) { return U.avatar(p, 'hx-avatar'); }).join('');
  });

  /* ---------- Filters: client carousel and topic pills ---------- */

  function carouselHTML() {
    var cards = [null].concat(D.caseStudies).map(function (cs) {
      var slug = cs ? cs.slug : '*';
      var meta = (cs ? esc(cs.client) + ' · ' : '') + entryWord(entriesFor(slug, '*').length);
      return '<li class="client-card' + (cs ? '' : ' client-card--all') + '">' +
        '<button type="button" class="client-card__btn" data-client="' + slug + '" aria-pressed="' + (state.client === slug) + '">' +
          (cs ? '<img class="client-card__img" src="' + U.caseImage(cs) + '" alt="">' : '') +
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

  function nothingSelected() { return state.client === '*' && state.topic === '*' && state.author === '*'; }

  function pillsHTML() {
    function pill(t, label) {
      // "All" is active only when nothing is selected.
      var on = t === '*' ? nothingSelected() : state.topic === t;
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
        '<a href="' + U.caseUrl(cs) + '" class="case-panel__cta">See case study <span aria-hidden="true">&rarr;</span></a>' +
      '</div>' +
    '</section>';
  }

  // A person filter (from their profile) shows who they are above the posts.
  function personPanelHTML(p) {
    return '<section class="person-panel" aria-label="Posts by ' + esc(p.name) + '">' +
      U.avatar(p, 'person-panel__avatar') +
      '<div class="person-panel__body">' +
        '<p class="person-panel__eyebrow">Posts by</p>' +
        '<h3 class="person-panel__name">' + esc(p.name) + '</h3>' +
        '<p class="person-panel__role">' + esc(p.role) + '</p>' +
      '</div>' +
      '<div class="person-panel__actions">' +
        '<a class="person-panel__link" href="' + U.personUrl(p) + '">View profile <span aria-hidden="true">&rarr;</span></a>' +
        '<button type="button" class="person-panel__clear" data-author="' + p.slug + '">Clear <span aria-hidden="true">&times;</span></button>' +
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
  // study or person panel, or the first cards), under the pinned bar.
  function scrollToResults() {
    requestAnimationFrame(function () {
      var target = filters.querySelector('.case-panel, .person-panel') || grid;
      var y = target.getBoundingClientRect().top + window.scrollY - bar.offsetHeight - 24;
      window.scrollTo({ top: Math.max(0, y), behavior: behaviour() });
    });
  }

  filters.addEventListener('click', function (ev) {
    var client = ev.target.closest('.client-card__btn');
    var pill = ev.target.closest('.tag-pill');
    var arrow = ev.target.closest('.client-carousel__arrow');
    var clear = ev.target.closest('.person-panel__clear');
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
    } else if (clear) {
      choose('author', clear.getAttribute('data-author'));
      render(false);
      filters.querySelector('[data-topic="*"]').focus({ preventScroll: true });
    } else if (arrow) {
      var track = filters.querySelector('.client-carousel__track');
      var card = track.querySelector('.client-card');
      var gap = parseFloat(getComputedStyle(track).columnGap) || 0;
      track.scrollBy({ left: +arrow.getAttribute('data-step') * (card.offsetWidth + gap), behavior: behaviour() });
    }
  });

  /* ---------- Cards ---------- */

  // Card tags are links to Our work; on a feed page they filter in place.
  grid.addEventListener('click', function (ev) {
    var tag = ev.target.closest('.card-tags__tag');
    if (!tag) return;
    ev.preventDefault();
    if (tag.hasAttribute('data-client')) choose('client', tag.getAttribute('data-client'));
    if (tag.hasAttribute('data-topic')) choose('topic', tag.getAttribute('data-topic'));
    render(true);
    // The tag itself is gone; move focus to the matching filter control.
    restoreFocus(tag);
    scrollToResults();
  });

  // Row spans depend on card heights, which change with width.
  if (window.ResizeObserver) new ResizeObserver(function () { Cards.layout(grid); }).observe(grid);

  function activeName() {
    return state.client !== '*' ? csBySlug[state.client].client
      : state.topic !== '*' ? topicLabel(state.topic)
      : state.author !== '*' ? U.personBySlug[state.author].name : '';
  }

  function renderGrid() {
    var list = itemsFor();
    var shown = list.slice(0, LIMIT);
    countEl.textContent = entryWord(list.length);
    grid.innerHTML = list.length
      ? Cards.masonry(shown.map(function (e) { return Cards.render(e, U.cardCtx); }))
      : '<p class="feed-empty">No entries match these filters yet.</p>';
    Cards.layout(grid);

    if (LIMIT === Infinity) { more.hidden = true; return; }
    // "See all" names the active filter and opens Our work with it.
    var name = activeName();
    var q = state.client !== '*' ? '?client=' + state.client
      : state.topic !== '*' ? '?topic=' + encodeURIComponent(state.topic) : '';
    more.innerHTML =
      (list.length > shown.length ? '<span class="feed-more__count">Showing ' + shown.length + ' of ' + list.length + '</span>' : '') +
      '<a href="work.html' + q + '" class="feed-more__btn">See all ' + (name ? esc(name) + ' ' : '') + 'work <span aria-hidden="true">&rarr;</span></a>';
  }

  /* ---------- Pinned filter bar and site menu ----------
     Pinned to the top once the full filter controls have scrolled away,
     while the feed is on screen. Clients and topics share one row. A menu
     button at its left opens Back to top, Our work, Our people and Get in
     touch; the bar also shows (menu only) on scroll-up outside the feed. */

  var bar = document.getElementById('filter-bar');
  var barShown = false;
  var menuOpen = false;

  function barHTML() {
    var person = U.personBySlug[state.author];
    var clients = [null].concat(D.caseStudies).map(function (cs) {
      var slug = cs ? cs.slug : '*';
      return '<button type="button" class="bar-chip bar-chip--client" data-client="' + slug + '" aria-pressed="' + (slug === '*' ? nothingSelected() : state.client === slug) + '">' +
        (cs ? '<img class="bar-chip__thumb" src="' + U.caseImage(cs) + '" alt="">' : '') +
        esc(cs ? cs.client : 'All work') + ' <span class="bar-chip__count">' + entriesFor(slug, '*').length + '</span></button>';
    }).join('');
    var topics = TOPICS.map(function (t) {
      return '<button type="button" class="bar-chip" data-topic="' + t + '" aria-pressed="' + (state.topic === t) + '">' + esc(topicLabel(t)) + '</button>';
    }).join('');
    var people = person
      ? '<span class="filter-bar__divider" aria-hidden="true"></span><div class="filter-bar__group" role="group" aria-label="People">' +
          '<button type="button" class="bar-chip" data-author="' + person.slug + '" aria-pressed="true">' + esc(person.name) + '</button></div>'
      : '';

    return '<div class="filter-bar__inner">' + U.menuButton(menuOpen) +
      '<div class="filter-bar__track">' +
        '<button type="button" class="filter-bar__arrow filter-bar__arrow--prev" data-bar-step="-1" aria-label="Scroll filters left" tabindex="-1">&larr;</button>' +
        '<div class="filter-bar__scroll">' +
          '<div class="filter-bar__group" role="group" aria-label="Clients">' + clients + '</div>' +
          '<span class="filter-bar__divider" aria-hidden="true"></span>' +
          '<div class="filter-bar__group" role="group" aria-label="Topics">' + topics + '</div>' + people +
        '</div>' +
        '<button type="button" class="filter-bar__arrow filter-bar__arrow--next" data-bar-step="1" aria-label="Scroll filters right" tabindex="-1">&rarr;</button>' +
      '</div>' +
      '<span class="filter-bar__count">' + entryWord(itemsFor().length) + '</span>' +
    '</div>' + U.menuHTML(menuOpen);
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

  // Bottom of the filter controls (the panels don't count).
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
    else if (chip.hasAttribute('data-author')) choose('author', chip.getAttribute('data-author'));
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
      : origin.hasAttribute('data-author') ? '[data-topic="*"]'
      : '[data-topic="' + origin.getAttribute('data-topic') + '"]';
    // Bar chips refocus in the bar; carousel, pills and card tags in the filters.
    var scope = origin.closest('#filter-bar') && !origin.hasAttribute('data-author') ? bar : filters;
    var el = scope.querySelector(sel);
    if (el) el.focus({ preventScroll: true });
  }

  function render(focusSelected) {
    var cs = csBySlug[state.client];
    var person = U.personBySlug[state.author];
    filters.innerHTML = carouselHTML() + pillsHTML() +
      (cs ? panelHTML(cs) : person ? personPanelHTML(person) : '');
    bindCarousel(focusSelected);
    renderGrid();
    renderBar();
    updateBar();
  }

  render(false);
  // Arriving with a filter in the URL: start at the results.
  if (!nothingSelected()) scrollToResults();
})();
