<?php
/**
 * Impact area page (/tag/{slug}, see the route in config.php), from the
 * Figma "Impact Area" frame (1240:9091), 2026-10-29. Impact areas are the
 * editorial tags in the site's tag list (Panel: Site > Global Site
 * Taxonomies). Sector slugs on this route still go to the work feed.
 *
 * - A strip of every published impact area with work, to move between
 *   them: soft chips, the current one lifted in white.
 * - Opening on a mint band: the name, then the tag's intro set and placed
 *   like the client page lead, and an optional "Currently interested in"
 *   card (the tag's own field) under it.
 * - Browse work: the area's posts and case studies, the newest large
 *   beside the next two (client page styles), with type filters.
 * - A collection: the newest case study in the area, its question and the
 *   first sentence of its summary, and its posts in a row that scrolls
 *   sideways.
 * - More work (the collection's case study isn't repeated), then
 *   "Explore more {area} work" (the filtered feed).
 * The Figma's newsletter and closing call to action are left to the site
 * footer for now.
 * Styles: the IMPACT AREA PAGE section of assets/css/pages.css.
 */

$filterField = $filterField ?? 'tags';
$filterValue = $filterValue ?? '';
if ($filterField !== 'tags') {
  snippet('feed/work-view', ['initial' => 'sector:' . $filterValue]);
  return;
}

$slug     = $tagSlug ?? $filterValue;
$siteTags = $site->tags()->toStructure()->filter(fn ($t) => $t->active()->toBool() !== false);
$tag      = $siteTags->findBy('slug', $slug);
$name     = $tag ? $tag->name()->value() : ($tagName ?? $slug);
$intro    = $tag ? $tag->intro()->or($tag->subtitle())->value() : ($tagIntro ?? '');
$intro    = $intro ?: wove_tag_intro($slug, $name);
$interest = $tag ? $tag->interest()->value() : '';
$feed     = wove_feed();
$key      = 'tag:' . $slug;

// Every published impact area (wove_impact_areas()), in the site's order
$areas = [];
foreach (wove_impact_areas() as $s => $n) $areas[] = ['slug' => $s, 'name' => $n];
$swatches = ['coral', 'aqua', 'lilac', 'mint', 'stone-200'];

// The area's posts and case studies, newest first (no sparks)
$items = array_values(array_filter($feed['items'], fn ($p) =>
  in_array($key, $feed['keys'][$p->id()] ?? [], true) && $p->format()->value() !== 'spark'));
// Collection: the newest case study in the area and its posts. It isn't
// repeated in the grids.
$case = null;
foreach ($items as $p) {
  if ($p->intendedTemplate()->name() === 'case-study') { $case = $p; break; }
}
$listed = array_values(array_filter($items, fn ($p) => $p !== $case));
$browse = array_slice($listed, 0, 5);
$more   = array_slice($listed, 5, 6);
$casePosts = $case && ($wm = $site->find('wove-mind'))
  ? $wm->children()->listed()->filter(fn ($p) => $p->case_study()->toPages()->has($case) && $p->format()->value() !== 'spark')->sortBy('date', 'desc')
  : null;

$types = [];
foreach (array_merge($browse, $more) as $p) $types[wove_post_type($p)] = true;

$imageOf = fn ($p) => $p->intendedTemplate()->name() === 'case-study'
  ? $p->caseStudyImages()->toFile()
  : $p->content()->get('image')->toFile();
$titleOf = fn ($p) => $p->intendedTemplate()->name() === 'case-study'
  ? ($p->heroTitle()->isNotEmpty() ? strip_tags($p->heroTitle()->value()) : $p->title()->value())
  : $p->title()->value();
$lineOf = fn ($p) => wove_card_excerpt($p->intendedTemplate()->name() === 'case-study'
  ? (string) $p->summary()->or($p->subStatement())->value()
  : (string) $p->body()->value());

$card = function ($p, bool $pinned = false) use ($imageOf, $titleOf, $lineOf) {
  $type  = wove_post_type($p);
  $image = $imageOf($p);
  $line  = $lineOf($p);
  $client = $p->intendedTemplate()->name() === 'case-study' ? $p : $p->case_study()->toPages()->first();
  $client = $client ? $client->eyebrow()->or($client->title())->value() : '';
  ?>
  <li class="cp-post" data-type="<?= $type ?>">
    <a class="cp-post__link" href="<?= $p->url() ?>">
      <span class="cp-post__media"<?= $image && $image->height() ? ' style="--ratio: ' . $image->width() . ' / ' . $image->height() . '"' : '' ?>>
        <?php if ($image): ?>
          <?php snippet('picture', [
            'file'   => $image,
            'widths' => [480, 720, 960, 1440],
            'sizes'  => $pinned ? '(min-width: 960px) 60vw, 100vw' : '(min-width: 960px) 30vw, 100vw',
            'class'  => 'cp-post__image',
          ]) ?>
        <?php endif ?>
        <span class="pc__pill"><?= html(wove_post_pill($p)) ?></span>
      </span>
      <span class="cp-post__title"><?= html($titleOf($p)) ?></span>
      <?php if ($client): ?><span class="cp-post__client"><?= html($client) ?></span><?php endif ?>
      <?php if ($line): ?><span class="cp-post__line"><?= html($line) ?></span><?php endif ?>
    </a>
  </li>
<?php };
?>
<?php snippet('header', [
  'css'             => ['/assets/css/home.css', '/assets/css/pages.css'],
  'nav'             => false,
  'metaTitle'       => $name,
  'metaDescription' => $tag ? $tag->meta()->or($intro)->value() : $intro,
  'metaUrl'         => url('tag/' . $slug),
]) ?>

