<?php
/**
 * Homepage featured item, under the hero text: a large image with a white
 * box over its bottom right corner (topic, title, client), linking to a
 * post or a case study.
 * The Wove Mind page's Featured field picks it; when that's empty, the
 * newest post or case study with an image is used.
 * Usage: <?php snippet('home/featured') ?>
 * Styles: .hx-featured in assets/css/home.css.
 */

$imageOf = fn ($p) => $p->intendedTemplate()->name() === 'case-study'
  ? $p->caseStudyImages()->toFile()
  : $p->content()->get('image')->toFile();

$item = $page->featured()->toPage();
if (!$item || !$item->isListed()) {
  $item = null;
  foreach (wove_feed()['items'] as $candidate) {
    if ($imageOf($candidate)) { $item = $candidate; break; }
  }
}
if (!$item) return;

$isCase = $item->intendedTemplate()->name() === 'case-study';
$image  = $imageOf($item);
$client = $isCase ? $item : $item->case_study()->toPages()->first();
$client = $client ? $client->eyebrow()->or($client->title())->value() : '';
$title  = $isCase && $item->heroTitle()->isNotEmpty() ? strip_tags($item->heroTitle()->value()) : $item->title()->value();

// Topic: the first published editorial tag, then the first service
$label    = '';
$siteTags = $site->tags()->toStructure();
foreach ($item->content()->get($isCase ? 'impactAreas' : 'tags')->split(',') as $t) {
  $tag = $siteTags->findBy('slug', $t) ?? $siteTags->findBy('name', $t);
  if ($tag && $tag->active()->toBool() !== false) { $label = $tag->name()->value(); break; }
}
if ($label === '') {
  $services = wove_service_labels();
  foreach ($item->services()->split(',') as $s) {
    if (isset($services[$s])) { $label = $services[$s]; break; }
  }
}
?>
<a class="hx-featured<?= $image ? '' : ' hx-featured--no-image' ?>" href="<?= $item->url() ?>">
  <?php if ($image): ?>
    <?php snippet('picture', [
      'file'   => $image,
      'widths' => [640, 960, 1280, 1920, 2560],
      'ratio'  => 81 / 40,
      'sizes'  => '(min-width: 1440px) 85rem, 100vw',
      'class'  => 'hx-featured__image',
    ]) ?>
  <?php endif ?>
  <span class="hx-featured__box">
    <?php if ($label): ?><span class="hx-featured__label"><?= html($label) ?></span><?php endif ?>
    <span class="hx-featured__title"><?= html($title) ?></span>
    <?php if ($client): ?><span class="hx-featured__client"><?= html($client) ?></span><?php endif ?>
  </span>
</a>
