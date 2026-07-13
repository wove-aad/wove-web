<?php snippet('header') ?>

<main id="main">

  <!-- PAGE HEAD -->
  <header class="page-head">
    <div class="page-head__inner">
      <h1 class="page-head__title"><?= $page->title()->html() ?></h1>
      <p class="page-head__tagline">Helping organisations plan, prioritise and change in lasting ways.</p>
    </div>
  </header>


  <!-- INTRO -->
  <section class="section section--flush-top container" aria-label="Introduction">
    <p class="lead indent">Organisations today face constant pressure to adapt, without losing sight of what makes them valuable. <strong>They need a clear-eyed view of how they create value, the confidence to test new ways of working, and a realistic path for making change stick.</strong> Whether you're setting direction for the first time or resetting after growth, Wove brings research, strategic thinking and hands-on delivery together to help you plan, prioritise and change in ways that last.</p>
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
      <p class="lead">Researching how your organisation creates value and who it really serves. Incubating and testing new products, services and business models before you commit. Navigating complexity and delivering change that lasts, from strategy through to implementation.</p>
    </div>
  </section>


  <!-- SERVICE OFFERINGS — static, not CMS-managed -->
  <section class="section container" aria-labelledby="offerings-heading">
    <h2 id="offerings-heading" class="visually-hidden">What we offer</h2>
    <div class="offerings">
      <div class="offering"><h3 class="offering__title">Research and insights</h3><p class="offering__body">Building a clear picture of how your organisation creates value, and for who.</p></div>
      <div class="offering"><h3 class="offering__title">Stakeholder engagement</h3><p class="offering__body">Bringing the right people into the process, so strategy reflects real needs.</p></div>
      <div class="offering"><h3 class="offering__title">Strategic planning</h3><p class="offering__body">Setting clear priorities and direction that your whole organisation can align behind.</p></div>
      <div class="offering"><h3 class="offering__title">Value proposition design</h3><p class="offering__body">Defining what makes you valuable, and sharpening how you deliver on it.</p></div>
      <div class="offering"><h3 class="offering__title">Business model innovation</h3><p class="offering__body">Rethinking how your organisation operates and generates value over time.</p></div>
      <div class="offering"><h3 class="offering__title">Product and service innovation</h3><p class="offering__body">Exploring, testing and shaping new offerings before you commit to building them.</p></div>
      <div class="offering"><h3 class="offering__title">Prototyping and usability testing</h3><p class="offering__body">Trying ideas out with real people, early, so you learn fast and waste less.</p></div>
      <div class="offering"><h3 class="offering__title">Roadmap development</h3><p class="offering__body">Turning strategy into a practical, sequenced plan your team can act on.</p></div>
      <div class="offering"><h3 class="offering__title">Systems thinking</h3><p class="offering__body">Understanding how the parts of your organisation connect, so change sticks.</p></div>
      <div class="offering"><h3 class="offering__title">Organisational design</h3><p class="offering__body">Structuring teams and roles around the strategy you're trying to deliver.</p></div>
      <div class="offering"><h3 class="offering__title">Culture design</h3><p class="offering__body">Aligning ways of working and shared values with where you're headed.</p></div>
      <div class="offering"><h3 class="offering__title">Change implementation</h3><p class="offering__body">Delivering change in a way people can actually follow and sustain.</p></div>
    </div>
  </section>


  <!-- QUOTE BANNER -->
  <section class="quote" aria-label="Quote">
    <figure class="quote__inner">
      <blockquote class="quote__text">&ldquo;Wove helped us clarify and articulate our value, and confidently set out our new strategy. The whole process was a pleasure: collaborative, creative and productive.&rdquo;</blockquote>
      <figcaption class="quote__attr">
        <span class="quote__name">Sarah Durcan,</span>
        <span class="quote__role">Executive Director, Science Gallery International</span>
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
        <img src="/assets/team/scott.jpg" alt="Scott, Wove" width="200" height="200" loading="lazy">
      </div>
      <div class="contact__body">
        <p class="contact__text" id="contact-heading">Have a project in mind? Curious about ways the strategy team could bring value to your organisation?</p>
        <a href="/contact" class="btn btn--primary btn--md">Talk to Scott today <span aria-hidden="true">&rarr;</span></a>
      </div>
    </div>
  </section>

  <!-- SERVICE PAGER -->
  <nav class="pager container" aria-label="Service pages">
    <a href="/services/labs" class="pager__link pager__link--prev" rel="prev">
      <span class="pager__dir"><span aria-hidden="true">&larr;</span> Previous service</span>
      <span class="pager__name">Labs</span>
    </a>
    <a href="/services/brand" class="pager__link pager__link--next" rel="next">
      <span class="pager__dir">Next service <span aria-hidden="true">&rarr;</span></span>
      <span class="pager__name">Brand</span>
    </a>
  </nav>

</main>

<?php snippet('service-page-scripts') ?>
<?php snippet('footer') ?>
