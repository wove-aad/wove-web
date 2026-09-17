<?php
/**
 * Our Work — mixed feed of case studies + wove mind entries
 * File: site/templates/work.php
 *
 * Uses the feed design system (dark-first, time-of-day light mode).
 * Route: /our-work → page('work') (see site/config/config.php)
 */

$caseStudies = kirby()->collection('case-studies');
$wmParent    = $site->find('wove-mind');
$wmEntries   = $wmParent
  ? $wmParent->children()->listed()->sortBy('date', 'desc')
  : new \Kirby\Cms\Pages();

$feedItems = [];
foreach ($caseStudies as $cs) {
  $feedItems[] = ['entry' => $cs, 'date' => $cs->date()->toDate('U') ?: 0];
}
foreach ($wmEntries as $e) {
  $feedItems[] = ['entry' => $e, 'date' => $e->date()->toDate('U') ?: 0];
}
usort($feedItems, fn ($a, $b) => $b['date'] <=> $a['date']);

$serviceLabels = ['strategy' => 'Strategy', 'labs' => 'Labs', 'digital' => 'Digital', 'brand' => 'Brand'];
$sectorLabels = [
  'arts-and-culture'  => 'Arts and Culture',
  'public-service'    => 'Public Service',
  'higher-education'  => 'Higher Education',
  'non-profit'        => 'Non-profit and Mission-led',
  'founders-ventures' => 'Founders and Ventures',
];
$tagStructure = $site->tags()->toStructure();

$usedServices = [];
$usedSectors  = [];
$usedTags     = [];

foreach ($feedItems as $item) {
  $e = $item['entry'];
  foreach ($e->services()->split(',') as $s) {
    $s = trim($s);
    if ($s && isset($serviceLabels[$s])) $usedServices[$s] = $serviceLabels[$s];
  }
  foreach ($e->sectors()->split(',') as $s) {
    $s = trim($s);
    if ($s && isset($sectorLabels[$s])) $usedSectors[$s] = $sectorLabels[$s];
  }
  $tagField = $e->intendedTemplate()->name() === 'case-study' ? 'impactAreas' : 'tags';
  foreach ($e->content()->get($tagField)->split(',') as $t) {
    $t = trim($t);
    if (!$t) continue;
    $tag = $tagStructure->findBy('slug', $t) ?: $tagStructure->findBy('name', $t);
    if ($tag && $tag->active()->toBool() !== false) {
      $usedTags[$tag->slug()->value()] = $tag->name()->value();
    }
  }
}

$totalCount = count($feedItems);
?>

<?php snippet('header') ?>

<div class="feed-wrap">

  <div class="tag-hero">
    <h1 class="tag-hero__name">Our Work</h1>
    <div class="tag-hero__meta">
      <?= $totalCount ?> entr<?= $totalCount === 1 ? 'y' : 'ies' ?>
    </div>
  </div>

  <?php if ($usedServices || $usedSectors || $usedTags): ?>
  <nav class="tag-cloud" aria-label="Filter by tag">
    <button class="tag-pill is-active" data-filter="*" type="button">All</button>
    <?php foreach ($usedServices as $slug => $label): ?>
      <button class="tag-pill" data-filter="service:<?= $slug ?>" type="button"><?= html($label) ?></button>
    <?php endforeach ?>
    <?php foreach ($usedSectors as $slug => $label): ?>
      <button class="tag-pill" data-filter="sector:<?= $slug ?>" type="button"><?= html($label) ?></button>
    <?php endforeach ?>
    <?php foreach ($usedTags as $slug => $label): ?>
      <button class="tag-pill" data-filter="tag:<?= html($slug) ?>" type="button"><?= html($label) ?></button>
    <?php endforeach ?>
  </nav>
  <?php endif ?>

  <div class="tag-grid">
    <div class="stream-grid">
      <?php foreach ($feedItems as $item):
        $e = $item['entry'];
        $services = implode(',', array_filter(array_map('trim', $e->services()->split(','))));
        $sectors  = implode(',', array_filter(array_map('trim', $e->sectors()->split(','))));
        $tagField = $e->intendedTemplate()->name() === 'case-study' ? 'impactAreas' : 'tags';
        $eTags    = implode(',', array_filter(array_map('trim', $e->content()->get($tagField)->split(','))));
      ?>
        <div class="work-feed-item"
             data-services="<?= html($services) ?>"
             data-sectors="<?= html($sectors) ?>"
             data-tags="<?= html($eTags) ?>">
          <?php snippet('stream-card', ['post' => $e]) ?>
        </div>
      <?php endforeach ?>
    </div>

    <?php if ($totalCount === 0): ?>
      <p class="our-work-empty">No entries published yet.</p>
    <?php endif ?>
  </div>

</div>

<script>
(function() {
  var h = new Date().getHours();
  if (h >= 7 && h < 19) {
    document.documentElement.setAttribute('data-theme', 'light');
  }
})();
</script>

<script>
(function () {
  var pills = document.querySelectorAll('.tag-pill');
  var items = document.querySelectorAll('.work-feed-item');
  if (!pills.length || !items.length) return;

  pills.forEach(function (pill) {
    pill.addEventListener('click', function () {
      pills.forEach(function (p) { p.classList.remove('is-active'); });
      pill.classList.add('is-active');
      var filter = pill.getAttribute('data-filter');

      items.forEach(function (item) {
        if (filter === '*') { item.hidden = false; return; }
        var parts = filter.split(':');
        var type  = parts[0];
        var value = parts[1];
        var attr  = item.getAttribute('data-' + type + 's') || '';
        item.hidden = attr.split(',').indexOf(value) === -1;
      });
    });
  });
})();
</script>

<?php snippet('footer') ?>
