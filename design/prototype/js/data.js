/* Placeholder content for the prototype.
   Case study: title, text, featured image, featured logo, KPIs.
   Entries: about half are linked to a case study (caseStudy), the rest are
   general studio posts. */

window.WOVE = {
  services: { strategy: 'Strategy', digital: 'Digital', brand: 'Brand', labs: 'Labs' },

  // Sectors, as named in the homepage description.
  sectors: {
    public: 'Public services',
    culture: 'Cultural institutions',
    mission: 'Mission-led organisations'
  },

  // Placeholder team for the Our People link.
  people: [
    { name: 'Fergal Walsh', role: 'Founder' },
    { name: 'Aoife Byrne', role: 'Strategy Director' },
    { name: 'Niamh Kelly', role: 'Design Lead' },
    { name: 'Ciarán Doyle', role: 'Technology Lead' },
    { name: 'Sinéad Murphy', role: 'Service Designer' },
    { name: 'Rory Gallagher', role: 'Brand Designer' },
    { name: 'Orla Kennedy', role: 'Researcher' },
    { name: 'Darragh Nolan', role: 'Developer' },
    { name: 'Méabh Quinn', role: 'Producer' },
    { name: 'Eoin Farrell', role: 'Developer' },
    { name: 'Clodagh Ryan', role: 'Content Designer' },
    { name: 'Tadhg Brennan', role: 'Labs Lead' }
  ],

  tags: {
    'system-change': 'System Change',
    climate: 'Climate',
    'people-power': 'People Power',
    design: 'Design',
    education: 'Education',
    nature: 'Nature'
  },

  // Featured images are generated as SVG from `palette` until real photos are in.
  // `logo` is a placeholder wordmark until the logo files are supplied.
  caseStudies: [
    {
      slug: 'circular',
      sector: 'public',
      client: 'Circular.ie',
      logo: 'circular.ie',
      title: 'Launching a national campaign for the circular economy',
      text: 'A public platform, campaign and toolkit that explains the circular economy in plain terms and helps people take their first step.',
      kpis: [
        { value: '€21.6m', label: 'Projected annual saving' },
        { value: '310k', label: 'Visitors in year one' },
        { value: '42', label: 'Partner organisations' }
      ],
      services: ['strategy', 'brand', 'digital'],
      palette: ['#ff8fb1', '#ffd23f', '#1d9e75']
    },
    {
      slug: 'dcu',
      sector: 'public',
      client: 'DCU',
      logo: 'DCU',
      title: 'Redesigning the student journey from offer to first week',
      text: 'Service design across admissions, accommodation and orientation, built with students and staff over two terms.',
      kpis: [
        { value: '38%', label: 'Fewer support tickets' },
        { value: '4.6/5', label: 'Student satisfaction' },
        { value: '12', label: 'Services redesigned' }
      ],
      services: ['strategy', 'digital'],
      palette: ['#1b2a6b', '#5ec8f2', '#f5f4f0']
    },
    {
      slug: 'dublin-inquirer',
      sector: 'mission',
      client: 'Dublin Inquirer',
      logo: 'Dublin Inquirer',
      title: 'A membership model for local, independent journalism',
      text: 'New membership tiers, a refreshed identity and a reader experience that puts local reporting first.',
      kpis: [
        { value: '2.4x', label: 'Member growth' },
        { value: '61%', label: 'Renewal rate' },
        { value: '9', label: 'Months to launch' }
      ],
      services: ['brand', 'digital'],
      palette: ['#111111', '#ed8c7c', '#f3eeeb']
    },
    {
      slug: 'pivot-dublin',
      sector: 'culture',
      client: 'Pivot Dublin',
      logo: 'PIVOT',
      title: 'Designing a city-wide programme for public space',
      text: 'A strategy and open call that brought designers, councils and communities together around streets and squares.',
      kpis: [
        { value: '120', label: 'Submissions' },
        { value: '18', label: 'Sites activated' },
        { value: '5', label: 'Councils involved' }
      ],
      services: ['strategy', 'labs'],
      palette: ['#e0bdff', '#2a50f3', '#d5faff']
    },
    {
      slug: 'silvercloud',
      sector: 'mission',
      client: 'SilverCloud',
      logo: 'SilverCloud',
      title: 'Making digital mental health support easier to start',
      text: 'Research-led onboarding and content design for a clinical platform used across health services.',
      kpis: [
        { value: '27%', label: 'Higher completion' },
        { value: '3', label: 'Health systems' },
        { value: '1.2m', label: 'Users reached' }
      ],
      services: ['digital', 'labs'],
      palette: ['#2f7a57', '#e1eee7', '#c6841e']
    },
    {
      slug: 'arts-council',
      sector: 'culture',
      client: 'Arts Partner',
      logo: 'Arts Partner',
      title: 'A shared digital front door for cultural venues',
      text: 'Discovery and research into how audiences find events, leading to a shared listings service for regional venues.',
      kpis: [
        { value: '64', label: 'Venues onboarded' },
        { value: '22%', label: 'Increase in bookings' },
        { value: '6', label: 'Counties' }
      ],
      services: ['strategy', 'digital'],
      palette: ['#c6841e', '#f7ecd3', '#6b3ec2']
    }
  ],

  // Formats: spark, thread, whatif, longread. `daysAgo` drives the date label.
  entries: [
    { format: 'longread', title: 'How do you launch a national campaign for something people have never heard of?', excerpt: 'The challenge was to explain the circular economy to everyone, from people taking their first step to organisations ready to change.', caseStudy: 'circular', services: ['brand'], tags: ['climate'], daysAgo: 2, image: true },
    { format: 'longread', title: 'Building a future-proofed platform for multiple audiences', excerpt: 'One digital platform that serves individuals, businesses and community groups, each with their own starting point.', caseStudy: 'circular', services: ['digital'], tags: ['system-change'], daysAgo: 4, image: true },
    { format: 'spark', quote: 'People do not need to understand the term "circular economy" to want less waste in their lives. Start with the habit, not the theory.', author: 'Aoife Byrne', caseStudy: 'circular', tags: ['climate'], daysAgo: 9 },
    { format: 'thread', title: 'What we learned running twelve co-design sessions with first-year students', caseStudy: 'dcu', services: ['strategy'], tags: ['education'], daysAgo: 6, image: true },
    { format: 'whatif', title: 'What if orientation started the day you accepted your offer?', excerpt: 'Most of the anxiety we heard about happens in the summer before term. That is where the service should begin.', caseStudy: 'dcu', tags: ['education', 'design'], daysAgo: 14 },
    { format: 'longread', title: 'Paying for local news: designing membership people want to keep', excerpt: 'Why the renewal moment matters more than the sign-up, and what we changed to support it.', caseStudy: 'dublin-inquirer', services: ['digital'], tags: ['people-power'], daysAgo: 11, image: true },
    { format: 'thread', title: 'A new identity for an independent newsroom', caseStudy: 'dublin-inquirer', services: ['brand'], tags: ['design'], daysAgo: 21, image: true },
    { format: 'whatif', title: 'What if every council had a public space open call?', excerpt: 'Pivot showed that small, well-run open calls can move faster than large capital projects.', caseStudy: 'pivot-dublin', services: ['labs'], tags: ['people-power', 'system-change'], daysAgo: 17 },
    { format: 'longread', title: 'Lowering the threshold: onboarding for digital mental health', excerpt: 'Small changes to the first five minutes had the largest effect on whether people came back.', caseStudy: 'silvercloud', services: ['digital'], tags: ['design'], daysAgo: 25, image: true },
    { format: 'thread', title: 'Mapping how audiences find out what is on', caseStudy: 'arts-council', services: ['strategy'], tags: ['people-power'], daysAgo: 30, image: true },

    { format: 'spark', quote: 'Strategy that cannot survive contact with delivery is not strategy. It is a wish list.', author: 'Fergal Walsh', tags: ['system-change'], daysAgo: 1 },
    { format: 'whatif', title: 'What if public services were designed around life events?', excerpt: 'Moving house, having a child, losing a job. People think in moments, not departments.', services: ['strategy'], tags: ['system-change'], daysAgo: 3 },
    { format: 'longread', title: 'Designing for nature: what biodiversity data tells us about place', excerpt: 'Notes from a Labs project looking at how communities can read and act on local nature data.', services: ['labs'], tags: ['nature', 'climate'], daysAgo: 5, image: true },
    { format: 'thread', title: 'Our approach to accessible design systems', services: ['digital'], tags: ['design'], daysAgo: 8 },
    { format: 'spark', quote: 'The best research question we asked this year: "What did you do the last time this went wrong?"', author: 'Niamh Kelly', tags: ['design'], daysAgo: 10 },
    { format: 'longread', title: 'Brand as infrastructure for mission-led organisations', excerpt: 'A brand is the set of decisions that lets a small team act consistently without asking permission every time.', services: ['brand'], tags: ['people-power'], daysAgo: 13, image: true },
    { format: 'whatif', title: 'What if schools could share their best timetabling tools?', excerpt: 'Every school solves the same scheduling problem alone. A shared toolkit could free up hours each week.', services: ['labs'], tags: ['education'], daysAgo: 16 },
    { format: 'thread', title: 'Running a climate assembly online: what worked', services: ['strategy'], tags: ['climate', 'people-power'], daysAgo: 19, image: true },
    { format: 'spark', quote: 'If a service needs a user guide, the service is the problem.', author: 'Fergal Walsh', tags: ['design'], daysAgo: 23 },
    { format: 'longread', title: 'Small pilots, large systems: how we scope Labs projects', excerpt: 'Why we start with a twelve-week pilot and a clear question, and what happens when the answer is no.', services: ['labs'], tags: ['system-change'], daysAgo: 28, image: true }
  ]
};

