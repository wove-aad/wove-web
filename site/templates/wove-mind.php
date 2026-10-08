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
 * Recent work: a client carousel (one card per case study) and topic pills
 * filter the feed in place, one filter at a time. Selecting a client opens
 * its case study panel. The feed shows 12 cards; "See all" goes to Our Work
 * with the active filter. A pinned bar repeats the filters and holds the
 * site menu once the full controls have scrolled away.
 * Behaviour: assets/js/home.js. Styles: assets/css/home.css.
 */

$totalCount = $entries->count();
?>

<?php snippet('header', ['css' => ['/assets/css/home.css'], 'nav' => false]) ?>

<div class="home" data-theme="light">

  <?php snippet('home/hero', ['clients' => $clients, 'people' => $people]) ?>
  <?php snippet('home/intro') ?>

  <div class="feed-wrap">

    <?php snippet('home/filter-bar', ['clients' => $clients, 'topics' => $topics, 'totalCount' => $totalCount]) ?>

    <main class="feed feed--home" id="main">

      <div class="feed__header" id="feed">
        <h2 class="feed__title">Recent work</h2>
      </div>

      <?php snippet('home/filters', ['clients' => $clients, 'topics' => $topics, 'totalCount' => $totalCount]) ?>

      <div class="feed-cards pc-grid" id="feed-grid" data-limit="<?= $feedLimit ?>">
        <?php $i = 0; foreach ($entries as $entry): ?>
          <?php snippet('home/post-card', [
            'post'    => $entry,
            'filters' => $entryFilters[$entry->id()] ?? [],
            'hidden'  => $i++ >= $feedLimit,
          ]) ?>
        <?php endforeach ?>
        <p class="feed-empty" id="feed-empty" hidden>No entries match this filter yet.</p>
      </div>

      <div class="feed-more" id="feed-more">
        <span class="feed-more__count" id="feed-more-count"<?= $totalCount > $feedLimit ? '' : ' hidden' ?>>Showing <?= min($feedLimit, $totalCount) ?> of <?= $totalCount ?></span>
        <a href="<?= url('our-work') ?>" class="feed-more__btn" id="feed-more-link">See all work <span aria-hidden="true">&rarr;</span></a>
      </div>

    </main>

    <!-- PROCESS BLOCK (to be designed) -->
    <section class="feed-process">
      <div class="feed-process__intro">
        <h2 class="feed-process__title">Transforming process</h2>
        <p class="feed-process__desc">How strategic design reshapes the way organisations move from insight through to delivery.</p>
      </div>
      <div class="feed-process__diagram">
        <div class="feed-process__ph">Double Diamond / Process Diagram Placeholder</div>
      </div>
    </section>

  </div>
</div>

<script src="/assets/js/home.js" defer></script>

<script>
// Footer follows the site's time-of-day palette; the homepage itself is light.
(function () {
  var h = new Date().getHours();
  if (h >= 7 && h < 19) document.documentElement.setAttribute('data-theme', 'light');
})();
</script>

<?php snippet('service-page-scripts') ?>
<?php snippet('footer') ?>
