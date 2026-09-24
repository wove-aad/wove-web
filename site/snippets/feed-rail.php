<?php
/**
 * Feed facet rail — sidebar with explainer and merged tag cloud
 * Usage: <?php snippet('feed-rail') ?>
 *
 * Cloud items are tags, services, and case studies merged into one flat
 * list, sorted by most recent usage (the publish date of the newest
 * entry each item appears on).
 */

$allEntries = $site->index()
  ->filter(fn ($p) => in_array($p->intendedTemplate()->name(), ['wove-mind-entry', 'case-study']))
  ->listed()
  ->sortBy('date', 'desc');

$siteTags = $site->tags()->toStructure()->filterBy('active', 'true');
$tagSlugMap = [];
foreach ($siteTags as $t) {
  $tagSlugMap[strtolower($t->name()->value())] = $t->slug()->value();
}

$serviceLabels = [
  'strategy' => 'Strategy',
  'labs'     => 'Labs',
  'digital'  => 'Digital',
  'brand'    => 'Brand',
];

$cloud = [];

foreach ($allEntries as $entry) {
  $entryDate = $entry->date()->toDate('U') ?: 0;

  foreach ($entry->tags()->split(',') as $t) {
    $t = trim($t);
    if ($t === '') continue;
    $key = 'tag:' . strtolower($t);
    if (!isset($cloud[$key]) || $entryDate > $cloud[$key]['date']) {
      $slug = $tagSlugMap[strtolower($t)] ?? Str::slug($t);
      $cloud[$key] = [
        'label' => $t,
        'url'   => '/our-work?filter=tag:' . $slug,
        'date'  => $entryDate,
      ];
    }
  }

  foreach ($entry->services()->split(',') as $s) {
    $s = trim($s);
    if ($s === '' || !isset($serviceLabels[$s])) continue;
    $key = 'svc:' . $s;
    if (!isset($cloud[$key]) || $entryDate > $cloud[$key]['date']) {
      $cloud[$key] = [
        'label' => $serviceLabels[$s],
        'url'   => '/our-work?filter=service:' . $s,
        'date'  => $entryDate,
      ];
    }
  }

  if ($entry->intendedTemplate()->name() === 'case-study') {
    $key = 'cs:' . $entry->slug();
    if (!isset($cloud[$key])) {
      $cloud[$key] = [
        'label' => $entry->eyebrow()->or($entry->title())->value(),
        'url'   => '/our-work?filter=cs:' . $entry->slug(),
        'date'  => $entryDate,
      ];
    }
  }
}

// Global tag order: case study, services, editorial tags; most recent first within each group
$typeRank = ['cs' => 0, 'svc' => 1, 'tag' => 2];
foreach ($cloud as $key => &$item) $item['rank'] = $typeRank[strstr($key, ':', true)] ?? 9;
unset($item);
usort($cloud, fn ($a, $b) => [$a['rank'], $b['date']] <=> [$b['rank'], $a['date']]);

$cloudLimit = 16;
$totalCloud = count($cloud);
?>

<aside class="feed-rail" aria-label="Filter">

  <div class="feed-explainer">
    <h3 class="feed-explainer__title">Strategic design</h3>
    <p class="feed-explainer__body">We use strategic design to help organisations move from insight to delivery — across services, systems, and culture.</p>
  </div>

  <div class="feed-rail__tags">
    <?php $i = 0; foreach ($cloud as $item):
      if ($i >= $cloudLimit) break;
    ?>
      <a href="<?= $item['url'] ?>" class="feed-rail__tag"><?= html($item['label']) ?></a>
    <?php $i++; endforeach ?>
  </div>
  <?php if ($totalCloud > $cloudLimit): ?>
    <div class="feed-rail__tag-more">
      <a href="/our-work">All <?= $totalCloud ?> topics &rarr;</a>
    </div>
  <?php endif ?>

</aside>
