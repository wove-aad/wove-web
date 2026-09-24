<?php
/**
 * Case Study — single page template
 * File: site/templates/case-study.php
 *
 * Blueprint: site/blueprints/pages/case-study.yml
 * Layout: breadcrumb, hero, image, stats, testimonial, body, related, team, footer
 */

$serviceSlugs = array_filter($page->services()->split(','));
$sectorSlugs  = array_filter($page->sectors()->split(','));
$serviceLabels = ['strategy' => 'Strategy', 'labs' => 'Labs', 'digital' => 'Digital', 'brand' => 'Brand'];
$sectorLabels = [
  'arts-and-culture'  => 'Arts and Culture',
  'public-service'    => 'Public Service',
  'higher-education'  => 'Higher Education',
  'non-profit'        => 'Non-profit and Mission-led',
  'founders-ventures' => 'Founders and Ventures',
];
$tagStructure = $site->tags()->toStructure();
$impactSlugs  = array_filter($page->impactAreas()->split(','));

$csTags = [];
foreach ($serviceSlugs as $s) {
  $csTags[] = ['label' => $serviceLabels[$s] ?? ucfirst($s), 'url' => '/our-work?filter=service:' . $s];
}
// Global tag order: services, editorial tags, sectors (the case study itself is this page)
foreach ($impactSlugs as $slug) {
  $match = $tagStructure->findBy('slug', $slug);
  if ($match) $csTags[] = ['label' => $match->name()->value(), 'url' => '/our-work?filter=tag:' . $slug];
}
foreach ($sectorSlugs as $s) {
  $csTags[] = ['label' => $sectorLabels[$s] ?? $s, 'url' => '/our-work?filter=sector:' . $s];
}

$heroImage  = $page->caseStudyImages()->toFile();
$stats      = $page->stats()->toStructure();
$testimonial = $page->testimonial()->toStructure()->first();
$allBlocks  = $page->blocks()->toBlocks();
$team       = $page->team()->toStructure();

$relatedEntries = $site->find('wove-mind')
  ? $site->find('wove-mind')->children()->listed()
      ->filter(fn ($p) => $p->case_study()->toPages()->findBy('id', $page->id()) !== null)
      ->sortBy('date', 'desc')
      ->limit(6)
  : new \Kirby\Cms\Pages();

$sectorSiblings = [];
if ($sectorSlugs) {
  $firstSector = $sectorSlugs[0];
  $sectorSiblings = $page->siblings()->listed()
    ->filterBy('sectors', $firstSector, ',')
    ->not($page)
    ->limit(4);
}

$prev = $page->prevListed();
$next = $page->nextListed();
?>

<?php snippet('header', ['css' => ['/assets/css/feed.css']]) ?>

<div class="feed-wrap">

  <!-- BREADCRUMB -->
  <nav class="cs-breadcrumb" aria-label="Breadcrumb">
    <a href="/wove-mind">Feed</a>
    <span class="cs-breadcrumb__sep">/</span>
    <a href="/our-work">Our Work</a>
    <span class="cs-breadcrumb__sep">/</span>
    <span><?= $page->eyebrow()->or($page->title())->html() ?></span>
  </nav>

  <!-- HERO -->
  <div class="cs-hero">
    <div class="cs-hero__eyebrow"><?= $page->eyebrow()->html() ?></div>
    <h1 class="cs-hero__title"><?= $page->heroTitle() ?></h1>

    <?php if ($csTags): ?>
      <div class="cs-hero__tags">
        <?php foreach ($csTags as $t): ?>
          <a href="<?= $t['url'] ?>" class="tag-pill"><?= html($t['label']) ?></a>
        <?php endforeach ?>
      </div>
    <?php endif ?>
  </div>

  <!-- HERO IMAGE -->
  <?php if ($heroImage): ?>
    <div class="cs-hero-image">
      <div class="cs-hero-image__inner">
        <img src="<?= $heroImage->url() ?>" alt="<?= $page->eyebrow()->html() ?>" loading="eager">
      </div>
    </div>
  <?php endif ?>

  <!-- STATS -->
  <?php if ($stats->count()): ?>
    <div class="cs-stats">
      <?php foreach ($stats as $stat): ?>
        <div class="cs-stat">
          <div class="cs-stat__value"><?= $stat->value()->html() ?></div>
          <div class="cs-stat__label"><?= $stat->label()->html() ?></div>
        </div>
      <?php endforeach ?>
    </div>
  <?php endif ?>

  <!-- TESTIMONIAL -->
  <?php if ($testimonial): ?>
    <div class="cs-testimonial">
      <p class="cs-testimonial__quote"><?= $testimonial->quote()->html() ?></p>
      <div class="cs-testimonial__attribution">
        <span class="cs-testimonial__name"><?= $testimonial->name()->html() ?></span><?php
        if ($testimonial->role()->isNotEmpty()): ?>, <?= $testimonial->role()->html() ?><?php endif ?><?php
        if ($testimonial->organisation()->isNotEmpty()): ?>, <?= $testimonial->organisation()->html() ?><?php endif ?>
      </div>
      <?php if ($testimonial->reference_available()->toBool()): ?>
        <p class="cs-testimonial__reference">Available as a reference on request</p>
      <?php endif ?>
    </div>
  <?php endif ?>

  <!-- BODY (blocks content) -->
  <?php if ($allBlocks->count()): ?>
    <div class="cs-body">
      <div class="cs-body__inner">
        <div class="cs-body__label">Case Study</div>
        <?php foreach ($allBlocks as $block): ?>
          <?= $block ?>
        <?php endforeach ?>
      </div>
    </div>
  <?php endif ?>

  <!-- RELATED ENTRIES -->
  <?php if ($relatedEntries->count()): ?>
    <div class="cs-related">
      <div class="cs-related__header">
        <div>
          <div class="cs-related__label">Explore</div>
          <h2 class="cs-related__title">In this collection</h2>
        </div>
        <a href="/our-work" class="cs-related__link">View all &rarr;</a>
      </div>

      <div class="feed-cards">
        <?php foreach ($relatedEntries as $entry): ?>
          <?php snippet('feed-card', ['post' => $entry]) ?>
        <?php endforeach ?>
      </div>

      <?php if ($sectorSlugs && $sectorSiblings && $sectorSiblings->count()): ?>
        <div class="cs-sector-nav">
          <div class="cs-sector-nav__label">Other collections in <?= html($sectorLabels[$firstSector] ?? $firstSector) ?></div>
          <div class="cs-sector-nav__links">
            <?php foreach ($sectorSiblings as $sibling): ?>
              <a href="<?= $sibling->url() ?>" class="cs-sector-nav__link"><?= $sibling->eyebrow()->or($sibling->title())->html() ?></a>
            <?php endforeach ?>
          </div>
        </div>
      <?php endif ?>
    </div>
  <?php endif ?>

  <!-- TEAM -->
  <?php if ($team->count()): ?>
    <div class="cs-team">
      <div class="cs-team__label">Project Team</div>
      <div class="cs-team__grid">
        <?php foreach ($team as $member): ?>
          <?php $avatar = $member->avatar()->toFile() ?>
          <div class="cs-team__member">
            <div class="cs-team__avatar">
              <?php if ($avatar): ?>
                <img src="<?= $avatar->url() ?>" alt="" width="40" height="40" loading="lazy">
              <?php endif ?>
            </div>
            <div class="cs-team__name"><?= $member->name()->html() ?></div>
          </div>
        <?php endforeach ?>
      </div>
    </div>
  <?php endif ?>

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
