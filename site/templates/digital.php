<?php snippet('header') ?>

<main id="main">

  <!-- PAGE HEAD -->
  <header class="page-head">
    <div class="page-head__inner">
      <h1 class="page-head__title"><?= $page->title()->html() ?></h1>
      <p class="page-head__tagline">Platforms and innovation that help you deliver and scale impact.</p>
    </div>
  </header>


  <!-- INTRO -->
  <section class="section section--flush-top container" aria-label="Introduction">
    <p class="lead indent">Organisations today need more than a website. <strong>They need platforms that earn trust, reflect their values, and connect meaningfully with the people they serve.</strong> Whether you're building something new or evolving what you have, Wove brings strategic thinking and technical craft together to build digital products that last.</p>
  </section>


  <!-- FEATURED CASE STUDIES — first card visible, rest behind "See more" -->
  <?php
    $featuredCaseStudies = kirby()->collection('case-studies')
      ->filter(fn ($cs) => in_array($page->slug(), $cs->services()->split(',')));
  ?>
  <?php if ($featuredCaseStudies->count()): ?>
  <section class="section--tight container" aria-labelledby="featured-heading">
    <h2 class="section__heading" id="featured-heading">Featured work</h2>
    <div class="features" id="more-cases">
      <?php snippet('case-study-feature-card', ['caseStudy' => $featuredCaseStudies->first()]) ?>
      <?php if ($featuredCaseStudies->count() > 1): ?>
        <div class="features__extra">
          <div class="features__extra-inner">
            <?php foreach ($featuredCaseStudies->slice(1) as $cs): ?>
              <?php snippet('case-study-feature-card', ['caseStudy' => $cs]) ?>
            <?php endforeach ?>
          </div>
        </div>
      <?php endif ?>
    </div>
    <?php if ($featuredCaseStudies->count() > 1): ?>
      <button type="button" class="btn btn--ghost btn--md btn--center js-more" aria-controls="more-cases" aria-expanded="false">
        <span class="js-more-label">See more case studies</span>
        <svg class="btn__chevron" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
      </button>
    <?php endif ?>
  </section>
  <?php endif ?>


  <!-- WHAT WE CAN HELP WITH -->
  <section class="section container" aria-labelledby="help-heading">
    <div class="indent">
      <h2 class="block-label" id="help-heading">What we can help with</h2>
      <p class="lead">Designing user journeys and service experiences that work for real people. Building accessible, performant platforms from the ground up. Developing design systems and component libraries that scale. Creating prototypes and proof-of-concepts that test ideas quickly. Evolving legacy digital products to meet new challenges. Ensuring everything we build meets AA accessibility standards as a minimum.</p>
    </div>
  </section>


  <!-- SERVICE OFFERINGS — static, not CMS-managed -->
  <section class="section container" aria-labelledby="offerings-heading">
    <h2 id="offerings-heading" class="visually-hidden">What we offer</h2>
    <div class="offerings">
      <div class="offering"><h3 class="offering__title">User journey design</h3><p class="offering__body">Mapping and designing the end-to-end experience for the people who matter most.</p></div>
      <div class="offering"><h3 class="offering__title">Prototyping</h3><p class="offering__body">Testing ideas quickly before committing to build, reducing risk and building confidence.</p></div>
      <div class="offering"><h3 class="offering__title">Design systems</h3><p class="offering__body">A consistent, scalable component library that makes future development faster and more coherent.</p></div>
      <div class="offering"><h3 class="offering__title">Platform development</h3><p class="offering__body">Collaborative development of accessible, performant web platforms built to last.</p></div>
      <div class="offering"><h3 class="offering__title">Accessibility</h3><p class="offering__body">AA as a baseline, AAA as the ambition. Inclusive design baked in from the start.</p></div>
      <div class="offering"><h3 class="offering__title">CMS &amp; content strategy</h3><p class="offering__body">Content models and editorial tools that make it easy for your team to keep things current.</p></div>
      <div class="offering"><h3 class="offering__title">Performance &amp; sustainability</h3><p class="offering__body">Low carbon, fast-loading, Green Web Foundation aligned. Good for users and the planet.</p></div>
      <div class="offering"><h3 class="offering__title">Data &amp; insight</h3><p class="offering__body">Understanding what's working, what isn't, and what to do about it.</p></div>
      <div class="offering"><h3 class="offering__title">Innovation &amp; emerging tech</h3><p class="offering__body">Exploring what AI and new tools could genuinely do for your organisation.</p></div>
    </div>
  </section>


  <!-- QUOTE BANNER -->
  <section class="quote" aria-label="Quote">
    <figure class="quote__inner">
      <blockquote class="quote__text">&ldquo;From start to finish, Wove were amazing to work with, ensuring our new brand sets us up for success.&rdquo;</blockquote>
      <figcaption class="quote__attr">
        <span class="quote__name">Big Jim,</span>
        <span class="quote__role">Abbey Theatre marketing director</span>
      </figcaption>
    </figure>
  </section>


  <!-- RECENT WORK (project highlights + WoveMind entries, tagged to this service) -->
  <?php
    $highlights = $site->find('wove-mind')->children()->listed()
      ->filter(fn ($p) => $p->format()->value() === 'project-highlight'
        && in_array($page->slug(), $p->services()->split(',')))
      ->limit(12);
    $entries = $site->find('wove-mind')->children()->listed()
      ->filter(fn ($p) => in_array($p->format()->value(), ['thread', 'whatif', 'longread'])
        && in_array($page->slug(), $p->services()->split(',')))
      ->limit(8);
  ?>
  <?php if ($highlights->count() || $entries->count()): ?>
  <section class="section container" aria-labelledby="work-heading">
    <h2 class="section__heading" id="work-heading">Recent work</h2>
    <div class="wovemind-cards">
      <?php foreach ($highlights as $h): ?>
        <?php snippet('wovemind-highlight-card', ['post' => $h]) ?>
      <?php endforeach ?>
      <?php foreach ($entries as $post): ?>
        <?php snippet('wovemind-related-card', ['post' => $post]) ?>
      <?php endforeach ?>
    </div>
  </section>
  <?php endif ?>


  <!-- CONTACT BANNER -->
  <section class="contact" aria-labelledby="contact-heading">
    <div class="contact__inner">
      <div class="contact__avatar">
        <img src="/assets/team/grace.jpg" alt="Grace, Wove" width="200" height="200" loading="lazy">
      </div>
      <div class="contact__body">
        <p class="contact__text" id="contact-heading">Have a project in mind? Curious about ways the digital team could bring value to your organisation?</p>
        <a href="/contact" class="btn btn--primary btn--md">Talk to Grace today <span aria-hidden="true">&rarr;</span></a>
      </div>
    </div>
  </section>

  <!-- SERVICE PAGER -->
  <nav class="pager container" aria-label="Service pages">
    <a href="/services/brand" class="pager__link pager__link--prev" rel="prev">
      <span class="pager__dir"><span aria-hidden="true">&larr;</span> Previous service</span>
      <span class="pager__name">Brand</span>
    </a>
    <a href="/services/labs" class="pager__link pager__link--next" rel="next">
      <span class="pager__dir">Next service <span aria-hidden="true">&rarr;</span></span>
      <span class="pager__name">Labs</span>
    </a>
  </nav>

</main>

<?php snippet('service-page-scripts') ?>
<?php snippet('footer') ?>
