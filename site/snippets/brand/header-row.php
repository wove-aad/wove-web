<?php
/**
 * Logo, nav and contact button: the top row of the homepage hero and of
 * every inner page header.
 * Usage: <?php snippet('brand/header-row', ['current' => 'work']) ?>
 * `current` marks a nav item as the current page ('work', 'people' or
 * 'contact'); the other links are then muted. 'nav' => false leaves out
 * the Our work / Our people links (the homepage links to both below its
 * hero text).
 */

$current = $current ?? null;
$showNav = $nav ?? true;
$mark    = fn ($id) => $current === $id ? ' aria-current="page"' : '';
?>
<div class="hx-row<?= $showNav ? '' : ' hx-row--no-nav' ?>">
  <a href="<?= url() ?>" class="hero-bar__logo"><?= $site->title()->html() ?>.</a>
  <?php if ($showNav): ?>
  <nav class="hx-nav<?= in_array($current, ['work', 'people'], true) ? ' hx-nav--has-current' : '' ?>" aria-label="Main">
    <a href="<?= wove_work_url() ?>"<?= $mark('work') ?>>Our work</a>
    <a href="<?= url('our-people') ?>"<?= $mark('people') ?>>Our people</a>
  </nav>
  <?php endif ?>
  <a href="<?= url('contact') ?>" class="btn btn--sm btn--primary hero-bar__cta"<?= $mark('contact') ?>>Get in touch</a>
</div>
