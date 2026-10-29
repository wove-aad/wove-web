<?php
/**
 * Pinned bar for pages without feed filters: a back-to-top button and the
 * logo. It shows on scroll-up once the page header has scrolled away
 * (assets/js/pages.js). The feed pages use home/filter-bar instead. The
 * back-to-top button replaced a menu on 2026-10-08.
 */
?>
<div class="filter-bar" id="filter-bar" role="region" aria-label="Page" inert data-menu-bar>
  <div class="filter-bar__inner">
    <button type="button" class="filter-bar__menu-btn" data-top aria-label="Back to top"><?php snippet('brand/top-icon') ?></button>
    <a class="filter-bar__logo" href="<?= url() ?>"><?php snippet('brand/logo') ?></a>
  </div>
</div>
