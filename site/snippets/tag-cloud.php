<?php
/**
 * Unified tag cloud — services, sectors, impact areas, case study names
 * Usage: <?php snippet('tag-cloud', ['active' => 'strategy', 'activeType' => 'service']) ?>
 *
 * Each pill is a navigation link. Services link to /services/{slug},
 * sectors and editorial tags link to /tag/{slug}, case study names
 * link directly to the case study page. A "Team" dropdown at the end
 * opens Our Work filtered by that team member's posts.
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

// Global tag order: case study, services, editorial tags, sectors (stable, so first-seen order holds within a group)
$typeRank = ['casestudy' => 0, 'service' => 1, 'tag' => 2, 'sector' => 3];
uasort($pills, fn ($a, $b) => ($typeRank[$a['type']] ?? 9) <=> ($typeRank[$b['type']] ?? 9));

// Team members with at least one credited entry, for the Team dropdown
$teamOptions = [];
foreach (wove_team_members() as $member) {
  if (wove_author_entries($member)->count()) {
    $teamOptions[wove_author_slug($member)] = $member->name()->value();
  }
}

$active     = $active ?? '';
$activeType = $activeType ?? '';
?>

<?php if ($pills || $teamOptions): ?>
<nav class="tag-cloud" aria-label="Tags">
  <?php foreach ($pills as $pill): ?>
    <a href="<?= $pill['url'] ?>"
       class="tag-pill<?= ($pill['slug'] === $active && $pill['type'] === $activeType) ? ' is-active' : '' ?>"><?= html($pill['label']) ?></a>
  <?php endforeach ?>
  <?php if ($teamOptions): ?>
    <label class="tag-pill tag-pill--select<?= $activeType === 'author' ? ' is-active' : '' ?>">
      <span class="visually-hidden">See work by team member</span>
      <select onchange="if (this.value) window.location.href = '/our-work?filter=' + encodeURIComponent('author:' + this.value)">
        <option value="">Team</option>
        <?php foreach ($teamOptions as $slug => $name): ?>
          <option value="<?= html($slug) ?>"<?= ($activeType === 'author' && $active === $slug) ? ' selected' : '' ?>><?= html($name) ?></option>
        <?php endforeach ?>
      </select>
    </label>
  <?php endif ?>
</nav>
<?php endif ?>
