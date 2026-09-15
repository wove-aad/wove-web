<?php
/**
 * Case Study — single page template
 * File: site/templates/case-study.php
 *
 * Blueprint: site/blueprints/pages/case-study.yml
 * Layout: breadcrumb, hero, image, stats, testimonial, body, related, team, footer
 */

$tagStructure = $site->tags()->toStructure();
$impactSlugs  = array_filter($page->impactAreas()->split(','));
$impactLabels = array_map(
  fn ($slug) => ($match = $tagStructure->findBy('slug', $slug)) ? $match->name()->value() : $slug,
  $impactSlugs
);

$serviceLabels = ['strategy' => 'Strategy', 'labs' => 'Labs', 'digital' => 'Digital', 'brand' => 'Brand'];
$serviceSlugs  = array_filter($page->services()->split(','));
$serviceTags   = array_map(fn ($slug) => $serviceLabels[$slug] ?? ucfirst($slug), $serviceSlugs);

$sectorSlugs  = array_filter($page->sectors()->split(','));
$sectorLabels = [
  'arts-and-culture'  => 'Arts and Culture',
  'public-service'    => 'Public Service',
  'higher-education'  => 'Higher Education',
  'non-profit'        => 'Non-profit and Mission-led',
  'founders-ventures' => 'Founders and Ventures',
];

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

    <?php if ($serviceTags): ?>
      <div class="cs-hero__services">
        <?php foreach ($serviceTags as $label): ?>
          <span class="cs-hero__service"><?= html($label) ?></span>
        <?php endforeach ?>
      </div>
    <?php endif ?>

    <?php if ($sectorSlugs || $serviceSlugs): ?>
      <div class="cs-hero__filters">
        <?php foreach ($sectorSlugs as $slug): ?>
          <a href="/sector/<?= $slug ?>" class="cs-hero__filter"><?= html($sectorLabels[$slug] ?? $slug) ?></a>
        <?php endforeach ?>
        <?php foreach ($serviceSlugs as $slug): ?>
          <a href="/<?= $slug ?>" class="cs-hero__filter"><?= html($serviceLabels[$slug] ?? ucfirst($slug)) ?></a>
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

  <!-- FOOTER -->
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
