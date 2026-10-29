<?php
/**
 * Homepage post card (the "Framed" treatment).
 * Usage: <?php snippet('home/post-card', ['post' => $entry, 'filters' => [...], 'hidden' => false]) ?>
 *
 * - One layout for every format: the image full width at the top, keeping
 *   its own shape, then the title stacked underneath (2026-10-29).
 * - A pill over the image names the service and the post type, "Labs ·
 *   Thinking" (wove_post_pill()); without an image it sits above the title.
 *   The format names (Thread, Long Read…) aren't shown.
 * - Posts from a project lead with the client name in bold, with the title
 *   under it and no excerpt; other posts show the title and the first
 *   sentence of the lead (wove_first_sentence()), never cut mid-sentence.
 *   What Ifs sit on a pale blue card with "Explore the idea". Sparks show
 *   their text in place of a title.
 * - The credited author (photo or initials, filtering the feed to their
 *   posts) closes the card. No date or tags (2026-10-29).
 * `filters` (from the controller) drives the data-filters attribute
 * assets/js/home.js matches against.
 */

$format = $post->format()->value() ?: 'thread';
$image  = $post->content()->get('image')->toFile();
$author = function_exists('wove_entry_author') ? wove_entry_author($post) : null;
$client = $post->case_study()->toPages()->first();
$client = $client ? $client->eyebrow()->or($client->title())->value() : '';
$pill   = '<span class="pc__pill">' . html(wove_post_pill($post)) . '</span>';
$linked = $format !== 'spark';

$excerpt = !$client && $post->body()->isNotEmpty() ? html(wove_first_sentence($post->body()->value())) : '';
$ratio   = $image && $image->height() ? $image->width() . ' / ' . $image->height() : null;

$titleText = $client
  ? '<span class="pc__client">' . html($client) . '</span> <span class="pc__subtitle">' . $post->title()->html() . '</span>'
  : $post->title()->html();
$classes = 'pc pc--' . $format . ($format === 'spark' ? ($image ? ' pc--spark-image' : ' pc--spark-text') : '') . ($image ? ' has-image' : '');
?>
<article class="<?= $classes ?>" data-filters="<?= html(implode(' ', $filters)) ?>"<?= $hidden ? ' hidden' : '' ?>>

  <?php if ($image): ?>
    <div class="pc__media" style="aspect-ratio: <?= $ratio ?>">
      <?php snippet('picture', [
        'file'   => $image,
        'widths' => [320, 480, 640, 960, 1280],
        'sizes'  => '(min-width: 960px) 33vw, (min-width: 600px) 50vw, 100vw',
      ]) ?>
      <?= $pill ?>
    </div>
  <?php endif ?>

  <div class="pc__body">
    <?php if (!$image): ?><?= $pill ?><?php endif ?>
    <?php if ($format === 'spark'): ?>
      <p class="<?= $image ? 'pc__caption' : 'pc__quote' ?>"><?= $post->body()->isNotEmpty() ? $post->body()->excerpt(200) : $post->title()->html() ?></p>
    <?php else: ?>
      <h3 class="pc__title<?= $client ? ' pc__title--client' : '' ?>"><a class="pc__link" href="<?= $post->url() ?>"><?= $titleText ?></a></h3>
      <?php if ($excerpt): ?><p class="pc__excerpt"><?= $excerpt ?></p><?php endif ?>
      <?php if ($format === 'whatif'): ?><span class="pc__cta" aria-hidden="true">Explore the idea &rarr;</span><?php endif ?>
    <?php endif ?>
    <?php if ($author): $avatar = $author->avatar() ?>
      <div class="pc__by">
        <a class="pc__author" href="<?= wove_work_url('author:' . wove_author_slug($author)) ?>">
          <?php if ($avatar): ?>
            <img class="pc__avatar" src="<?= $avatar->crop(48, 48)->url() ?>" alt="" width="24" height="24">
          <?php else: ?>
            <span class="pc__avatar pc__avatar--initials" aria-hidden="true"><?= html(wove_initials((string) $author->name())) ?></span>
          <?php endif ?>
          <span><?= $author->name()->html() ?></span>
        </a>
      </div>
    <?php endif ?>
  </div>

</article>
