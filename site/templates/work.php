<?php
/**
 * Our Work — case study portfolio grid with tag-cloud filtering
 * File: site/templates/work.php
 *
 * Blueprint: site/blueprints/pages/work.yml
 * Case studies are children of this page, queried via the
 * site/collections/case-studies.php collection.
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

<?php snippet('header') ?>

<main id="main">

  <header class="page-head">
    <div class="page-head__inner">
      <h1 class="page-head__title">Our Work</h1>
    </div>
  </header>

  <?php if ($usedServices || $usedSectors || $usedTags): ?>
  <nav class="our-work-filter" aria-label="Filter by tag">
    <div class="our-work-filter__inner container">
      <button class="work-pill is-active" data-filter="*" type="button">All</button>
      <?php foreach ($usedServices as $slug => $label): ?>
        <button class="work-pill" data-filter="service:<?= $slug ?>" type="button"><?= html($label) ?></button>
      <?php endforeach ?>
      <?php foreach ($usedSectors as $slug => $label): ?>
        <button class="work-pill" data-filter="sector:<?= $slug ?>" type="button"><?= html($label) ?></button>
      <?php endforeach ?>
      <?php foreach ($usedTags as $slug => $label): ?>
        <button class="work-pill" data-filter="tag:<?= html($slug) ?>" type="button"><?= html($label) ?></button>
      <?php endforeach ?>
    </div>
  </nav>
  <?php endif ?>

  <section class="section section--flush-top container" aria-label="Case studies">
    <div class="our-work-grid">
      <?php foreach ($caseStudies as $cs): ?>
        <?php snippet('work-grid-card', ['caseStudy' => $cs]) ?>
      <?php endforeach ?>
    </div>

    <?php if ($caseStudies->count() === 0): ?>
      <p class="our-work-empty">No case studies published yet.</p>
    <?php endif ?>
  </section>

</main>

<script>
(function () {
  var pills = document.querySelectorAll('.work-pill');
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

  /* Reveal on scroll */
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
