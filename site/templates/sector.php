<?php
/**
 * Sector collection page
 * File: site/templates/sector.php
 *
 * Shows all feed entries and case studies tagged with this sector.
 * Blueprint: site/blueprints/pages/sector.yml
 * Fields: headline, intro, sectorKey, heroImage, featured
 */

$sectorKey = $page->sectorKey()->value();

$allEntries = $site->index()->filter(function ($p) {
  return in_array($p->intendedTemplate()->name(), ['wove-mind-entry', 'case-study']);
})->filterBy('sectors', $sectorKey, ',')->listed()->sortBy('date', 'desc');

$featured = $page->featured()->toPages();

$perPage   = 12;
$paginated = $allEntries->paginate($perPage);
$pageNum   = param('page') ? (int) param('page') : 1;
?>

<?php snippet('header', ['css' => ['/assets/css/feed.css']]) ?>

<div class="feed-wrap">

  <section class="sector-hero">
    <p class="sector-hero__eyebrow">Sector</p>
    <h1 class="sector-hero__title"><?= $page->headline()->html() ?></h1>
    <?php if ($page->intro()->isNotEmpty()): ?>
      <p class="sector-hero__intro"><?= $page->intro()->html() ?></p>
    <?php endif ?>
  </section>

  <div class="feed-shell">

    <?php snippet('feed-rail', ['activeSector' => $sectorKey]) ?>

    <main class="feed" id="main">

      <div class="feed__header">
        <h2 class="feed__title">In this collection</h2>
        <span class="feed__showing label"><?= $allEntries->count() ?> entries</span>
      </div>

      <?php if ($featured->count()): ?>
        <div class="feed-cards" style="margin-bottom: var(--feed-space-32);">
          <?php foreach ($featured as $post): ?>
            <?php snippet('feed-card', ['post' => $post]) ?>
          <?php endforeach ?>
        </div>
      <?php endif ?>

      <div class="feed-cards">
        <?php foreach ($paginated as $post): ?>
          <?php snippet('feed-card', ['post' => $post]) ?>
        <?php endforeach ?>
      </div>

      <?php if ($paginated->pagination()->hasPages()): ?>
        <div class="feed__footer">
          <?php if ($paginated->pagination()->hasNextPage()): ?>
            <a href="<?= $paginated->pagination()->nextPageUrl() ?>" class="feed__more">Load more &rarr;</a>
          <?php endif ?>
          <?php $remaining = $allEntries->count() - ($pageNum * $perPage); ?>
          <?php if ($remaining > 0): ?>
            <span class="feed__count label"><?= $remaining ?> more</span>
          <?php endif ?>
        </div>
      <?php endif ?>

    </main>

  </div>

  <footer class="feed-footer">
    <div class="feed-footer__inner">
      <span class="feed-footer__brand"><?= $site->title()->html() ?> &mdash; Strategic Design &amp; Technology</span>
      <div class="feed-footer__links">
        <a href="/contact#enquiries" class="feed-footer__link">Enquiries</a>
        <a href="/contact#tenders" class="feed-footer__link">Tenders</a>
        <a href="/contact#strategy" class="feed-footer__link">Strategy</a>
      </div>
      <span>&copy; <?= date('Y') ?></span>
    </div>
  </footer>

</div>

<script>
(function() {
  var h = new Date().getHours();
  if (h >= 7 && h < 19) {
    document.documentElement.setAttribute('data-theme', 'light');
  }
})();
</script>

<?php snippet('footer') ?>
