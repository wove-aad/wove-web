<?php
/**
 * Wove Mind — post card snippet
 * Usage: <?php snippet('wovemind-card', ['post' => $post]) ?>
 * Used unfiltered by the main Wove Mind feed and the homepage feed, so it
 * has to handle every format including project-highlight — which has no
 * page/URL and uses client()/excerpt() instead of title()/body().
 */

$format      = $post->format()->value();
$isHighlight = $format === 'project-highlight';
$hasTitle    = in_array($format, ['thread', 'whatif', 'longread']);
// $post->image() hits Kirby's built-in HasFiles::image() (first file
// uploaded to the page), not the "Featured image" content field —
// content()->get() is needed to reach the actual field value.
$image       = in_array($format, ['project-highlight', 'thread', 'whatif', 'longread']) ? $post->content()->get('image')->toFile() : null;
$showAuthor  = $post->show_author()->isTrue();
$author      = $showAuthor ? $post->author()->toUser() : null;

$formatLabels = [
  'spark'             => 'Spark',
  'project-highlight' => 'Project highlight',
  'thread'            => 'Thread',
  'whatif'            => 'What if',
  'longread'          => 'Long read',
];
?>

<article class="wovemind-card wovemind-card--<?= $format ?>">

  <?php if ($image): ?>
    <div class="wovemind-card__image">
      <img src="<?= $image->url() ?>" alt="<?= $image->alt()->html() ?>">
    </div>
  <?php endif ?>

  <div class="wovemind-card__body">

    <span class="wovemind-card__format"><?= $formatLabels[$format] ?? $format ?></span>

    <?php if ($hasTitle && $post->title()->isNotEmpty()): ?>
      <h2 class="wovemind-card__title">
        <a href="<?= $post->url() ?>"><?= $post->title()->html() ?></a>
      </h2>
    <?php elseif ($isHighlight): ?>
      <h2 class="wovemind-card__title"><?= $post->client()->html() ?></h2>
    <?php endif ?>

    <div class="wovemind-card__excerpt">
      <?= $isHighlight ? $post->excerpt()->html() : $post->body()->excerpt(160) ?>
    </div>

    <?php if ($isHighlight && $post->website()->isNotEmpty()): ?>
      <a href="<?= $post->website() ?>" class="wovemind-card__external" target="_blank" rel="noopener noreferrer">
        Visit website <span aria-hidden="true">&#8599;</span><span class="visually-hidden"> (opens in a new tab)</span>
      </a>
    <?php endif ?>

    <footer class="wovemind-card__footer">
      <?php if ($author): ?>
        <span class="wovemind-card__author"><?= $author->name()->html() ?></span>
      <?php endif ?>
      <time class="wovemind-card__date" datetime="<?= $post->date('Y-m-d') ?>">
        <?= $post->date('j M Y') ?>
      </time>
    </footer>

  </div>

</article>
