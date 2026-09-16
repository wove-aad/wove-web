<?php snippet('header') ?>

<main id="main">

  <!-- PAGE HEAD -->
  <header class="page-head">
    <div class="page-head__inner">
      <h1 class="page-head__title"><?= $page->title()->html() ?></h1>
      <p class="page-head__tagline">Research and development for a more human future.</p>
    </div>
  </header>


  <!-- INTRO -->
  <section class="section section--flush-top container" aria-label="Introduction">
    <p class="lead indent">Organisations today are grappling with challenges that are harder than ever to get to grips with. <strong>How do we make complex issues accessible and engaging? How do we imagine, and actively shape, a more human future with the people we serve?</strong> Wove Labs is our research and development engine, using creative methods, hands-on experimentation and real-world engagement to explore what's possible and bring new ideas to life.</p>
  </section>


  <!-- FEATURED CASE STUDY -->
  <?php
    $featuredCaseStudy = kirby()->collection('case-studies')
      ->filter(fn ($cs) => in_array($page->slug(), $cs->services()->split(',')))
      ->first();
  ?>
  <?php if ($featuredCaseStudy): ?>
  <section class="section--tight container" aria-labelledby="featured-heading">
    <h2 class="section__heading" id="featured-heading">Featured work</h2>
    <?php snippet('case-study-feature-card', ['caseStudy' => $featuredCaseStudy]) ?>
  </section>
  <?php endif ?>


  <!-- WHAT WE CAN HELP WITH -->
  <section class="section container" aria-labelledby="help-heading">
    <div class="indent">
      <h2 class="block-label" id="help-heading">What we can help with</h2>
      <p class="lead">Making a complex or invisible issue accessible and engaging. Running creative research that goes beyond conventional methods. Imagining and exploring what the future could look like for your organisation or sector. Designing public-facing experiences, games or exhibitions that bring people into the conversation. Building the evidence base for something genuinely new.</p>
    </div>
  </section>


  <!-- SERVICE OFFERINGS — static, not CMS-managed -->
  <section class="section container" aria-labelledby="offerings-heading">
    <h2 id="offerings-heading" class="visually-hidden">What we offer</h2>
    <div class="offerings">
      <div class="offering"><h3 class="offering__title">Research and insight</h3><p class="offering__body">Building a clear picture of the challenge, the people it affects and the systems around it.</p></div>
      <div class="offering"><h3 class="offering__title">Co-design and facilitation</h3><p class="offering__body">Bringing the right people into the room to surface insights, build understanding and generate ideas together.</p></div>
      <div class="offering"><h3 class="offering__title">Speculative and <span class="tooltip" tabindex="0" aria-describedby="tip-provotype">provotype<span class="tooltip__text" id="tip-provotype" role="tooltip">A provocative prototype: less a finished proposal, more a bold idea built to spark imagination and discussion about possible futures.</span></span> design</h3><p class="offering__body">Bold, provocative ideas designed to spark conversation and collective imagination about possible futures.</p></div>
      <div class="offering"><h3 class="offering__title">Game and interactive design</h3><p class="offering__body">Turning complex or invisible issues into hands-on experiences that engage wide audiences.</p></div>
      <div class="offering"><h3 class="offering__title">Exhibition and experience design</h3><p class="offering__body">Physical experiences that bring research and ideas to life, from pop-up installations to touring exhibitions.</p></div>
      <div class="offering"><h3 class="offering__title">Public engagement and events</h3><p class="offering__body">Designing formats that make complex topics accessible, enjoyable and worth showing up for.</p></div>
      <div class="offering"><h3 class="offering__title">Digital prototyping</h3><p class="offering__body">Early stage digital tools and experiences that test ideas and bring data to life in new, tangible ways.</p></div>
      <div class="offering"><h3 class="offering__title">Storytelling and content</h3><p class="offering__body">Translating research and ideas into compelling stories across publications, talks and digital platforms.</p></div>
      <div class="offering"><h3 class="offering__title">Sustainability and circular design</h3><p class="offering__body">Applying <span class="tooltip" tabindex="0" aria-describedby="tip-circular-economy">circular economy<span class="tooltip__text" id="tip-circular-economy" role="tooltip">An approach where materials are kept in use and reused or regenerated, rather than the usual make, use and throw away.</span></span> principles to how we design and deliver, from exhibition materials to digital outputs.</p></div>
    </div>
  </section>


  <!-- QUOTE BANNER -->
  <section class="quote" aria-label="Quote">
    <figure class="quote__inner">
      <blockquote class="quote__text">&ldquo;We need a <em>more</em> imagined future. We need to get excited about what's around the corner&rdquo;</blockquote>
      <figcaption class="quote__attr">
        <span class="quote__name">Mary Robinson,</span>
        <span class="quote__role">Former president of Ireland</span>
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
        <img src="/assets/team/karl-twomey.jpg" alt="Karl Twomey, Wove" width="200" height="200" loading="lazy">
      </div>
      <div class="contact__body">
        <p class="contact__text" id="contact-heading">Have a project in mind? Curious about ways the Labs team could bring value to your organisation?</p>
        <a href="/contact" class="btn btn--primary btn--md">Talk to Karl today <span aria-hidden="true">&rarr;</span></a>
      </div>
    </div>
  </section>

  <!-- SERVICE PAGER -->
  <nav class="pager container" aria-label="Service pages">
    <a href="/services/digital" class="pager__link pager__link--prev" rel="prev">
      <span class="pager__dir"><span aria-hidden="true">&larr;</span> Previous service</span>
      <span class="pager__name">Digital</span>
    </a>
    <a href="/services/strategy" class="pager__link pager__link--next" rel="next">
      <span class="pager__dir">Next service <span aria-hidden="true">&rarr;</span></span>
      <span class="pager__name">Strategy</span>
    </a>
  </nav>

</main>

<?php snippet('service-page-scripts') ?>
<?php snippet('footer') ?>
