<?php
/**
 * Wove Mind page (/wove-mind, see the route in config.php), 2026-10-29:
 * every listed Wove Mind post in one feed, newest first, in three
 * columns of compact cards (2 on tablets, 1 on phones), 18 at a
 * time with "Load more". Projects / Thinking / News filter it in place,
 * and ?filter= links work as on the homepage, with their context panels.
 * The homepage is the Wove Mind page in the Panel ('home' => 'wove-mind'),
 * so this renders its data (site/controllers/wove-mind.php) through its
 * own template. Behaviour: assets/js/home.js; image heights: assets/js/feed.js.
 */

$perPage    = 18;
$totalCount = $entries->count();
$initial    = get('filter') ?: '*';
$intro      = $page->content()->get('feedIntro')->or('Ideas, experiments, project updates and news from across the studio.')->value();
?>
<?php snippet('header', [
  'css'       => ['/assets/css/home.css', '/assets/css/pages.css'],
  'nav'       => false,
  'metaTitle' => 'Wove Mind',
  'metaUrl'   => url('wove-mind'),
]) ?>

<div class="home wm" data-theme="light">

  <header class="hx hx--page">
    <div class="hx__inner">
      <?php snippet('brand/header-row', ['current' => 'wove-mind']) ?>
      <h1 class="cp-head__title">Wove Mind</h1>
      <div class="cp-head__lead">
        <p class="cp-head__summary"><?= html($intro) ?></p>
      </div>
    </div>
  </header>

  <div class="feed-wrap">
    <?php snippet('brand/menu-bar', ['current' => 'wove-mind']) ?>

    <main class="feed feed--home wm-feed" id="main">

      <div class="feed__header" id="feed">
        <h2 class="feed__title">Latest</h2>
        <?php if ($types): ?>
          <div class="feed-types" id="feed-types" role="group" aria-label="Filter by type">
            <?php foreach ($types as $key => $type): ?>
              <button type="button" class="feed-type" data-filter="<?= html($key) ?>" aria-pressed="false"><?= html($type['label']) ?> <span class="feed-type__count"><?= $type['count'] ?></span></button>
            <?php endforeach ?>
          </div>
        <?php endif ?>
      </div>

      <?php snippet('home/filters', ['clients' => $clients, 'topics' => $topics, 'authors' => $authors, 'sectors' => $sectors, 'totalCount' => $totalCount]) ?>

      <div class="feed-cards pc-grid pc-grid--cols-3 pc-grid--grid pc-grid--heights-set pc-grid--card-compact pc-grid--card-open" id="feed-grid" data-heights tabindex="-1" data-initial="<?= html($initial) ?>" data-url="<?= url('wove-mind') ?>" data-page="<?= $perPage ?>">
        <?php $i = 0; foreach ($entries as $entry): ?>
          <?php snippet('home/post-card', [
            'post'    => $entry,
            'filters' => $entryFilters[$entry->id()] ?? [],
            'hidden'  => $i++ >= $perPage,
          ]) ?>
        <?php endforeach ?>
        <p class="feed-empty" id="feed-empty" hidden>No posts match this filter yet.</p>
      </div>

      <div class="feed-more" id="feed-more"<?= $totalCount > $perPage ? '' : ' hidden' ?>>
        <span class="feed-more__count" id="feed-more-count">Showing <?= min($perPage, $totalCount) ?> of <?= $totalCount ?></span>
        <button type="button" class="feed-more__btn" id="feed-load-more">Load more</button>
      </div>

    </main>
  </div>
</div>

<script src="<?= wove_asset('/assets/js/home.js') ?>" defer></script>
<?php snippet('footer') ?>
