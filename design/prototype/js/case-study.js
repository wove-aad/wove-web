/* Case study page: ?cs=<slug> (defaults to the first case study). */
(function () {
  var D = window.WOVE, U = window.WoveUI, Cards = window.WoveCards, esc = U.esc;
  var cs = U.csBySlug[U.param('cs')] || D.caseStudies[0];
  var i = D.caseStudies.indexOf(cs);
  document.title = cs.client + ' | Wove';

  function serviceLinks(sep) {
    return cs.services.map(function (s) {
      return '<a href="' + U.filterUrl('topic', 'service:' + s) + '">' + D.services[s] + '</a>';
    }).join(sep);
  }

  document.getElementById('cs-header').innerHTML =
    '<nav class="ph__crumbs" aria-label="Breadcrumb"><a href="work.html">Our work</a><span aria-hidden="true">/</span>' +
      '<a href="' + U.filterUrl('client', cs.slug) + '">' + esc(cs.client) + '</a></nav>' +
    '<p class="ph__eyebrow">Case study · ' + esc(cs.client) + '</p>' +
    '<h1 class="ph__title">' + esc(cs.title) + '</h1>' +
    '<p class="ph__intro">' + esc(cs.text) + '</p>' +
    '<div class="ph__pills">' + cs.services.map(function (s) {
      return '<a class="ph__pill" href="' + U.filterUrl('topic', 'service:' + s) + '">' + D.services[s] + '</a>';
    }).join('') + '</div>';

  document.getElementById('cs-image').innerHTML =
    '<img src="' + U.caseImage(cs, 1600, 686) + '" alt="" width="1600" height="686" style="aspect-ratio:21/9">';

  document.getElementById('csp-stats').innerHTML = cs.kpis.map(function (k) {
    return '<div class="csp-stat"><dt>' + esc(k.label) + '</dt><dd>' + esc(k.value) + '</dd></div>';
  }).join('');

  var team = cs.team.map(function (name) {
    var p = D.people.filter(function (x) { return x.name === name; })[0];
    return '<a href="' + U.personUrl(p) + '">' + U.avatar(p) + '<span>' + esc(p.name) + '</span></a>';
  }).join('');
  document.getElementById('cs-facts').innerHTML = '<dl class="cs-facts">' +
    '<div><dt>Client</dt><dd>' + esc(cs.client) + '</dd></div>' +
    '<div><dt>Sector</dt><dd>' + esc(cs.sector) + '</dd></div>' +
    '<div><dt>Services</dt><dd>' + serviceLinks(', ') + '</dd></div>' +
    '<div><dt>Year</dt><dd>' + cs.year + '</dd></div>' +
    '<div><dt>Project team</dt><dd class="csp-team">' + team + '</dd></div>' +
  '</dl>';

  document.getElementById('cs-body').innerHTML =
    '<h2>The challenge</h2>' +
    '<p>' + esc(cs.client) + ' came to us with a clear ambition and a crowded landscape. The people they serve were already juggling a dozen other services, and most had never heard of the work in the first place.</p>' +
    '<p>We spent the first weeks listening: interviews, site visits and a review of what already existed. The brief changed as a result, from launching something new to joining up what was already there.</p>' +
    '<h2>What we did</h2>' +
    '<p>We worked in short cycles with a small group from the client team, testing ideas with the people who would use them before anything was built.</p>' +
    '<ul><li>Research with the people using the service and the staff running it</li><li>A strategy and roadmap agreed with the leadership team</li><li>Design and build, released in stages</li></ul>' +
    '<figure><img src="' + U.image('cs-fig-' + cs.slug, cs.palette.slice().reverse(), i + 40, 1200, 750) + '" alt=""><figcaption>Working sessions with the ' + esc(cs.client) + ' team.</figcaption></figure>' +
    '<blockquote>' + esc(cs.quote.text) + '</blockquote>' +
    '<h2>What changed</h2>' +
    '<p>The results are in the figures above, but the larger change was in how the team works: decisions are made with evidence, and the people affected are part of them.</p>';

  document.getElementById('cs-quote').innerHTML =
    '<blockquote>' + esc(cs.quote.text) + '</blockquote>' +
    '<figcaption><strong>' + esc(cs.quote.name) + '</strong>' + esc(cs.quote.org) + '</figcaption>';

  // Posts linked to this case study, newest first.
  var posts = D.entries.filter(function (e) { return e.caseStudy === cs.slug; })
    .sort(function (a, b) { return a.daysAgo - b.daysAgo; });
  var grid = document.getElementById('cs-posts');
  if (posts.length) {
    grid.innerHTML = Cards.masonry(posts.map(function (e) { return Cards.render(e, U.cardCtx); }));
    document.getElementById('cs-posts-all').href = U.filterUrl('client', cs.slug);
    Cards.layout(grid);
    if (window.ResizeObserver) new ResizeObserver(function () { Cards.layout(grid); }).observe(grid);
  } else {
    document.getElementById('cs-posts-section').hidden = true;
  }

  var next = D.caseStudies[(i + 1) % D.caseStudies.length];
  document.getElementById('cs-next').innerHTML =
    '<article class="cs-next">' +
      '<div class="cs-next__media"><img src="' + U.caseImage(next) + '" alt=""><span class="pc__logo">' + esc(next.logo) + '</span></div>' +
      '<div class="cs-next__body">' +
        '<p class="cs-next__eyebrow">Next case study · ' + esc(next.client) + '</p>' +
        '<h2 class="cs-next__title">' + esc(next.title) + '</h2>' +
        '<a class="pg-btn cs-next__link" href="' + U.caseUrl(next) + '">See case study <span aria-hidden="true">&rarr;</span></a>' +
      '</div>' +
    '</article>';

  U.menuBar(document.getElementById('filter-bar'), document.querySelector('.hx'));
})();
