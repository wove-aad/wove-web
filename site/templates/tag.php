<?php
/**
 * Tag/sector destination template — unified filtered view
 * File: site/templates/tag.php
 *
 * Reached via the route /tag/{slug} defined in config.php.
 * Handles editorial tags (from site.tags structure) and sectors.
 * The route passes $tagSlug, $tagName, $tagIntro, and $filterField/$filterValue.
 */

$tagSlug  = $tagSlug ?? '';
$tagName  = $tagName ?? '';
$tagIntro = $tagIntro ?? '';
$filterField = $filterField ?? 'tags';
$filterValue = $filterValue ?? $tagName;

if (!$tagName) go('/our-work');

$entries = $site->index()
  ->filter(fn ($p) => in_array($p->intendedTemplate()->name(), ['wove-mind-entry', 'case-study']))
  ->listed()
  ->sortBy('date', 'desc');

if ($filterField === 'sectors') {
  $entries = $entries->filterBy('sectors', $filterValue, ',');
} elseif ($filterField === 'impactAreas') {
  $entries = $entries->filterBy('impactAreas', $filterValue, ',');
} else {
  $filtered = new \Kirby\Cms\Pages();
  foreach ($entries as $e) {
    $tmpl = $e->intendedTemplate()->name();
    if ($tmpl === 'case-study') {
      if (in_array($filterValue, $e->impactAreas()->split(','))) {
        $filtered->add($e);
      }
    } else {
      $tags = $e->tags()->split(',');
      if (in_array($filterValue, $tags) || in_array($tagName, $tags)) {
        $filtered->add($e);
      }
    }
  }
  $entries = $filtered;
}

$perPage   = 12;
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

  <nav class="tag-breadcrumb" aria-label="Breadcrumb">
    <a href="/wove-mind">Feed</a>
    <span class="tag-breadcrumb__sep">/</span>
    <a href="/our-work">Our Work</a>
    <span class="tag-breadcrumb__sep">/</span>
    <span><?= html($tagName) ?></span>
  </nav>

  <div class="tag-hero">
    <div class="tag-hero__label">Showing results for</div>
    <h1 class="tag-hero__name"><?= html($tagName) ?></h1>
    <?php if ($tagIntro): ?>
      <p class="tag-hero__intro"><?= html($tagIntro) ?></p>
    <?php endif ?>
    <div class="tag-hero__meta">
      <?= $entries->count() ?> entr<?= $entries->count() === 1 ? 'y' : 'ies' ?>
    </div>
  </div>

  <?php snippet('tag-cloud', ['active' => $tagSlug, 'activeType' => ($filterField === 'sectors' ? 'sector' : 'tag')]) ?>

  <section class="stream-section" aria-label="<?= html($tagName) ?> entries">
    <div class="stream-grid">
      <?php foreach ($paginated as $post): ?>
        <?php snippet('stream-card', ['post' => $post]) ?>
      <?php endforeach ?>
    </div>
  </section>

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
          <a href="/our-work?filter=tag:<?= $rtSlug ?>" class="tag-related__tag"><?= html($rt) ?></a>
        <?php endforeach ?>
      </div>
    </div>
  <?php endif ?>

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
