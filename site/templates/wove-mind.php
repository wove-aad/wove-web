<?php
/**
 * Wove Mind — feed home template
 * File: site/templates/wove-mind.php
 *
 * Dark-first palette with time-of-day light mode, facet rail with
 * filter pills, flat card grid mixing all entry formats.
 * Client-side filtering: pills filter the grid in place, capped
 * at 5 items per filter with a "See all" link to /our-work.
 */

$entries = $page->children()->listed()->sortBy('date', 'desc')
  ->filter(fn ($p) => $p->format()->value() !== 'project-highlight');

$feedMax     = 20;
$totalCount  = $entries->count();
$feedItems   = $entries->limit($feedMax);

$serviceLabels = ['strategy' => 'Strategy', 'labs' => 'Labs', 'digital' => 'Digital', 'brand' => 'Brand'];
$siteTags      = $site->tags()->toStructure()->filterBy('active', 'true');
$tagSlugMap    = [];
foreach ($siteTags as $t) {
  $tagSlugMap[strtolower($t->name()->value())] = $t->slug()->value();
}

$usedServices = [];
$usedTags     = [];
foreach ($entries as $e) {
  foreach ($e->services()->split(',') as $s) {
    $s = trim($s);
    if ($s && isset($serviceLabels[$s])) $usedServices[$s] = $serviceLabels[$s];
  }
  foreach ($e->tags()->split(',') as $t) {
    $t = trim($t);
    if (!$t) continue;
    $slug = $tagSlugMap[strtolower($t)] ?? Str::slug($t);
    $tag  = $siteTags->findBy('slug', $slug);
    if ($tag) $usedTags[$slug] = $tag->name()->value();
  }
}

// Service promo cards, placed second in the grid when that service's filter is active
$servicePromos = [];
foreach ($usedServices as $slug => $label) {
  $servicePage = page('services/' . $slug);
  $promoImage  = $servicePage ? $servicePage->content()->get('image')->toFile() : null;
  $servicePromos[$slug] = [
    'label' => $label,
    'url'   => $servicePage ? $servicePage->url() : url('services/' . $slug),
    'image' => $promoImage ? $promoImage->resize(1200)->url() : null,
  ];
}
?>

<?php snippet('header', ['css' => ['/assets/css/feed.css']]) ?>

<div class="feed-wrap">

  <!-- HERO -->
  <section class="feed-hero">
    <h1 class="feed-hero__title"><?= $page->headline()->or('Shaping a better Ireland through strategic design and technology')->html() ?></h1>
    <p class="feed-hero__desc"><?= $page->tagline()->or('We help the people running Ireland\'s public services, cultural institutions, and mission-led organisations move from strategy to delivery.')->html() ?></p>
  </section>

  <div class="feed-shell">

    <aside class="feed-rail" aria-label="Filter">
      <div class="feed-explainer">
        <h3 class="feed-explainer__title">Strategic design</h3>
        <p class="feed-explainer__body">We use strategic design to help organisations move from insight to delivery &mdash; across services, systems, and culture.</p>
      </div>

      <div class="feed-rail__tags" id="feed-filter-pills">
        <button class="tag-pill is-active" data-filter="*" type="button">All</button>
        <?php foreach ($usedServices as $slug => $label): ?>
          <button class="tag-pill" data-filter="service:<?= $slug ?>" type="button"><?= html($label) ?></button>
        <?php endforeach ?>
        <?php foreach ($usedTags as $slug => $label): ?>
          <button class="tag-pill" data-filter="tag:<?= $slug ?>" type="button"><?= html($label) ?></button>
        <?php endforeach ?>
      </div>
    </aside>

    <!-- FEED -->
    <main class="feed" id="main">

      <div class="feed__header">
        <h2 class="feed__title">Latest from the studio</h2>
        <span class="feed__showing label" id="feed-count"><?= $totalCount ?> entries</span>
      </div>

      <div class="feed-cards" id="feed-grid">
        <?php foreach ($feedItems as $post):
          $services = implode(',', array_filter(array_map('trim', $post->services()->split(','))));
          $tags     = implode(',', array_filter(array_map('trim', $post->tags()->split(','))));
        ?>
          <div class="feed-item"
               data-services="<?= html($services) ?>"
               data-tags="<?= html($tags) ?>">
            <?php snippet('feed-card', ['post' => $post]) ?>
          </div>
        <?php endforeach ?>
        <?php foreach ($servicePromos as $slug => $promo): ?>
          <a class="feed-promo<?= $promo['image'] ? ' feed-promo--image' : '' ?>"
             href="<?= $promo['url'] ?>"
             data-promo="service:<?= $slug ?>"
             hidden>
            <?php if ($promo['image']): ?>
              <img class="feed-promo__img" src="<?= $promo['image'] ?>" alt="" loading="lazy">
            <?php endif ?>
            <span class="feed-promo__btn">See all our <?= html($promo['label']) ?> work &rarr;</span>
          </a>
        <?php endforeach ?>
      </div>

      <div class="feed__footer" id="feed-more" hidden>
        <a href="/our-work" class="feed__more" id="feed-more-link">See all work &rarr;</a>
      </div>

    </main>

  </div>

  <!-- PROCESS BLOCK -->
  <section class="feed-process">
    <div class="feed-process__intro">
      <h2 class="feed-process__title">Transforming process</h2>
      <p class="feed-process__desc">How strategic design reshapes the way organisations move from insight through to delivery.</p>
    </div>
    <div class="feed-process__diagram">
      <div class="feed-process__ph">Double Diamond / Process Diagram Placeholder</div>
    </div>
  </section>

