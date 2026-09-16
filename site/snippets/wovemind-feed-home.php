<?php
/**
 * Wove Mind — homepage feed snippet
 * Usage: <?php snippet('wovemind-feed-home') ?>
 * Shows 4 most recent listed posts
 */

$posts = $site->find('wove-mind')->children()->listed()->sortBy('date', 'desc')->limit(4);

if ($posts->count() === 0) return;
?>

<section class="wovemind-feed-home">

  <header class="wovemind-feed-home__header">
    <h2 class="wovemind-feed-home__title">Wove Mind</h2>
    <a class="wovemind-feed-home__link" href="<?= $site->find('wove-mind')->url() ?>">
      See all
    </a>
  </header>

  <div class="wovemind-feed-home__grid">
    <?php foreach ($posts as $post): ?>
      <?php snippet('wovemind-card', ['post' => $post]) ?>
    <?php endforeach ?>
  </div>

</section>
