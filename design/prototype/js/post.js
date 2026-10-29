/* Post page: #e.<entry index> (defaults to the newest long read).
   Threads, What Ifs and long reads have pages; sparks live in the feed. */
(function () {
  var D = window.WOVE, U = window.WoveUI, Cards = window.WoveCards, esc = U.esc;
  var LABELS = { thread: 'Thread', whatif: 'What If', longread: 'Long Read' };
  var e = D.entries[+U.param('e')];
  if (!e || e.format === 'spark') e = D.entries.filter(function (x) { return x.format === 'longread'; })[0];
  var cs = e.caseStudy && U.csBySlug[e.caseStudy];
  var person = e.author && D.people.filter(function (p) { return p.name === e.author; })[0];
  var minutes = e.minutes || (e.format === 'thread' ? 2 : 4);
  document.title = e.title + ' | Wove';

  var hx = document.getElementById('post-hx');
  if (e.format === 'whatif') hx.classList.add('hx--whatif');

  var top = '<div class="ph__top"><a class="ph__back" href="work.html"><span aria-hidden="true">&larr;</span> All work</a>' +
    '<p class="post-chip post-chip--' + e.format + '">' + LABELS[e.format] + '</p></div>';
  var byline = '<div class="byline">' +
    (person ? '<span class="byline__who"><a href="' + U.personUrl(person) + '">' + U.avatar(person) +
      '<span><span class="byline__name">' + esc(person.name) + '</span><span class="byline__role">' + esc(person.role) + '</span></span></a></span>' : '') +
    '<span class="byline__meta"><time>' + U.fullDate(e.daysAgo) + '</time> · ' + minutes + ' min read</span>' +
  '</div>';
  document.getElementById('post-header').innerHTML = top +
    '<h1 class="ph__title">' + esc(e.title) + '</h1>' +
    (e.excerpt ? '<p class="ph__intro">' + esc(e.excerpt) + '</p>' : '') + byline;

  // Wide images lead under the header; square and portrait ones sit in the text.
  var ratio = e.ratio || 1.5;
  var figure = '';
  if (e.image && ratio >= 1.4) {
    var lead = document.getElementById('post-image');
    lead.hidden = false;
    lead.innerHTML = '<img src="' + U.entryImage(e) + '" alt="" style="aspect-ratio:' + ratio + '">';
    hx.classList.add('hx--lead');
  } else if (e.image) {
    figure = '<figure><img src="' + U.entryImage(e) + '" alt="" style="aspect-ratio:' + ratio + '"></figure>';
  }

  var about = cs ? 'our work with ' + esc(cs.client) : 'a question we keep coming back to in the studio';
  var body = {
    thread:
      figure +
      '<p>A short note on ' + about + '. Threads are where we share what we are learning while the work is still happening.</p>' +
      '<p>Three things stood out this week:</p>' +
      '<ul><li>The people closest to the problem had already tried most of the obvious fixes.</li><li>Small tests told us more than the long planning session did.</li><li>Writing it down as we went made the hand-over easy.</li></ul>' +
      '<p>More soon as the project moves on.</p>',
    whatif:
      '<p>This is a What If: an idea we think is worth exploring, set out so others can pick it up, push back or build on it.</p>' +
      figure +
      '<h2>The idea</h2><p>' + esc(e.excerpt || '') + ' We have seen versions of this work at a small scale. The question is what it would take to make it normal.</p>' +
      '<h2>Why now</h2><p>The pieces are already in place: the data exists, the people are willing and the cost of doing nothing keeps rising.</p>' +
      '<blockquote>The best ideas in public services are rarely new. They are usually waiting for someone to join them up.</blockquote>' +
      '<h2>What it would take</h2><ul><li>One organisation willing to go first</li><li>A small, funded pilot with a clear question</li><li>A way to share what is learned, good or bad</li></ul>' +
      '<p>If you are working on something like this, we would like to hear from you.</p>',
    longread:
      figure +
      '<p>' + esc(e.excerpt || '') + ' This piece sets out what we did, what we learned and what we would do differently, from ' + about + '.</p>' +
      '<h2>Where we started</h2><p>Every project starts with a brief, and every brief is a guess. Ours changed within the first fortnight, once we had spoken to the people the work was meant to help.</p>' +
      '<p>We mapped the journey as it is today, not as it appears in the process documents. The gaps were in the hand-overs between teams, not inside any one of them.</p>' +
      '<blockquote>The gaps were never inside one team. They were in the space between them.</blockquote>' +
      '<h2>What we tried</h2><p>We ran a series of short tests, each with a single question. Most failed quickly, which was the point: each one narrowed the options for the next.</p>' +
      '<ul><li>Plain-language rewrites of the first three screens</li><li>A single point of contact for the first month</li><li>Shared notes between teams, visible to the person using the service</li></ul>' +
      '<h2>What we learned</h2><p>The changes that mattered most were small and cheap. The hard part was agreeing who owned them.</p>' +
      '<p>We are now working on the next phase, and will share more as it develops.</p>'
  };
  document.getElementById('post-body').innerHTML = body[e.format];

  // Tags in the site-wide order: case study, services, editorial tags.
  var tags = (cs ? ['<a href="' + U.filterUrl('client', cs.slug) + '">' + esc(cs.client) + '</a>'] : [])
    .concat((e.services || []).map(function (s) { return '<a href="' + U.filterUrl('topic', 'service:' + s) + '">' + D.services[s] + '</a>'; }))
    .concat((e.tags || []).map(function (t) { return '<a href="' + U.filterUrl('topic', 'tag:' + t) + '">' + D.tags[t] + '</a>'; }));
  document.getElementById('post-tags').innerHTML = tags.join('');

  if (person) {
    var count = D.entries.filter(function (x) { return x.author === person.name; }).length;
    var first = person.name.split(' ')[0];
    document.getElementById('post-author').innerHTML =
      '<aside class="author-card" aria-label="About the author">' + U.avatar(person) +
        '<div><p class="author-card__label">Written by</p>' +
          '<p class="author-card__name">' + esc(person.name) + '</p>' +
          '<p class="author-card__role">' + esc(person.role) + '</p>' +
          (person.bio ? '<p class="author-card__bio">' + esc(person.bio) + '</p>' : '') +
          '<p class="author-card__links"><a href="' + U.personUrl(person) + '">View profile <span aria-hidden="true">&rarr;</span></a>' +
            '<a href="' + U.filterUrl('author', person.slug) + '">See all ' + esc(first) + '’s posts (' + count + ') <span aria-hidden="true">&rarr;</span></a></p>' +
        '</div>' +
      '</aside>';
  }

  // The case study this post belongs to, as the homepage panel.
  if (cs) {
    var sec = document.getElementById('post-case');
    sec.hidden = false;
    sec.insertAdjacentHTML('beforeend',
      '<section class="case-panel" aria-label="Case study: ' + esc(cs.client) + '">' +
        '<div class="case-panel__media"><img src="' + U.image('p' + cs.slug, cs.palette, 11, 900, 640) + '" alt="">' +
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
      '</section>');
  }

  // More posts: the same project first, then the newest others.
  var others = D.entries.filter(function (x) { return x !== e; });
  var same = others.filter(function (x) { return cs && x.caseStudy === cs.slug; });
  var rest = others.filter(function (x) { return same.indexOf(x) === -1; }).sort(function (a, b) { return a.daysAgo - b.daysAgo; });
  var more = same.concat(rest).slice(0, 6);
  var grid = document.getElementById('post-more');
  grid.innerHTML = Cards.masonry(more.map(function (x) { return Cards.render(x, U.cardCtx); }));
  Cards.layout(grid);
  if (window.ResizeObserver) new ResizeObserver(function () { Cards.layout(grid); }).observe(grid);

  U.freshOnHashChange();
  U.menuBar(document.getElementById('filter-bar'), hx);
})();
