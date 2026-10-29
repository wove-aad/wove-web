<?php
/**
 * Logo, main menu and contact button: the top row of the homepage hero and
 * of every inner page header.
 * Usage: <?php snippet('brand/header-row', ['current' => 'people']) ?>
 * `current` marks a menu item as the current page ('people' or 'contact');
 * the other links are then muted.
 *
 * Menu (2026-10-29): Services and Impact areas open a list of filtered
 * views of the work feed, then People and Get in touch. Services are the
 * four service pages; impact areas are the editorial tags (site order)
 * that have work. Both go to the homepage feed with that filter selected
 * (service and tag pages redirect there); on the homepage, home.js filters
 * in place (data-jump-filter). The lists are <details> so they open
 * without JS; assets/js/nav.js closes them on an outside click or Escape.
 */

$current = $current ?? null;
$mark    = fn ($id) => $current === $id ? ' aria-current="page"' : '';

$services = [];
foreach (['labs', 'strategy', 'brand', 'digital'] as $slug) {
  $services[] = ['href' => url('services/' . $slug), 'key' => 'service:' . $slug, 'label' => wove_service_labels()[$slug]];
}

$impactAreas = [];
foreach (wove_feed()['topics'] as $key => $label) {
  if (str_starts_with($key, 'tag:')) {
    $impactAreas[] = ['href' => url('tag/' . substr($key, 4)), 'key' => $key, 'label' => $label];
  }
}

$menus = array_filter([
  'services' => ['label' => 'Services', 'items' => $services],
  'impact'   => ['label' => 'Impact areas', 'items' => $impactAreas],
], fn ($menu) => $menu['items']);
?>
<div class="hx-row">
  <a href="<?= url() ?>" class="hero-bar__logo"><?php snippet('brand/logo') ?></a>
  <nav class="hx-nav<?= $current === 'people' ? ' hx-nav--has-current' : '' ?>" aria-label="Main">
    <?php foreach ($menus as $id => $menu): ?>
      <details class="hx-menu" data-nav-menu>
        <summary class="hx-menu__toggle"><?= $menu['label'] ?> <span class="hx-menu__caret" aria-hidden="true"></span></summary>
        <ul class="hx-menu__list" role="list">
          <?php foreach ($menu['items'] as $item): ?>
            <li><a href="<?= $item['href'] ?>" data-jump-filter="<?= html($item['key']) ?>"><?= html($item['label']) ?></a></li>
          <?php endforeach ?>
        </ul>
      </details>
    <?php endforeach ?>
    <a href="<?= url('our-people') ?>"<?= $mark('people') ?>>People</a>
  </nav>
  <a href="<?= url('contact') ?>" class="btn btn--sm btn--primary hero-bar__cta"<?= $mark('contact') ?>>Get in touch</a>
</div>
