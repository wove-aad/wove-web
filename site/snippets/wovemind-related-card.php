<?php
/**
 * Wove Mind — related post card (image-led placement)
 * Usage: <?php snippet('wovemind-related-card', ['post' => $post]) ?>
 *
 * Used on service pages (and case study pages) for the "related Wove Mind
 * entries" feed described in wove-mind-cms-brief.md. Distinct from
 * wovemind-card.php, which is the format-eyebrow/author/date card used on
 * the main Wove Mind feed and the homepage feed — this placement is
 * image-led with a "Read thread" CTA and no format/author/date meta.
 *
 * NEEDS ADAM TO CONFIRM: no snippet for this placement existed yet: named
 * to sit alongside wovemind-card.php rather than reusing it or the old
 * `.feed-item` prototype naming (which predates wovemind-card.php and isn't
 * part of the built Kirby templates).
 */

$image = $post->image();
?>

<article class="wovemind-related-card">

  <div class="wovemind-related-card__media">
    <?php if ($image): ?>
      <img src="<?= $image->url() ?>" alt="" width="636" height="340" loading="lazy">
    <?php endif ?>
  </div>

  <div class="wovemind-related-card__body">
    <h3 class="wovemind-related-card__title"><?= $post->title()->html() ?></h3>
    <p class="wovemind-related-card__desc"><?= $post->body()->excerpt(160) ?></p>
    <a href="<?= $post->url() ?>" class="btn btn--ghost btn--md wovemind-related-card__cta" aria-label="Read thread: <?= $post->title()->html() ?>">
      Read thread <span aria-hidden="true">&rarr;</span>
    </a>
  </div>

  <a href="<?= $post->url() ?>" class="wovemind-related-card__link" tabindex="-1" aria-hidden="true"></a>

</article>
