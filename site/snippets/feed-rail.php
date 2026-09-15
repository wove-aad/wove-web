<?php
/**
 * Feed facet rail — sidebar with explainer and tag cloud
 * Usage: <?php snippet('feed-rail') ?>
 *
 * Data sources:
 *   Explainer → hardcoded (site-level, not editable yet)
 *   Tags      → site.tags structure (tabs/taxonomies.yml)
 */

$tags = $site->tags()->toStructure()->filterBy('active', 'true')->sortBy('name', 'asc');
$tagLimit = 12;
$totalTags = $tags->count();
?>

<aside class="feed-rail" aria-label="Filter">

  <div class="feed-explainer">
    <h3 class="feed-explainer__title">Strategic design</h3>
    <p class="feed-explainer__body">We use strategic design to help organisations move from insight to delivery — across services, systems, and culture.</p>
  </div>

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

</aside>
