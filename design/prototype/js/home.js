/* Homepage feed: client carousel filter, topic filters, case study panel and card grid.
   Layouts for the filter area, switched with the bar at the bottom of the page
   (or #a, #b, #d in the URL; C, a single-block layout, was dropped):
   A  Current:     carousel, then a flat row of topic pills.
   B  Linked:      each filter shows counts for the other; a summary of active filters.
   D  Client first: the panel under the carousel always describes the selection and
                    holds the topic filter for it.
   The switcher also toggles between the 20-entry sample and a large one. */
(function () {
  var D = window.WOVE;
  var csBySlug = {};
  D.caseStudies.forEach(function (cs) { csBySlug[cs.slug] = cs; });

  var VARIANTS = {
    a: { name: 'Current', note: 'Carousel, then a separate row of topic pills.' },
    b: { name: 'Counts', note: 'Grouped topics with post counts, and a summary of the active filter.' },
    d: { name: 'Client first', note: 'The panel under the carousel describes the selection and holds the topics.' }
  };

  var state = { client: '*', topic: '*', variant: 'a', large: false, shown: 0, cards: '1' };
  var Cards = window.WoveCards;

  // One active filter at a time, across clients and topics: choosing one
  // clears the other. Choosing the active one again clears it.
  function choose(kind, value) {
    if (kind === 'client') {
      state.client = state.client === value ? '*' : value;
      state.topic = '*';
    } else {
      state.topic = state.topic === value ? '*' : value;
      state.client = '*';
    }
  }
  var LIMIT = 12; // homepage shows a fixed number of cards; the rest live on Our Work
  D.entries = D.entriesSmall;

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
  function cachedPlaceholder(key, palette, seed, w, h) {
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

  var TOPIC_GROUPS = [
    { label: 'Services', items: Object.keys(D.services).map(function (k) { return 'service:' + k; }) },
    { label: 'Topics', items: Object.keys(D.tags).map(function (k) { return 'tag:' + k; }) }
  ];

  /* ---------- Components ---------- */

  // Client carousel.
  function carouselHTML(opts) {
    opts = opts || {};
    var cards = [null].concat(D.caseStudies).map(function (cs, i) {
      var slug = cs ? cs.slug : '*';
      var n = entriesFor(slug, '*').length;
      var meta = (cs ? esc(cs.client) + ' · ' : '') + entryWord(n);
      return '<li class="client-card' + (cs ? '' : ' client-card--all') + '">' +
        '<button type="button" class="client-card__btn" data-client="' + slug + '" aria-pressed="' + (state.client === slug) + '">' +
          (cs ? '<img class="client-card__img" src="' + cachedPlaceholder('c' + i, cs.palette, i + 2) + '" alt="">' : '') +
          '<span class="client-card__logo">' + esc(cs ? cs.logo : 'All work') + '</span>' +
          '<span class="client-card__meta">' + meta + '</span>' +
        '</button>' +
      '</li>';
    }).join('');

    var arrows = '<div class="client-carousel__arrows">' +
      '<button type="button" class="client-carousel__arrow" data-step="-1" aria-label="Previous clients">&larr;</button>' +
      '<button type="button" class="client-carousel__arrow" data-step="1" aria-label="Next clients">&rarr;</button>' +
    '</div>';

    return '<section class="client-carousel' + (opts.compact ? ' client-carousel--compact' : '') + '" aria-label="Filter by client">' +
      (opts.head ? '<div class="client-carousel__head"><h3 class="filter-label">' + opts.head + '</h3>' + arrows + '</div>' : '') +
      '<ul class="client-carousel__track" role="list">' + cards + '</ul>' +
      '<div class="client-carousel__controls">' +
        '<input type="range" class="client-carousel__slider" min="0" max="1000" value="0" aria-label="Scroll clients">' +
        (opts.head ? '' : arrows) +
      '</div>' +
    '</section>';
  }

  // Topic pills. Options: grouped (label per group), counts, allLabel.
  function pillsHTML(opts) {
    opts = opts || {};
    function pill(t, label) {
      var n = entriesFor('*', t).length;
      // "All" is active only when no client or topic is selected.
      var on = t === '*' ? state.topic === '*' && state.client === '*' : state.topic === t;
      return '<button type="button" class="tag-pill' + (on ? ' is-active' : '') + '" data-topic="' + t + '" aria-pressed="' + on + '"' +
        '>' + esc(label) +
        (opts.counts ? ' <span class="tag-pill__count">' + n + '</span>' : '') + '</button>';
    }
    if (!opts.grouped) {
      var all = pill('*', opts.allLabel || 'All');
      var flat = TOPIC_GROUPS.reduce(function (acc, g) { return acc.concat(g.items); }, []);
      return '<div class="topic-pills' + (opts.cls ? ' ' + opts.cls : '') + '" aria-label="Filter by topic">' + all +
        flat.map(function (t) { return pill(t, topicLabel(t)); }).join('') + '</div>';
    }
    return '<div class="topic-groups' + (opts.cls ? ' ' + opts.cls : '') + '">' +
      TOPIC_GROUPS.map(function (g) {
        var pills = g.items.map(function (t) { return pill(t, topicLabel(t)); }).join('');
        if (!pills) return '';
        return '<div class="topic-group"><h3 class="filter-label">' + g.label + '</h3>' +
          '<div class="topic-group__pills">' + pills + '</div></div>';
      }).join('') +
    '</div>';
  }

  function kpisHTML(cs) {
    return '<dl class="case-panel__kpis">' + cs.kpis.map(function (k) {
      return '<div class="case-panel__kpi"><dt>' + esc(k.label) + '</dt><dd>' + esc(k.value) + '</dd></div>';
    }).join('') + '</dl>';
  }

  // Case study panel. `extra` is appended to the body (used by D for its topic filter).
  function panelHTML(cs, opts) {
    opts = opts || {};
    return '<section class="case-panel' + (opts.cls ? ' ' + opts.cls : '') + '" aria-live="polite">' +
      '<div class="case-panel__media"><img src="' + cachedPlaceholder('p' + cs.slug, cs.palette, 11, 900, 640) + '" alt="">' +
        '<span class="case-panel__logo">' + esc(cs.logo) + '</span></div>' +
      '<div class="case-panel__body">' +
        '<p class="case-panel__eyebrow">Case study · ' + esc(cs.client) + '</p>' +
        '<h3 class="case-panel__title">' + esc(cs.title) + '</h3>' +
        '<p class="case-panel__text">' + esc(cs.text) + '</p>' +
        kpisHTML(cs) +
        '<a href="#" class="case-panel__cta">See case study <span aria-hidden="true">&rarr;</span></a>' +
        (opts.extra || '') +
      '</div>' +
    '</section>';
  }

  // Summary of active filters with remove buttons (B).
  function summaryHTML() {
    var n = entriesFor(state.client, state.topic).length;
    var chips = [];
    if (state.client !== '*') chips.push('<button type="button" class="filter-chip" data-clear="client">' + esc(csBySlug[state.client].client) + ' <span aria-hidden="true">&times;</span><span class="visually-hidden">Remove</span></button>');
    if (state.topic !== '*') chips.push('<button type="button" class="filter-chip" data-clear="topic">' + esc(topicLabel(state.topic)) + ' <span aria-hidden="true">&times;</span><span class="visually-hidden">Remove</span></button>');
    return '<div class="filter-summary" aria-live="polite">' +
      '<span class="filter-summary__count">Showing ' + entryWord(n) + '</span>' +
      (chips.length ? chips.join('') + '<button type="button" class="filter-summary__clear" data-clear="all">Clear all</button>'
                    : '<span class="filter-summary__hint">from all clients and topics</span>') +
    '</div>';
  }

  /* ---------- Variant layouts ---------- */

  var layouts = {
    a: function () {
      var cs = csBySlug[state.client];
      return carouselHTML() + pillsHTML({ cls: 'topic-pills--ruled' }) + (cs ? panelHTML(cs) : '');
    },

    b: function () {
      var cs = csBySlug[state.client];
      return carouselHTML() +
        pillsHTML({ grouped: true, counts: true }) +
        summaryHTML() +
        (cs ? panelHTML(cs) : '');
    },

    d: function () {
      var cs = csBySlug[state.client];
      var heading = 'Explore by topic';
      var topics = '<div class="context-topics"><h3 class="filter-label">' + heading + '</h3>' +
        pillsHTML({ counts: true, allLabel: 'Everything', cls: 'topic-pills--tight' }) + '</div>';
      var context = cs
        ? panelHTML(cs, { extra: topics })
        : '<section class="case-panel case-panel--intro" aria-live="polite">' +
            '<div class="case-panel__body">' +
              '<p class="case-panel__eyebrow">All work</p>' +
              '<h3 class="case-panel__title">Strategic design</h3>' +
              '<p class="case-panel__text">We use strategic design to help organisations move from insight to delivery, across services, systems and culture. Choose a client above to see their case study and the posts behind it.</p>' +
              topics +
            '</div>' +
          '</section>';
      return carouselHTML() + context;
    }
  };

  /* ---------- Mount ---------- */

  var filters = document.getElementById('filters');
  function behaviour() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth';
  }
  var grid = document.getElementById('feed-grid');
  var countEl = document.getElementById('feed-count');
  var savedScroll = 0;

  function bindCarousel(focusSelected) {
    var track = filters.querySelector('.client-carousel__track');
    var slider = filters.querySelector('.client-carousel__slider');
    if (!track) return;
    track.scrollLeft = savedScroll;

    function maxScroll() { return track.scrollWidth - track.clientWidth; }
    function sync() {
      savedScroll = track.scrollLeft;
      var max = maxScroll();
      slider.value = max > 0 ? Math.round(track.scrollLeft / max * 1000) : 0;
      // The bar marks the part of the client list currently in view.
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
      if (active) {
        var li = active.parentElement;
        if (li.offsetLeft < track.scrollLeft || li.offsetLeft + li.offsetWidth > track.scrollLeft + track.clientWidth) {
          // The page scrolls down to the results straight after, and two smooth
          // scrolls at once can cancel each other (Safari), so this one jumps.
          track.scrollTo({ left: li.offsetLeft - track.offsetLeft, behavior: 'auto' });
        }
      }
    }
  }

  window.addEventListener('resize', function () { bindCarousel(false); });

  // After a filter change, scroll to the top of the filtered results (case
  // study panel, or the first cards), with the condensed filter bar pinned
  // above. Same on every screen size.
  var feedTop = document.getElementById('feed');
  function scrollToFeedTop() { scrollToResults(); }
  function scrollToResults() {
    // Wait a frame so the re-rendered filters and cards have laid out.
    requestAnimationFrame(function () {
      var target = filters.querySelector('.case-panel:not(.case-panel--intro)') || grid;
      var y = target.getBoundingClientRect().top + window.scrollY - bar.offsetHeight - 24;
      window.scrollTo({ top: Math.max(0, y), behavior: behaviour() });
    });
  }

  filters.addEventListener('click', function (ev) {
    var t = ev.target;
    var client = t.closest('.client-card__btn');
    var pill = t.closest('.tag-pill');
    var clear = t.closest('[data-clear]');
    var arrow = t.closest('.client-carousel__arrow');

    if (client) {
      choose('client', client.getAttribute('data-client'));
      render(true);
      scrollToFeedTop();
    } else if (pill) {
      choose('topic', pill.getAttribute('data-topic'));
      render(false);
      scrollToFeedTop();
    } else if (clear) {
      var what = clear.getAttribute('data-clear');
      if (what !== 'topic') state.client = '*';
      if (what !== 'client') state.topic = '*';
      render(true);
      scrollToFeedTop();
    } else if (arrow) {
      var track = filters.querySelector('.client-carousel__track');
      var card = track.querySelector('.client-card');
      var gap = parseFloat(getComputedStyle(track).columnGap) || 0;
      track.scrollBy({ left: +arrow.getAttribute('data-step') * (card.offsetWidth + gap), behavior: 'smooth' });
    }
  });

  /* ---------- Cards ---------- */

  var formatLabels = { whatif: 'What If', longread: 'Long Read' };

  function cardTags(e) {
    var t = [];
    if (e.caseStudy) t.push('<button type="button" class="card-tags__tag" data-client="' + e.caseStudy + '">' + esc(csBySlug[e.caseStudy].client) + '</button>');
    (e.services || []).forEach(function (s) { t.push('<button type="button" class="card-tags__tag" data-topic="service:' + s + '">' + D.services[s] + '</button>'); });
    (e.tags || []).forEach(function (s) { t.push('<button type="button" class="card-tags__tag" data-topic="tag:' + s + '">' + D.tags[s] + '</button>'); });
    return t.length ? '<div class="card-tags">' + t.join('') + '</div>' : '';
  }

  function card(e) {
    var i = D.entries.indexOf(e);
    var meta = '<div class="feed-card__meta">' +
      (e.author ? '<span class="feed-card__author">' + esc(e.author) + '</span><span class="feed-card__dot">&middot;</span>' : '') +
      '<time>' + dateLabel(e.daysAgo) + '</time></div>';
    var format = formatLabels[e.format] ? '<span class="feed-card__format feed-card__format--' + e.format + '">' + formatLabels[e.format] + '</span>' : '';

    if (e.format === 'spark') {
      return '<article class="feed-card--spark"><p class="feed-card__quote">' + esc(e.quote) + '</p>' + meta + cardTags(e) + '</article>';
    }
    var body = format + '<h3 class="feed-card__title">' + esc(e.title) + '</h3>' +
      (e.excerpt && e.format !== 'thread' ? '<p class="feed-card__excerpt">' + esc(e.excerpt) + '</p>' : '') + meta + cardTags(e);
    if (e.image) {
      var pal = e.caseStudy ? csBySlug[e.caseStudy].palette : GENERIC;
      return '<article class="feed-card"><div class="feed-card__media"><img src="' + cachedPlaceholder('e' + i, pal, i + 20) + '" alt=""></div>' +
        '<div class="feed-card__body">' + body + '</div></article>';
    }
    return '<article class="feed-card--compact">' + body + '</article>';
  }

  grid.addEventListener('click', function (ev) {
    var tag = ev.target.closest('.card-tags__tag');
    if (!tag) return;
    if (tag.hasAttribute('data-client')) choose('client', tag.getAttribute('data-client'));
    if (tag.hasAttribute('data-topic')) choose('topic', tag.getAttribute('data-topic'));
    render(true);
    scrollToFeedTop();
  });

  /* ---------- Variant switcher ---------- */

  var switcher = document.getElementById('variant-switcher');

  var Hero = window.WoveHero;
  var lastChanged = 'feed';

  function renderSwitcher() {
    var v = VARIANTS[state.variant];
    var h = Hero.variants[Hero.current];
    var note = lastChanged === 'hero' ? h : lastChanged === 'cards' ? Cards.styles[state.cards] : v;
    switcher.innerHTML =
      '<span class="variant-switcher__label">Hero</span>' +
      '<div class="variant-switcher__buttons" role="group" aria-label="Hero layout">' +
        Object.keys(Hero.variants).map(function (k) {
          return '<button type="button" data-hero="' + k + '" aria-pressed="' + (k === Hero.current) + '">' +
            k + '<span class="visually-hidden"> ' + Hero.variants[k].name + '</span></button>';
        }).join('') +
      '</div>' +
      '<span class="variant-switcher__label">Cards</span>' +
      '<div class="variant-switcher__buttons" role="group" aria-label="Card style">' +
        Object.keys(Cards.styles).map(function (k) {
          return '<button type="button" data-cards="' + k + '" aria-pressed="' + (k === state.cards) + '">' +
            k + '<span class="visually-hidden"> ' + Cards.styles[k].name + '</span></button>';
        }).join('') +
      '</div>' +
      '<span class="variant-switcher__label">Feed</span>' +
      '<div class="variant-switcher__buttons" role="group" aria-label="Filter layout">' +
        Object.keys(VARIANTS).map(function (k) {
          return '<button type="button" data-variant="' + k + '" aria-pressed="' + (k === state.variant) + '">' +
            k.toUpperCase() + '<span class="visually-hidden"> ' + VARIANTS[k].name + '</span></button>';
        }).join('') +
      '</div>' +
      '<p class="variant-switcher__note" title="' + note.note + '"><strong>' + note.name + '</strong></p>' +
      '<button type="button" class="variant-switcher__volume" data-volume aria-pressed="' + state.large + '">' +
        (state.large ? D.entriesLarge.length + ' posts' : '20 posts') + '</button>' +
      '<button type="button" class="variant-switcher__volume" data-reveal-toggle aria-pressed="' + revealOn() + '">' +
        'Reveal ' + (revealOn() ? 'on' : 'off') + '</button>';
  }

  function revealOn() { return !document.documentElement.hasAttribute('data-reveal-off'); }

  switcher.addEventListener('click', function (ev) {
    if (ev.target.closest('[data-reveal-toggle]')) {
      document.documentElement.toggleAttribute('data-reveal-off');
      window.dispatchEvent(new Event('scroll'));
      renderSwitcher();
      return;
    }
    if (ev.target.closest('[data-volume]')) {
      state.large = !state.large;
      D.entries = state.large ? D.entriesLarge : D.entriesSmall;
      Hero.refresh();
      renderSwitcher();
      render(false);
      return;
    }
    var cb = ev.target.closest('[data-cards]');
    if (cb) {
      lastChanged = 'cards';
      state.cards = cb.getAttribute('data-cards');
      renderGrid();
      renderSwitcher();
      saveHash();
      return;
    }
    var hb = ev.target.closest('[data-hero]');
    if (hb) {
      lastChanged = 'hero';
      Hero.set(hb.getAttribute('data-hero'));
      renderSwitcher();
      saveHash();
      window.dispatchEvent(new Event('scroll'));
      return;
    }
    var b = ev.target.closest('[data-variant]');
    if (!b) return;
    lastChanged = 'feed';
    setVariant(b.getAttribute('data-variant'));
    saveHash();
  });

  // URL hash holds the choices: feed letter, hero digit, card digit (e.g. #b23).
  function saveHash() {
    try { history.replaceState(null, '', '#' + state.variant + Hero.current + state.cards); } catch (e) { /* sandboxed */ }
  }
  function readHash() {
    var h = location.hash.slice(1);
    if (Hero.variants[h.charAt(1)]) Hero.set(h.charAt(1));
    if (Cards.styles[h.charAt(2)]) state.cards = h.charAt(2);
    return VARIANTS[h.charAt(0)] ? h.charAt(0) : 'a';
  }

  function setVariant(v) {
    if (!VARIANTS[v]) return;
    state.variant = v;
    document.documentElement.setAttribute('data-variant', v);
    renderSwitcher();
    render(true);
  }

  window.addEventListener('hashchange', function () { setVariant(readHash()); });

  /* ---------- Condensed filter bar ----------
     Pinned to the top of the screen once the full filter block has scrolled
     away, while the feed is on screen. Clients and topics share one row. */

  var bar = document.getElementById('filter-bar');
  var barShown = false;

  function barHTML() {
    var clients = [null].concat(D.caseStudies).map(function (cs, i) {
      var slug = cs ? cs.slug : '*';
      var n = entriesFor(slug, '*').length;
      return '<button type="button" class="bar-chip bar-chip--client" data-client="' + slug + '" aria-pressed="' + (state.client === slug) + '">' +
        (cs ? '<img class="bar-chip__thumb" src="' + cachedPlaceholder('c' + i, cs.palette, i + 2) + '" alt="">' : '') +
        esc(cs ? cs.client : 'All work') + ' <span class="bar-chip__count">' + n + '</span></button>';
    }).join('');
    var topics = TOPIC_GROUPS.reduce(function (acc, g) { return acc.concat(g.items); }, [])
      .map(function (t) {
        return '<button type="button" class="bar-chip" data-topic="' + t + '" aria-pressed="' + (state.topic === t) + '">' + esc(topicLabel(t)) + '</button>';
      }).join('');
    return '<div class="filter-bar__inner">' +
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
    '</div>';
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

  // The last filter control in the full block (the case study panel doesn't count).
  function controlsBottom() {
    var controls = [].filter.call(filters.children, function (el) { return !el.classList.contains('case-panel'); });
    var last = controls[controls.length - 1];
    return last ? last.getBoundingClientRect().bottom : 0;
  }

  function updateBar() {
    var show = controlsBottom() < bar.offsetHeight &&
      grid.getBoundingClientRect().bottom > bar.offsetHeight + 80;
    if (show === barShown) return;
    barShown = show;
    bar.classList.toggle('is-visible', show);
    syncBarEdges();
    bar.inert = !show;
  }

  var barTick = false;
  window.addEventListener('scroll', function () {
    if (barTick) return;
    barTick = true;
    requestAnimationFrame(function () { barTick = false; updateBar(); });
  }, { passive: true });

  bar.addEventListener('click', function (ev) {
    var arrow = ev.target.closest('[data-bar-step]');
    if (arrow) {
      var sc = bar.querySelector('.filter-bar__scroll');
      sc.scrollBy({ left: +arrow.getAttribute('data-bar-step') * sc.clientWidth * 0.6, behavior: behaviour() });
      return;
    }
    var chip = ev.target.closest('.bar-chip');
    if (!chip) return;
    if (chip.hasAttribute('data-client')) {
      choose('client', chip.getAttribute('data-client'));
    } else {
      choose('topic', chip.getAttribute('data-topic'));
    }
    render(true);
    scrollToResults();
  });

  /* ---------- Render ---------- */

  var more = document.getElementById('feed-more');
  var lastColumns = 0;

  var cardCtx = {
    image: function (e) {
      var i = D.entries.indexOf(e);
      var pal = e.caseStudy ? csBySlug[e.caseStudy].palette : GENERIC;
      var w = 800, h = Math.round(800 / (e.ratio || 1.5));
      return cachedPlaceholder('e' + i + ':' + (e.ratio || 1.5), pal, i + 20, w, h);
    },
    tags: cardTags,
    date: function (e) { return dateLabel(e.daysAgo); }
  };

  // Masonry columns depend on width.
  window.addEventListener('resize', function () {
    if (state.cards !== '1' && Cards.columnCount() !== lastColumns) renderGrid();
  });

  function render(focusSelected) {
    filters.className = 'filters filters--' + state.variant;
    filters.innerHTML = layouts[state.variant]();
    bindCarousel(focusSelected);
    renderGrid();
    renderBar();
    updateBar();
  }

  function renderGrid() {
    var list = entriesFor(state.client, state.topic);
    var shown = list.slice(0, LIMIT);
    countEl.textContent = entryWord(list.length);
    var styled = state.cards !== '1';
    grid.className = 'feed-cards feed-cards--home' + (styled ? ' pc-grid cards--' + state.cards : '');
    grid.innerHTML = !list.length
      ? '<p class="feed-empty">No entries match these filters yet.</p>'
      : styled ? Cards.masonry(shown.map(function (e) { return Cards.render(e, cardCtx); }))
      : shown.map(card).join('');
    lastColumns = Cards.columnCount();

    // "See all" names the active filter, like the live homepage.
    var name = state.client !== '*' ? csBySlug[state.client].client
      : state.topic !== '*' ? topicLabel(state.topic) : '';
    more.hidden = false;
    more.innerHTML =
      (list.length > shown.length ? '<span class="feed-more__count">Showing ' + shown.length + ' of ' + list.length + '</span>' : '') +
      '<a href="#" class="feed-more__btn">See all ' + (name ? esc(name) + ' ' : '') + 'work <span aria-hidden="true">&rarr;</span></a>';
  }

  Hero.init({
    image: cachedPlaceholder,
    // Hero links into the feed: select (never toggle off) and jump to results.
    filter: function (kind, value) {
      if ((kind === 'client' ? state.client : state.topic) !== value) choose(kind, value);
      render(true);
      scrollToResults();
    }
  });
  setVariant(readHash());
})();
