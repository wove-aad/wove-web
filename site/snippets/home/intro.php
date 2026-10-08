<?php
/**
 * Homepage intro, between the hero and the feed: the description, then the
 * four services, each with a one-line description (wove_service_intro()).
 * Each service links to Our work filtered by it; on the homepage
 * assets/js/home.js filters the feed in place and scrolls to it instead.
 * Usage: <?php snippet('home/intro') ?>
 * The text comes from the Wove Mind page's Hero description (`intro`).
 */

$text     = $page->intro()->or('We help the people running Ireland’s public services, cultural institutions and mission-led organisations move from strategy to delivery.')->value();
$labels   = wove_service_labels();
$services = ['labs', 'strategy', 'brand', 'digital'];
?>
<section class="hx-intro" aria-label="What we do">
  <div class="hx-intro__inner">
    <p class="hx-intro__text"><?= html($text) ?></p>
    <ul class="hx-services" role="list">
      <?php foreach ($services as $slug): ?>
        <li>
          <a class="hx-service" href="<?= wove_work_url('service:' . $slug) ?>" data-jump-filter="service:<?= $slug ?>">
            <span class="hx-service__name"><?= html($labels[$slug]) ?> <span aria-hidden="true">&darr;</span></span>
            <span class="hx-service__line"><?= html(wove_service_intro($slug)) ?></span>
          </a>
        </li>
      <?php endforeach ?>
    </ul>
  </div>
</section>
