<?php
/**
 * Service page — filtered view of case studies + Wove Mind entries
 * File: site/templates/service.php
 *
 * Replaces the four per-service templates (strategy, labs, digital, brand).
 * The service slug comes from the route in config.php or from the page slug.
 */

$serviceSlug  = $serviceSlug ?? $page->slug();
$serviceLabel = $page->title()->value();

$serviceIntros = [
  'strategy' => 'Helping organisations plan, prioritise and change in lasting ways.',
  'labs'     => 'Research and development for a more human future.',
  'digital'  => 'Platforms and innovation that help you deliver and scale impact.',
  'brand'    => 'Identities, content and campaigns that culturally connect.',
];
$intro = $page->intro()->or($serviceIntros[$serviceSlug] ?? '')->value();

$caseStudies = kirby()->collection('case-studies')
  ->filter(fn ($cs) => in_array($serviceSlug, $cs->services()->split(',')));

$wmParent = $site->find('wove-mind');
$wmEntries = $wmParent
  ? $wmParent->children()->listed()
      ->filter(fn ($p) => in_array($serviceSlug, $p->services()->split(',')))
      ->sortBy('date', 'desc')
  : new \Kirby\Cms\Pages();

$allEntries = [];
foreach ($caseStudies as $cs) $allEntries[] = $cs;
foreach ($wmEntries as $e) $allEntries[] = $e;
$totalCount = count($allEntries);
?>

<?php snippet('header', ['css' => ['/assets/css/feed.css']]) ?>

<div class="feed-wrap">

  <nav class="tag-breadcrumb" aria-label="Breadcrumb">
    <a href="/wove-mind">Feed</a>
    <span class="tag-breadcrumb__sep">/</span>
    <a href="/our-work">Our Work</a>
    <span class="tag-breadcrumb__sep">/</span>
    <span><?= html($serviceLabel) ?></span>
  </nav>

  <div class="tag-hero">
    <h1 class="tag-hero__name"><?= $page->title()->html() ?></h1>
    <?php if ($intro): ?>
      <p class="tag-hero__intro"><?= html($intro) ?></p>
    <?php endif ?>
    <div class="tag-hero__meta"><?= $totalCount ?> entr<?= $totalCount === 1 ? 'y' : 'ies' ?></div>
  </div>

  <?php snippet('tag-cloud', ['active' => $serviceSlug, 'activeType' => 'service']) ?>

  <section class="stream-section" aria-label="<?= html($serviceLabel) ?> work">
    <div class="stream-grid">
      <?php foreach ($allEntries as $post): ?>
        <?php snippet('stream-card', ['post' => $post]) ?>
      <?php endforeach ?>
    </div>
  </section>

</div>

<script>
(function() {
  var h = new Date().getHours();
  if (h >= 7 && h < 19) {
    document.documentElement.setAttribute('data-theme', 'light');
  }
})();
</script>

<?php snippet('service-page-scripts') ?>
<?php snippet('footer') ?>
