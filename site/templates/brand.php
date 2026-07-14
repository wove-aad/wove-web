<?php snippet('header') ?>

<main id="main">

  <!-- PAGE HEAD -->
  <header class="page-head">
    <div class="page-head__inner">
      <h1 class="page-head__title"><?= $page->title()->html() ?></h1>
      <p class="page-head__tagline">Identities, content and campaigns that culturally connect.</p>
    </div>
  </header>


  <!-- INTRO -->
  <section class="section section--flush-top container" aria-label="Introduction">
    <p class="lead indent">Organisations today need more than a logo. <strong>They need a brand and content that earns trust, reflects their values and connects meaningfully with the people they serve.</strong> Whether you're building something new or evolving what you have, Wove brings strategic thinking and creative craft together to build brands, content and communications that deepen customer and audience relationships.</p>
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
      <p class="lead">We offer a full range of brand capabilities, from strategy and identity through to campaigns, content and experience.</p>
    </div>
  </section>


  <!-- SERVICE OFFERINGS — static, not CMS-managed -->
  <section class="section container" aria-labelledby="offerings-heading">
    <h2 id="offerings-heading" class="visually-hidden">What we offer</h2>
    <div class="offerings">
      <div class="offering"><h3 class="offering__title">Research and brand strategy</h3><p class="offering__body">Building a clear, holistic picture of the people you serve and defining the strategies that best deliver for them.</p></div>
      <div class="offering"><h3 class="offering__title">Brand experience and journey design</h3><p class="offering__body">Designing the structures, interactions and details that drive meaningful connections across the whole customer journey.</p></div>
      <div class="offering"><h3 class="offering__title">Co-design</h3><p class="offering__body">Workshops that deepen understanding, sharpen alignment and unlock opportunities with your teams and users.</p></div>
      <div class="offering"><h3 class="offering__title">Brand identity</h3><p class="offering__body">Translating what makes you valuable into a meaningful brand idea, story and system.</p></div>
      <div class="offering"><h3 class="offering__title">Naming and language</h3><p class="offering__body">Brand naming, proposition and UX writing that communicates your value and builds a distinct identity.</p></div>
      <div class="offering"><h3 class="offering__title">Campaigns and content</h3><p class="offering__body">Creative campaigns and content that deliver impact across channels from TV and radio to social and OOH.</p></div>
      <div class="offering"><h3 class="offering__title">Motion and video</h3><p class="offering__body">From concept to delivery, bringing your brand story to life with dynamic motion and video content.</p></div>
      <div class="offering"><h3 class="offering__title">Illustration and image direction</h3><p class="offering__body">Original visual work created in collaboration with local illustrators and image makers.</p></div>
      <div class="offering"><h3 class="offering__title">Packaging</h3><p class="offering__body">Brand-led packaging design that stands out and treads lightly on the planet.</p></div>
      <div class="offering"><h3 class="offering__title">Exhibition and environmental design</h3><p class="offering__body">Physical brand experiences, from exhibitions and installations to retail and event environments.</p></div>
      <div class="offering"><h3 class="offering__title">Sustainability</h3><p class="offering__body">Consultancy and expertise in sustainable brand and packaging, backed by our BCorp commitment.</p></div>
      <div class="offering"><h3 class="offering__title">Accessibility</h3><p class="offering__body">Designing for everyone, from plain English content to WCAG AA compliant brand and digital assets, as standard.</p></div>
    </div>
  </section>


  <!-- QUOTE BANNER -->
  <section class="quote" aria-label="Quote">
    <figure class="quote__inner">
      <blockquote class="quote__text">&ldquo;Wove have been essential partners in the Abbey Theatre's brand redevelopment journey. The result has been really successful, with our visual comms cutting through the noise.&rdquo;</blockquote>
      <figcaption class="quote__attr">
        <span class="quote__name">John Tierney,</span>
        <span class="quote__role">Marketing Manager, The Abbey Theatre</span>
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
        <img src="/assets/team/johnny.jpg" alt="Johnny, Wove" width="200" height="200" loading="lazy">
      </div>
      <div class="contact__body">
        <p class="contact__text" id="contact-heading">Have a project in mind? Curious about ways the brand team could bring value to your organisation?</p>
        <a href="/contact" class="btn btn--primary btn--md">Talk to Johnny today <span aria-hidden="true">&rarr;</span></a>
      </div>
    </div>
  </section>

  <!-- SERVICE PAGER -->
  <nav class="pager container" aria-label="Service pages">
    <a href="/services/strategy" class="pager__link pager__link--prev" rel="prev">
      <span class="pager__dir"><span aria-hidden="true">&larr;</span> Previous service</span>
      <span class="pager__name">Strategy</span>
    </a>
    <a href="/services/digital" class="pager__link pager__link--next" rel="next">
      <span class="pager__dir">Next service <span aria-hidden="true">&rarr;</span></span>
      <span class="pager__name">Digital</span>
    </a>
  </nav>

</main>

<?php snippet('service-page-scripts') ?>
<?php snippet('footer') ?>
