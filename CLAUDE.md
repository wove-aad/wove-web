# wove-web — project context

_Last updated 2026-07-14. Kirby CMS build for wove.group._

## Branches

- **`main`** — production (the live `wove.group`). Deploy workflow (`deploy-prod.yml`) exists but is entirely commented out — pushing to `main` does not currently deploy anywhere.
- **`dev`** — staging. `deploy-staging.yml` triggers on every push to `dev` and FTPs the repo straight to `staging.wove.group`. This is the source of truth right now.
- **`proto`** — used for the WoveMind/service-page work below. As of 2026-07-14 it's been fully merged into `dev` — it has no commits `dev` doesn't already have. Branch fresh off `dev` for new work rather than continuing on `proto`.

## What's built

### Global shell
- `site/snippets/header.php` — owns the doctype/head (title, SEO meta from the `seo` tab fields, canonical, OG tags, font preloads, stylesheet link) through to the nav. `aria-current="page"` is computed from `$page->uri()`, not hardcoded.
- `site/snippets/footer.php` — footer through to the closing `</body></html>`. Dynamic copyright year.
- `site/snippets/service-page-scripts.php` — shared behaviour JS (reveal-on-scroll, mobile offerings scroll-spy, "See more case studies" toggle). No-ops safely on pages that don't have the relevant elements.
- `assets/css/site.css` — the full design-token/component stylesheet, migrated from the Labs prototype's inline `<style>` into a shared file. `assets/design-tokens.json` is the canonical token source (`_meta._repoNote` explains the sync process — `site.css`'s `:root` is kept in sync with it by hand, no build step yet).
- `assets/fonts/Ballinger-*.{woff,woff2}` — real font files (Regular/Bold/X-Bold/Italic), pulled from `dev`'s own asset scaffold. **No files exist for weights 300 (light) / 500 (medium) / 900 (black)** — deliberately not faked; per the CSS font-matching spec an unregistered weight resolves to the nearest registered weight in the same family, so text stays in Ballinger rather than falling through to a different fallback font. `font-display: optional` (not `swap`) to avoid a visible font-swap jump on slow/uncached loads.

### Service pages
Four real Kirby templates, one per service: `site/templates/{labs,strategy,brand,digital}.php`. Each follows the same shape:
- **Static** (hardcoded in the template, not CMS-managed by design — see `frontend-development-guidelines.md`'s note that services/home/about are static templates): page head, intro, "what we can help with," the offerings grid, the quote.
- **Dynamic — featured case study/studies**: `kirby()->collection('case-studies')->filter(fn ($cs) => in_array($page->slug(), $cs->services()->split(',')))`, rendered via `site/snippets/case-study-feature-card.php`. Labs shows one card; Strategy/Brand/Digital show every matching case study with a "See more" expand/collapse (first card always visible).
- **Dynamic — recent work**: two card types in one `.wovemind-cards` grid —
  - `project-highlight` entries (non-linked, external "Visit website" CTA) via `wovemind-highlight-card.php`
  - `thread`/`whatif`/`longread` entries (linked, internal CTA) via `wovemind-related-card.php`

  Both queried with `in_array($page->slug(), $p->services()->split(','))` — **note:** `Field::split(',')` returns a plain PHP array, not a Kirby collection, so `->includes()` (the pattern in the original brief) never works; always use `in_array()`.

Content pages exist locally at `content/services/{labs,strategy,brand,digital}` (plus a `content/services/services.txt` parent) so the URLs resolve — these are gitignored (`content/` is deliberately excluded, "keep content out of main repo for now"), so they only exist on whichever machine/server actually has them. The real content on `dev`/staging was created through the Panel directly.

### WoveMind content model
`site/blueprints/pages/wove-mind-entry.yml` — format is now `spark | project-highlight | thread | whatif | longread`. `project-highlight` replaces what used to be a repurposed `thread`; a genuine `thread` format now exists alongside it. Fields: `client`, `excerpt` (200 char cap — **not yet confirmed against real copy**, Figma descriptions run ~150–160 chars), `website` (optional, drives the "Visit website" link) — all shown only for `project-highlight`. `title`/`image`/`body` show for **every** format (see gap below).

`site/snippets/wovemind-card.php` (used unfiltered by the main Wove Mind feed and the homepage feed) branches on format to render `project-highlight` correctly (`client()`/`excerpt()`) instead of falling through to `title()`/`body()`.

### Panel
Working locally — first-account installation completed, a real `project-highlight` entry ("Circular.ie," tagged Strategy) exists and renders correctly on the Strategy service page.

## Known gaps / open items

- **Kirby's `when` field condition only supports a single exact value, not a list.** Confirmed by reading `kirby/src/Form/Mixin/When.php` directly — `when: format: in: [...]` can never match (a string can never `===` an array), so any field using that pattern is permanently hidden regardless of format. This was already fixed on `title`/`image`/`body` in `wove-mind-entry.yml` (they now show unconditionally instead), but **if this pattern shows up in any new blueprint work, it won't work** — needs restructuring (e.g. per-value fields, or just showing unconditionally with an `info:` note).
- **No stored `excerpt` field for `thread`/`whatif`/`longread`** — `wovemind-related-card.php` computes its excerpt from `body()->excerpt(160)`. The original prototype scaffolds assumed a stored excerpt existed for those formats too; it doesn't. Flag if a real stored field is wanted there.
- **No "unlisted by default" mechanism for `spark`/`project-highlight`** — was only ever a comment/intention in the original CMS brief, never actually implemented. Kirby blueprints can't conditionally set page status from a field value; would need a page model hook (`site/models/`) or an editorial convention.
- **No `ogImage` field** on the SEO tab (`site/blueprints/tabs/seo.yml`) — `header.php`'s Open Graph tags don't include `og:image`.
- **`deploy-prod.yml` is broken** (pre-existing, unrelated to this work) — the whole file is commented out with no `on:`/`jobs:`, so GitHub Actions treats it as an invalid workflow and fails it on every push to any branch. Cosmetic (shows as a failed check) but worth cleaning up or removing.
- **Font weights 300/500/900 have no real files** — currently relying on CSS font-matching fallback to the nearest registered weight (see above). Fine visually, but if real Light/Medium/Black files ever turn up, add them.

## Local dev environment

PHP 8.3 (via `winget install --id PHP.PHP.8.3 --source winget`) — the winget build ships with no `php.ini` configured, needs `openssl`/`curl`/`mbstring`/`fileinfo`/`zip`/`gd`/`intl` enabled manually. Composer via a standalone `composer.phar` (no system install needed). Run `composer install` **at the project root** (not inside `kirby/`) — Kirby's custom installer (`getkirby/composer-installer`) redirects the package install into `./kirby/` directly rather than `vendor/getkirby/cms/`, which is also where the Panel's built frontend assets (`kirby/panel/dist/`, gitignored) come from. `composer start` (or `php -S localhost:8000 kirby/router.php`) boots the dev server.

## Deploy state (as of 2026-07-14)

`proto`'s full history was merged into `dev` (clean merge, no conflicts — an earlier blueprint conflict with Adam's parallel taxonomy/blocks/date-field work on `dev` resolved itself as both sides' changes evolved into non-overlapping regions) and pushed. The staging deploy ran successfully — everything above is live on `staging.wove.group`. `main`/production untouched.
