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
 * (templates/service.php); Impact areas lists every published tag in
 * the site's Taxonomies tab (wove_impact_areas()), in its order, each going to its impact area page
 * (templates/tag.php), after Work (templates/work.php). Then People, Wove Mind (every post,
 * templates/wove-mind-feed.php) and Get in touch. The lists are
 * <details> so they open without JS; assets/js/nav.js opens them on
 * hover where there is a mouse and closes them on an outside click or
 * Escape.
 *
 * Impact areas (2026-10-30, option C "Joined", chosen from four): with
 * data-menu="c" on <html> (snippets/header.php), choosing Impact areas
 * opens the impact areas as chips in one joined control in the menu's
 * place (.hx-areas), and an impact area page opens with them showing,
 * its own chip active; × or a click outside closes them, and the menu
 * item then names the current area (.hx-menu__now).
 * Services work the same way since 2026-10-30: choosing Services opens
 * the four services as chips (each with its service colour), and a
 * service page opens with them showing. data-open on .hx-row names the
 * open bar ('services' or 'impact'; empty when closed).
 */

$current = $current ?? null;
$mark    = fn ($id) => $current === $id ? ' aria-current="page"' : '';

$services = [];
foreach (['labs', 'strategy', 'brand', 'digital'] as $slug) {
  $services[] = ['href' => url('services/' . $slug), 'current' => $current === 'service:' . $slug, 'label' => wove_service_labels()[$slug], 'swatch' => 'service-' . $slug];
}

$impactAreas = [];
foreach (wove_impact_areas() as $slug => $label) {
  $impactAreas[] = ['href' => url('tag/' . $slug), 'current' => $current === 'tag:' . $slug, 'label' => $label, 'swatch' => ['coral', 'aqua', 'lilac', 'mint', 'stone-200'][count($impactAreas) % 5]];
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
      <details class="hx-menu<?= $hasCurrent ? ' hx-menu--current' : '' ?>" data-nav-menu data-bar-menu="<?= $id ?>">
        <summary class="hx-menu__toggle"><?= $menu['label'] ?><?php if ($hasCurrent): ?><span class="hx-menu__now"><?= html(array_values(array_filter($menu['items'], fn ($i) => $i['current']))[0]['label']) ?></span><?php endif ?> <span class="hx-menu__caret" aria-hidden="true"></span></summary>
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
  <?php foreach ($menus as $id => $menu): ?>
    <div class="hx-areas" data-areas="<?= $id ?>">
      <span class="hx-areas__label"><?= $menu['label'] ?></span>
      <div class="hx-areas__scroller">
      <ul class="hx-areas__list" role="list">
        <?php foreach ($menu['items'] as $item): ?>
          <li><a class="hx-chip" href="<?= $item['href'] ?>"<?= $item['current'] ? ' aria-current="page"' : '' ?>><span class="hx-chip__dot" style="--dot: var(--color-<?= $item['swatch'] ?>)" aria-hidden="true"></span><?= html($item['label']) ?></a></li>
        <?php endforeach ?>
      </ul>
      </div>
      <button type="button" class="hx-areas__close" data-areas-close aria-label="Close <?= Str::lower($menu['label']) ?>"><span aria-hidden="true">&times;</span></button>
    </div>
  <?php endforeach ?>
</div>
