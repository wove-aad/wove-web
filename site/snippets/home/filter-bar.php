<?php
/**
 * Pinned filter bar with the site menu. Shown by assets/js/home.js once the
 * full filter controls have scrolled away (while the feed is on screen), and
 * on scroll-up elsewhere past the hero (menu button only).
 * Chips show a round thumbnail of the featured image where one is set: the
 * case study's hero image for clients, the service page's featured image
 * for services. Editorial tags have no image.
 * Usage: <?php snippet('home/filter-bar', ['clients' => $clients, 'topics' => $topics]) ?>
 */
?>
<div class="filter-bar" id="filter-bar" role="region" aria-label="Filters and menu" inert>
  <div class="filter-bar__inner">
    <button type="button" class="filter-bar__menu-btn" data-menu aria-label="Menu" aria-expanded="false" aria-controls="filter-bar-menu">
      <span class="filter-bar__menu-icon" aria-hidden="true"></span>
    </button>
    <div class="filter-bar__track">
      <button type="button" class="filter-bar__arrow filter-bar__arrow--prev" data-bar-step="-1" aria-label="Scroll filters left" tabindex="-1">&larr;</button>
      <div class="filter-bar__scroll">
        <div class="filter-bar__group" role="group" aria-label="Clients">
          <button type="button" class="bar-chip bar-chip--client bar-chip--all" data-filter="*" aria-pressed="true">All work <span class="bar-chip__count"><?= $totalCount ?></span></button>
          <?php foreach ($clients as $client):
            $image = $client['page']->caseStudyImages()->toFile();
          ?>
            <button type="button" class="bar-chip bar-chip--client<?= $image ? ' bar-chip--thumb' : '' ?>" data-filter="cs:<?= html($client['slug']) ?>" aria-pressed="false">
              <?php if ($image): ?><img class="bar-chip__thumb" src="<?= $image->crop(64, 64)->url() ?>" alt="" width="32" height="32"><?php endif ?>
              <?= html($client['name']) ?> <span class="bar-chip__count"><?= $client['count'] ?></span>
            </button>
          <?php endforeach ?>
        </div>
        <span class="filter-bar__divider" aria-hidden="true"></span>
        <div class="filter-bar__group" role="group" aria-label="Topics">
          <?php foreach ($topics as $key => $label):
            [$type, $slug] = explode(':', $key, 2);
            $image = $type === 'service' ? page('services/' . $slug)?->content()->get('image')->toFile() : null;
          ?>
            <button type="button" class="bar-chip<?= $image ? ' bar-chip--thumb' : '' ?>" data-filter="<?= html($key) ?>" aria-pressed="false">
              <?php if ($image): ?><img class="bar-chip__thumb" src="<?= $image->crop(64, 64)->url() ?>" alt="" width="32" height="32"><?php endif ?>
              <?= html($label) ?>
            </button>
          <?php endforeach ?>
        </div>
      </div>
      <button type="button" class="filter-bar__arrow filter-bar__arrow--next" data-bar-step="1" aria-label="Scroll filters right" tabindex="-1">&rarr;</button>
    </div>
    <span class="filter-bar__count" data-count></span>
  </div>
  <?php snippet('brand/menu') ?>
</div>
