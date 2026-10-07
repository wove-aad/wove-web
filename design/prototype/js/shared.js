/* Helpers shared by every prototype page: placeholder images, labels, card
   context, the page header's current link and the menu-only pinned bar. */
(function () {
  var D = window.WOVE;
  var csBySlug = {};
  D.caseStudies.forEach(function (cs) { csBySlug[cs.slug] = cs; });
  var personBySlug = {};
  D.people.forEach(function (p) { p.slug = slugify(p.name); personBySlug[p.slug] = p; });

  function slugify(s) {
    return String(s).normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
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

  // The featured image of a case study, the same on every page.
  function caseImage(cs, w, h) {
    var i = D.caseStudies.indexOf(cs);
    if (w) return image('cs-' + cs.slug + '-' + w + 'x' + h, cs.palette, i + 3, w, h);
    return image('c' + (i + 1), cs.palette, i + 3);
  }

  function entryImage(e) {
    var i = D.entries.indexOf(e);
    var pal = e.caseStudy ? csBySlug[e.caseStudy].palette : GENERIC;
    return image('e' + i, pal, i + 20, 800, Math.round(800 / (e.ratio || 1.5)));
  }

  /* ---------- Text ---------- */

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

  function fullDate(days) {
    var d = new Date(); d.setDate(d.getDate() - days);
    return d.toLocaleDateString('en-IE', { day: 'numeric', month: 'long', year: 'numeric' });
  }

  function initials(name) {
    return name.split(/\s+/).slice(0, 2).map(function (w) { return w.charAt(0); }).join('');
  }

  var TONES = ['#ed8c7c', '#d5faff', '#e0bdff', '#f7ecd3', '#e1eee7', '#ebf0fe'];
  function tone(person) { return TONES[D.people.indexOf(person) % TONES.length]; }

  function avatar(person, cls) {
    return '<span class="' + (cls || 'avatar') + '" style="--tone:' + tone(person) + '" aria-hidden="true">' + esc(initials(person.name)) + '</span>';
  }

  function entryWord(n) { return n + ' entr' + (n === 1 ? 'y' : 'ies'); }
  function postWord(n) { return n + ' post' + (n === 1 ? '' : 's'); }

  /* ---------- Links ---------- */

  function entryUrl(e) { return 'post.html?e=' + D.entries.indexOf(e); }
  function caseUrl(cs) { return 'case-study.html?cs=' + cs.slug; }
  function personUrl(p) { return 'people.html#' + p.slug; }
  function filterUrl(kind, value) { return 'work.html?' + kind + '=' + encodeURIComponent(value); }

  function param(name) { return new URLSearchParams(location.search).get(name); }

  /* ---------- Cards ---------- */

  // Card tags link to Our Work, filtered. The feed pages filter in place.
  function cardTags(e) {
    var t = [];
    if (e.caseStudy) t.push('<a class="card-tags__tag" data-client="' + e.caseStudy + '" href="' + filterUrl('client', e.caseStudy) + '">' + esc(csBySlug[e.caseStudy].client) + '</a>');
    (e.services || []).forEach(function (s) { t.push('<a class="card-tags__tag" data-topic="service:' + s + '" href="' + filterUrl('topic', 'service:' + s) + '">' + D.services[s] + '</a>'); });
    (e.tags || []).forEach(function (s) { t.push('<a class="card-tags__tag" data-topic="tag:' + s + '" href="' + filterUrl('topic', 'tag:' + s) + '">' + D.tags[s] + '</a>'); });
    return t.length ? '<div class="card-tags">' + t.join('') + '</div>' : '';
  }

  var cardCtx = {
    image: entryImage,
    tags: cardTags,
    date: function (e) { return dateLabel(e.daysAgo); },
    url: entryUrl,
    caseImage: function (cs) { return caseImage(cs); },
    caseUrl: caseUrl
  };

  function behaviour() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth';
  }

  /* ---------- Page header ---------- */

  // Mark the current page in the header nav (bold; the others are muted).
  document.querySelectorAll('.hx-nav[data-current]').forEach(function (nav) {
    var current = nav.getAttribute('data-current');
    nav.querySelectorAll('a').forEach(function (a) {
      if (a.getAttribute('href') === current) a.setAttribute('aria-current', 'page');
    });
    if (nav.querySelector('[aria-current]')) nav.classList.add('hx-nav--has-current');
  });

  /* ---------- Menu-only pinned bar ----------
     Pages without feed filters still get the quiet menu: the bar shows when
     scrolling up past the page header. */

  var MENU_LINKS = [
    ['top', 'Back to top'], ['work.html', 'Our work'], ['people.html', 'Our people'], ['#contact', 'Get in touch']
  ];

  function menuHTML(open) {
    return '<div class="filter-bar__menu" id="filter-bar-menu"' + (open ? '' : ' hidden') + '><ul role="list">' +
      MENU_LINKS.map(function (l) {
        return '<li>' + (l[0] === 'top'
          ? '<button type="button" data-top>' + l[1] + ' <span aria-hidden="true">&uarr;</span></button>'
          : '<a href="' + l[0] + '">' + l[1] + ' <span aria-hidden="true">&rarr;</span></a>') + '</li>';
      }).join('') +
    '</ul></div>';
  }

  function menuButton(open) {
    return '<button type="button" class="filter-bar__menu-btn" data-menu aria-label="Menu" aria-expanded="' + open + '" aria-controls="filter-bar-menu">' +
      '<span class="filter-bar__menu-icon" aria-hidden="true"></span></button>';
  }

  function menuBar(bar, header) {
    var shown = false, open = false;
    var lastY = window.scrollY, travel = 0, up = false;
    bar.innerHTML = '<div class="filter-bar__inner">' + menuButton(false) +
      '<a class="filter-bar__logo" href="index.html">Wove Group.</a></div>' + menuHTML(false);

    function toggle(o) {
      open = o;
      bar.querySelector('.filter-bar__menu').hidden = !o;
      bar.querySelector('[data-menu]').setAttribute('aria-expanded', String(o));
    }
    function update() {
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
    }
    var tick = false;
    window.addEventListener('scroll', function () {
      if (tick) return;
      tick = true;
      requestAnimationFrame(function () { tick = false; update(); });
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
      if (ev.key === 'Escape' && open) { toggle(false); bar.querySelector('[data-menu]').focus(); }
    });
  }

  window.WoveUI = {
    csBySlug: csBySlug, personBySlug: personBySlug,
    image: image, caseImage: caseImage, entryImage: entryImage,
    esc: esc, dateLabel: dateLabel, fullDate: fullDate, initials: initials, tone: tone, avatar: avatar,
    entryWord: entryWord, postWord: postWord,
    entryUrl: entryUrl, caseUrl: caseUrl, personUrl: personUrl, filterUrl: filterUrl, param: param,
    cardTags: cardTags, cardCtx: cardCtx, behaviour: behaviour,
    MENU_LINKS: MENU_LINKS, menuHTML: menuHTML, menuButton: menuButton, menuBar: menuBar
  };
})();
