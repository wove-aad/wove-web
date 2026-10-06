/* Homepage hero directions, switched from the prototype bar (digits in the URL,
   e.g. #a3).
   1  Current:    header row, then the description as a separate block below.
   2  One voice:  statement and description run as one paragraph, with a nav
                  and two plain links to Our work and Our people.
   3  Explainer:  phrases in the description open examples: clients per sector,
                  services, and the team.
   4  Signposts:  description under the statement, then two link cards to Our
                  work and Our people with a preview of each.
   home.js calls WoveHero.init() with shared helpers once it has loaded. */
(function () {
  var D = window.WOVE;
  var helpers = null;
  var current = '1';
  var open = null; // explainer phrase currently open

  var VARIANTS = {
    '1': { name: 'Current', note: 'Header row, then the description as its own block.' },
    '2': { name: 'One voice', note: 'Statement and description as one paragraph, with nav links.' },
    '3': { name: 'Explainer', note: 'Highlighted phrases open examples of clients, services and people.' },
    '4': { name: 'Signposts', note: 'Description, then link cards to Our work and Our people.' }
  };

  var STATEMENT = 'Shaping a better Ireland through strategic design and technology';
  var URLS = { work: '#our-work', people: '#our-people' };

  function esc(s) {
    return String(s).replace(/[&<>"]/g, function (ch) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[ch];
    });
  }

  /* ---------- Shared pieces ---------- */

  var AVATAR_TONES = ['#ed8c7c', '#d5faff', '#e0bdff', '#f7ecd3', '#e1eee7', '#ebf0fe'];

  function initials(name) {
    return name.split(/\s+/).slice(0, 2).map(function (w) { return w.charAt(0); }).join('');
  }

  function avatars(n) {
    return '<span class="hx-avatars" aria-hidden="true">' +
      D.people.slice(0, n).map(function (p, i) {
        return '<span class="hx-avatar" style="--tone:' + AVATAR_TONES[i % AVATAR_TONES.length] + '">' + esc(initials(p.name)) + '</span>';
      }).join('') + '</span>';
  }

  function thumbs(n) {
    return '<span class="hx-thumbs" aria-hidden="true">' +
      D.caseStudies.slice(0, n).map(function (cs, i) {
        return '<img class="hx-thumb" src="' + helpers.image('c' + (i + 1), cs.palette, i + 3) + '" alt="">';
      }).join('') + '</span>';
  }

  function topRow(middle) {
    return '<a href="index.html" class="hero-bar__logo">Wove Group.</a>' + middle +
      '<a href="#contact" class="btn btn--sm btn--primary hero-bar__cta">Get in touch</a>';
  }

  function postCount() { return D.entries.length; }

  /* ---------- Layouts ---------- */

  var layouts = {
    '2': function () {
      return '<header class="hx hx--voice"><div class="hx__inner">' +
        '<div class="hx-row hx-row--nav">' + topRow(
          '<nav class="hx-nav" aria-label="Main">' +
            '<a href="' + URLS.work + '">Our work</a><a href="' + URLS.people + '">Our people</a>' +
          '</nav>') +
        '</div>' +
        '<div class="hx-body"><h1 class="hx-voice__text"><span class="hx-voice__statement">' + STATEMENT + '.</span> ' +
          'We help the people running Ireland’s public services, cultural institutions and mission-led organisations move from strategy to delivery.</h1>' +
        '<div class="hx-voice__links">' +
          '<a class="hx-link" href="' + URLS.work + '">' + thumbs(3) + '<span>See our work <span aria-hidden="true">&rarr;</span></span></a>' +
          '<a class="hx-link" href="' + URLS.people + '">' + avatars(4) + '<span>Meet our people <span aria-hidden="true">&rarr;</span></span></a>' +
        '</div></div>' +
      '</div></header>';
    },

    '3': function () {
      function phrase(key, label) {
        return '<button type="button" class="hx-phrase" data-phrase="' + key + '" aria-expanded="' + (open === key) + '" aria-controls="hx-drawer">' +
          esc(label) + '<span class="hx-phrase__icon" aria-hidden="true">' + (open === key ? '&minus;' : '+') + '</span></button>';
      }
      return '<header class="hx hx--explainer"><div class="hx__inner">' +
        '<div class="hx-row">' + topRow('<h1 class="hero-bar__statement">' + STATEMENT + '</h1>') + '</div>' +
        '<div class="hx-body hx-explainer__body">' +
          '<p class="hx-explainer__text">We help the people running Ireland’s ' +
            phrase('public', 'public services') + ', ' +
            phrase('culture', 'cultural institutions') + ' and ' +
            phrase('mission', 'mission-led organisations') + ' move ' +
            phrase('services', 'from strategy to delivery') + '.</p>' +
          '<div class="hx-drawer" id="hx-drawer" aria-live="polite"' + (open ? '' : ' hidden') + '>' + (open ? drawer(open) : '') + '</div>' +
          '<p class="hx-explainer__links">' +
            '<a class="hx-link" href="' + URLS.work + '">Our work <span aria-hidden="true">&rarr;</span></a>' +
            '<a class="hx-link" href="' + URLS.people + '">Our people <span aria-hidden="true">&rarr;</span></a>' +
          '</p>' +
        '</div>' +
      '</div></header>';
    },

    '4': function () {
      return '<header class="hx hx--signposts"><div class="hx__inner">' +
        '<div class="hx-row">' + topRow('<h1 class="hero-bar__statement">' + STATEMENT + '</h1>') + '</div>' +
        '<div class="hx-body hx-signposts__body">' +
          '<p class="hx-signposts__text">We help the people running Ireland’s public services, cultural institutions and mission-led organisations move from strategy to delivery.</p>' +
          '<div class="hx-signposts__cards">' +
            '<a class="hx-card" href="' + URLS.work + '">' + thumbs(3) +
              '<span class="hx-card__title">Our work</span>' +
              '<span class="hx-card__meta">' + D.caseStudies.length + ' clients · ' + postCount() + ' posts</span>' +
              '<span class="hx-card__arrow" aria-hidden="true">&rarr;</span></a>' +
            '<a class="hx-card" href="' + URLS.people + '">' + avatars(5) +
              '<span class="hx-card__title">Our people</span>' +
              '<span class="hx-card__meta">' + D.people.length + ' people across strategy, design and technology</span>' +
              '<span class="hx-card__arrow" aria-hidden="true">&rarr;</span></a>' +
          '</div>' +
        '</div>' +
      '</div></header>';
    }
  };

  // Explainer drawer contents
  function drawer(key) {
    if (key === 'services') {
      var notes = {
        strategy: 'Research, service design and organisational strategy.',
        brand: 'Identity and voice for organisations with a mission.',
        digital: 'Platforms and products, designed and built.',
        labs: 'Pilots that test new ideas before they scale.'
      };
      return '<div class="hx-drawer__head"><h2 class="hx-drawer__title">From strategy to delivery</h2>' +
          '<button type="button" class="hx-drawer__close" data-phrase="' + key + '" aria-label="Close">&times;</button></div>' +
        '<ul class="hx-drawer__grid" role="list">' + Object.keys(D.services).map(function (s) {
          return '<li><button type="button" class="hx-tile" data-filter-topic="service:' + s + '">' +
            '<span class="hx-tile__title">' + D.services[s] + '</span>' +
            '<span class="hx-tile__text">' + notes[s] + '</span>' +
            '<span class="hx-tile__more">See ' + D.services[s] + ' work <span aria-hidden="true">&rarr;</span></span></button></li>';
        }).join('') + '</ul>' +
        '<a class="hx-drawer__people" href="' + URLS.people + '">' + avatars(6) + '<span>Meet the team behind it <span aria-hidden="true">&rarr;</span></span></a>';
    }
    var clients = D.caseStudies.filter(function (cs) { return cs.sector === key; });
    return '<div class="hx-drawer__head"><h2 class="hx-drawer__title">' + esc(D.sectors[key]) + '</h2>' +
        '<button type="button" class="hx-drawer__close" data-phrase="' + key + '" aria-label="Close">&times;</button></div>' +
      '<ul class="hx-drawer__grid" role="list">' + clients.map(function (cs) {
        var i = D.caseStudies.indexOf(cs);
        return '<li><button type="button" class="hx-tile hx-tile--client" data-filter-client="' + cs.slug + '">' +
          '<img class="hx-tile__img" src="' + helpers.image('c' + (i + 1), cs.palette, i + 3) + '" alt="">' +
          '<span class="hx-tile__eyebrow">' + esc(cs.client) + '</span>' +
          '<span class="hx-tile__title">' + esc(cs.title) + '</span>' +
          '<span class="hx-tile__more">See the work <span aria-hidden="true">&rarr;</span></span></button></li>';
      }).join('') + '</ul>';
  }

  /* ---------- Mount ---------- */

  var mount = document.getElementById('hero-alt');

  function render() {
    document.documentElement.setAttribute('data-hero', current);
    mount.innerHTML = current === '1' ? '' : layouts[current]();
  }

  mount.addEventListener('click', function (ev) {
    var phrase = ev.target.closest('[data-phrase]');
    var client = ev.target.closest('[data-filter-client]');
    var topic = ev.target.closest('[data-filter-topic]');
    if (phrase) {
      var key = phrase.getAttribute('data-phrase');
      open = open === key ? null : key;
      render();
      var btn = mount.querySelector('.hx-phrase[data-phrase="' + key + '"]');
      if (btn) btn.focus();
    } else if (client) {
      helpers.filter('client', client.getAttribute('data-filter-client'));
    } else if (topic) {
      helpers.filter('topic', topic.getAttribute('data-filter-topic'));
    }
  });

  window.WoveHero = {
    variants: VARIANTS,
    get current() { return current; },
    init: function (h) { helpers = h; render(); },
    set: function (v) {
      if (!VARIANTS[v]) return;
      current = v;
      open = null;
      if (helpers) render();
    },
    refresh: function () { if (helpers) render(); }
  };
})();
