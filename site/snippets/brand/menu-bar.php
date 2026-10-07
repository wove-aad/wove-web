<?php
/**
 * Pinned bar for pages without feed filters: the menu button and the logo.
 * It shows on scroll-up once the page header has scrolled away
 * (assets/js/pages.js). The feed pages use home/filter-bar instead.
 */
?>
<div class="filter-bar" id="filter-bar" role="region" aria-label="Menu" inert data-menu-bar>
  <div class="filter-bar__inner">
    <button type="button" class="filter-bar__menu-btn" data-menu aria-label="Menu" aria-expanded="false" aria-controls="filter-bar-menu">
      <span class="filter-bar__menu-icon" aria-hidden="true"></span>
    </button>
    <a class="filter-bar__logo" href="<?= url() ?>"><?= $site->title()->html() ?>.</a>
  </div>
  <?php snippet('brand/menu') ?>
</div>
