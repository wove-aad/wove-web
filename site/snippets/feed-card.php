<?php
/**
 * Feed card snippet — new feed design system
 * Usage: <?php snippet('feed-card', ['post' => $post]) ?>
 *
 * Handles all four entry formats: spark, thread, whatif, longread.
 * Three visual treatments:
 *   - Standard card (.feed-card): image + body — threads, whatif, longread with image; sparks with image
 *   - Compact card (.feed-card--compact): no image — threads, whatif without image
 *   - Spark card (.feed-card--spark): text-only quote — sparks without image
 *
 * Format labels shown only for "What If" (blue) and "Long Read" (purple).
 * Excerpts shown on whatif and longread cards, omitted from threads.
 */

$format  = $post->format()->value();
$isSpark = $format === 'spark';
$isLinked = in_array($format, ['thread', 'whatif', 'longread', 'project-highlight']);

$image = $post->content()->get('image')->toFile();

$author = $post->show_author()->toBool() && $post->author()->isNotEmpty()
  ? $post->author()->toUser()
  : null;

// Initials shown in the avatar circle when the author has no profile image
$authorInitials = $author
  ? Str::upper(implode('', array_map(fn ($w) => mb_substr($w, 0, 1), array_slice(preg_split('/\s+/', trim($author->name()->value() ?? '')), 0, 2))))
  : '';

$postMidnight = strtotime('midnight', $post->date()->toTimestamp());
$daysAgo      = (int) round((strtotime('today') - $postMidnight) / 86400);
$dateLabel    = match (true) {
  $daysAgo === 0 => 'Today',
  $daysAgo === 1 => 'Yesterday',
  $daysAgo <= 6  => 'Last ' . date('l', $postMidnight),
  default        => date('j M', $postMidnight),
};

$showFormat = in_array($format, ['whatif', 'longread']);
$formatLabels = ['whatif' => 'What If', 'longread' => 'Long Read'];
?>

<?php if ($isSpark && !$image): ?>

  <article class="feed-card--spark">
    <p class="feed-card__quote"><?= $post->body()->excerpt(200) ?></p>
    <div class="feed-card__meta">
      <?php if ($author): ?>
        <span class="feed-card__avatar">
          <?php if ($avatar = $author->avatar()): ?>
            <img src="<?= $avatar->url() ?>" alt="" loading="lazy">
          <?php else: ?>
            <?= html($authorInitials) ?>
          <?php endif ?>
        </span>
        <span class="feed-card__author"><?= $author->name()->html() ?></span>
        <span class="feed-card__dot">&middot;</span>
      <?php endif ?>
      <time datetime="<?= date('Y-m-d', $postMidnight) ?>"><?= $dateLabel ?></time>
    </div>
    <?php snippet('card-tags', ['post' => $post]) ?>
  </article>

<?php elseif ($isSpark && $image): ?>

  <article class="feed-card">
    <div class="feed-card__media">
      <img src="<?= $image->url() ?>" alt="" loading="lazy">
    </div>
    <div class="feed-card__body">
      <p class="feed-card__quote"><?= $post->body()->excerpt(200) ?></p>
      <div class="feed-card__meta">
        <?php if ($author): ?>
          <span class="feed-card__avatar">
            <?php if ($avatar = $author->avatar()): ?>
              <img src="<?= $avatar->url() ?>" alt="" loading="lazy">
            <?php else: ?>
              <?= html($authorInitials) ?>
            <?php endif ?>
          </span>
          <span class="feed-card__author"><?= $author->name()->html() ?></span>
          <span class="feed-card__dot">&middot;</span>
        <?php endif ?>
        <time datetime="<?= date('Y-m-d', $postMidnight) ?>"><?= $dateLabel ?></time>
      </div>
      <?php snippet('card-tags', ['post' => $post]) ?>
    </div>
  </article>

<?php elseif ($image): ?>

  <article class="feed-card">
    <div class="feed-card__media">
      <img src="<?= $image->url() ?>" alt="" loading="lazy">
    </div>
    <div class="feed-card__body">
      <?php if ($showFormat): ?>
        <span class="feed-card__format feed-card__format--<?= $format ?>"><?= $formatLabels[$format] ?></span>
      <?php endif ?>
      <h3 class="feed-card__title"><?= $post->title()->html() ?></h3>
      <?php if ($format !== 'thread' && $post->body()->isNotEmpty()): ?>
        <p class="feed-card__excerpt"><?= $post->body()->excerpt(120) ?></p>
      <?php endif ?>
      <div class="feed-card__meta">
        <?php if ($author): ?>
          <span class="feed-card__avatar">
            <?php if ($avatar = $author->avatar()): ?>
              <img src="<?= $avatar->url() ?>" alt="" loading="lazy">
            <?php else: ?>
              <?= html($authorInitials) ?>
            <?php endif ?>
          </span>
          <span class="feed-card__author"><?= $author->name()->html() ?></span>
          <span class="feed-card__dot">&middot;</span>
        <?php endif ?>
        <time datetime="<?= date('Y-m-d', $postMidnight) ?>"><?= $dateLabel ?></time>
      </div>
      <?php snippet('card-tags', ['post' => $post]) ?>
    </div>
    <?php if ($isLinked): ?>
      <a href="<?= $post->url() ?>" class="feed-card__link" tabindex="-1" aria-hidden="true"></a>
    <?php endif ?>
  </article>

<?php else: ?>

  <article class="feed-card--compact">
    <?php if ($showFormat): ?>
      <span class="feed-card__format feed-card__format--<?= $format ?>"><?= $formatLabels[$format] ?></span>
    <?php endif ?>
    <h3 class="feed-card__title"><?= $post->title()->html() ?></h3>
    <?php if ($format !== 'thread' && $post->body()->isNotEmpty()): ?>
      <p class="feed-card__excerpt"><?= $post->body()->excerpt(120) ?></p>
    <?php endif ?>
    <div class="feed-card__meta">
      <?php if ($author): ?>
        <span class="feed-card__avatar">
          <?php if ($avatar = $author->avatar()): ?>
            <img src="<?= $avatar->url() ?>" alt="" loading="lazy">
          <?php else: ?>
            <?= html($authorInitials) ?>
          <?php endif ?>
        </span>
        <span class="feed-card__author"><?= $author->name()->html() ?></span>
        <span class="feed-card__dot">&middot;</span>
      <?php endif ?>
      <time datetime="<?= date('Y-m-d', $postMidnight) ?>"><?= $dateLabel ?></time>
    </div>
    <?php snippet('card-tags', ['post' => $post]) ?>
    <?php if ($isLinked): ?>
      <a href="<?= $post->url() ?>" class="feed-card__link" tabindex="-1" aria-hidden="true"></a>
    <?php endif ?>
  </article>

<?php endif ?>
