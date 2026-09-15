<?php
/**
 * Feed facet rail — sidebar with explainer, tag cloud, and services
 * Usage: <?php snippet('feed-rail') ?>
 *
 * Data sources:
 *   Explainer → hardcoded (site-level, not editable yet)
 *   Tags      → site.tags structure (tabs/taxonomies.yml)
 *   Services  → hardcoded list matching blueprint multiselect options
 *   Featured  → TBC (will be configurable, e.g. mark-as-featured)
 */

$tags = $site->tags()->toStructure()->filterBy('active', 'true')->sortBy('name', 'asc');
$tagLimit = 12;
$totalTags = $tags->count();

$services = [
  'strategy' => 'Strategy',
  'labs'     => 'Labs',
  'digital'  => 'Digital',
  'brand'    => 'Brand',
];

$caseStudies = $site->index()
  ->filterBy('intendedTemplate', 'case-study')
  ->listed()
  ->sortBy('date', 'desc')
  ->limit(4);
?>

<aside class="feed-rail" aria-label="Filter">

  <div class="feed-explainer">
    <h3 class="feed-explainer__title">Strategic design</h3>
    <p class="feed-explainer__body">We use strategic design to help organisations move from insight to delivery — across services, systems, and culture.</p>
  </div>

  <div class="feed-rail__group">
    <span class="feed-rail__group-label label">Services</span>
    <div class="feed-rail__tags">
      <?php foreach ($services as $slug => $name): ?>
        <a href="/<?= $slug ?>" class="feed-rail__tag"><?= $name ?></a>
      <?php endforeach ?>
    </div>
  </div>

  <div class="feed-rail__group">
    <span class="feed-rail__group-label label">Topics</span>
    <div class="feed-rail__tags">
      <?php $i = 0; foreach ($tags as $tag):
        if ($i >= $tagLimit) break;
        $slug = $tag->slug()->value();
      ?>
        <a href="/tag/<?= $slug ?>" class="feed-rail__tag"><?= $tag->name()->html() ?></a>
      <?php $i++; endforeach ?>
    </div>
    <?php if ($totalTags > $tagLimit): ?>
      <div class="feed-rail__tag-more">
        <a href="/topics">All <?= $totalTags ?> topics &rarr;</a>
      </div>
    <?php endif ?>
  </div>

  <?php if ($caseStudies->count()): ?>
    <div class="feed-rail__group">
      <span class="feed-rail__group-label label">Case studies</span>
      <div class="feed-rail__tags">
        <?php foreach ($caseStudies as $cs): ?>
          <a href="<?= $cs->url() ?>" class="feed-rail__tag"><?= $cs->eyebrow()->or($cs->title())->html() ?></a>
        <?php endforeach ?>
      </div>
    </div>
  <?php endif ?>

</aside>
