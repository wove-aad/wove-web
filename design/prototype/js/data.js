/* Placeholder content for the prototype.
   Case study: title, text, featured image, featured logo, KPIs.
   Entries: about half are linked to a case study (caseStudy), the rest are
   general studio posts. */

window.WOVE = {
  services: { strategy: 'Strategy', digital: 'Digital', brand: 'Brand', labs: 'Labs' },

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
