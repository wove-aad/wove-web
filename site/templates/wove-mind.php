<?php
/**
 * Homepage (the Wove Mind page; 'home' => 'wove-mind' in config.php)
 * File: site/templates/wove-mind.php
 * Data: site/controllers/wove-mind.php
 *
 * Hero ("One voice"): statement and description as one paragraph, with
 * links to Our work and Our people. The page's own nav is in the hero, so
 * the global header nav is left out.
 *
 * Wove latest: Projects, Thinking and News beside the heading filter the
 * feed in place, one filter at a time (choosing the active one again shows
 * everything). ?filter= links (Impact areas, authors) still filter, with
 * their context panel. The feed shows 12 cards with "Load more". A pinned
 * bar repeats the type filters once the heading has scrolled away.
 * Behaviour: assets/js/home.js. Styles: assets/css/home.css.
 */

$totalCount = $entries->count();
// Shareable filtered views: /?filter=service:strategy (Our work, service,
// tag and sector pages redirect here). Unknown keys fall back to all work
// in home.js.
$initial = get('filter') ?: '*';
?>

<?php snippet('header', ['css' => ['/assets/css/home.css', '/assets/css/pages.css'], 'nav' => false]) ?>

<div class="home" data-theme="light">

  <?php snippet('home/hero', ['clients' => $clients, 'people' => $people]) ?>
  <?php snippet('home/intro') ?>

  <div class="feed-wrap">

    <?php snippet('home/filter-bar', ['types' => $types, 'totalCount' => $totalCount]) ?>

    <main class="feed feed--home" id="main">

      <div class="feed__header" id="feed">
        <h2 class="feed__title">Wove latest</h2>
        <?php if ($types): ?>
          <div class="feed-types" id="feed-types" role="group" aria-label="Filter by type">
            <?php foreach ($types as $key => $type): ?>
              <button type="button" class="feed-type" data-filter="<?= html($key) ?>" aria-pressed="false"><?= html($type['label']) ?> <span class="feed-type__count"><?= $type['count'] ?></span></button>
            <?php endforeach ?>
          </div>
        <?php endif ?>
      </div>

      <?php snippet('home/filters', ['clients' => $clients, 'topics' => $topics, 'authors' => $authors, 'sectors' => $sectors, 'totalCount' => $totalCount]) ?>

      <div class="feed-cards pc-grid" id="feed-grid" tabindex="-1" data-initial="<?= html($initial) ?>" data-url="<?= url() ?>/" data-page="<?= $feedLimit ?>">
        <?php $i = 0; foreach ($entries as $entry): ?>
          <?php snippet('home/post-card', [
            'post'    => $entry,
            'filters' => $entryFilters[$entry->id()] ?? [],
            'hidden'  => $i++ >= $feedLimit,
          ]) ?>
        <?php endforeach ?>
        <p class="feed-empty" id="feed-empty" hidden>No entries match this filter yet.</p>
      </div>

      <div class="feed-more" id="feed-more"<?= $totalCount > $feedLimit ? '' : ' hidden' ?>>
        <span class="feed-more__count" id="feed-more-count">Showing <?= min($feedLimit, $totalCount) ?> of <?= $totalCount ?></span>
        <button type="button" class="feed-more__btn" id="feed-load-more">Load more</button>
      </div>

    </main>


  </div>
</div>

<script src="<?= wove_asset('/assets/js/home.js') ?>" defer></script>

<script>
// Footer follows the site's time-of-day palette; the homepage itself is light.
(function () {
  var h = new Date().getHours();
  if (h >= 7 && h < 19) document.documentElement.setAttribute('data-theme', 'light');
})();
</script>

<?php snippet('service-page-scripts') ?>
<?php snippet('footer') ?>