<div class="home ia" data-theme="light">

  <header class="hx hx--page hx--aligned ia-head">
    <div class="hx__inner">
      <?php snippet('brand/header-row', ['current' => $key]) ?>

      <?php if (count($areas) > 1): ?>
        <nav class="ia-areas" aria-label="Impact areas">
          <ul class="ia-areas__list" role="list">
            <?php foreach ($areas as $i => $area): ?>
              <li>
                <a class="ia-area" href="<?= url('tag/' . $area['slug']) ?>"<?= $area['slug'] === $slug ? ' aria-current="page"' : '' ?>>
                  <span class="ia-area__dot" style="--dot: var(--color-<?= $swatches[$i % count($swatches)] ?>)" aria-hidden="true"></span>
                  <?= html($area['name']) ?>
                </a>
              </li>
            <?php endforeach ?>
          </ul>
        </nav>
      <?php endif ?>

      <h1 class="sv-head__title ia-head__title"><?= html($name) ?></h1>
      <?php if ($intro || $interest): ?>
        <div class="cp-head__lead ia-head__lead">
          <?php if ($intro): ?><p class="cp-head__summary"><?= html($intro) ?></p><?php endif ?>
          <?php if ($interest): ?>
            <div class="sv-interest ia-head__interest">
              <span class="sv-interest__kicker">Currently interested in</span>
              <span class="sv-interest__name"><?= html($interest) ?></span>
            </div>
          <?php endif ?>
        </div>
      <?php endif ?>
    </div>
  </header>

  <div class="feed-wrap">
    <?php snippet('brand/menu-bar', ['current' => $key]) ?>

    <main class="pg ia-main" id="main">

      <?php if ($browse): ?>
        <section class="cp-activity" aria-labelledby="ia-browse">
          <div class="cp-activity__head">
            <h2 class="cp-activity__title" id="ia-browse">Browse work</h2>
            <?php if (count($types) > 1): ?>
              <div class="feed-types" role="group" aria-label="Filter by type" data-activity-filters>
                <button type="button" class="feed-type" data-type="*" aria-pressed="true">All</button>
                <?php foreach (wove_post_type_labels() as $type => $label): if (empty($types[$type])) continue ?>
                  <button type="button" class="feed-type" data-type="<?= $type ?>" aria-pressed="false"><?= $label ?></button>
                <?php endforeach ?>
              </div>
            <?php endif ?>
          </div>
          <ul class="cp-posts feed-varied" role="list" data-activity data-heights>
            <?php foreach ($browse as $i => $p) $card($p, $i === 0) ?>
          </ul>
        </section>
      <?php else: ?>
        <p class="feed-empty">No work in this area yet.</p>
      <?php endif ?>

    </main>

    <?php if ($case && $casePosts && $casePosts->count()):
      $caseName = $case->eyebrow()->or($case->title())->value();
    ?>
      <section class="ia-collection" aria-labelledby="ia-collection">
        <div class="ia-collection__inner">
          <h2 class="ia-collection__title" id="ia-collection"><?= html($titleOf($case)) ?> <span class="ia-collection__client"><?= html($caseName) ?></span></h2>
          <?php if ($statement = wove_first_sentence((string) $case->summary()->or($case->subStatement())->value())): ?>
            <p class="ia-collection__text"><?= html($statement) ?></p>
          <?php endif ?>
        </div>
        <ul class="ia-pieces" role="list">
          <?php foreach ($casePosts as $p):
            $image = $imageOf($p);
            $services = array_values(array_intersect_key(wove_service_labels(), array_flip($p->services()->split(','))));
          ?>
            <li class="ia-piece">
              <a class="ia-piece__link" href="<?= $p->url() ?>">
                <span class="ia-piece__media">
                  <?php if ($image): ?>
                    <?php snippet('picture', ['file' => $image, 'widths' => [380, 760], 'ratio' => 38 / 25, 'sizes' => '23.75rem', 'class' => 'ia-piece__image']) ?>
                  <?php endif ?>
                  <span class="pc__pill"><?= html(wove_post_pill($p)) ?></span>
                </span>
                <span class="ia-piece__title"><?= $p->title()->html() ?></span>
                <?php if ($services): ?><span class="ia-piece__meta"><?= html(implode(', ', $services)) ?></span><?php endif ?>
              </a>
            </li>
          <?php endforeach ?>
        </ul>
        <div class="ia-collection__inner">
          <a class="ia-collection__link" href="<?= $case->url() ?>">See the <?= html($caseName) ?> project <span aria-hidden="true">&rarr;</span></a>
        </div>
      </section>
    <?php endif ?>

    <?php if ($browse): ?>
      <div class="pg ia-more">
        <?php if ($more): ?>
          <ul class="cp-posts feed-varied" role="list" data-activity data-flat data-heights>
            <?php foreach ($more as $p) $card($p) ?>
          </ul>
        <?php endif ?>
        <a class="cp-explore" href="<?= wove_work_url($key) ?>">Explore more <?= html($name) ?> work <span aria-hidden="true">&rarr;</span></a>
      </div>
    <?php endif ?>
  </div>
</div>

<script src="<?= wove_asset('/assets/js/pages.js') ?>" defer></script>
<?php snippet('footer') ?>
