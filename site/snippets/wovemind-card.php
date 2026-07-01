<?php
/**
 * Wove Mind — post card snippet
 * Usage: <?php snippet('wovemind-card', ['post' => $post]) ?>
 */

$format      = $post->format()->value();
$hasTitle    = in_array($format, ['thread', 'whatif', 'longread']);
$image       = in_array($format, ['thread', 'whatif', 'longread']) ? $post->image() : null;
$showAuthor  = $post->show_author()->isTrue();
$author      = $showAuthor ? $post->author()->toUser() : null;

$formatLabels = [
  'spark'    => 'Spark',
  'thread'   => 'Thread',
  'whatif'   => 'What if',
  'longread' => 'Long read',
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
    <?php endif ?>

    <div class="wovemind-card__excerpt">
      <?= $post->body()->excerpt(160) ?>
    </div>

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
