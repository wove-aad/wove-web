<?php
/**
 * Work page (/work, the case studies' parent page), from the Figma
 * wireframe "Work (updated)" (1208:770), 2026-10-30.
 *
 * A lean header (title and one line), then the filters (2026-10-30, the
 * homepage's earlier filter bar brought back): a client carousel (one
 * card per client with projects, All work first; choosing one again
 * clears it), then the services as chips in a row and the impact areas
 * in a dropdown, both multi-select. All filters combine with AND. With a
 * client selected, a line above the grid names it and links to its page.
 * The grid holds every project post (wove_post_type() 'project'), newest
 * first, or oldest first for one client, 12 at a time with "Load more".
 * Filters come in from the address (?theme=a,b&discipline=labs&client=x)
 * and the address follows them.
 * Two layouts for review (?filters=top|side, kept per browser): top (the
 * services and the impact areas dropdown in a row under the carousel)
 * and side (they sit in a column beside a two-column grid, the impact
 * areas as chips).
 * Styles: the WORK PAGE section of assets/css/pages.css.
 * Behaviour: assets/js/work.js; image heights: assets/js/feed.js.
 */

$feed     = wove_feed();
$perPage  = 12;
$services = wove_service_labels();
$siteTags = $site->tags()->toStructure()->filter(fn ($t) => $t->active()->toBool() !== false);
$intro    = $page->intro()->or('Projects from across the studio, with clients in culture, public service, education and business.')->value();

$units = array_values(array_filter($feed['entries']->values(), fn ($p) =>
  wove_post_type($p) === 'project' && !in_array($p->format()->value(), ['spark', 'whatif'], true)));

// Each unit's filter values, from the feed keys (tag:x, service:y, cs:z)
$valuesOf = function ($p, string $prefix) use ($feed): array {
  $out = [];
  foreach ($feed['keys'][$p->id()] ?? [] as $key) {
    if (str_starts_with($key, $prefix . ':')) $out[] = substr($key, strlen($prefix) + 1);
  }
  return $out;
};

// Themes in use, in the site order
$usedTags = [];
foreach ($units as $p) foreach ($valuesOf($p, 'tag') as $t) $usedTags[$t] = true;
$themes = [];
foreach ($siteTags as $tag) {
  if (isset($usedTags[$tag->slug()->value()])) $themes[$tag->slug()->value()] = $tag->name()->value();
}

// Clients with projects, in the case studies' order (newest first), with
// their number of projects
$unitCounts = [];
foreach ($units as $p) foreach ($valuesOf($p, 'cs') as $c) $unitCounts[$c] = ($unitCounts[$c] ?? 0) + 1;
$clients = array_values(array_filter($feed['clients'], fn ($c) => !empty($unitCounts[$c['slug']])));

$total = count($units);
?>
<?php snippet('header', [
  'css'       => ['/assets/css/home.css', '/assets/css/pages.css'],
  'nav'       => false,
  'metaTitle' => $page->seoTitle()->or('Work')->value(),
]) ?>

