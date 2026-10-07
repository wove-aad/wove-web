# Frontend prototype

Static HTML prototypes for exploring layout and styling before moving the
markup into Kirby templates.

- `css/tokens.css`: a copy of the `:root` tokens from `assets/css/site.css`.
  Edit freely; nothing on the live site reads it.
- `css/site.css`, `css/feed.css`: copies of the live stylesheets, with the
  token block removed.
- `css/prototype.css`: new components (hero, client carousel, topic pills,
  case study panel, post cards, pinned filter bar and menu).
- `css/pages.css`: the homepage approach applied to the inner pages (page
  header, case study and person cards, case study, post and people layouts).
- `js/data.js`: placeholder case studies, posts and team.
- `js/shared.js`: placeholder images, labels, links, card context and the
  menu-only pinned bar.
- `js/cards.js`: post and case study card markup and masonry rows.
- `js/feed.js`: filters, case study panel, feed and pinned bar, for the
  homepage and Our work (options on `<body>`: `data-limit`,
  `data-case-cards`, `data-url-state`).
- `js/case-study.js`, `js/post.js`, `js/people.js`: the inner pages.

Pages: `index.html` (homepage), `work.html` (Our work, filters in the URL:
`?client=`, `?topic=`, `?author=`), `people.html` (Our people, `#slug` opens
a person), `case-study.html?cs=<slug>` and `post.html?e=<entry index>`.

Open `index.html` in a browser from a checkout, or run `python3 build.py` to
bundle each page (CSS, JS, fonts) into `dist/<page>.html`, which opens on its
own (`dist/<page>.artifact.html` is the same page without the document shell,
for publishing as an artifact). `dist/` is gitignored.
