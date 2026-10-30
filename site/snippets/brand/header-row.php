<?php
/**
 * Logo, main menu and contact button: the top row of the homepage hero and
 * of every inner page header.
 * Usage: <?php snippet('brand/header-row', ['current' => 'people']) ?>
 * `current` marks a menu item as the current page ('work', 'people', 'wove-mind',
 * 'contact', 'service:{slug}' or 'tag:{slug}'); the other links are then
 * muted.
 *
 * Menu (2026-10-29): Work, then Services lists the four service pages
 * (templates/service.php); Impact areas lists the editorial tags (site
 * order) that have work, each going to its impact area page
 * (templates/tag.php), after Work (templates/work.php). Then People, Wove Mind (every post,
 * templates/wove-mind-feed.php) and Get in touch. The lists are
 * <details> so they open without JS; assets/js/nav.js opens them on
 * hover where there is a mouse and closes them on an outside click or
 * Escape.
 */

$current = $current ?? null;
$mark    = fn ($id) => $current === $id ? ' aria-current="page"' : '';

$services = [];
foreach (['labs', 'strategy', 'brand', 'digital'] as $slug) {
  $services[] = ['href' => url('services/' . $slug), 'current' => $current === 'service:' . $slug, 'label' => wove_service_labels()[$slug]];
}

$impactAreas = [];
foreach (wove_feed()['topics'] as $key => $label) {
  if (str_starts_with($key, 'tag:')) {
    $impactAreas[] = ['href' => url('tag/' . substr($key, 4)), 'current' => $current === $key, 'label' => $label];
  }
}

$menus = array_filter([
  'services' => ['label' => 'Services', 'items' => $services],
  'impact'   => ['label' => 'Impact areas', 'items' => $impactAreas],
], fn ($menu) => $menu['items']);
?>
<div class="hx-row">
  <a href="<?= url() ?>" class="hero-bar__logo"><?php snippet('brand/logo') ?></a>
  <nav class="hx-nav<?= in_array($current, ['work', 'people', 'wove-mind'], true) || preg_match('/^(service|tag):/', (string) $current) ? ' hx-nav--has-current' : '' ?>" aria-label="Main">
    <a href="<?= url('work') ?>"<?= $mark('work') ?>>Work</a>
    <?php foreach ($menus as $id => $menu): $hasCurrent = in_array(true, array_column($menu['items'], 'current'), true) ?>
      <details class="hx-menu<?= $hasCurrent ? ' hx-menu--current' : '' ?>" data-nav-menu>
        <summary class="hx-menu__toggle"><?= $menu['label'] ?> <span class="hx-menu__caret" aria-hidden="true"></span></summary>
        <ul class="hx-menu__list" role="list">
          <?php foreach ($menu['items'] as $item): ?>
            <li><a href="<?= $item['href'] ?>"<?= !empty($item['current']) ? ' aria-current="page"' : '' ?>><?= html($item['label']) ?></a></li>
          <?php endforeach ?>
        </ul>
      </details>
    <?php endforeach ?>
    <a href="<?= url('our-people') ?>"<?= $mark('people') ?>>People</a>
    <a href="<?= url('wove-mind') ?>"<?= $mark('wove-mind') ?>>Wove Mind</a>
  </nav>
  <a href="<?= url('contact') ?>" class="btn btn--sm btn--primary hero-bar__cta"<?= $mark('contact') ?>>Get in touch</a>
</div>
