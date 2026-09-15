<?php
/**
 * Case Study — single page template
 * File: site/templates/case-study.php
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

$team       = $page->team()->toStructure();
$allBlocks  = $page->blocks()->toBlocks();
$narrativeBlocks = [];
$galleryBlocks    = [];
foreach ($allBlocks as $block) {
  if ($block->type() === 'gallery') {
    $galleryBlocks[] = $block;
  } else {
    $narrativeBlocks[] = $block;
  }
}
$stats  = $page->stats()->toStructure();
$contact = $page->contact()->toStructure()->first();

$prev = $page->prevListed();
$next = $page->nextListed();

// WoveMind entries that link back to this case study via their `case_study` field.
$relatedEntries = $site->find('wove-mind')->children()->listed()
  ->filter(fn ($p) => $p->case_study()->toPages()->findBy('id', $page->id()) !== null);

// Testimonial
$testimonial = $page->testimonial()->toStructure()->first();

// Sectors for this case study
$sectorSlugs = array_filter($page->sectors()->split(','));
$sectorLabels = [
  'arts-and-culture'  => 'Arts and Culture',
  'public-service'    => 'Public Service',
  'higher-education'  => 'Higher Education',
  'non-profit'        => 'Non-profit and Mission-led',
  'founders-ventures' => 'Founders and Ventures',
];

// Sibling case studies in the same sector(s) for sector navigation
$sectorSiblings = [];
if ($sectorSlugs) {
  $firstSector = $sectorSlugs[0];
  $sectorSiblings = $page->siblings()->listed()
    ->filterBy('sectors', $firstSector, ',')
    ->not($page)
    ->limit(4);
}
?>

<?php snippet('header', ['css' => ['/assets/css/feed.css']]) ?>

<main class="case-study">

  <!-- HERO -->
  <header class="case-study-hero">
    <div class="container container--wide">
      <h1 class="page-head__title"><?= $page->eyebrow()->html() ?></h1>
      <div class="page-head__tagline"><?= $page->heroTitle() ?></div>

      <?php if ($impactLabels || $serviceTags): ?>
        <ul class="case-study-tags" role="list">
          <?php foreach ($impactLabels as $label): ?>
            <li class="tag"><?= html($label) ?></li>
          <?php endforeach ?>
          <?php foreach ($serviceTags as $label): ?>
            <li class="tag"><?= html($label) ?></li>
          <?php endforeach ?>
        </ul>
      <?php endif ?>

      <?php if ($sectorSlugs): ?>
        <div class="cs-hero-filters">
          <?php foreach ($sectorSlugs as $slug): ?>
            <a href="/sector/<?= $slug ?>" class="cs-sector-nav__chip"><?= html($sectorLabels[$slug] ?? $slug) ?></a>
          <?php endforeach ?>
          <?php foreach ($serviceSlugs as $slug): ?>
            <a href="/services/<?= $slug ?>" class="cs-sector-nav__chip"><?= html($serviceLabels[$slug] ?? ucfirst($slug)) ?></a>
          <?php endforeach ?>
        </div>
      <?php endif ?>
    </div>
  </header>


  <!-- OVERVIEW — team roster + main narrative blocks -->
  <?php if ($team->count() || $narrativeBlocks): ?>
    <section class="section container" aria-label="Overview">
      <div class="case-study-overview">

        <?php if ($team->count()): ?>
          <aside class="team-list">
            <p class="team-list__heading">Team</p>
            <ul role="list">
              <?php foreach ($team as $member): ?>
                <?php $avatar = $member->avatar()->toFile() ?>
                <li class="team-member">
                  <?php if ($avatar): ?>
                    <img src="<?= $avatar->url() ?>" alt="" class="team-member__avatar" width="48" height="48" loading="lazy">
                  <?php endif ?>
                  <span class="team-member__name"><?= $member->name()->html() ?></span>
                </li>
              <?php endforeach ?>
            </ul>
          </aside>
        <?php endif ?>

        <?php if ($narrativeBlocks): ?>
          <div class="case-study-blocks">
            <?php foreach ($narrativeBlocks as $block): ?>
              <?= $block ?>
            <?php endforeach ?>
          </div>
        <?php endif ?>

      </div>
    </section>
  <?php endif ?>


  <!-- IMAGE GALLERY — breaks out to full width -->
  <?php if ($galleryBlocks): ?>
    <section class="section--tight container container--wide" aria-label="Gallery">
      <?php foreach ($galleryBlocks as $block): ?>
        <div class="case-study-gallery">
          <?= $block ?>
        </div>
      <?php endforeach ?>
    </section>
  <?php endif ?>


  <!-- IMPACT STATS -->
  <?php if ($stats->count()): ?>
    <section class="section--tight container" aria-label="Impact stats">
      <div class="case-study-stats">
        <?php foreach ($stats as $stat): ?>
          <div class="stat">
            <p class="stat__value"><?= $stat->value()->html() ?></p>
            <p class="stat__label"><?= $stat->label()->html() ?></p>
          </div>
        <?php endforeach ?>
      </div>
    </section>
  <?php endif ?>


  <!-- TESTIMONIAL -->
  <?php if ($testimonial): ?>
    <section class="cs-testimonial" aria-label="Client testimonial">
      <blockquote class="cs-testimonial__quote">&ldquo;<?= $testimonial->quote()->html() ?>&rdquo;</blockquote>
      <p class="cs-testimonial__attribution">
        <?= $testimonial->name()->html() ?>
        <?php if ($testimonial->role()->isNotEmpty()): ?>, <?= $testimonial->role()->html() ?><?php endif ?>
        <?php if ($testimonial->organisation()->isNotEmpty()): ?>, <?= $testimonial->organisation()->html() ?><?php endif ?>
      </p>
      <?php if ($testimonial->reference_available()->toBool()): ?>
        <p class="cs-testimonial__reference">References available on request from the commissioning Chair or Director.</p>
      <?php endif ?>
    </section>
  <?php elseif ($page->testimonial()->toStructure()->count() === 0): ?>
    <?php
      $refCheck = $page->content()->get('testimonial');
      if ($refCheck->isEmpty()):
    ?>
    <?php endif ?>
  <?php endif ?>

  <!-- IN THIS COLLECTION (renamed from "Related") -->
  <?php if ($relatedEntries->count()): ?>
    <section class="case-study-related" aria-labelledby="related-heading">
      <div class="container">
        <div class="case-study-related-intro">
          <p class="label">Explore</p>
          <h2 class="section__heading" id="related-heading">In this collection</h2>
          <?php if ($page->relatedIntro()->isNotEmpty()): ?>
            <p class="lead"><?= $page->relatedIntro()->html() ?></p>
          <?php endif ?>
        </div>
        <div class="wovemind-cards">
          <?php foreach ($relatedEntries as $entry): ?>
            <?php snippet('wovemind-related-card', ['post' => $entry]) ?>
          <?php endforeach ?>
        </div>
      </div>
    </section>
  <?php endif ?>

  <!-- SECTOR NAVIGATION -->
  <?php if ($sectorSlugs && $sectorSiblings && $sectorSiblings->count()): ?>
    <section class="cs-sector-nav" aria-label="Other collections in this sector">
      <p class="cs-sector-nav__label">Other collections in <?= html($sectorLabels[$firstSector] ?? $firstSector) ?></p>
      <div class="cs-sector-nav__links">
        <?php foreach ($sectorSiblings as $sibling): ?>
          <a href="<?= $sibling->url() ?>" class="cs-sector-nav__chip"><?= $sibling->eyebrow()->or($sibling->title())->html() ?></a>
        <?php endforeach ?>
      </div>
    </section>
  <?php endif ?>


  <!-- PREV / NEXT -->
  <?php if ($prev || $next): ?>
    <nav class="pager container" aria-label="Case studies">
      <?php if ($prev): ?>
        <a href="<?= $prev->url() ?>" class="pager__link pager__link--prev" rel="prev">
          <span class="pager__dir"><span aria-hidden="true">&larr;</span> Previous project</span>
          <span class="pager__name"><?= $prev->eyebrow()->or($prev->title())->html() ?></span>
        </a>
      <?php else: ?>
        <span></span>
      <?php endif ?>
      <?php if ($next): ?>
        <a href="<?= $next->url() ?>" class="pager__link pager__link--next" rel="next">
          <span class="pager__dir">Next project <span aria-hidden="true">&rarr;</span></span>
          <span class="pager__name"><?= $next->eyebrow()->or($next->title())->html() ?></span>
        </a>
      <?php endif ?>
    </nav>
  <?php endif ?>


  <!-- CONTACT BANNER -->
  <?php if ($contact): ?>
    <?php
      $avatar = $contact->avatar()->toFile();
      $ctaFallback = $contact->name()->isNotEmpty() ? 'Talk to ' . $contact->name()->value() . ' today' : 'Get in touch today';
    ?>
    <section class="contact" aria-labelledby="contact-heading">
      <div class="contact__inner">
        <?php if ($avatar): ?>
          <div class="contact__avatar">
            <img src="<?= $avatar->url() ?>" alt="<?= $contact->name()->html() ?>" width="200" height="200" loading="lazy">
          </div>
        <?php endif ?>
        <div class="contact__body">
          <p class="contact__text" id="contact-heading">
            <?= $page->subStatement()->or('Have a project in mind? Curious about ways we could bring value to your organisation?')->html() ?>
          </p>
          <a href="/contact" class="btn btn--primary btn--md">
            <?= $page->ctaPrompt()->or($ctaFallback)->html() ?> <span aria-hidden="true">&rarr;</span>
          </a>
        </div>
      </div>
    </section>
  <?php endif ?>

</main>

<?php snippet('service-page-scripts') ?>
<script>
(function() {
  var h = new Date().getHours();
  if (h >= 7 && h < 19) {
    document.documentElement.setAttribute('data-theme', 'light');
  }
})();
</script>
<?php snippet('footer') ?>
