<?php
/**
 * Work page (/work, the case studies' parent page), from the Figma
 * wireframe "Work (updated)" (1208:770), 2026-10-30.
 *
 * A lean header (title and one line), then the filters in a column
 * beside the grid (2026-10-30): clients (a small image, the name and the
 * number of projects; eight, then "Show all"), services and impact areas
 * as chips. One client at a time, any number of services and impact
 * areas, all combined with AND. With a client chosen, a card in second
 * place in the grid names it and links to its page. Below 961px the
 * filters sit above the grid, the clients in a row that swipes.
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
          <section class="wk-group" aria-labelledby="wk-clients">
            <h2 class="wk-group__label" id="wk-clients">Clients</h2>
            <ul class="wk-clients" role="list">
              <?php foreach ($clients as $i => $client):
                $cs    = $client['page'];
                $image = $cs->caseStudyImages()->toFile();
                $n     = $unitCounts[$client['slug']];
              ?>
                <li<?= $i >= 8 ? ' data-more-client' : '' ?>>
                  <button type="button" class="wk-cl" data-client="<?= html($client['slug']) ?>" data-label="<?= html($client['name']) ?>" data-url="<?= $cs->url() ?>"<?= $image ? ' data-img="' . $image->crop(640, 480)->url() . '"' : '' ?> aria-pressed="false">
                    <?php if ($image): ?>
                      <img class="wk-cl__thumb" src="<?= $image->crop(80, 80)->url() ?>" alt="" width="40" height="40" loading="lazy">
                    <?php else: ?>
                      <span class="wk-cl__thumb wk-cl__thumb--initials" aria-hidden="true"><?= html(wove_initials($client['name'])) ?></span>
                    <?php endif ?>
                    <span class="wk-cl__name"><?= html($client['name']) ?></span>
                    <span class="wk-cl__count"><?= $n ?></span>
                  </button>
                </li>
              <?php endforeach ?>
            </ul>
            <?php if (count($clients) > 8): ?>
              <button type="button" class="wk-group__more" data-more-clients hidden>Show all <?= count($clients) ?> clients</button>
            <?php endif ?>
          </section>
        <?php endif ?>

        <section class="wk-group" aria-labelledby="wk-services">
          <h2 class="wk-group__label" id="wk-services">Services</h2>
          <div class="wk-chips">
            <?php foreach ($services as $slug => $name): ?>
              <button type="button" class="wk-chip" data-group="discipline" data-value="<?= html($slug) ?>" aria-pressed="false"><?= html($name) ?></button>
            <?php endforeach ?>
          </div>
        </section>

        <?php if ($themes): ?>
          <section class="wk-group" aria-labelledby="wk-areas">
            <h2 class="wk-group__label" id="wk-areas">Impact areas</h2>
            <div class="wk-chips">
              <?php foreach ($themes as $slug => $name): ?>
                <button type="button" class="wk-chip" data-group="theme" data-value="<?= html($slug) ?>" aria-pressed="false"><?= html($name) ?></button>
              <?php endforeach ?>
            </div>
          </section>
        <?php endif ?>

      </div>

      <div class="wk-status">
        <p class="wk-status__count" aria-live="polite" data-work-count><?= $total ?> <?= $total === 1 ? 'project' : 'projects' ?></p>
        <button type="button" class="wk-status__clear" data-work-clear hidden>Clear filters</button>
      </div>

      <ul class="wk-grid feed-varied" role="list" data-work-grid data-heights data-page="<?= $perPage ?>">
        <li class="wk-showing" data-client-active hidden>
          <span class="wk-showing__media"><img alt="" data-client-active-img hidden></span>
          <span class="wk-showing__kicker">Showing work for</span>
          <strong class="wk-showing__name" data-client-active-name></strong>
          <a class="wk-showing__link" href="#" data-client-active-link>See the full client page <span aria-hidden="true">&rarr;</span></a>
        </li>
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