/* Large sample, for testing how filters and counts hold up at real volumes.
   Built from the entries above with fixed per-service totals:
   Labs 44, Digital 30, Brand 22, Strategy 12, plus 16 sparks with no service.
   About half are linked to a case study, weighted towards the bigger clients.
   Titles repeat; only the volumes matter here. */
(function (W) {
  var base = W.entries;
  var byFormat = { spark: [], thread: [], whatif: [], longread: [] };
  base.forEach(function (e) { byFormat[e.format].push(e); });

  var seed = 7;
  function rand() { seed = (seed * 9301 + 49297) % 233280; return seed / 233280; }
  function pick(list) { return list[Math.floor(rand() * list.length)]; }

  var tagKeys = Object.keys(W.tags);
  var quotas = [['labs', 44], ['digital', 30], ['brand', 22], ['strategy', 12], [null, 16]];
  var csWeights = [['circular', 20], ['dcu', 14], ['dublin-inquirer', 10], ['pivot-dublin', 8], ['silvercloud', 6], ['arts-council', 4]];
  var formats = ['longread', 'thread', 'whatif', 'longread', 'thread'];

  var large = [];
  quotas.forEach(function (q) {
    for (var i = 0; i < q[1]; i++) {
      var format = q[0] ? formats[i % formats.length] : 'spark';
      var src = pick(byFormat[format]);
      var e = {};
      for (var k in src) e[k] = src[k];
      e.services = q[0] ? [q[0]] : [];
      e.tags = [pick(tagKeys)];
      if (rand() < 0.4) e.tags.push(pick(tagKeys));
      e.tags = e.tags.filter(function (t, j, a) { return a.indexOf(t) === j; });
      e.caseStudy = null;
      e.image = format !== 'spark' && rand() < 0.6;
      large.push(e);
    }
  });

  // Link case studies, preferring entries in one of the client's services.
  var csServices = {};
  W.caseStudies.forEach(function (cs) { csServices[cs.slug] = cs.services; });
  csWeights.forEach(function (w) {
    var placed = 0;
    for (var pass = 0; pass < 2 && placed < w[1]; pass++) {
      large.forEach(function (e) {
        if (placed >= w[1] || e.caseStudy || e.format === 'spark' && pass === 0) return;
        var fits = pass === 1 || e.services.some(function (s) { return csServices[w[0]].indexOf(s) !== -1; });
        if (fits && rand() < 0.5) { e.caseStudy = w[0]; placed++; }
      });
    }
  });

  // Shuffle, then date newest first.
  for (var i = large.length - 1; i > 0; i--) {
    var j = Math.floor(rand() * (i + 1));
    var t = large[i]; large[i] = large[j]; large[j] = t;
  }
  large.forEach(function (e, i) { e.daysAgo = Math.floor(i * 2.9); });

  W.entriesSmall = base;
  W.entriesLarge = large;
})(window.WOVE);
