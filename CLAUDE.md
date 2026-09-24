# wove-web — project context

_Last updated 2026-09-24. Kirby CMS build for wove.group._

## Branches

- **`main`** — production (the live `wove.group`). Deploy workflow (`deploy-prod.yml`) exists but is entirely commented out — pushing to `main` does not currently deploy anywhere.
- **`dev`** — staging. `deploy-staging.yml` triggers on every push to `dev` and FTPs the repo straight to `staging.wove.group`. This is the source of truth right now.
- **`proto`** — used for the WoveMind/service-page work below. As of 2026-07-14 it's been fully merged into `dev` — it has no commits `dev` doesn't already have. Branch fresh off `dev` for new work rather than continuing on `proto`.

## What's built

### Global shell
- `site/snippets/header.php` — owns the doctype/head (title, SEO meta from the `seo` tab fields, canonical, OG tags, font preloads, stylesheet link) through to the nav. `aria-current="page"` is computed from `$page->uri()`, not hardcoded.
- `site/snippets/footer.php` — footer through to the closing `</body></html>`. Dynamic copyright year.
- `site/snippets/service-page-scripts.php` — shared behaviour JS (reveal-on-scroll, mobile offerings scroll-spy, "See more case studies" toggle). No-ops safely on pages that don't have the relevant elements. Also loaded by `work.php`, `service.php` and `tag.php` (as well as `home.php`) so case study cards (`.work-grid-card`, which start at `opacity: 0` under `.js`) get revealed. **Any template that renders `stream-card`/`work-grid-card` must include this snippet**, or case study cards stay invisible while their links still work.
- `assets/css/site.css` — the full design-token/component stylesheet, migrated from the Labs prototype's inline `<style>` into a shared file. `assets/design-tokens.json` is the canonical token source (`_meta._repoNote` explains the sync process — `site.css`'s `:root` is kept in sync with it by hand, no build step yet).
- `assets/fonts/Ballinger-*.{woff,woff2}` — real font files (Regular/Bold/X-Bold/Italic), pulled from `dev`'s own asset scaffold. **No files exist for weights 300 (light) / 500 (medium) / 900 (black)** — deliberately not faked; per the CSS font-matching spec an unregistered weight resolves to the nearest registered weight in the same family, so text stays in Ballinger rather than falling through to a different fallback font. `font-display: optional` (not `swap`) to avoid a visible font-swap jump on slow/uncached loads.

### Service pages
One shared template, `site/templates/service.php`, replaced the four per-service templates (`{labs,strategy,brand,digital}.php`, now removed along with `case-study-feature-card.php`, `wovemind-highlight-card.php` and `wovemind-related-card.php`). It uses the feed design system (`assets/css/feed.css`):
- Breadcrumb (Feed / Our Work / service), a `tag-hero` with the page title, intro (`$page->intro()`, falling back to a hardcoded intro per service) and an entry count, then the `tag-cloud` snippet.
- **Dynamic — one mixed stream**: matching case studies (`kirby()->collection('case-studies')`) plus listed Wove Mind entries, both filtered with `in_array($serviceSlug, $p->services()->split(','))`, rendered through `stream-card` (case studies → `work-grid-card`, entries → `feed-card`). **Note:** `Field::split(',')` returns a plain PHP array, not a Kirby collection, so `->includes()` never works; always use `in_array()`.

Routing (`site/config/config.php`): `/strategy` etc. redirect to `/services/{slug}`. The `services/(:any)` route only renders a `{slug}.php` template if one exists; otherwise it returns `false` and Kirby renders `page('services/{slug}')` with its own template. `service.php` gets the slug from a `$serviceSlug` variable or the page slug.

Content pages exist locally at `content/services/{labs,strategy,brand,digital}` (plus a `content/services/services.txt` parent) so the URLs resolve — these are gitignored (`content/` is deliberately excluded, "keep content out of main repo for now"), so they only exist on whichever machine/server actually has them. The real content on `dev`/staging was created through the Panel directly.

