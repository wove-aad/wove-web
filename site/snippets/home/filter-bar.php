<?php
/**
 * Pinned filter bar. Shown by assets/js/home.js once the full filter
 * controls have scrolled away (while the feed is on screen), and on
 * scroll-up elsewhere past the hero (home button only). The home button
 * goes back to the top of the homepage; on other pages it links home.
 * (It replaced a site menu on 2026-10-08: Our people is two clicks away
 * from the top either way.)
 *
 * In the bar: All work, the three newest clients (case studies, newest
 * first) and the two services used most recently (by the newest post that
 * has them). The More panel lists every client (so the full list is in
 * one place), then the remaining services and the tags. Client and service chips show a round thumbnail of
 * the featured image where one is set: the case study's hero image, the
 * service page's featured image. Tags have no image. After the topics
 * come sectors, then Team: the people credited on posts (authors only),
 * with their photo or initials, filtering by author:{slug}. The button at
 * the left is an up arrow: back to the top on the homepage, a link to the
 * homepage elsewhere.
 * Usage: <?php snippet('home/filter-bar', ['clients' => $clients, 'topics' => $topics, 'authors' => $authors]) ?>
 */

// Services by the newest post or case study that uses them
$serviceOrder = [];
$feed = wove_feed();
foreach ($feed['items'] as $item) {
  foreach ($feed['keys'][$item->id()] ?? [] as $key) {
    if (str_starts_with($key, 'service:') && isset($topics[$key]) && !in_array($key, $serviceOrder, true)) {
      $serviceOrder[] = $key;
    }
  }
}
foreach (array_keys($topics) as $key) {
  if (str_starts_with($key, 'service:') && !in_array($key, $serviceOrder, true)) $serviceOrder[] = $key;
}
$tagKeys = array_values(array_filter(array_keys($topics), fn ($k) => !str_starts_with($k, 'service:')));

$barClients  = array_slice($clients, 0, 3);
$moreClients = array_slice($clients, 3);
$barServices = array_slice($serviceOrder, 0, 2);
$moreServices = array_slice($serviceOrder, 2);

