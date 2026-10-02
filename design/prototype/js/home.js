/* Homepage feed: client carousel filter, topic pills, case study panel and card grid. */
(function () {
  var D = window.WOVE;
  var csBySlug = {};
  D.caseStudies.forEach(function (cs) { csBySlug[cs.slug] = cs; });

  var state = { client: '*', topic: '*' };

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

  /* ---------- Client carousel ---------- */

  var track = document.getElementById('client-track');
  var slider = document.getElementById('client-slider');
  var prev = document.getElementById('client-prev');
  var next = document.getElementById('client-next');

  function clientCard(cs, i) {
    var count = cs ? D.entries.filter(function (e) { return e.caseStudy === cs.slug; }).length : D.entries.length;
    var slug = cs ? cs.slug : '*';
    var img = cs ? placeholder(cs.palette, i + 3) : null;
    return '<li class="client-card' + (cs ? '' : ' client-card--all') + '">' +
      '<button type="button" class="client-card__btn" data-client="' + slug + '" aria-pressed="' + (state.client === slug) + '">' +
        (img ? '<img class="client-card__img" src="' + img + '" alt="">' : '') +
        '<span class="client-card__logo">' + esc(cs ? cs.logo : 'All work') + '</span>' +
        '<span class="client-card__meta">' + (cs ? esc(cs.client) + ' · ' : '') + entryWord(count) + '</span>' +
      '</button>' +
    '</li>';
  }

  track.innerHTML = clientCard(null, 0) + D.caseStudies.map(clientCard).join('');

  track.addEventListener('click', function (ev) {
    var btn = ev.target.closest('.client-card__btn');
    if (!btn) return;
    var slug = btn.getAttribute('data-client');
    state.client = state.client === slug && slug !== '*' ? '*' : slug;
    render();
  });

  function maxScroll() { return track.scrollWidth - track.clientWidth; }

  function syncSlider() {
    var max = maxScroll();
    slider.value = max > 0 ? Math.round(track.scrollLeft / max * 1000) : 0;
    slider.style.setProperty('--fill', (slider.value / 10) + '%');
    prev.disabled = track.scrollLeft <= 2;
    next.disabled = track.scrollLeft >= max - 2;
  }

  function step(dir) {
    var card = track.querySelector('.client-card');
    var gap = parseFloat(getComputedStyle(track).columnGap) || 0;
    track.scrollBy({ left: dir * (card.offsetWidth + gap), behavior: 'smooth' });
  }

  track.addEventListener('scroll', syncSlider, { passive: true });
  window.addEventListener('resize', syncSlider);
  slider.addEventListener('input', function () {
    track.scrollLeft = slider.value / 1000 * maxScroll();
  });
  prev.addEventListener('click', function () { step(-1); });
  next.addEventListener('click', function () { step(1); });

  /* ---------- Topic pills ---------- */

  var pillWrap = document.getElementById('topic-pills');
  var topics = [['*', 'All topics']]
    .concat(Object.keys(D.services).map(function (k) { return ['service:' + k, D.services[k]]; }))
    .concat(Object.keys(D.tags).map(function (k) { return ['tag:' + k, D.tags[k]]; }));

  pillWrap.innerHTML = topics.map(function (t) {
    return '<button type="button" class="tag-pill" data-topic="' + t[0] + '">' + esc(t[1]) + '</button>';
  }).join('');

  pillWrap.addEventListener('click', function (ev) {
    var btn = ev.target.closest('.tag-pill');
    if (!btn || btn.disabled) return;
    state.topic = btn.getAttribute('data-topic');
    render();
  });

  /* ---------- Case study panel ---------- */

  var panel = document.getElementById('case-panel');

  function renderPanel() {
    var cs = csBySlug[state.client];
    if (!cs) { panel.hidden = true; panel.innerHTML = ''; return; }
    panel.hidden = false;
    panel.innerHTML =
      '<div class="case-panel__media"><img src="' + placeholder(cs.palette, 11, 900, 640) + '" alt="">' +
        '<span class="case-panel__logo">' + esc(cs.logo) + '</span></div>' +
      '<div class="case-panel__body">' +
        '<p class="case-panel__eyebrow">Case study · ' + esc(cs.client) + '</p>' +
        '<h3 class="case-panel__title">' + esc(cs.title) + '</h3>' +
        '<p class="case-panel__text">' + esc(cs.text) + '</p>' +
        '<dl class="case-panel__kpis">' + cs.kpis.map(function (k) {
          return '<div class="case-panel__kpi"><dt>' + esc(k.label) + '</dt><dd>' + esc(k.value) + '</dd></div>';
        }).join('') + '</dl>' +
        '<a href="#" class="case-panel__cta">See case study <span aria-hidden="true">&rarr;</span></a>' +
      '</div>';
  }

  /* ---------- Cards ---------- */

  var grid = document.getElementById('feed-grid');
  var countEl = document.getElementById('feed-count');
  var formatLabels = { whatif: 'What If', longread: 'Long Read' };

  function cardTags(e) {
    var t = [];
    if (e.caseStudy) t.push('<button type="button" class="card-tags__tag" data-client="' + e.caseStudy + '">' + esc(csBySlug[e.caseStudy].client) + '</button>');
    (e.services || []).forEach(function (s) { t.push('<button type="button" class="card-tags__tag" data-topic="service:' + s + '">' + D.services[s] + '</button>'); });
    (e.tags || []).forEach(function (s) { t.push('<button type="button" class="card-tags__tag" data-topic="tag:' + s + '">' + D.tags[s] + '</button>'); });
    return t.length ? '<div class="card-tags">' + t.join('') + '</div>' : '';
  }

  function card(e, i) {
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
      return '<article class="feed-card"><div class="feed-card__media"><img src="' + placeholder(pal, i + 20) + '" alt=""></div>' +
        '<div class="feed-card__body">' + body + '</div></article>';
    }
    return '<article class="feed-card--compact">' + body + '</article>';
  }

  grid.addEventListener('click', function (ev) {
    var tag = ev.target.closest('.card-tags__tag');
    if (!tag) return;
    if (tag.hasAttribute('data-client')) state.client = tag.getAttribute('data-client');
    if (tag.hasAttribute('data-topic')) state.topic = tag.getAttribute('data-topic');
    render();
    document.getElementById('feed').scrollIntoView({ behavior: 'smooth', block: 'start' });
  });

  /* ---------- Render ---------- */

  function render() {
    // Carousel state
    track.querySelectorAll('.client-card__btn').forEach(function (b) {
      b.setAttribute('aria-pressed', String(b.getAttribute('data-client') === state.client));
    });
    var active = track.querySelector('[aria-pressed="true"]');
    if (active) {
      var li = active.parentElement;
      if (li.offsetLeft < track.scrollLeft || li.offsetLeft + li.offsetWidth > track.scrollLeft + track.clientWidth) {
        track.scrollTo({ left: li.offsetLeft - track.offsetLeft, behavior: 'smooth' });
      }
    }

    // Pills: disable topics with no entries for the selected client
    pillWrap.querySelectorAll('.tag-pill').forEach(function (p) {
      var t = p.getAttribute('data-topic');
      p.classList.toggle('is-active', t === state.topic);
      p.disabled = t !== '*' && entriesFor(state.client, t).length === 0;
    });

    renderPanel();

    var list = entriesFor(state.client, state.topic);
    countEl.textContent = entryWord(list.length);
    grid.innerHTML = list.length
      ? list.map(card).join('')
      : '<p class="feed-empty">No entries match these filters yet.</p>';
  }

  render();
  syncSlider();
})();
