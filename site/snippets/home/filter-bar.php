<?php
/**
 * Pinned filter bar. Shown by assets/js/home.js once the feed heading and
 * its type filters have scrolled away (while the feed is on screen), and
 * on scroll-up elsewhere past the hero (home button only). The home button
 * goes back to the top of the homepage; on other pages it links home.
 *
 * In the bar: All work, then Projects, Thinking and News with their
 * counts, as beside the feed heading (2026-10-29; clients, services and a
 * More panel with topics, sectors and team before).
 * Usage: <?php snippet('home/filter-bar', ['types' => $types, 'totalCount' => $totalCount]) ?>
 */

$types = $types ?? [];
?>
<div class="filter-bar" id="filter-bar" role="region" aria-label="Filters" inert>
  <div class="filter-bar__inner">
    <?php if ($page->isHomePage()): ?>
      <button type="button" class="filter-bar__menu-btn" data-top aria-label="Back to top"><?php snippet('brand/top-icon') ?></button>
    <?php else: ?>
      <a class="filter-bar__menu-btn" href="<?= url() ?>" aria-label="Home"><?php snippet('brand/top-icon') ?></a>
    <?php endif ?>
    <div class="filter-bar__track">
      <div class="filter-bar__chips" role="group" aria-label="Filters">
        <button type="button" class="bar-chip bar-chip--all" data-filter="*" aria-pressed="true">All work <span class="bar-chip__count"><?= $totalCount ?></span></button>
        <?php foreach ($types as $key => $type): ?>
          <button type="button" class="bar-chip" data-filter="<?= html($key) ?>" aria-pressed="false"><?= html($type['label']) ?> <span class="bar-chip__count"><?= $type['count'] ?></span></button>
        <?php endforeach ?>
      </div>
    </div>
  </div>
</div>