$clientImage  = fn ($client) => $client['page']->caseStudyImages()->toFile();
$serviceImage = fn ($key) => page('services/' . substr($key, 8))?->content()->get('image')->toFile();
$thumb = fn ($image) => $image ? '<img class="bar-chip__thumb" src="' . $image->crop(64, 64)->url() . '" alt="" width="32" height="32">' : '';
$authors = $authors ?? [];
$sectors = $sectors ?? [];
$hasMore = $clients || $moreServices || $tagKeys || $sectors || $authors;
?>
<div class="filter-bar" id="filter-bar" role="region" aria-label="Filters and menu" inert>
  <div class="filter-bar__inner">
    <?php if ($page->isHomePage()): ?>
      <button type="button" class="filter-bar__menu-btn" data-top aria-label="Back to top"><svg class="filter-bar__home-icon" viewBox="0 0 20 20" aria-hidden="true" focusable="false"><path d="M4.5 3.5h11M10 16.5V7M5.5 11.5 10 7l4.5 4.5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
    <?php else: ?>
      <a class="filter-bar__menu-btn" href="<?= url() ?>" aria-label="Home"><svg class="filter-bar__home-icon" viewBox="0 0 20 20" aria-hidden="true" focusable="false"><path d="M4.5 3.5h11M10 16.5V7M5.5 11.5 10 7l4.5 4.5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
    <?php endif ?>
    <div class="filter-bar__track">
      <div class="filter-bar__chips" role="group" aria-label="Filters">
        <button type="button" class="bar-chip bar-chip--all" data-filter="*" aria-pressed="true">All work <span class="bar-chip__count"><?= $totalCount ?></span></button>
        <?php foreach ($barClients as $client): $image = $clientImage($client) ?>
          <button type="button" class="bar-chip<?= $image ? ' bar-chip--thumb' : '' ?>" data-filter="cs:<?= html($client['slug']) ?>" aria-pressed="false">
            <?= $thumb($image) ?><?= html($client['name']) ?> <span class="bar-chip__count"><?= $client['count'] ?></span>
          </button>
        <?php endforeach ?>
        <?php if ($barServices): ?><span class="filter-bar__divider" aria-hidden="true"></span><?php endif ?>
        <?php foreach ($barServices as $key): $image = $serviceImage($key) ?>
          <button type="button" class="bar-chip<?= $image ? ' bar-chip--thumb' : '' ?>" data-filter="<?= html($key) ?>" aria-pressed="false">
            <?= $thumb($image) ?><?= html($topics[$key]) ?>
          </button>
        <?php endforeach ?>
        <?php /* Phones: the rest swipe in the row too (also in More) */ ?>
        <?php foreach ($moreClients as $client): $image = $clientImage($client) ?>
          <button type="button" class="bar-chip bar-chip--narrow<?= $image ? ' bar-chip--thumb' : '' ?>" data-filter="cs:<?= html($client['slug']) ?>" aria-pressed="false">
            <?= $thumb($image) ?><?= html($client['name']) ?> <span class="bar-chip__count"><?= $client['count'] ?></span>
          </button>
        <?php endforeach ?>
        <?php foreach ($moreServices as $key): $image = $serviceImage($key) ?>
          <button type="button" class="bar-chip bar-chip--narrow<?= $image ? ' bar-chip--thumb' : '' ?>" data-filter="<?= html($key) ?>" aria-pressed="false">
            <?= $thumb($image) ?><?= html($topics[$key]) ?>
          </button>
        <?php endforeach ?>
        <?php foreach ($tagKeys as $key): ?>
          <button type="button" class="bar-chip bar-chip--narrow" data-filter="<?= html($key) ?>" aria-pressed="false"><?= html($topics[$key]) ?></button>
        <?php endforeach ?>
      </div>
      <?php if ($hasMore): ?>
        <button type="button" class="bar-chip bar-chip--more" data-more aria-expanded="false" aria-controls="filter-bar-more">
          <span data-more-label>More</span> <span class="bar-chip__caret" aria-hidden="true"></span>
        </button>
      <?php endif ?>
    </div>
  </div>
  <?php if ($hasMore): ?>
    <div class="filter-more" id="filter-bar-more" hidden>
      <div class="filter-more__inner">
        <?php if ($clients): ?>
          <div class="filter-more__group" role="group" aria-labelledby="filter-more-clients">
            <p class="filter-more__label" id="filter-more-clients">Clients</p>
            <?php foreach ($clients as $client): $image = $clientImage($client) ?>
              <button type="button" class="filter-more__item" data-filter="cs:<?= html($client['slug']) ?>" aria-pressed="false">
                <?= $thumb($image) ?><span class="filter-more__name"><?= html($client['name']) ?></span> <span class="bar-chip__count"><?= $client['count'] ?></span>
              </button>
            <?php endforeach ?>
          </div>
        <?php endif ?>
        <?php if ($moreServices): ?>
          <div class="filter-more__group" role="group" aria-labelledby="filter-more-services">
            <p class="filter-more__label" id="filter-more-services">Services</p>
            <?php foreach ($moreServices as $key): $image = $serviceImage($key) ?>
              <button type="button" class="filter-more__item" data-filter="<?= html($key) ?>" aria-pressed="false">
                <?= $thumb($image) ?><span class="filter-more__name"><?= html($topics[$key]) ?></span>
              </button>
            <?php endforeach ?>
          </div>
        <?php endif ?>
        <?php if ($tagKeys): ?>
          <div class="filter-more__group" role="group" aria-labelledby="filter-more-tags">
            <p class="filter-more__label" id="filter-more-tags">Topics</p>
            <?php foreach ($tagKeys as $key): ?>
              <button type="button" class="filter-more__item" data-filter="<?= html($key) ?>" aria-pressed="false"><span class="filter-more__name"><?= html($topics[$key]) ?></span></button>
            <?php endforeach ?>
          </div>
        <?php endif ?>
        <?php if ($sectors): ?>
          <div class="filter-more__group" role="group" aria-labelledby="filter-more-sectors">
            <p class="filter-more__label" id="filter-more-sectors">Sectors</p>
            <?php foreach ($sectors as $key => $label): ?>
              <button type="button" class="filter-more__item" data-filter="sector:<?= html($key) ?>" aria-pressed="false"><span class="filter-more__name"><?= html($label) ?></span></button>
            <?php endforeach ?>
          </div>
        <?php endif ?>
        <?php if ($authors): ?>
          <div class="filter-more__group" role="group" aria-labelledby="filter-more-team">
            <p class="filter-more__label" id="filter-more-team">Team</p>
            <?php foreach ($authors as $slug => $member): $name = (string) $member->name(); $avatar = $member->avatar() ?>
              <button type="button" class="filter-more__item" data-filter="author:<?= html($slug) ?>" aria-pressed="false">
                <?php if ($avatar): ?>
                  <img class="bar-chip__thumb" src="<?= $avatar->crop(64, 64)->url() ?>" alt="" width="32" height="32">
                <?php else: ?>
                  <span class="filter-more__initials" aria-hidden="true"><?= html(wove_initials($name)) ?></span>
                <?php endif ?>
                <span class="filter-more__name"><?= html($name) ?></span>
              </button>
            <?php endforeach ?>
          </div>
        <?php endif ?>
      </div>
    </div>
  <?php endif ?>
</div>
