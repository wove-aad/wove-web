<?php
/**
 * Tag destination template
 * File: site/templates/tag.php
 *
 * Reached via the route /tag/{slug} defined in config.php.
 * The route passes $tagSlug and $tagData (structure item from site.tags).
 * Displays: breadcrumb, tag hero, 3-column card grid, pagination, related tags.
 */

$tag     = $tagData ?? null;
$tagSlug = $tagSlug ?? '';

if (!$tag) go('/wove-mind');

$tagName  = $tag->name()->value();
$tagIntro = $tag->intro()->value();

$entries = $site->index()
  ->filter(fn ($p) => in_array($p->intendedTemplate()->name(), ['wove-mind-entry', 'case-study']))
  ->filterBy('tags', $tagName, ',')
  ->listed()
  ->sortBy('date', 'desc');

$caseStudies = $entries->filterBy('intendedTemplate', 'case-study');

$perPage   = 9;
$paginated = $entries->paginate($perPage);
$pagination = $paginated->pagination();

$allTags   = $site->tags()->toStructure()->filterBy('active', 'true')->sortBy('name', 'asc');
$entryTags = [];
foreach ($entries as $e) {
  foreach ($e->tags()->split(',') as $t) {
    $t = trim($t);
    if ($t !== '' && strtolower($t) !== strtolower($tagName)) {
      $entryTags[$t] = ($entryTags[$t] ?? 0) + 1;
    }
  }
}
arsort($entryTags);
$relatedTags = array_slice(array_keys($entryTags), 0, 6);
?>

<?php snippet('header', ['css' => ['/assets/css/feed.css']]) ?>

<div class="feed-wrap">

  <!-- BREADCRUMB -->
  <nav class="tag-breadcrumb" aria-label="Breadcrumb">
    <a href="/wove-mind">Feed</a>
    <span class="tag-breadcrumb__sep">/</span>
    <span><?= html($tagName) ?></span>
  </nav>

  <!-- TAG HERO -->
  <div class="tag-hero">
    <div class="tag-hero__label">Showing results for</div>
    <h1 class="tag-hero__name"><?= html($tagName) ?></h1>
    <?php if ($tagIntro): ?>
      <p class="tag-hero__intro"><?= html($tagIntro) ?></p>
    <?php endif ?>
    <div class="tag-hero__meta">
      <?= $entries->count() ?> entr<?= $entries->count() === 1 ? 'y' : 'ies' ?>
      <?php if ($caseStudies->count() > 0): ?>
        &middot; <?= $caseStudies->count() ?> case stud<?= $caseStudies->count() === 1 ? 'y' : 'ies' ?>
      <?php endif ?>
    </div>
  </div>

  <!-- CARD GRID -->
  <div class="tag-grid">
    <div class="tag-cards">
      <?php foreach ($paginated as $post): ?>
        <?php snippet('feed-card', ['post' => $post]) ?>
      <?php endforeach ?>
    </div>
  </div>

  <?php if ($pagination->hasPages()): ?>
    <nav class="tag-pagination" aria-label="Pagination">
      <?php for ($i = 1; $i <= $pagination->pages(); $i++): ?>
        <?php if ($i === $pagination->page()): ?>
          <span class="tag-pagination__btn tag-pagination__btn--active"><?= $i ?></span>
        <?php else: ?>
          <a href="/tag/<?= $tagSlug ?>/page:<?= $i ?>" class="tag-pagination__btn"><?= $i ?></a>
        <?php endif ?>
      <?php endfor ?>
      <?php if ($pagination->hasNextPage()): ?>
        <a href="/tag/<?= $tagSlug ?>/page:<?= ($pagination->page() + 1) ?>" class="tag-pagination__btn">Next &rarr;</a>
      <?php endif ?>
    </nav>
  <?php endif ?>

  <?php if (!empty($relatedTags)): ?>
    <div class="tag-related">
      <div class="tag-related__label">Related Topics</div>
      <div class="tag-related__tags">
        <?php foreach ($relatedTags as $rt):
          $rtTag = $allTags->findBy('name', $rt);
          $rtSlug = $rtTag ? $rtTag->slug()->value() : Str::slug($rt);
        ?>
          <a href="/tag/<?= $rtSlug ?>" class="tag-related__tag"><?= html($rt) ?></a>
        <?php endforeach ?>
      </div>
    </div>
  <?php endif ?>

  <!-- FEED FOOTER -->
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
