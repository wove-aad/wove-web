<?php
/**
 * Wove Mind — feed card snippet
 * Usage: <?php snippet('wovemind-card', ['post' => $post]) ?>
 * Used unfiltered by the main Wove Mind feed (wovemind.php) and the
 * orphaned wovemind-feed-home.php, so it still has to handle every format
 * including project-highlight, even though the main feed's own query now
 * excludes it (project-highlight isn't one of the feed's filter tabs —
 * it only ever appears embedded on service pages).
 *
 * Four visual treatments per Figma (Wove Mind frame, 165:568) + Grace's
 * follow-up notes (2026-07-19 and 2026-07-20):
 *  - spark:              icon + body text only, not linked (no single
 *                         page view — same as project-highlight below)
 *  - thread:              standard card — contained media (if present) +
 *                         title + a meta row (format label bottom-left +
 *                         arrow bottom-right, same as every other linked
 *                         format below), linked
 *  - whatif:              full-bleed card — photo bleeds to the card's
 *                         edges, title below it, then a meta row with the
 *                         format label bottom-left + arrow bottom-right.
 *                         Falls back to the standard "thread" treatment
 *                         when there's no image to bleed.
 *  - longread:            featured card — cream bg + big title + excerpt
 *                         + a meta row (eyebrow + arrow). Always this
 *                         treatment regardless of whether an image is
 *                         set — the image field isn't used on this card
 *                         at all (2026-07-20: dropped the full-bleed
 *                         photo variant this format had briefly).
 *  - project-highlight:   client name + excerpt + optional external link,
 *                         not linked (kept for wovemind-feed-home.php)
 */

$format      = $post->format()->value();
$isHighlight = $format === 'project-highlight';
$isSpark     = $format === 'spark';
$isLongread  = $format === 'longread';
$isLinked    = in_array($format, ['thread', 'whatif', 'longread']);

// $post->image() hits Kirby's built-in HasFiles::image() (first file
// uploaded to the page), not the "Featured image" content field —
// content()->get() is needed to reach the actual field value.
// Longread doesn't use an image on its card at all, so it's excluded here.
$image = in_array($format, ['project-highlight', 'thread', 'whatif'])
  ? $post->content()->get('image')->toFile()
  : null;

$formatLabels = [
  'spark'             => 'Spark',
  'project-highlight' => 'Project highlight',
  'thread'            => 'Thread',
  'whatif'            => 'What if',
  'longread'          => 'The Long Read',
];
?>

<article class="wovemind-card wovemind-card--<?= $format ?><?= $image ? ' has-image' : '' ?>" data-format="<?= $format ?>">

  <?php if ($isSpark): ?>

    <!-- Decorative illustration, not a per-entry field — same static
         asset on every Spark card, matching the illustration reuse
         pattern already established on the homepage service cards. -->
    <img src="/assets/illustrations/lightbulb.png" alt="" class="wovemind-card__icon" width="64" height="68" loading="lazy">
    <div class="wovemind-card__spark-text"><?= $post->body() ?></div>

  <?php elseif ($isLongread): ?>

    <h2 class="wovemind-card__title"><?= $post->title()->html() ?></h2>
    <p class="wovemind-card__excerpt"><?= $post->body()->excerpt(160) ?></p>
    <div class="wovemind-card__meta">
      <p class="wovemind-card__eyebrow"><?= $formatLabels[$format] ?></p>
      <span class="card-arrow" aria-hidden="true">&rarr;</span>
    </div>

  <?php elseif ($format === 'whatif' && $image): ?>

    <div class="wovemind-card__media wovemind-card__media--bleed">
      <img src="<?= $image->url() ?>" alt="" loading="lazy">
    </div>
    <h2 class="wovemind-card__title"><?= $post->title()->html() ?></h2>
    <div class="wovemind-card__meta">
      <p class="wovemind-card__eyebrow"><?= $formatLabels[$format] ?></p>
      <span class="card-arrow" aria-hidden="true">&rarr;</span>
    </div>

  <?php elseif ($isHighlight): ?>

    <?php if ($image): ?>
      <div class="wovemind-card__media">
        <img src="<?= $image->url() ?>" alt="" loading="lazy">
      </div>
    <?php endif ?>
    <h2 class="wovemind-card__title"><?= $post->client()->html() ?></h2>
    <p class="wovemind-card__excerpt"><?= $post->excerpt()->html() ?></p>
    <?php if ($post->website()->isNotEmpty()): ?>
      <a href="<?= $post->website() ?>" class="btn btn--ghost btn--sm" target="_blank" rel="noopener noreferrer">
        Visit website <span aria-hidden="true">&#8599;</span><span class="visually-hidden"> (opens in a new tab)</span>
      </a>
    <?php endif ?>

  <?php else: /* thread, or whatif with no image to bleed */ ?>

    <?php if ($image): ?>
      <div class="wovemind-card__media">
        <img src="<?= $image->url() ?>" alt="" loading="lazy">
      </div>
    <?php endif ?>
    <h2 class="wovemind-card__title"><?= $post->title()->html() ?></h2>
    <div class="wovemind-card__meta">
      <p class="wovemind-card__eyebrow"><?= $formatLabels[$format] ?></p>
      <span class="card-arrow" aria-hidden="true">&rarr;</span>
    </div>

  <?php endif ?>

  <?php if ($isLinked): ?>
    <a href="<?= $post->url() ?>" class="wovemind-card__link" tabindex="-1" aria-hidden="true"></a>
  <?php endif ?>

</article>
