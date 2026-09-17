<?php
/**
 * Work grid card — used on the Our Work portfolio page
 * Usage: <?php snippet('work-grid-card', ['caseStudy' => $cs]) ?>
 */

$image    = $caseStudy->caseStudyImages()->toFile();
$services = implode(',', array_filter($caseStudy->services()->split(',')));
$sectors  = implode(',', array_filter($caseStudy->sectors()->split(',')));
$tags     = implode(',', array_filter($caseStudy->impactAreas()->split(',')));
?>

<a href="<?= $caseStudy->url() ?>"
   class="work-grid-card"
   data-services="<?= html($services) ?>"
   data-sectors="<?= html($sectors) ?>"
   data-tags="<?= html($tags) ?>">

  <div class="work-grid-card__media">
    <?php if ($image): ?>
      <img src="<?= $image->url() ?>" alt="" width="640" height="427" loading="lazy">
    <?php endif ?>
  </div>

  <div class="work-grid-card__body">
    <?php if ($caseStudy->eyebrow()->isNotEmpty()): ?>
      <p class="work-grid-card__client"><?= $caseStudy->eyebrow()->html() ?></p>
    <?php endif ?>
    <?php if ($caseStudy->subStatement()->isNotEmpty()): ?>
      <p class="work-grid-card__desc"><?= $caseStudy->subStatement()->html() ?></p>
    <?php elseif ($caseStudy->heroTitle()->isNotEmpty()): ?>
      <p class="work-grid-card__desc"><?= strip_tags($caseStudy->heroTitle()->value()) ?></p>
    <?php endif ?>
  </div>

</a>