### WoveMind content model
`site/blueprints/pages/wove-mind-entry.yml` — format is `spark | thread | whatif | longread`. The earlier `project-highlight` format and its `client`/`excerpt`/`website` fields have been removed from the blueprint; `wove-mind.php` still filters out any old entries with `format: project-highlight`. Fields: `title`, `blocks` (body content), `body` (writer, labelled "Excerpt / Lead"), `image` (featured image), author fields (`show_author`, `author`, `author_role`, `author_bio`) and relations (`case_study`, `services`, `sectors`, `tags`). `title`/`image`/`body` show for **every** format (see gap below).

`site/snippets/feed-card.php` renders entries in every feed (homepage, Our Work, service and tag pages, via `stream-card`). It picks a treatment by format and image: spark quote card, standard card with image, or compact card without one. Format labels show only for What If and Long Read; excerpts come from `body()->excerpt()` and are skipped for threads.

### Panel
Working locally — first-account installation completed, a real `project-highlight` entry ("Circular.ie," tagged Strategy) exists and renders correctly on the Strategy service page.

### Homepage feed (2026-09-24)
The homepage is `site/templates/wove-mind.php` (`'home' => 'wove-mind'` in `site/config/config.php`), not `home.php`.
- **Case studies are in the feed**: `kirby()->collection('case-studies')` is merged with listed Wove Mind entries and sorted by `date`, rendered through `stream-card`. Services come from `services`; tags come from `tags` (entries) or `impactAreas` (case studies), normalised to site tag slugs so both match the tag pills. The template loads `service-page-scripts` so case study cards get revealed.
- **Case study card styling in feeds**: `.feed-wrap .work-grid-card` in `feed.css` matches `.feed-card` (surface background, border, `--feed-radius-sm`, 16:10 image, padded body, regular-weight title). It overrides the `site.css` `.work-grid-card` rules (image radius, body margin), which still apply outside `.feed-wrap`.
- Clicking a filter pill scrolls back to the top of `.feed-shell` if the reader has scrolled past it (instant for reduced-motion users). `home.php` has the same behaviour.
- The footer link always shows: "See all {filter}" linking to `/our-work?filter=…` when a filter has more than 5 matches, otherwise "See all work" linking to `/our-work`. `.feed__footer` sets `display: flex`, so it needs an explicit `[hidden] { display: none; }` rule in `feed.css`.
- **Service promo card**: when a service filter is active, a `.feed-promo` card appears second in the grid (or after the only match). It links to `/our-work?filter=service:{slug}` and shows the service page's featured image as a thumbnail (a grey placeholder when none is set) above a centred outline button, "See all our {Service} work →". No card background or border.
- **Case study filters**: the rail also has a pill per case study (labelled by `eyebrow`, falling back to title), filtering with `cs:{slug}`. Items carry `data-casestudies`: a case study's own slug, or an entry's linked `case_study` pages. The promo for a case study filter reads "See case study →", links to the case study and uses its `caseStudyImages` image as the thumbnail. Promos and footer labels are keyed by the full filter string (`service:…`, `cs:…`).
- **Card tags**: `site/snippets/card-tags.php` (used by `feed-card.php` and `work-grid-card.php`) lists, in this order, a card's case study (the case study itself, or an entry's linked `case_study` pages, linking to `cs:{slug}`), services, editorial tags (`tags`, or `impactAreas` for case studies) and sectors as small blue links to `/our-work?filter=…`. The first four show; a "+N" button expands the rest (click handler in `service-page-scripts.php`). `.feed-wrap a` sets `color: inherit`, so tag colour rules need the `.feed-wrap` prefix.
- **`work-grid-card` markup**: the card is now a `<div>` with a stretched `.work-grid-card__link` overlay (same pattern as `.feed-card__link`), so the tag links inside it aren't nested anchors. Tags sit above the overlay with `z-index: 2`.
- **Author avatars on feed cards**: `feed-card.php` shows the author's Panel profile image, or their initials when none is uploaded. User accounts are gitignored, so profile images only exist on the server where they were uploaded.

