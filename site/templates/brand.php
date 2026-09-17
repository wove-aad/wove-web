<?php
/**
 * Brand service page — simplified filtered view
 * File: site/templates/brand.php
 */

$serviceSlug = 'brand';

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
    <span>Brand</span>
  </nav>

  <div class="tag-hero">
    <h1 class="tag-hero__name"><?= $page->title()->html() ?></h1>
    <p class="tag-hero__intro">Identities, content and campaigns that culturally connect.</p>
    <div class="tag-hero__meta"><?= $totalCount ?> entr<?= $totalCount === 1 ? 'y' : 'ies' ?></div>
  </div>

  <?php snippet('tag-cloud', ['active' => $serviceSlug, 'activeType' => 'service']) ?>

  <section class="stream-section" aria-label="Brand work">
    <div class="stream-grid">
      <?php foreach ($allEntries as $post): ?>
        <?php snippet('stream-card', ['post' => $post]) ?>
      <?php endforeach ?>
    </div>
  </section>

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

<?php snippet('footer') ?>
