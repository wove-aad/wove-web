<?php
/**
 * Error page — styled 404 with tag cloud navigation
 * File: site/templates/error.php
 */

$serviceLinks = [
  ['label' => 'Labs',     'url' => '/our-work?filter=service:labs'],
  ['label' => 'Strategy', 'url' => '/our-work?filter=service:strategy'],
  ['label' => 'Brand',    'url' => '/our-work?filter=service:brand'],
  ['label' => 'Digital',  'url' => '/our-work?filter=service:digital'],
];

$pageLinks = [
  ['label' => 'Home',       'url' => '/'],
  ['label' => 'Our Work',   'url' => '/our-work'],
  ['label' => 'Our People', 'url' => '/our-people'],
  ['label' => 'Wove Mind',  'url' => '/wove-mind'],
  ['label' => 'Contact',    'url' => '/contact'],
];
?>

<?php snippet('header', ['css' => ['/assets/css/feed.css']]) ?>

<div class="feed-wrap">

  <main id="main" class="error-page">
    <div class="error-page__hero">
      <p class="error-page__code">404</p>
      <h1 class="error-page__title">Page not found</h1>
      <p class="error-page__message">The page you're looking for doesn't exist or has been moved.</p>
    </div>

    <nav class="error-page__nav" aria-label="Browse by service">
      <p class="error-page__nav-label">Services</p>
      <div class="error-page__pills">
        <?php foreach ($serviceLinks as $link): ?>
          <a href="<?= $link['url'] ?>" class="tag-pill"><?= $link['label'] ?></a>
        <?php endforeach ?>
      </div>
    </nav>

    <nav class="error-page__nav" aria-label="Quick links">
      <p class="error-page__nav-label">Pages</p>
      <div class="error-page__pills">
        <?php foreach ($pageLinks as $link): ?>
          <a href="<?= $link['url'] ?>" class="tag-pill"><?= $link['label'] ?></a>
        <?php endforeach ?>
      </div>
    </nav>

    <?php snippet('tag-cloud') ?>
  </main>

</div>

<script>
(function() {
  var h = new Date().getHours();
  if (h >= 7 && h < 19) {
    document.documentElement.setAttribute('data-theme', 'light');
  }
})();
</script>

<?php snippet('footer') ?>
