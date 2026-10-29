<?php
/**
 * Homepage post card (the "Framed" treatment).
 * Usage: <?php snippet('home/post-card', ['post' => $entry, 'filters' => [...], 'hidden' => false]) ?>
 *
 * - One layout for every format (2026-10-29): the image full width at the
 *   top, keeping its own shape, then the format label, title, excerpt and
 *   footer stacked underneath. Formats differ only in colour: long reads
 *   add a reading time, What Ifs sit on a pale blue card with "Explore the
 *   idea". Sparks show their text in place of a title.
 * - Posts from a project lead with the client name in bold, with the title
 *   under it as the subtitle; posts without one lead with the title. The
 *   subtitle and excerpt are one line each.
 * The footer has one row with the author (photo or initials, a link that
 * filters the feed to their posts) and the date on the right, then the
 * tags underneath, small and quiet. Tags are links to the work feed; on the homepage assets/js/home.js filters in
 * place instead. `filters` (from the controller) drives the data-filters
 * attribute the script matches against.
 */

$format = $post->format()->value() ?: 'thread';
$image  = $post->content()->get('image')->toFile();
$author = function_exists('wove_entry_author') ? wove_entry_author($post) : null;
$client = $post->case_study()->toPages()->first();
$client = $client ? $client->eyebrow()->or($client->title())->value() : '';

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
  $words   = str_word_count(wove_mind_plain_text($post->body()->value()) . ' ' . wove_mind_blocks_text((string) $post->blocks()->value()));
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
$top = function (string $label) { ?>
  <p class="pc__label"><?= $label ?></p>
<?php };
// Footer: author (photo or initials), then tags with the date at the right
$end = function () use ($post, $author, $date) {
  $avatar = $author?->avatar();
  ?>
  <div class="pc__end">
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
        <?= $date ?>
    </div>
    <?php snippet('card-tags', ['post' => $post]) ?>
  </div>
<?php };
$title = function () use ($post, $linked, $client) {
  $text = $client
    ? '<span class="pc__client">' . html($client) . '</span> <span class="pc__subtitle">' . $post->title()->html() . '</span>'
    : $post->title()->html();
  ?>
  <h3 class="pc__title<?= $client ? ' pc__title--client' : '' ?>"><?php if ($linked): ?><a class="pc__link" href="<?= $post->url() ?>"><?= $text ?></a><?php else: ?><?= $text ?><?php endif ?></h3>
<?php };
$classes = 'pc pc--' . $format . ($format === 'spark' ? ($image ? ' pc--spark-image' : ' pc--spark-text') : '') . ($image ? ' has-image' : '');
?>
<article class="<?= $classes ?>" data-filters="<?= html(implode(' ', $filters)) ?>"<?= $hidden ? ' hidden' : '' ?>>

  <?= $media ?>
  <div class="pc__body">
    <?php if ($format === 'spark'): ?>
      <p class="<?= $image ? 'pc__caption' : 'pc__quote' ?>"><?= $post->body()->isNotEmpty() ? $post->body()->excerpt(200) : $post->title()->html() ?></p>
    <?php else: ?>
      <?php $top($labels[$format] . ($minutes ? ' <span class="pc__time">' . $minutes . ' min read</span>' : '')) ?>
      <?php $title() ?>
      <?php if ($excerpt): ?><p class="pc__excerpt"><?= $excerpt ?></p><?php endif ?>
      <?php if ($format === 'whatif'): ?><span class="pc__cta" aria-hidden="true">Explore the idea &rarr;</span><?php endif ?>
    <?php endif ?>
    <?php $end() ?>
  </div>

</article>
