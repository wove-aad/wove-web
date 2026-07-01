<?php
/**
 * Wove Mind — single post template
 * File: site/templates/wovemind-post.php
 * Used by: Thread, What if, Long read
 * Sparks are unlisted and do not need a single template
 */

$format     = $page->format()->value();
$image      = $page->image();
$showAuthor = $page->show_author()->isTrue();
$author     = $showAuthor ? $page->author()->toUser() : null;
$tags       = $page->tags()->split(',');

$formatLabels = [
  'thread'   => 'Thread',
  'whatif'   => 'What if',
  'longread' => 'Long read',
];
?>

<?php snippet('header') ?>

<main class="wovemind-post wovemind-post--<?= $format ?>">

  <article class="wovemind-post__inner">

    <header class="wovemind-post__header">
      <span class="wovemind-post__format"><?= $formatLabels[$format] ?? $format ?></span>

      <?php if ($page->title()->isNotEmpty()): ?>
        <h1 class="wovemind-post__title"><?= $page->title()->html() ?></h1>
      <?php endif ?>

      <div class="wovemind-post__meta">
        <?php if ($author): ?>
          <span class="wovemind-post__author"><?= $author->name()->html() ?></span>
        <?php endif ?>
        <time class="wovemind-post__date" datetime="<?= $page->date('Y-m-d') ?>">
          <?= $page->date('j M Y') ?>
        </time>
      </div>
    </header>

    <?php if ($image): ?>
      <div class="wovemind-post__image">
        <img src="<?= $image->url() ?>" alt="<?= $image->alt()->html() ?>">
      </div>
    <?php endif ?>

    <div class="wovemind-post__body">
      <?= $page->body()->toBlocks() ?>
    </div>

    <?php if ($tags): ?>
      <footer class="wovemind-post__tags">
        <?php foreach ($tags as $tag): ?>
          <span class="wovemind-post__tag"><?= html($tag) ?></span>
        <?php endforeach ?>
      </footer>
    <?php endif ?>

  </article>

</main>

<?php snippet('footer') ?>
