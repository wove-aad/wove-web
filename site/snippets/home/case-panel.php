<?php
/**
 * Case study panel: image with the client logo, title, summary, three
 * figures and a link to the case study. Shown in the feed filters when that
 * client is selected (hidden until then), and under a post as "From the
 * project".
 * Usage: <?php snippet('home/case-panel', ['cs' => $page, 'hidden' => true]) ?>
 */

$name  = $cs->eyebrow()->or($cs->title())->value();
$image = $cs->caseStudyImages()->toFile();
$logo  = $cs->logo()->toFile();
$title = $cs->heroTitle()->isNotEmpty() ? strip_tags($cs->heroTitle()->value()) : $cs->title()->value();
$text  = $cs->summary()->or($cs->subStatement())->value();
$stats = $cs->stats()->toStructure()->limit(3);
?>
<section class="case-panel"<?= ($hidden ?? false) ? ' data-panel="cs:' . html($cs->slug()) . '" hidden' : '' ?> aria-label="Case study: <?= html($name) ?>">
  <div class="case-panel__media">
    <?php if ($image): ?>
      <?php snippet('picture', [
        'file'   => $image,
        'widths' => [480, 640, 900, 1200],
        'ratio'  => 5 / 4,
        'sizes'  => '(min-width: 961px) 40vw, 100vw',
      ]) ?>
    <?php endif ?>
    <span class="case-panel__logo">
      <?php if ($logo): ?>
        <img src="<?= $logo->url() ?>" alt="<?= html($name) ?>">
      <?php else: ?>
        <?= html($name) ?>
      <?php endif ?>
    </span>
  </div>
  <div class="case-panel__body">
    <p class="case-panel__eyebrow">Case study · <?= html($name) ?></p>
    <h3 class="case-panel__title"><?= html($title) ?></h3>
    <?php if ($text): ?>
      <p class="case-panel__text"><?= html($text) ?></p>
    <?php endif ?>
    <?php if ($stats->count()): ?>
      <dl class="case-panel__kpis">
        <?php foreach ($stats as $stat): ?>
          <div class="case-panel__kpi"><dt><?= $stat->label()->html() ?></dt><dd><?= $stat->value()->html() ?></dd></div>
        <?php endforeach ?>
      </dl>
    <?php endif ?>
    <a href="<?= $cs->url() ?>" class="case-panel__cta">See case study <span aria-hidden="true">&rarr;</span></a>
  </div>
</section>
