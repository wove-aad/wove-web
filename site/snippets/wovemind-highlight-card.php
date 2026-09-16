<?php
/**
 * Wove Mind — project highlight card (non-linked)
 * Usage: <?php snippet('wovemind-highlight-card', ['post' => $post]) ?>
 *
 * For the "project-highlight" format only: no page/URL, so no full-card
 * overlay link like wovemind-related-card.php has. Shows client + excerpt,
 * with an optional "Visit website" external link when `website` is set.
 * Same image-led card family as wovemind-related-card.php (shares its
 * __media/__body/__title/__desc styling in site.css) — just a different
 * root class since the footer/link behaviour differs structurally.
 */

// $post->image() hits Kirby's built-in HasFiles::image() (first file
// uploaded to the page), not the "Featured image" content field —
// content()->get() is needed to reach the actual field value.
$image = $post->content()->get('image')->toFile();
?>

<article class="wovemind-highlight-card">

  <div class="wovemind-highlight-card__media">
    <?php if ($image): ?>
      <img src="<?= $image->url() ?>" alt="" width="636" height="340" loading="lazy">
    <?php endif ?>
  </div>

  <div class="wovemind-highlight-card__body">
    <h3 class="wovemind-highlight-card__title"><?= $post->client()->html() ?></h3>
    <p class="wovemind-highlight-card__desc"><?= $post->excerpt()->html() ?></p>
    <?php if ($post->website()->isNotEmpty()): ?>
      <a href="<?= $post->website() ?>" class="btn btn--ghost btn--sm wovemind-highlight-card__external" target="_blank" rel="noopener noreferrer">
        Visit website <span aria-hidden="true">&#8599;</span><span class="visually-hidden"> (opens in a new tab)</span>
      </a>
    <?php endif ?>
  </div>

</article>
