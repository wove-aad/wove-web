<?php
/**
 * Wove Mind — feed home template (Our Work)
 * File: site/templates/wove-mind.php
 *
 * New feed design: dark-first palette with time-of-day light mode,
 * facet rail (explainer + sectors + topics), flat card grid mixing
 * all entry formats, author avatars, hero above the fold.
 *
 * Loads feed.css alongside site.css via the header snippet's $css slot.
 */

$entries = $page->children()->listed()->sortBy('date', 'desc')
  ->filter(fn ($p) => $p->format()->value() !== 'project-highlight');

$perPage   = 10;
$pageNum   = param('page') ? (int) param('page') : 1;
$paginated = $entries->paginate($perPage);
?>

<?php snippet('header', ['css' => ['/assets/css/feed.css']]) ?>

<div class="feed-wrap">

  <!-- HERO -->
  <section class="feed-hero">
    <h1 class="feed-hero__title"><?= $page->headline()->or('Shaping a better Ireland through strategic design and technology')->html() ?></h1>
    <p class="feed-hero__desc"><?= $page->tagline()->or('We help the people running Ireland\'s public services, cultural institutions, and mission-led organisations move from strategy to delivery.')->html() ?></p>
  </section>

  <div class="feed-shell">

    <?php snippet('feed-rail') ?>

    <!-- FEED -->
    <main class="feed" id="main">

      <div class="feed__header">
        <h2 class="feed__title">Latest from the studio</h2>
        <span class="feed__showing label"><?= $entries->count() ?> entries</span>
      </div>

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
          <?php $remaining = $entries->count() - ($pageNum * $perPage); ?>
          <?php if ($remaining > 0): ?>
            <span class="feed__count label"><?= $remaining ?> more entries</span>
          <?php endif ?>
        </div>
      <?php endif ?>

    </main>

  </div>

  <!-- PROCESS BLOCK -->
  <section class="feed-process">
    <div class="feed-process__intro">
      <h2 class="feed-process__title">Transforming process</h2>
      <p class="feed-process__desc">How strategic design reshapes the way organisations move from insight through to delivery.</p>
    </div>
    <div class="feed-process__diagram">
      <div class="feed-process__ph">Double Diamond / Process Diagram Placeholder</div>
    </div>
  </section>

  <!-- FEED FOOTER (simple) -->
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
