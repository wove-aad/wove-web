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
 * The date sits at the top right, beside the format label (sparks, which
 * have no label, keep it in the footer). The footer, under a hairline, has
 * the author with their photo or initials when one is credited (a link
 * that filters the feed to their posts), then the tags. Tags are links to the work feed; on the homepage assets/js/home.js filters in
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
$date = '<time class="pc__date" datetime="' . date('Y-m-d', $postMidnight) . '">' . $dateLabel . '</time>';
// Label row: the format pill on the left, the date on the right
$top = function (string $label) use ($date) { ?>
  <div class="pc__top"><p class="pc__label"><?= $label ?></p><?= $date ?></div>
<?php };
// Footer: author (photo or initials) and, for sparks, the date; then tags
$end = function (bool $withDate = false) use ($post, $author, $date) {
  $avatar = $author?->avatar();
  ?>
  <div class="pc__end">
    <?php if ($author || $withDate): ?>
      <div class="pc__by">
        <?php if ($author): ?>
          <a class="pc__author" href="<?= wove_work_url('author:' . wove_author_slug($author)) ?>">
            <?php if ($avatar): ?>
              <img class="pc__avatar" src="<?= $avatar->crop(48, 48)->url() ?>" alt="" width="24" height="24">
            <?php else: ?>
              <span class="pc__avatar pc__avatar--initials" aria-hidden="true"><?= html(wove_initials((string) $author->name())) ?></span>
            <?php endif ?>
            <span><?= $author->name()->html() ?></span>
          </a>
        <?php endif ?>
        <?php if ($withDate): ?><?= $date ?><?php endif ?>
      </div>
    <?php endif ?>
    <?php snippet('card-tags', ['post' => $post]) ?>
  </div>
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
      <?php $end(true) ?>
    </div>

  <?php elseif ($format === 'thread'): ?>
    <div class="pc__body">
      <?php $top($labels['thread']) ?>
      <div class="pc__row">
        <?php $title() ?>
        <?php if ($image): ?>
          <?php snippet('picture', ['file' => $image, 'widths' => [160, 320], 'sizes' => '9rem', 'class' => 'pc__thumb', 'attrs' => ['style' => 'aspect-ratio: ' . $ratio]]) ?>
        <?php endif ?>
      </div>
      <?php $end() ?>
    </div>

  <?php elseif ($format === 'whatif'): ?>
    <div class="pc__body">
      <?php $top($labels['whatif']) ?>
      <?php $title() ?>
      <?php if ($excerpt): ?><p class="pc__excerpt"><?= $excerpt ?></p><?php endif ?>
    </div>
    <?= $media ?>
    <div class="pc__foot">
      <span class="pc__cta" aria-hidden="true">Explore the idea &rarr;</span>
      <?php $end() ?>
    </div>

  <?php else: ?>
    <?= $media ?>
    <div class="pc__body">
      <?php $top($labels['longread'] . ($minutes ? ' <span class="pc__time">' . $minutes . ' min read</span>' : '')) ?>
      <?php $title() ?>
      <?php if ($excerpt): ?><p class="pc__excerpt"><?= $excerpt ?></p><?php endif ?>
      <?php $end() ?>
    </div>
  <?php endif ?>

</article>
