<?php
/**
 * Wove Mind — feed template
 * File: site/templates/wovemind.php
 */

$posts = $page->children()->listed()->sortBy('date', 'desc');
?>

<?php snippet('header') ?>

<main class="wovemind-feed">

  <header class="wovemind-feed__header">
    <h1><?= $page->title()->html() ?></h1>
  </header>

  <div class="wovemind-feed__grid">
    <?php foreach ($posts as $post): ?>
      <?php snippet('wovemind-card', ['post' => $post]) ?>
    <?php endforeach ?>
  </div>

</main>

<?php snippet('footer') ?>
