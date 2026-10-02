# Frontend prototype

Static HTML prototypes for exploring layout and styling before moving the
markup into Kirby templates.

- `css/tokens.css`: a copy of the `:root` tokens from `assets/css/site.css`.
  Edit freely; nothing on the live site reads it.
- `css/site.css`, `css/feed.css`: copies of the live stylesheets, with the
  token block removed.
- `css/prototype.css`: new components (hero bar, client carousel, case study
  panel).
- `js/data.js`: placeholder case studies and entries.

Open `index.html` in a browser from a checkout, or run `python3 build.py` to
bundle everything (CSS, JS, fonts) into `dist/index.html`, which opens on its
own. `dist/` is gitignored.
