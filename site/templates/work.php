<?php
/**
 * Our Work — case study portfolio grid with tag-cloud filtering
 * File: site/templates/work.php
 *
 * Uses the feed design system (dark-first, time-of-day light mode).
 * Route: /our-work → page('work') (see site/config/config.php)
 */

$caseStudies = kirby()->collection('case-studies');

$serviceLabels = ['strategy' => 'Strategy', 'labs' => 'Labs', 'digital' => 'Digital', 'brand' => 'Brand'];
$sectorLabels = [
  'arts-and-culture'  => 'Arts and Culture',
  'public-service'    => 'Public Service',
  'higher-education'  => 'Higher Education',
  'non-profit'        => 'Non-profit and Mission-led',
  'founders-ventures' => 'Founders and Ventures',
];
$tagStructure = $site->tags()->toStructure();

$usedServices = [];
$usedSectors  = [];
$usedTags     = [];

foreach ($caseStudies as $cs) {
  foreach ($cs->services()->split(',') as $s) {
    if (isset($serviceLabels[$s])) $usedServices[$s] = $serviceLabels[$s];
  }
  foreach ($cs->sectors()->split(',') as $s) {
    if (isset($sectorLabels[$s])) $usedSectors[$s] = $sectorLabels[$s];
  }
  foreach ($cs->impactAreas()->split(',') as $slug) {
    $tag = $tagStructure->findBy('slug', $slug);
    if ($tag) $usedTags[$slug] = $tag->name()->value();
  }
}
?>

<?php snippet('header', ['css' => ['/assets/css/feed.css']]) ?>

<div class="feed-wrap">

  <nav class="tag-breadcrumb" aria-label="Breadcrumb">
    <a href="/wove-mind">Feed</a>
    <span class="tag-breadcrumb__sep">/</span>
    <span>Our Work</span>
  </nav>

  <div class="tag-hero">
    <h1 class="tag-hero__name">Our Work</h1>
    <div class="tag-hero__meta">
      <?= $caseStudies->count() ?> case stud<?= $caseStudies->count() === 1 ? 'y' : 'ies' ?>
    </div>
  </div>

  <?php if ($usedServices || $usedSectors || $usedTags): ?>
  <nav class="tag-cloud" aria-label="Filter by tag">
    <button class="tag-pill is-active" data-filter="*" type="button">All</button>
    <?php foreach ($usedServices as $slug => $label): ?>
      <button class="tag-pill" data-filter="service:<?= $slug ?>" type="button"><?= html($label) ?></button>
    <?php endforeach ?>
    <?php foreach ($usedSectors as $slug => $label): ?>
      <button class="tag-pill" data-filter="sector:<?= $slug ?>" type="button"><?= html($label) ?></button>
    <?php endforeach ?>
    <?php foreach ($usedTags as $slug => $label): ?>
      <button class="tag-pill" data-filter="tag:<?= html($slug) ?>" type="button"><?= html($label) ?></button>
    <?php endforeach ?>
  </nav>
  <?php endif ?>

  <div class="tag-grid">
    <div class="our-work-grid">
      <?php foreach ($caseStudies as $cs): ?>
        <?php snippet('work-grid-card', ['caseStudy' => $cs]) ?>
      <?php endforeach ?>
    </div>

    <?php if ($caseStudies->count() === 0): ?>
      <p class="our-work-empty">No case studies published yet.</p>
    <?php endif ?>
  </div>

  <footer class="feed-footer">
    <div class="feed-footer__inner">
      <span class="feed-footer__brand"><?= $site->title()->html() ?> &mdash; Strategic Design &amp; Technology</span>
      <div class="feed-footer__links">
        <a href="/contact#enquiries" class="feed-footer__link">Enquiries</a>
        <a href="/contact#tenders" class="feed-footer__link">Tenders</a>
        <a href="/contact#strategy" class="feed-footer__link">Strategy</a>
      </div>
      <span>&copy; <?= date('Y') ?></span>
    </div>
  </footer>

</div>

<script>
(function() {
  var h = new Date().getHours();
  if (h >= 7 && h < 19) {
    document.documentElement.setAttribute('data-theme', 'light');
  }
})();
</script>

<script>
(function () {
  var pills = document.querySelectorAll('.tag-pill');
  var cards = document.querySelectorAll('.work-grid-card');
  if (!pills.length || !cards.length) return;

  pills.forEach(function (pill) {
    pill.addEventListener('click', function () {
      pills.forEach(function (p) { p.classList.remove('is-active'); });
      pill.classList.add('is-active');
      var filter = pill.getAttribute('data-filter');

      cards.forEach(function (card) {
        if (filter === '*') { card.hidden = false; return; }
        var parts = filter.split(':');
        var type = parts[0];
        var value = parts[1];
        var attr = card.getAttribute('data-' + type + 's') || '';
        card.hidden = attr.split(',').indexOf(value) === -1;
      });
    });
  });

  var revealTargets = document.querySelectorAll('.work-grid-card');
  if (!revealTargets.length) return;
  if (!('IntersectionObserver' in window)) {
    revealTargets.forEach(function (el) { el.classList.add('is-visible'); });
    return;
  }
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        io.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -5% 0px' });
  revealTargets.forEach(function (el) { io.observe(el); });
})();
</script>

<?php snippet('footer') ?>
