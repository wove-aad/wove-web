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
?>

<?php snippet('header') ?>

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


  <!-- RELATED WOVE MIND ENTRIES -->
  <?php if ($relatedEntries->count()): ?>
    <section class="case-study-related" aria-labelledby="related-heading">
      <div class="container">
        <div class="case-study-related-intro">
          <h2 class="section__heading" id="related-heading"><?= $page->relatedHeading()->or('Design at multiple layers')->html() ?></h2>
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
<?php snippet('footer') ?>
