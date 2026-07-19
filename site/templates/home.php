<?php snippet('header') ?>

<main id="main">

  <!-- HERO -->
  <section class="home-hero" aria-label="Introduction">
    <div class="home-hero__inner">
      <div class="home-hero__text">
        <h1 class="home-hero__title">Wove</h1>
        <p class="home-hero__tagline">We use strategic design to make things work better, for everyone.</p>
      </div>
      <div class="home-hero__media" aria-hidden="true">
        <img src="/assets/illustrations/headcollar.png" alt="" width="983" height="1116" loading="eager" fetchpriority="high">
      </div>
    </div>
  </section>


  <!-- WHAT WE DO -->
  <section class="section container" aria-labelledby="what-we-do-heading">
    <div class="section-title">
      <p class="section-title__eyebrow">What we do</p>
      <h2 class="section-title__heading" id="what-we-do-heading">Lasting impact through four connected services</h2>
    </div>

    <div class="services-grid">
      <a href="/services/labs" class="service-card">
        <div class="service-card__media">
          <img src="/assets/illustrations/headcollar.png" alt="" width="983" height="1116" loading="lazy">
        </div>
        <div class="service-card__body">
          <h3 class="service-card__title">Labs</h3>
          <p class="service-card__desc">Research and development for a more human future.</p>
          <span class="btn btn--ghost btn--sm service-card__cta">Explore Labs <span aria-hidden="true">&rarr;</span></span>
        </div>
      </a>
      <a href="/services/strategy" class="service-card">
        <div class="service-card__media">
          <img src="/assets/illustrations/headcollar.png" alt="" width="983" height="1116" loading="lazy">
        </div>
        <div class="service-card__body">
          <h3 class="service-card__title">Strategy</h3>
          <p class="service-card__desc">Helping organisations plan, prioritise and change in lasting ways.</p>
          <span class="btn btn--ghost btn--sm service-card__cta">Explore Strategy <span aria-hidden="true">&rarr;</span></span>
        </div>
      </a>
      <a href="/services/brand" class="service-card">
        <div class="service-card__media">
          <img src="/assets/illustrations/headcollar.png" alt="" width="983" height="1116" loading="lazy">
        </div>
        <div class="service-card__body">
          <h3 class="service-card__title">Brand</h3>
          <p class="service-card__desc">Identities, content and campaigns that culturally connect.</p>
          <span class="btn btn--ghost btn--sm service-card__cta">Explore Brand <span aria-hidden="true">&rarr;</span></span>
        </div>
      </a>
      <a href="/services/digital" class="service-card">
        <div class="service-card__media">
          <img src="/assets/illustrations/headcollar.png" alt="" width="983" height="1116" loading="lazy">
        </div>
        <div class="service-card__body">
          <h3 class="service-card__title">Digital</h3>
          <p class="service-card__desc">Platforms and innovation that help you deliver and scale impact.</p>
          <span class="btn btn--ghost btn--sm service-card__cta">Explore Digital <span aria-hidden="true">&rarr;</span></span>
        </div>
      </a>
    </div>

    <div class="services-cta">
      <a href="/services" class="btn btn--ghost btn--md">Learn more about our services <span aria-hidden="true">&rarr;</span></a>
    </div>
  </section>


  <!-- RECENT WORK — Kirby case-studies collection embed -->
  <?php
    $recentCaseStudies = kirby()->collection('case-studies')->limit(4);
  ?>
  <?php if ($recentCaseStudies->count()): ?>
  <section class="section container" aria-labelledby="recent-work-heading">
    <div class="section-title">
      <p class="section-title__eyebrow">Recent work</p>
      <h2 class="section-title__heading" id="recent-work-heading">Delivering change for clients right across Irish society</h2>
    </div>

    <div class="work-cards">
      <?php foreach ($recentCaseStudies as $caseStudy): ?>
        <?php $image = $caseStudy->caseStudyImages()->toFile() ?>
        <a href="<?= $caseStudy->url() ?>" class="work-card">
          <div class="work-card__media">
            <?php if ($image): ?>
              <img src="<?= $image->url() ?>" alt="" width="280" height="280" loading="lazy">
            <?php endif ?>
          </div>
          <div class="work-card__body">
            <p class="work-card__title"><?= $caseStudy->heroTitle() ?></p>
            <span class="btn btn--ghost btn--sm">Read more <span aria-hidden="true">&rarr;</span></span>
          </div>
        </a>
      <?php endforeach ?>
    </div>
  </section>
  <?php endif ?>


  <!-- WHO WE WORK WITH -->
  <section class="section container" aria-labelledby="clients-heading">
    <div class="section-title">
      <p class="section-title__eyebrow">Who we work with</p>
      <h2 class="section-title__heading" id="clients-heading">Over 20 years partnering with clients across the civic, cultural and commercial sectors</h2>
    </div>

    <div class="client-marquee">
      <img src="/assets/clients/pivot-dublin.png" alt="Pivot Dublin" width="300" height="111" loading="lazy">
      <img src="/assets/clients/dublin-inquirer.png" alt="Dublin Inquirer" width="270" height="108" loading="lazy">
      <img src="/assets/clients/dcu.png" alt="DCU" width="350" height="238" loading="lazy">
      <img src="/assets/clients/silvercloud.png" alt="SilverCloud" width="300" height="65" loading="lazy">
    </div>
  </section>


  <!-- TESTIMONIAL -->
  <section class="testimonial" aria-label="Testimonial">
    <div class="testimonial__inner">
      <blockquote class="testimonial__quote">&ldquo;Organisations today face <em>more</em> complexity, <em>more</em> accountability and <em>more</em> scrutiny than ever before. We use strategic design to help them meet those challenges.&rdquo;</blockquote>
      <figcaption class="testimonial__attr">
        <div class="testimonial__avatar">
          <img src="/assets/team/scott.jpg" alt="" width="86" height="86" loading="lazy">
        </div>
        <div>
          <p class="testimonial__name">Scott Burnett</p>
          <p class="testimonial__role">Strategic Director</p>
        </div>
      </figcaption>
      <a href="/approach" class="btn btn--primary btn--md testimonial__cta">Explore Approach <span aria-hidden="true">&rarr;</span></a>
    </div>
    <div class="testimonial__media" aria-hidden="true">
      <img src="/assets/illustrations/ladder.png" alt="" width="750" height="1259" loading="lazy">
    </div>
  </section>


  <!-- WHO WE ARE -->
  <section class="section container" aria-labelledby="who-we-are-heading">
    <div class="section-title">
      <p class="section-title__eyebrow">Who we are</p>
      <h2 class="section-title__heading" id="who-we-are-heading">We're a team of strategists, designers and technologists based all across Ireland. And we're proud to be Ireland's first BCorp design agency.</h2>
    </div>
    <a href="/about" class="btn btn--ghost btn--md">Learn more about us <span aria-hidden="true">&rarr;</span></a>

    <div class="about-closing-image">
      <img src="/assets/photos/home-closing.png" alt="The Wove team" width="1200" height="801" loading="lazy">
    </div>
  </section>

</main>

<?php snippet('service-page-scripts') ?>
<?php snippet('footer') ?>
