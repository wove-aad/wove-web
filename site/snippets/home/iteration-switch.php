<?php
/**
 * The switcher for the homepage iterations (snippets/home/iteration.php),
 * fixed to the bottom corner. "Current" is ?home=0: the homepage as it is
 * (the switcher shows there with or without it).
 * Usage: <?php snippet('home/iteration-switch', ['v' => '0']) ?>
 */
?>
<aside class="feed-options hi-switch" aria-label="Homepage iterations">
  <p class="feed-options__title">Homepage</p>
  <div class="feed-options__group">
    <?php foreach (['0' => 'Current', '1' => 'A Featured', '2' => 'B Full image', '3' => 'C Right now'] as $k => $label): ?>
      <a href="<?= url() ?>/?home=<?= $k ?>"<?= (string) $v === (string) $k ? ' aria-current="true"' : '' ?>><?= $label ?></a>
    <?php endforeach ?>
  </div>
</aside>
