<?php
/**
 * Case study — featured card snippet
 * Usage: <?php snippet('case-study-feature-card', ['caseStudy' => $caseStudy]) ?>
 *
 * NEEDS ADAM TO CONFIRM: no case-study card snippet exists yet anywhere in
 * the Kirby build (home's case-study grid is still static prototype HTML).
 * Named/structured to match this project's snippet convention until Adam's
 * case-study card component lands — align or rename then.
 */

$image = $caseStudy->caseStudyImages()->toFile();
?>

<a href="<?= $caseStudy->url() ?>" class="feature-card" aria-label="Read case study: <?= $caseStudy->eyebrow()->html() ?>">

  <div class="feature-card__media">
    <?php if ($image): ?>
      <img src="<?= $image->url() ?>" alt="" width="1289" height="664" loading="lazy">
    <?php endif ?>
  </div>

  <div class="feature-card__foot">
    <div class="feature-card__text">
      <p class="feature-card__client"><?= $caseStudy->eyebrow()->html() ?></p>
      <p class="feature-card__desc"><?= $caseStudy->subStatement()->html() ?></p>
    </div>
    <span class="btn btn--ghost btn--md feature-card__arrow">See case study <span aria-hidden="true">&rarr;</span></span>
  </div>

</a>
