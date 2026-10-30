<?php
/**
 * Work page (/work, the case studies' parent page), from the Figma
 * wireframe "Work (updated)" (1208:770), 2026-10-30.
 *
 * A lean header (title and one line), then the filter bar: themes (impact
 * areas) and disciplines (services) as multi-select chips, and a
 * searchable client dropdown that doubles as a client directory (each
 * client links to its client page). All filters combine with AND. With a
 * client selected, a line above the grid names it and links to its page.
 * The grid holds every project post (wove_post_type() 'project'), newest
 * first, or oldest first for one client, 12 at a time with "Load more".
 * Filters come in from the address (?theme=a,b&discipline=labs&client=x)
 * and the address follows them.
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

// Clients: every listed case study, A to Z, with its number of units
$unitCounts = [];
foreach ($units as $p) foreach ($valuesOf($p, 'cs') as $c) $unitCounts[$c] = ($unitCounts[$c] ?? 0) + 1;
$clients = $feed['clients'];
usort($clients, fn ($a, $b) => strcasecmp($a['name'], $b['name']));

$total = count($units);
?>
<?php snippet('header', [
  'css'       => ['/assets/css/home.css', '/assets/css/pages.css'],
  'nav'       => false,
  'metaTitle' => $page->seoTitle()->or('Work')->value(),
]) ?>

<div class="home wk" data-theme="light">

  <header class="hx hx--page wk-head">
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

      <div class="wk-filters" data-work-filters aria-label="Filter work" role="group">
        <?php if ($themes): ?>
          <div class="wk-filter">
            <span class="wk-filter__label" id="wk-themes">Themes</span>
            <div class="wk-chips" role="group" aria-labelledby="wk-themes">
              <?php foreach ($themes as $slug => $name): ?>
                <button type="button" class="wk-chip" data-group="theme" data-value="<?= html($slug) ?>" aria-pressed="false"><?= html($name) ?></button>
              <?php endforeach ?>
            </div>
          </div>
        <?php endif ?>

        <div class="wk-filter">
          <span class="wk-filter__label" id="wk-disciplines">Disciplines</span>
          <div class="wk-chips" role="group" aria-labelledby="wk-disciplines">
            <?php foreach ($services as $slug => $name): ?>
              <button type="button" class="wk-chip" data-group="discipline" data-value="<?= html($slug) ?>" aria-pressed="false"><?= html($name) ?></button>
            <?php endforeach ?>
          </div>
        </div>

        <?php if ($clients): ?>
          <div class="wk-filter">
            <span class="wk-filter__label" id="wk-clients">Client</span>
            <div class="wk-client" data-client-picker>
              <button type="button" class="wk-client__toggle" aria-expanded="false" aria-controls="wk-client-panel" aria-labelledby="wk-clients wk-client-current">
                <span id="wk-client-current" data-client-current>All clients</span>
                <span class="hx-menu__caret" aria-hidden="true"></span>
              </button>
              <div class="wk-client__panel" id="wk-client-panel">
                <input type="search" class="wk-client__search" placeholder="Search clients" aria-label="Search clients" autocomplete="off" data-client-search hidden>
                <ul class="wk-client__list" role="list">
                  <?php foreach ($clients as $client): $n = $unitCounts[$client['slug']] ?? 0 ?>
                    <li class="wk-client__row" data-client-row data-name="<?= html(Str::lower($client['name'])) ?>">
                      <button type="button" class="wk-client__pick" data-client="<?= html($client['slug']) ?>" data-label="<?= html($client['name']) ?>" data-url="<?= $client['page']->url() ?>" aria-pressed="false" hidden>
                        <?= html($client['name']) ?> <span class="wk-client__count"><?= $n ?></span>
                      </button>
                      <span class="wk-client__name" data-client-name><?= html($client['name']) ?></span>
                      <a class="wk-client__page" href="<?= $client['page']->url() ?>">See client page <span aria-hidden="true">&rarr;</span></a>
                    </li>
                  <?php endforeach ?>
                </ul>
                <p class="wk-client__none" data-client-none hidden>No clients match.</p>
              </div>
            </div>
          </div>
        <?php endif ?>
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
