<?php
/**
 * Card tags — services, sectors and editorial tags on a feed card
 * Usage: <?php snippet('card-tags', ['post' => $post]) ?>
 *
 * Each tag links to Our Work filtered by it. The first four show; the
 * rest sit behind a "+N" button that expands them in place (see
 * service-page-scripts.php).
 */

$maxVisible    = 4;
$serviceLabels = ['strategy' => 'Strategy', 'labs' => 'Labs', 'digital' => 'Digital', 'brand' => 'Brand'];
$sectorLabels  = [
  'arts-and-culture'  => 'Arts and Culture',
  'public-service'    => 'Public Service',
  'higher-education'  => 'Higher Education',
  'non-profit'        => 'Non-profit and Mission-led',
  'founders-ventures' => 'Founders and Ventures',
];
$siteTags = site()->tags()->toStructure();
$tagField = $post->intendedTemplate()->name() === 'case-study' ? 'impactAreas' : 'tags';

$cardTags = [];
foreach ($post->services()->split(',') as $s) {
  $s = trim($s);
  if (isset($serviceLabels[$s])) $cardTags['service:' . $s] = $serviceLabels[$s];
}
foreach ($post->sectors()->split(',') as $s) {
  $s = trim($s);
  if (isset($sectorLabels[$s])) $cardTags['sector:' . $s] = $sectorLabels[$s];
}
foreach ($post->content()->get($tagField)->split(',') as $t) {
  $t   = trim($t);
  $tag = $t ? ($siteTags->findBy('slug', $t) ?: $siteTags->findBy('name', $t)) : null;
  if ($tag && $tag->active()->toBool() !== false) {
    $cardTags['tag:' . $tag->slug()->value()] = $tag->name()->value();
  }
}

if (!$cardTags) return;
$extra = count($cardTags) - $maxVisible;
$i     = 0;
?>
<div class="card-tags">
  <?php foreach ($cardTags as $filter => $label): ?>
    <a class="card-tags__tag" href="<?= url('our-work') . '?filter=' . rawurlencode($filter) ?>"<?= $i++ >= $maxVisible ? ' hidden' : '' ?>><?= html($label) ?></a>
  <?php endforeach ?>
  <?php if ($extra > 0): ?>
    <button class="card-tags__more" type="button" aria-expanded="false" aria-label="Show <?= $extra ?> more tags">+<?= $extra ?></button>
  <?php endif ?>
</div>
