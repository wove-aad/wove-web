<?php
/**
 * Pinned header: the full header row (logo, menu, Get in touch) in a bar
 * that slides down on scroll-up once the page header has scrolled away
 * (assets/js/pages.js; on the homepage assets/js/home.js). It replaced
 * the back-to-top and logo bar on 2026-10-29.
 * Usage: <?php snippet('brand/menu-bar', ['current' => 'people']) ?>
 * `current` is passed on to brand/header-row.
 */
?>
<div class="filter-bar sticky-head" id="sticky-head" role="region" aria-label="Site header" inert data-menu-bar>
  <div class="sticky-head__inner">
    <?php snippet('brand/header-row', ['current' => $current ?? null]) ?>
  </div>
</div>
