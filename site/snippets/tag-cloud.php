<?php
/**
 * Unified tag cloud — services, sectors, impact areas, case study names
 * Usage: <?php snippet('tag-cloud', ['active' => 'strategy', 'activeType' => 'service']) ?>
 *
 * Each pill is a navigation link. Services link to /services/{slug},
 * sectors and editorial tags link to /tag/{slug}, case study names
 * link directly to the case study page.
 */

$caseStudies  = kirby()->collection('case-studies');
$tagStructure = $site->tags()->toStructure();
$wmParent     = $site->find('wove-mind');

$serviceLabels = ['strategy' => 'Strategy', 'labs' => 'Labs', 'digital' => 'Digital', 'brand' => 'Brand'];
$sectorLabels  = [
  'arts-and-culture'  => 'Arts and Culture',
  'public-service'    => 'Public Service',
  'higher-education'  => 'Higher Education',
  'non-profit'        => 'Non-profit and Mission-led',
  'founders-ventures' => 'Founders and Ventures',
];

$pills = [];

foreach ($caseStudies as $cs) {
  foreach ($cs->services()->split(',') as $s) {
    $s = trim($s);
    if ($s && isset($serviceLabels[$s]) && !isset($pills['svc:' . $s])) {
      $pills['svc:' . $s] = ['label' => $serviceLabels[$s], 'url' => '/our-work?filter=service:' . $s, 'slug' => $s, 'type' => 'service'];
    }
  }
  foreach ($cs->sectors()->split(',') as $s) {
    $s = trim($s);
    if ($s && isset($sectorLabels[$s]) && !isset($pills['sec:' . $s])) {
      $pills['sec:' . $s] = ['label' => $sectorLabels[$s], 'url' => '/our-work?filter=sector:' . $s, 'slug' => $s, 'type' => 'sector'];
    }
  }
  foreach ($cs->impactAreas()->split(',') as $slug) {
    $slug = trim($slug);
    if ($slug && !isset($pills['tag:' . $slug])) {
      $tag = $tagStructure->findBy('slug', $slug);
      if ($tag && $tag->active()->toBool() !== false) {
        $pills['tag:' . $slug] = ['label' => $tag->name()->value(), 'url' => '/our-work?filter=tag:' . $slug, 'slug' => $slug, 'type' => 'tag'];
      }
    }
  }
}

if ($wmParent) {
  foreach ($wmParent->children()->listed() as $entry) {
    foreach ($entry->services()->split(',') as $s) {
      $s = trim($s);
      if ($s && isset($serviceLabels[$s]) && !isset($pills['svc:' . $s])) {
        $pills['svc:' . $s] = ['label' => $serviceLabels[$s], 'url' => '/our-work?filter=service:' . $s, 'slug' => $s, 'type' => 'service'];
      }
    }
    foreach ($entry->sectors()->split(',') as $s) {
      $s = trim($s);
      if ($s && isset($sectorLabels[$s]) && !isset($pills['sec:' . $s])) {
        $pills['sec:' . $s] = ['label' => $sectorLabels[$s], 'url' => '/our-work?filter=sector:' . $s, 'slug' => $s, 'type' => 'sector'];
      }
    }
    foreach ($entry->tags()->split(',') as $t) {
      $t = trim($t);
      if (!$t) continue;
      $tag = $tagStructure->findBy('slug', $t) ?: $tagStructure->findBy('name', $t);
      if ($tag && $tag->active()->toBool() !== false) {
        $slug = $tag->slug()->value();
        if (!isset($pills['tag:' . $slug])) {
          $pills['tag:' . $slug] = ['label' => $tag->name()->value(), 'url' => '/our-work?filter=tag:' . $slug, 'slug' => $slug, 'type' => 'tag'];
        }
      }
    }
  }
}

foreach ($caseStudies as $cs) {
  $name = $cs->eyebrow()->value();
  if ($name) {
    $key = 'cs:' . $cs->slug();
    if (!isset($pills[$key])) {
      $pills[$key] = ['label' => $name, 'url' => '/our-work?filter=cs:' . $cs->slug(), 'slug' => $cs->slug(), 'type' => 'casestudy'];
    }
  }
}

$active     = $active ?? '';
$activeType = $activeType ?? '';
?>

<?php if ($pills): ?>
<nav class="tag-cloud" aria-label="Tags">
  <?php foreach ($pills as $pill): ?>
    <a href="<?= $pill['url'] ?>"
       class="tag-pill<?= ($pill['slug'] === $active && $pill['type'] === $activeType) ? ' is-active' : '' ?>"><?= html($pill['label']) ?></a>
  <?php endforeach ?>
</nav>
<?php endif ?>
