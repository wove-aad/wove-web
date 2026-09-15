<?php
/**
 * Feed facet rail — sidebar with explainer, sectors, and topics
 * Usage: <?php snippet('feed-rail') ?>
 *
 * Data sources:
 *   Explainer → hardcoded (site-level, not editable yet)
 *   Sectors   → sector pages (site/blueprints/pages/sector.yml)
 *   Topics    → site.tags structure (tabs/taxonomies.yml)
 */

$sectorKeys = [
  'arts-and-culture'   => 'Arts and Culture',
  'public-service'     => 'Public Service',
  'higher-education'   => 'Higher Education',
  'non-profit'         => 'Non-profit and Mission-led',
  'founders-ventures'  => 'Founders and Ventures',
];

$allEntries = $site->index()->filter(function ($p) {
  return in_array($p->intendedTemplate()->name(), ['wove-mind-entry', 'case-study']);
});

$tags = $site->tags()->toStructure()->filterBy('active', 'true')->sortBy('name', 'asc');
$tagLimit = 8;
$totalTags = $tags->count();
?>

<aside class="feed-rail" aria-label="Filter">

  <div class="feed-explainer">
    <h3 class="feed-explainer__title">Strategic design</h3>
    <p class="feed-explainer__body">We use strategic design to help organisations move from insight to delivery — across services, systems, and culture.</p>
  </div>

  <div class="feed-rail__group">
    <div class="feed-rail__group-label">Sectors</div>
    <ul class="feed-rail__list">
      <?php foreach ($sectorKeys as $key => $label):
        $count = $allEntries->filterBy('sectors', $key, ',')->count();
        $isActive = isset($activeSector) && $activeSector === $key;
      ?>
        <li class="feed-rail__item<?= $isActive ? ' feed-rail__item--active' : '' ?>">
          <a href="/sector/<?= $key ?>"><span><?= html($label) ?></span></a>
          <span class="feed-rail__count"><?= $count ?></span>
        </li>
      <?php endforeach ?>
    </ul>
  </div>

  <div class="feed-rail__group">
    <div class="feed-rail__group-label">Topics</div>
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

</aside>