<div class="home wk" data-theme="light">
  <script>
    // Filter layout for review (?filters=top|side, kept per browser), see assets/js/work.js
    (function (el) { try {
      var m = (location.search.match(/[?&]filters=(top|side)\b/) || [])[1];
      if (m) localStorage.setItem('wove.workFilters', m);
      el.setAttribute('data-filter-layout', localStorage.getItem('wove.workFilters') || 'top');
    } catch (e) { el.setAttribute('data-filter-layout', 'top'); } })(document.currentScript.parentElement);
  </script>

  <header class="hx hx--page hx--aligned wk-head">
    <div class="hx__inner">
      <?php snippet('brand/header-row', ['current' => 'work']) ?>
      <h1 class="cp-head__title">Work</h1>
      <div class="cp-head__lead">
        <p class="cp-head__summary"><?= html($intro) ?></p>
      </div>
    </div>
  </header>

  <div class="feed-wrap">
    <?php snippet('brand/menu-bar', ['current' => 'work']) ?>

    <main class="pg wk-main" id="main">

      <div class="wk-filters" data-work-filters>

        <?php if ($clients): ?>
          <section class="client-carousel" aria-label="Filter by client" data-carousel>
            <ul class="client-carousel__track" role="list">
              <li class="client-card client-card--all">
                <button type="button" class="client-card__btn" data-client="" aria-pressed="true">
                  <span class="client-card__logo">All work</span>
                  <span class="client-card__meta"><?= $total ?> <?= $total === 1 ? 'project' : 'projects' ?></span>
                </button>
              </li>
              <?php foreach ($clients as $client):
                $cs    = $client['page'];
                $image = $cs->caseStudyImages()->toFile();
                $logo  = $cs->logo()->toFile();
                $n     = $unitCounts[$client['slug']];
              ?>
                <li class="client-card">
                  <button type="button" class="client-card__btn" data-client="<?= html($client['slug']) ?>" data-label="<?= html($client['name']) ?>" data-url="<?= $cs->url() ?>" aria-pressed="false">
                    <?php if ($image): ?>
                      <?php snippet('picture', [
                        'file'   => $image,
                        'widths' => [320, 480, 640, 960],
                        'ratio'  => 5 / 4,
                        'sizes'  => '(min-width: 961px) 20vw, (min-width: 601px) 40vw, 70vw',
                        'class'  => 'client-card__img',
                      ]) ?>
                    <?php endif ?>
                    <span class="client-card__logo">
                      <?php if ($logo): ?><img src="<?= $logo->url() ?>" alt="<?= html($client['name']) ?>"><?php else: ?><?= html($client['name']) ?><?php endif ?>
                    </span>
                    <span class="client-card__meta"><?= html($client['name']) ?> · <?= $n ?> <?= $n === 1 ? 'project' : 'projects' ?></span>
                  </button>
                </li>
              <?php endforeach ?>
            </ul>
            <div class="client-carousel__controls">
              <span class="client-carousel__bar" aria-hidden="true"><span></span></span>
              <div class="client-carousel__arrows">
                <button type="button" class="client-carousel__arrow" data-step="-1" aria-label="Previous clients">&larr;</button>
                <button type="button" class="client-carousel__arrow" data-step="1" aria-label="Next clients">&rarr;</button>
              </div>
            </div>
          </section>
        <?php endif ?>

        <div class="wk-row">
          <span class="wk-row__label" aria-hidden="true">Services</span>
          <div class="wk-chips" role="group" aria-label="Filter by service">
            <?php foreach ($services as $slug => $name): ?>
              <button type="button" class="wk-chip" data-group="discipline" data-value="<?= html($slug) ?>" aria-pressed="false"><?= html($name) ?></button>
            <?php endforeach ?>
          </div>

          <?php if ($themes): ?>
            <span class="wk-row__label" aria-hidden="true">Impact areas</span>
            <div class="wk-drop" data-drop>
              <button type="button" class="wk-drop__toggle" aria-expanded="false" aria-controls="wk-areas">Impact areas<span class="wk-drop__n" data-group-count="theme"></span> <span class="hx-menu__caret" aria-hidden="true"></span></button>
              <div class="wk-drop__panel" id="wk-areas">
                <div class="wk-chips" role="group" aria-label="Filter by impact area">
                  <?php foreach ($themes as $slug => $name): ?>
                    <button type="button" class="wk-chip" data-group="theme" data-value="<?= html($slug) ?>" aria-pressed="false"><?= html($name) ?></button>
                  <?php endforeach ?>
                </div>
              </div>
            </div>
          <?php endif ?>
        </div>

      </div>

      <div class="wk-status">
        <p class="wk-status__count" aria-live="polite" data-work-count><?= $total ?> <?= $total === 1 ? 'project' : 'projects' ?></p>
        <button type="button" class="wk-status__clear" data-work-clear hidden>Clear filters</button>
      </div>

      <p class="wk-active" data-client-active hidden>
        Showing work for <strong data-client-active-name></strong>
        <span aria-hidden="true">&middot;</span>
        <a href="#" data-client-active-link>See <span data-client-active-label></span>&rsquo;s full page <span aria-hidden="true">&rarr;</span></a>
      </p>

      <ul class="wk-grid feed-varied" role="list" data-work-grid data-heights data-page="<?= $perPage ?>">
        <?php foreach ($units as $i => $p):
          $image  = $p->content()->get('image')->toFile();
          $client = $p->case_study()->toPages()->first();
          $themeSlugs = $valuesOf($p, 'tag');
          $line   = $p->body()->isNotEmpty() ? wove_card_excerpt((string) $p->body()->value()) : '';
        ?>
          <li class="cp-post wk-unit"
              data-theme-values="<?= html(implode(' ', $themeSlugs)) ?>"
              data-discipline-values="<?= html(implode(' ', $valuesOf($p, 'service'))) ?>"
              data-client="<?= html(implode(' ', $valuesOf($p, 'cs'))) ?>"
              data-date="<?= $p->date()->toTimestamp() ?: 0 ?>"
              <?= $i >= $perPage ? 'data-later' : '' ?>>
            <span class="cp-post__media"<?= $image && $image->height() ? ' style="--ratio: ' . $image->width() . ' / ' . $image->height() . '"' : '' ?>>
              <?php if ($image): ?>
                <?php snippet('picture', [
                  'file'   => $image,
                  'widths' => [480, 720, 960, 1440],
                  'sizes'  => '(min-width: 960px) 30vw, (min-width: 600px) 50vw, 100vw',
                  'class'  => 'cp-post__image',
                ]) ?>
              <?php endif ?>
              <span class="pc__pill"><?= html(wove_post_pill($p)) ?></span>
            </span>
            <?php if ($client): ?>
              <a class="cp-post__client wk-unit__client" href="<?= $client->url() ?>"><?= html($client->eyebrow()->or($client->title())->value()) ?></a>
            <?php endif ?>
            <h3 class="cp-post__title"><a class="wk-unit__link" href="<?= $p->url() ?>"><?= $p->title()->html() ?></a></h3>
            <?php if ($line): ?><p class="cp-post__line"><?= html($line) ?></p><?php endif ?>
          </li>
        <?php endforeach ?>
      </ul>
      <p class="feed-empty wk-empty" data-work-empty<?= $total ? ' hidden' : '' ?>>No projects match these filters.</p>

      <div class="feed-more" data-work-more<?= $total > $perPage ? '' : ' hidden' ?>>
        <span class="feed-more__count" data-work-more-count>Showing <?= min($perPage, $total) ?> of <?= $total ?></span>
        <button type="button" class="feed-more__btn" data-work-load>Load more</button>
      </div>

    </main>
  </div>
</div>

<script src="<?= wove_asset('/assets/js/pages.js') ?>" defer></script>
<script src="<?= wove_asset('/assets/js/work.js') ?>" defer></script>
<?php snippet('footer') ?>
