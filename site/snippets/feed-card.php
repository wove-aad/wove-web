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
 * Editorial tags shown on all types except sparks.
 */

$format  = $post->format()->value();
$isSpark = $format === 'spark';
$isLinked = in_array($format, ['thread', 'whatif', 'longread']);

$image = $post->content()->get('image')->toFile();

$author = $post->show_author()->toBool() && $post->author()->isNotEmpty()
  ? $post->author()->toUser()
  : null;

$postMidnight = strtotime('midnight', $post->date()->toTimestamp());
$daysAgo      = (int) round((strtotime('today') - $postMidnight) / 86400);
$dateLabel    = match (true) {
  $daysAgo === 0 => 'Today',
  $daysAgo === 1 => 'Yesterday',
  $daysAgo <= 6  => 'Last ' . date('l', $postMidnight),
  default        => date('j M', $postMidnight),
};

$tags     = $post->tags()->split(',');
$siteTags = $site->tags()->toStructure();

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
          <?php endif ?>
        </span>
        <span class="feed-card__author"><?= $author->name()->html() ?></span>
        <span class="feed-card__dot">&middot;</span>
      <?php endif ?>
      <time datetime="<?= date('Y-m-d', $postMidnight) ?>"><?= $dateLabel ?></time>
    </div>
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
            <?php endif ?>
          </span>
          <span class="feed-card__author"><?= $author->name()->html() ?></span>
          <span class="feed-card__dot">&middot;</span>
        <?php endif ?>
        <time datetime="<?= date('Y-m-d', $postMidnight) ?>"><?= $dateLabel ?></time>
      </div>
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
      <?php if (!empty($tags)): ?>
        <div class="feed-card__tags">
          <?php foreach ($tags as $tag):
            $tagMatch = $siteTags->findBy('name', $tag);
            $tagHref  = $tagMatch ? '/tag/' . $tagMatch->slug()->value() : '/tag/' . Str::slug($tag);
          ?>
            <a href="<?= $tagHref ?>" class="feed-card__tag"><?= html($tag) ?></a>
          <?php endforeach ?>
        </div>
      <?php endif ?>
      <h3 class="feed-card__title"><?= $post->title()->html() ?></h3>
      <?php if ($post->body()->isNotEmpty()): ?>
        <p class="feed-card__excerpt"><?= $post->body()->excerpt(120) ?></p>
      <?php endif ?>
      <div class="feed-card__meta">
        <?php if ($author): ?>
          <span class="feed-card__avatar">
            <?php if ($avatar = $author->avatar()): ?>
              <img src="<?= $avatar->url() ?>" alt="" loading="lazy">
            <?php endif ?>
          </span>
          <span class="feed-card__author"><?= $author->name()->html() ?></span>
          <span class="feed-card__dot">&middot;</span>
        <?php endif ?>
        <time datetime="<?= date('Y-m-d', $postMidnight) ?>"><?= $dateLabel ?></time>
      </div>
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
    <?php if (!empty($tags)): ?>
      <div class="feed-card__tags">
        <?php foreach ($tags as $tag):
          $tagMatch = $siteTags->findBy('name', $tag);
          $tagHref  = $tagMatch ? '/tag/' . $tagMatch->slug()->value() : '/tag/' . Str::slug($tag);
        ?>
          <a href="<?= $tagHref ?>" class="feed-card__tag"><?= html($tag) ?></a>
        <?php endforeach ?>
      </div>
    <?php endif ?>
    <h3 class="feed-card__title"><?= $post->title()->html() ?></h3>
    <?php if ($post->body()->isNotEmpty()): ?>
      <p class="feed-card__excerpt"><?= $post->body()->excerpt(120) ?></p>
    <?php endif ?>
    <div class="feed-card__meta">
      <?php if ($author): ?>
        <span class="feed-card__avatar">
          <?php if ($avatar = $author->avatar()): ?>
            <img src="<?= $avatar->url() ?>" alt="" loading="lazy">
          <?php endif ?>
        </span>
        <span class="feed-card__author"><?= $author->name()->html() ?></span>
        <span class="feed-card__dot">&middot;</span>
      <?php endif ?>
      <time datetime="<?= date('Y-m-d', $postMidnight) ?>"><?= $dateLabel ?></time>
    </div>
    <?php if ($isLinked): ?>
      <a href="<?= $post->url() ?>" class="feed-card__link" tabindex="-1" aria-hidden="true"></a>
    <?php endif ?>
  </article>

<?php endif ?>
