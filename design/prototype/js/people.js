/* Our people: a grid of cards. Choosing one opens a panel under its row
   with the bio, latest posts and links; the other cards step back.
   people.html#<slug> opens that person on load. */
(function () {
  var D = window.WOVE, U = window.WoveUI, esc = U.esc;
  var grid = document.getElementById('people-grid');

  function postsBy(p) {
    return D.entries.filter(function (e) { return e.author === p.name && e.format !== 'spark'; })
      .sort(function (a, b) { return a.daysAgo - b.daysAgo; });
  }
  function countBy(p) { return D.entries.filter(function (e) { return e.author === p.name; }).length; }

  grid.innerHTML = D.people.map(function (p) {
    return '<li class="person-card" id="' + p.slug + '">' +
      '<button type="button" class="person-card__btn" aria-expanded="false" aria-controls="person-detail" data-person="' + p.slug + '">' +
        '<span class="person-card__photo" style="--tone:' + U.tone(p) + '" aria-hidden="true">' + esc(U.initials(p.name)) + '</span>' +
        '<span class="person-card__name">' + esc(p.name) + '</span>' +
        '<span class="person-card__role">' + esc(p.role) + '</span>' +
      '</button></li>';
  }).join('');

  function detailHTML(p) {
    var posts = postsBy(p).slice(0, 3);
    var n = countBy(p);
    var first = p.name.split(' ')[0];
    var bio = p.bio || first + ' is a ' + p.role.toLowerCase() + ' at Wove, working across strategy, design and delivery with clients in public services, culture and education.';
    return '<li class="person-detail" id="person-detail" role="region" aria-label="' + esc(p.name) + '" tabindex="-1">' +
      '<div class="person-detail__head"><div>' +
        '<p class="person-detail__name">' + esc(p.name) + '</p><p class="person-detail__role">' + esc(p.role) + '</p></div>' +
        '<button type="button" class="person-detail__close" aria-label="Close">&times;</button></div>' +
      '<p class="person-detail__bio">' + esc(bio) + '</p>' +
      (posts.length
        ? '<div><p class="person-detail__label">Latest</p><div class="person-detail__posts">' + posts.map(function (e) {
            return '<a href="' + U.entryUrl(e) + '"><strong>' + esc(e.title) + '</strong><span>' + U.dateLabel(e.daysAgo) + '</span></a>';
          }).join('') + '</div></div>'
        : '<div></div>') +
      '<div class="person-detail__foot">' +
        (n ? '<a class="pg-btn" href="' + U.filterUrl('author', p.slug) + '">See all ' + esc(first) + '’s posts (' + n + ') <span aria-hidden="true">&rarr;</span></a>' : '') +
        '<a class="pg-btn pg-btn--ghost" href="#">LinkedIn <span aria-hidden="true">&nearr;</span></a>' +
      '</div>' +
    '</li>';
  }

  var open = null;

  // The panel goes after the last card in the open card's row.
  function lastInRow(card) {
    var top = card.offsetTop, last = card, el = card.nextElementSibling;
    while (el) {
      if (el.classList.contains('person-card')) {
        if (el.offsetTop !== top) break;
        last = el;
      }
      el = el.nextElementSibling;
    }
    return last;
  }

  function close(focus) {
    var panel = document.getElementById('person-detail');
    if (panel) panel.remove();
    grid.classList.remove('has-open');
    if (open) {
      var btn = grid.querySelector('[data-person="' + open + '"]');
      btn.setAttribute('aria-expanded', 'false');
      if (focus) btn.focus();
    }
    open = null;
    history.replaceState(null, '', location.pathname);
  }

  function show(slug, scroll) {
    var p = U.personBySlug[slug];
    if (!p) return;
    close(false);
    open = slug;
    var card = document.getElementById(slug);
    lastInRow(card).insertAdjacentHTML('afterend', detailHTML(p));
    card.querySelector('button').setAttribute('aria-expanded', 'true');
    grid.classList.add('has-open');
    history.replaceState(null, '', '#' + slug);
    if (scroll) {
      // Bring the card and its panel into view together.
      var panel = document.getElementById('person-detail');
      var r = panel.getBoundingClientRect();
      if (r.bottom > window.innerHeight || card.getBoundingClientRect().top < 0) {
        var y = Math.min(card.getBoundingClientRect().top - 16, r.bottom - window.innerHeight + 16);
        window.scrollBy({ top: y, behavior: U.behaviour() });
      }
    }
  }

  // Re-seat the panel when the number of columns changes. Phones fire resize
  // as the address bar moves, so leave it alone unless its row has changed.
  window.addEventListener('resize', function () {
    var panel = document.getElementById('person-detail');
    if (!open || !panel) return;
    var after = lastInRow(document.getElementById(open));
    if (after.nextElementSibling !== panel) after.insertAdjacentElement('afterend', panel);
  });

  grid.addEventListener('click', function (ev) {
    var btn = ev.target.closest('.person-card__btn');
    if (btn) {
      var slug = btn.getAttribute('data-person');
      if (open === slug) close(true); else show(slug, true);
      return;
    }
    if (ev.target.closest('.person-detail__close')) close(true);
  });
  document.addEventListener('keydown', function (ev) {
    if (ev.key === 'Escape' && open) close(true);
  });

  var hash = location.hash.slice(1);
  if (U.personBySlug[hash]) {
    show(hash, false);
    requestAnimationFrame(function () {
      document.getElementById(hash).scrollIntoView({ block: 'start' });
    });
  }

  U.menuBar(document.getElementById('filter-bar'), document.querySelector('.hx'));
})();
