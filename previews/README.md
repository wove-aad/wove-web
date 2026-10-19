# Wove previews (October 2026)

Static HTML previews from the 2026-10-16 design session. Each file is self-contained
(fonts embedded) and opens directly in a browser. Nothing in the Kirby site reads them.
Once deployed they are served as plain files: `staging.wove.group/previews/` lists them.
The published copies on claude.ai are listed beside each file.

| File | What it is | Published |
|---|---|---|
| `homepage.html` | The homepage preview, using the dev styles: shorter hero text, Route 1 cards, hover option B, team band, one button system | [Wove Homepage Preview](https://claude.ai/artifact/Ri5QFcL1VGCtb93CTyo6RY) |
| `client-page.html` | Client page for Circular.ie with placeholder content: header, 5 things to know, projects by date or discipline, related thinking, and a switch for three news placements | [Wove Client Page](https://claude.ai/artifact/YRaqEQMpWkEF9s4yoC2fxZ) |
| `hero-routes.html` | The hero and four variations (shorter, with an image, accent ground, names as a strip) | [Wove Hero Routes](https://claude.ai/artifact/P4QSvwwZ8USm84W3PwT4Y3) |
| `card-review.html` | The four card types, now and with one set of spacing rules | [Wove Card Review](https://claude.ai/artifact/KNJ5BjLJH4mHeq2QgMdAgF) |
| `card-routes.html` | Four card treatments in the full featured block. Route 1 (Flush) was chosen; Route 2 (Contained) is kept for reference | [Wove Card Routes](https://claude.ai/artifact/Dt5uMgYL3yioJ7wkFMnSEY) |
| `card-hovers.html` | Six hover options for project cards. Option B (image lifts, arrow turns coral) was chosen | [Wove Card Hovers](https://claude.ai/artifact/LTp9JQPw31yfPHYFjXmxqP) |
| `button-system.html` | The button system proposal (built on branch `claude/tokens-and-buttons`, not merged) | [Wove Button System](https://claude.ai/artifact/QUPWyXVaFHjD1TJqbTbKiF) |

## Decisions so far

- **Tokens:** the dev homepage CSS is the source of truth (`site.css` `:root`, `home.css` `.home`, `feed.css`). The token tidy-up is on branch `claude/tokens-and-buttons`, not merged.
- **Buttons:** one `.btn` with `--primary`, `--ghost` and `--inverse`; `.btn--sm` for the header only. Built on branch `claude/tokens-and-buttons`, not merged.
- **Hero:** shorter text, no image for now. An image could come later as a studio photo set in site settings.
- **Cards:** Route 1 (text flush with the image; contained cards share one grey fill and 32px padding). Hover option B, also on keyboard focus, and only on devices that can hover.
- **Team band:** photo at content width with a large radius, then "The people behind the work" and a "Meet our team" button.

## Still open

- Client page: where news sits (inline, sidebar or its own block).
- Client page: real projects, figures and the "5 things to know" copy.
- Carrying the card spacing, hover and team band into the dev templates.
- Merging `claude/tokens-and-buttons` (token tidy-up and button system) into `dev`.