</div>

<script>window.__feedLabels = <?= json_encode(
  array_merge(
    array_map(fn ($l) => ['label' => $l, 'type' => 'service'], $usedServices),
    array_map(fn ($l) => ['label' => $l, 'type' => 'tag'], $usedTags)
  ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;</script>

<script>
(function () {
  var pills = document.querySelectorAll('#feed-filter-pills .tag-pill');
  var items = document.querySelectorAll('#feed-grid .feed-item');
  var moreWrap = document.getElementById('feed-more');
  var moreLink = document.getElementById('feed-more-link');
  var countEl  = document.getElementById('feed-count');
  var grid     = document.getElementById('feed-grid');
  var promos   = document.querySelectorAll('#feed-grid .feed-promo');
  var labels   = window.__feedLabels || {};
  if (!pills.length || !items.length) return;

  var LIMIT = 5;
  var attrMap = { service: 'data-services', tag: 'data-tags' };

  function entryWord(n) { return n + ' entr' + (n === 1 ? 'y' : 'ies'); }

  function applyFilter(filter) {
    var matched = [];
    items.forEach(function (item) {
      if (filter === '*') {
        matched.push(item);
      } else {
        var parts = filter.split(':');
        var type  = parts[0];
        var value = parts.slice(1).join(':');
        var attr  = item.getAttribute(attrMap[type] || '') || '';
        if (attr.split(',').indexOf(value) !== -1) {
          matched.push(item);
        }
      }
    });

    var showAll = filter === '*';
    var cap = showAll ? items.length : LIMIT;
    var shown = 0;

    items.forEach(function (item) {
      if (matched.indexOf(item) !== -1 && shown < cap) {
        item.hidden = false;
        shown++;
      } else {
        item.hidden = true;
      }
    });

    countEl.textContent = entryWord(matched.length);

    // Show the matching service promo in position 2 (or last, if only one entry matches)
    promos.forEach(function (promo) { promo.hidden = true; });
    var promo = grid.querySelector('.feed-promo[data-promo="' + filter + '"]');
    if (promo) {
      var first = matched[0];
      grid.insertBefore(promo, first ? first.nextSibling : grid.firstChild);
      promo.hidden = false;
    }

    if (!showAll && matched.length > LIMIT) {
      var slug = filter.split(':').slice(1).join(':');
      var meta = labels[slug];
      var name = meta ? meta.label : slug;
      moreLink.href = '/our-work?filter=' + encodeURIComponent(filter);
      moreLink.textContent = 'See all ' + name + ' →';
      moreWrap.hidden = false;
    } else {
      moreLink.href = '/our-work';
      moreLink.textContent = 'See all work →';
      moreWrap.hidden = false;
    }
  }

  // Bring the top of the feed back into view if the reader has scrolled past it
  var shell = document.querySelector('.feed-shell');
  function scrollToFeedTop() {
    if (!shell || shell.getBoundingClientRect().top >= 0) return;
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    shell.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
  }

  pills.forEach(function (pill) {
    pill.addEventListener('click', function () {
      pills.forEach(function (p) { p.classList.remove('is-active'); });
      pill.classList.add('is-active');
      applyFilter(pill.getAttribute('data-filter'));
      scrollToFeedTop();
    });
  });

  applyFilter('*');
})();
</script>

<script>
(function() {
  var h = new Date().getHours();
  if (h >= 7 && h < 19) {
    document.documentElement.setAttribute('data-theme', 'light');
  }
})();
</script>

<?php snippet('footer') ?>