### Service page blueprint (2026-09-24)
`site/blueprints/pages/service.yml` adds a **Featured image** field (`image`) to service pages, used by the homepage promo card. `strategy.yml`, `labs.yml`, `digital.yml` and `brand.yml` each just `extends: pages/service`, because it wasn't confirmed whether the service content files are named `service.txt` or after each service. Delete whichever set turns out unused.

### Wove Mind Panel plugin avatars (2026-09-24)
`site/plugins/wove-mind` shows the user's Kirby profile image (`$user->avatar()`, cropped to 96px) in the top bar and next to each author in the entries list, falling back to initials. `wove_mind_user_summary()` and `wove_mind_avatar_url()` in `index.php` supply the data. Rebuild with `npm run build` in the plugin folder after editing `src/`.

## Known gaps / open items

- **Kirby's `when` field condition only supports a single exact value, not a list.** Confirmed by reading `kirby/src/Form/Mixin/When.php` directly — `when: format: in: [...]` can never match (a string can never `===` an array), so any field using that pattern is permanently hidden regardless of format. This was already fixed on `title`/`image`/`body` in `wove-mind-entry.yml` (they now show unconditionally instead), but **if this pattern shows up in any new blueprint work, it won't work** — needs restructuring (e.g. per-value fields, or just showing unconditionally with an `info:` note).
- **No stored `excerpt` field for `thread`/`whatif`/`longread`** — `feed-card.php` computes its excerpt from `body()->excerpt()`. The original prototype scaffolds assumed a stored excerpt existed for those formats too; it doesn't. Flag if a real stored field is wanted there.
- **No "unlisted by default" mechanism for `spark`** — was only ever a comment/intention in the original CMS brief, never actually implemented. Kirby blueprints can't conditionally set page status from a field value; would need a page model hook (`site/models/`) or an editorial convention.
- **No `ogImage` field** on the SEO tab (`site/blueprints/tabs/seo.yml`) — `header.php`'s Open Graph tags don't include `og:image`.
- **`deploy-prod.yml` is broken** (pre-existing, unrelated to this work) — the whole file is commented out with no `on:`/`jobs:`, so GitHub Actions treats it as an invalid workflow and fails it on every push to any branch. Cosmetic (shows as a failed check) but worth cleaning up or removing.
- **Font weights 300/500/900 have no real files** — currently relying on CSS font-matching fallback to the nearest registered weight (see above). Fine visually, but if real Light/Medium/Black files ever turn up, add them.

## Local dev environment

PHP 8.3 (via `winget install --id PHP.PHP.8.3 --source winget`) — the winget build ships with no `php.ini` configured, needs `openssl`/`curl`/`mbstring`/`fileinfo`/`zip`/`gd`/`intl` enabled manually. Composer via a standalone `composer.phar` (no system install needed). Run `composer install` **at the project root** (not inside `kirby/`) — Kirby's custom installer (`getkirby/composer-installer`) redirects the package install into `./kirby/` directly rather than `vendor/getkirby/cms/`, which is also where the Panel's built frontend assets (`kirby/panel/dist/`, gitignored) come from. `composer start` (or `php -S localhost:8000 kirby/router.php`) boots the dev server.

## Deploy state

**2026-09-24:** today's work (avatars, case study reveal fix, homepage feed scroll, "See all" link, service promo card and blueprint) was fast-forwarded onto `dev` and deployed to staging. `main`/production untouched.

### As of 2026-07-14

`proto`'s full history was merged into `dev` (clean merge, no conflicts — an earlier blueprint conflict with Adam's parallel taxonomy/blocks/date-field work on `dev` resolved itself as both sides' changes evolved into non-overlapping regions) and pushed. The staging deploy ran successfully — everything above is live on `staging.wove.group`. `main`/production untouched.
