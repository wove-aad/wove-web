/* Sample team for the exploration. Names, roles, bios and posts are placeholders. */
window.TEAM = [
  { slug: 'aoife-byrne', name: 'Aoife Byrne', role: 'Strategy Director', hue: 18, services: ['Strategy'],
    bio: 'Aoife leads strategy work with public services and cultural institutions, helping teams move from research to decisions they can act on.',
    posts: [['What if services were designed with the people who run them?', '3 Sep 2026'], ['Notes from the Public Services conference', '28 Aug 2026'], ['Three ways to test a strategy early', '2 Jul 2026']], count: 9 },
  { slug: 'cian-walsh', name: 'Cian Walsh', role: 'Design Lead', hue: 210, services: ['Brand', 'Digital'],
    bio: 'Cian works across brand and digital, with a focus on identities that hold up in everyday use.',
    posts: [['A brand is a set of habits', '12 Sep 2026'], ['Designing for the second visit', '19 Aug 2026']], count: 4 },
  { slug: 'niamh-oconnor', name: 'Niamh O’Connor', role: 'Research Lead', hue: 150, services: ['Labs', 'Strategy'],
    bio: 'Niamh runs research and discovery, from interviews to service safaris.',
    posts: [['What we heard in 40 interviews', '5 Sep 2026']], count: 1 },
  { slug: 'darragh-kelly', name: 'Darragh Kelly', role: 'Technical Director', hue: 260, services: ['Digital', 'Labs'],
    bio: 'Darragh leads engineering and makes sure what we design can be built and maintained.',
    posts: [['Boring technology, on purpose', '14 Sep 2026'], ['How we prototype with real data', '1 Aug 2026'], ['Accessibility is a build step', '10 Jul 2026']], count: 12 },
  { slug: 'sinead-murphy', name: 'Sinéad Murphy', role: 'Content Designer', hue: 330, services: ['Brand'],
    bio: 'Sinéad writes and edits, and helps clients say what they mean in fewer words.',
    posts: [], count: 0 },
  { slug: 'eoin-brennan', name: 'Eoin Brennan', role: 'Service Designer', hue: 45, services: ['Strategy', 'Labs'],
    bio: 'Eoin maps services end to end and designs the changes that make them easier to use.',
    posts: [['The map is not the service', '20 Sep 2026'], ['Blueprints for a housing service', '22 Aug 2026']], count: 5 },
  { slug: 'roisin-doyle', name: 'Róisín Doyle', role: 'Producer', hue: 100, services: ['Digital'],
    bio: 'Róisín keeps projects moving and teams talking.',
    posts: [['Kick-offs that actually kick off', '8 Sep 2026']], count: 2 },
  { slug: 'fionn-healy', name: 'Fionn Healy', role: 'Designer', hue: 190, services: ['Brand', 'Digital'],
    bio: 'Fionn designs interfaces and systems, and likes a good grid.',
    posts: [['Type scales that survive the CMS', '16 Sep 2026']], count: 3 }
];

window.initials = function (name) { return name.split(/\s+/).slice(0, 2).map(function (w) { return w[0]; }).join('').toUpperCase(); };
window.photoHTML = function (p, extraClass) {
  return '<span class="photo ' + (extraClass || '') + '"><span class="photo__initials">' + initials(p.name) + '</span>' +
    '<span class="photo__img" style="background: linear-gradient(160deg, hsl(' + p.hue + ' 35% 72%), hsl(' + (p.hue + 30) + ' 30% 48%))"></span></span>';
};
window.postsHTML = function (p) {
  if (!p.posts.length) return '<p class="role">No posts yet.</p>';
  return '<div class="posts">' + p.posts.map(function (x) { return '<a href="#"><span>' + x[0] + '</span><time>' + x[1] + '</time></a>'; }).join('') + '</div>' +
    '<a class="all" href="#">See all ' + p.name.split(' ')[0] + '’s posts (' + p.count + ') →</a>';
};

/* Tweaks bar: theme and photos */
window.initBar = function () {
  var theme = document.getElementById('tw-theme'), photos = document.getElementById('tw-photos');
  theme.addEventListener('click', function () {
    var dark = document.documentElement.getAttribute('data-theme') === 'dark';
    document.documentElement.setAttribute('data-theme', dark ? 'light' : 'dark');
    theme.setAttribute('aria-pressed', String(!dark)); theme.textContent = dark ? 'Dark' : 'Dark ✓';
  });
  photos.addEventListener('click', function () {
    var off = document.body.classList.toggle('no-photos');
    photos.setAttribute('aria-pressed', String(off)); photos.textContent = off ? 'No photos ✓' : 'No photos';
  });
};
