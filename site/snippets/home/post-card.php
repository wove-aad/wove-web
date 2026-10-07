<?php
/**
 * Homepage post card (the "Framed" treatment).
 * Usage: <?php snippet('home/post-card', ['post' => $entry, 'filters' => [...], 'hidden' => false]) ?>
 *
 * - Images keep their own shape (aspect-ratio from the file), no cropping.
 * - Sparks with an image read as a captioned image with small quiet text;
 *   sparks without one are a plain quote card.
 * - Long reads: image-led, with a reading time.
 * - What Ifs: the question leads, on a pale blue card, "Explore the idea".
 * - Threads: compact title and meta with a small thumbnail.
 * Tags are links to Our Work; on the homepage assets/js/home.js filters in
 * place instead. `filters` (from the controller) drives the data-filters
 * attribute the script matches against.
 */

$format = $post->format()->value() ?: 'thread';
$image  = $post->content()->get('image')->toFile();
$author = function_exists('wove_entry_author') ? wove_entry_author($post) : null;

$postMidnight = strtotime('midnight', $post->date()->toTimestamp() ?: $post->modified());
$daysAgo      = (int) round((strtotime('today') - $postMidnight) / 86400);
$dateLabel    = match (true) {
  $daysAgo === 0 => 'Today',
  $daysAgo === 1 => 'Yesterday',
  $daysAgo <= 6  => 'Last ' . date('l', $postMidnight),
  default        => date('j M', $postMidnight),
};

$excerpt = $post->body()->isNotEmpty() ? $post->body()->excerpt(160) : '';
$ratio   = $image && $image->height() ? $image->width() . ' / ' . $image->height() : null;

// Reading time for long reads, from the body and blocks text.
$minutes = null;
if ($format === 'longread' && function_exists('wove_mind_blocks_text')) {
  $words   = str_word_count(wove_mind_plain_text($post->body()->value()) . ' ' . wove_mind_blocks_text($post->blocks()->value()));
  $minutes = max(1, (int) ceil($words / 200));
}

$labels = ['spark' => 'Spark', 'thread' => 'Thread', 'whatif' => 'What If', 'longread' => 'Long Read'];
$linked = $format !== 'spark';

$media = $image
  ? '<div class="pc__media" style="aspect-ratio: ' . $ratio . '">' . snippet('picture', [
      'file'   => $image,
      'widths' => [320, 480, 640, 960, 1280],
      'sizes'  => '(min-width: 960px) 33vw, (min-width: 600px) 50vw, 100vw',
    ], true) . '</div>'
  : '';
$meta = function () use ($author, $postMidnight, $dateLabel) { ?>
  <p class="pc__meta">
    <?php if ($author): ?><span><?= $author->name()->html() ?></span><span aria-hidden="true"> &middot; </span><?php endif ?>
    <time datetime="<?= date('Y-m-d', $postMidnight) ?>"><?= $dateLabel ?></time>
  </p>
<?php };
$title = function () use ($post, $linked) { ?>
  <h3 class="pc__title"><?php if ($linked): ?><a class="pc__link" href="<?= $post->url() ?>"><?= $post->title()->html() ?></a><?php else: ?><?= $post->title()->html() ?><?php endif ?></h3>
<?php };
$classes = 'pc pc--' . $format . ($format === 'spark' ? ($image ? ' pc--spark-image' : ' pc--spark-text') : '') . ($image ? ' has-image' : '');
?>
<article class="<?= $classes ?>" data-filters="<?= html(implode(' ', $filters)) ?>"<?= $hidden ? ' hidden' : '' ?>>

  <?php if ($format === 'spark'): ?>
    <?= $media ?>
    <div class="pc__body">
      <p class="<?= $image ? 'pc__caption' : 'pc__quote' ?>"><?= $post->body()->isNotEmpty() ? $post->body()->excerpt(200) : $post->title()->html() ?></p>
      <?php $meta() ?>
      <?php snippet('card-tags', ['post' => $post]) ?>
    </div>

  <?php elseif ($format === 'thread'): ?>
    <div class="pc__body">
      <p class="pc__label"><?= $labels['thread'] ?></p>
      <div class="pc__row">
        <?php $title() ?>
        <?php if ($image): ?>
          <?php snippet('picture', ['file' => $image, 'widths' => [120, 240], 'sizes' => '7rem', 'class' => 'pc__thumb', 'attrs' => ['style' => 'aspect-ratio: ' . $ratio]]) ?>
        <?php endif ?>
      </div>
      <?php $meta() ?>
      <?php snippet('card-tags', ['post' => $post]) ?>
    </div>

  <?php elseif ($format === 'whatif'): ?>
    <div class="pc__body">
      <p class="pc__label"><?= $labels['whatif'] ?></p>
      <?php $title() ?>
      <?php if ($excerpt): ?><p class="pc__excerpt"><?= $excerpt ?></p><?php endif ?>
    </div>
    <?= $media ?>
    <div class="pc__foot">
      <span class="pc__cta" aria-hidden="true">Explore the idea &rarr;</span>
      <?php $meta() ?>
      <?php snippet('card-tags', ['post' => $post]) ?>
    </div>

  <?php else: ?>
    <?= $media ?>
    <div class="pc__body">
      <p class="pc__label"><?= $labels['longread'] ?><?php if ($minutes): ?> <span class="pc__time"><?= $minutes ?> min read</span><?php endif ?></p>
      <?php $title() ?>
      <?php if ($excerpt): ?><p class="pc__excerpt"><?= $excerpt ?></p><?php endif ?>
      <?php $meta() ?>
      <?php snippet('card-tags', ['post' => $post]) ?>
    </div>
  <?php endif ?>

</article>
