<?php
/**
 * Feed layout options panel, for review (2026-10-29). Fixed to the bottom
 * corner of the homepage, always shown (2026-10-30).
 * Each link changes one option and keeps the others.
 * Usage: <?php snippet('home/feed-options', ['opt' => $opt]) ?>
 */

$groups = [
  'cols'    => ['Columns', ['3' => '3', '2' => '2']],
  'layout'  => ['Layout', ['grid' => 'Grid', 'masonry' => 'Masonry']],
  'heights' => ['Images', ['own' => 'Own shape', 'set' => 'Varied heights']],
  'card'    => ['Cards', ['framed' => 'Framed', 'open' => 'Open', 'tinted' => 'Tinted', 'compact' => 'Compact']],
];
$link = fn ($key, $value) => url() . '/?' . http_build_query(array_merge($opt, [$key => $value])) . '#feed';
?>
<aside class="feed-options" aria-label="Feed layout options">
  <p class="feed-options__title">Feed options</p>
  <?php foreach ($groups as $key => [$label, $values]): ?>
    <div class="feed-options__group">
      <span class="feed-options__label"><?= $label ?></span>
      <?php foreach ($values as $value => $text): ?>
        <a href="<?= $link($key, $value) ?>"<?= $opt[$key] === (string) $value ? ' aria-current="true"' : '' ?>><?= $text ?></a>
      <?php endforeach ?>
    </div>
  <?php endforeach ?>
</aside>
