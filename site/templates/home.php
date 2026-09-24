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
      <a href="/our-work?filter=service:labs" class="service-card">
        <div class="service-card__media">
          <img src="/assets/illustrations/headcollar.png" alt="" width="983" height="1116" loading="lazy">
        </div>
        <div class="service-card__body">
          <h3 class="service-card__title">Labs</h3>
          <p class="service-card__desc">Research and development for a more human future.</p>
          <span class="btn btn--ghost btn--sm service-card__cta">Explore Labs <span aria-hidden="true">&rarr;</span></span>
        </div>
      </a>
      <a href="/our-work?filter=service:strategy" class="service-card">
        <div class="service-card__media">
          <img src="/assets/illustrations/headcollar.png" alt="" width="983" height="1116" loading="lazy">
        </div>
        <div class="service-card__body">
          <h3 class="service-card__title">Strategy</h3>
          <p class="service-card__desc">Helping organisations plan, prioritise and change in lasting ways.</p>
          <span class="btn btn--ghost btn--sm service-card__cta">Explore Strategy <span aria-hidden="true">&rarr;</span></span>
        </div>
      </a>
      <a href="/our-work?filter=service:brand" class="service-card">
        <div class="service-card__media">
          <img src="/assets/illustrations/headcollar.png" alt="" width="983" height="1116" loading="lazy">
        </div>
        <div class="service-card__body">
          <h3 class="service-card__title">Brand</h3>
          <p class="service-card__desc">Identities, content and campaigns that culturally connect.</p>
          <span class="btn btn--ghost btn--sm service-card__cta">Explore Brand <span aria-hidden="true">&rarr;</span></span>
        </div>
      </a>
      <a href="/our-work?filter=service:digital" class="service-card">
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
      <a href="/our-work" class="btn btn--ghost btn--md">Learn more about our services <span aria-hidden="true">&rarr;</span></a>
    </div>
  </section>


  <!-- MIXED FEED -->
  <?php
    $caseStudies = kirby()->collection('case-studies');
    $wmParent    = $site->find('wove-mind');
    $wmEntries   = $wmParent
      ? $wmParent->children()->listed()->sortBy('date', 'desc')
      : new \Kirby\Cms\Pages();

    $feedItems = [];
    foreach ($caseStudies as $cs) {
      $feedItems[] = ['entry' => $cs, 'date' => $cs->date()->toDate('U') ?: 0];
    }
    foreach ($wmEntries as $e) {
      $feedItems[] = ['entry' => $e, 'date' => $e->date()->toDate('U') ?: 0];
    }
    usort($feedItems, fn ($a, $b) => $b['date'] <=> $a['date']);

    $feedMax   = 12;
    $feedItems = array_slice($feedItems, 0, $feedMax);

    $serviceLabels = ['strategy' => 'Strategy', 'labs' => 'Labs', 'digital' => 'Digital', 'brand' => 'Brand'];
    $sectorLabels  = [
      'arts-and-culture'  => 'Arts and Culture',
      'public-service'    => 'Public Service',
      'higher-education'  => 'Higher Education',
      'non-profit'        => 'Non-profit and Mission-led',
      'founders-ventures' => 'Founders and Ventures',
    ];
    $tagStructure  = $site->tags()->toStructure();

    $usedServices = [];
    $usedSectors  = [];
    $usedTags     = [];
    foreach ($feedItems as $item) {
      $e = $item['entry'];
      foreach ($e->services()->split(',') as $s) {
        $s = trim($s);
        if ($s && isset($serviceLabels[$s])) $usedServices[$s] = $serviceLabels[$s];
      }
      foreach ($e->sectors()->split(',') as $s) {
        $s = trim($s);
        if ($s && isset($sectorLabels[$s])) $usedSectors[$s] = $sectorLabels[$s];
      }
      $tagField = $e->intendedTemplate()->name() === 'case-study' ? 'impactAreas' : 'tags';
      foreach ($e->content()->get($tagField)->split(',') as $t) {
        $t = trim($t);
        if (!$t) continue;
        $tag = $tagStructure->findBy('slug', $t) ?: $tagStructure->findBy('name', $t);
        if ($tag && $tag->active()->toBool() !== false) {
          $usedTags[$tag->slug()->value()] = $tag->name()->value();
        }
      }
    }
  ?>
  <div class="feed-wrap home-feed">
    <section class="home-feed__shell" aria-labelledby="home-feed-heading">
      <aside class="home-feed__rail" aria-label="Filter">
        <h2 class="home-feed__rail-title" id="home-feed-heading">Stream</h2>
        <div class="home-feed__pills">
          <button class="tag-pill is-active" data-filter="*" type="button">All</button>
          <?php foreach ($usedServices as $slug => $label): ?>
            <button class="tag-pill" data-filter="service:<?= $slug ?>" type="button"><?= html($label) ?></button>
          <?php endforeach ?>
          <?php foreach ($usedSectors as $slug => $label): ?>
            <button class="tag-pill" data-filter="sector:<?= $slug ?>" type="button"><?= html($label) ?></button>
          <?php endforeach ?>
          <?php foreach ($usedTags as $slug => $label): ?>
            <button class="tag-pill" data-filter="tag:<?= html($slug) ?>" type="button"><?= html($label) ?></button>
          <?php endforeach ?>
        </div>
      </aside>

      <div class="home-feed__main">
        <div class="home-feed__grid">
          <?php foreach ($feedItems as $item):
            $e = $item['entry'];
            $services = implode(',', array_filter(array_map('trim', $e->services()->split(','))));
            $sectors  = implode(',', array_filter(array_map('trim', $e->sectors()->split(','))));
            $tagField = $e->intendedTemplate()->name() === 'case-study' ? 'impactAreas' : 'tags';
            $eTags    = implode(',', array_filter(array_map('trim', $e->content()->get($tagField)->split(','))));
          ?>
            <div class="home-feed__item"
                 data-services="<?= html($services) ?>"
                 data-sectors="<?= html($sectors) ?>"
                 data-tags="<?= html($eTags) ?>">
              <?php snippet('stream-card', ['post' => $e]) ?>
            </div>
          <?php endforeach ?>
        </div>

        <div class="home-feed__more" id="home-feed-more" hidden>
          <a href="/our-work" class="home-feed__more-btn" id="home-feed-more-link">See all work &rarr;</a>
        </div>
      </div>
    </section>
  </div>


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

<script>
(function() {
  var h = new Date().getHours();
  if (h >= 7 && h < 19) {
    document.documentElement.setAttribute('data-theme', 'light');
  }
})();
</script>

<script>window.__homeFeedLabels = <?= json_encode(
  array_merge(
    array_map(fn ($l) => ['label' => $l, 'filter' => 'service'], $usedServices),
    array_map(fn ($l) => ['label' => $l, 'filter' => 'sector'], $usedSectors),
    array_map(fn ($l) => ['label' => $l, 'filter' => 'tag'], $usedTags)
  ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;</script>

<script>
(function () {
  var pills = document.querySelectorAll('.home-feed__pills .tag-pill');
  var items = document.querySelectorAll('.home-feed__item');
  var moreWrap = document.getElementById('home-feed-more');
  var moreLink = document.getElementById('home-feed-more-link');
  var labels = window.__homeFeedLabels || {};
  if (!pills.length || !items.length) return;

  var LIMIT = 5;
  var attrMap = { service: 'data-services', sector: 'data-sectors', tag: 'data-tags' };

  function applyFilter(filter) {
    var matched = [];
    items.forEach(function (item) {
      if (filter === '*') {
        matched.push(item);
      } else {
        var parts = filter.split(':');
        var type  = parts[0];
        var value = parts.slice(1).join(':');
        var attr  = item.getAttribute(attrMap[type] || '') || '';
        if (attr.split(',').indexOf(value) !== -1) {
          matched.push(item);
        }
      }
    });

    var showAll = filter === '*';
    var cap = showAll ? items.length : LIMIT;
    var shown = 0;

    items.forEach(function (item) {
      if (matched.indexOf(item) !== -1 && shown < cap) {
        item.hidden = false;
        shown++;
      } else {
        item.hidden = true;
      }
    });

    if (!showAll && matched.length > LIMIT) {
      var slug = filter.split(':').slice(1).join(':');
      var meta = labels[slug];
      var name = meta ? meta.label : slug;
      moreLink.href = '/our-work?filter=' + encodeURIComponent(filter);
      moreLink.textContent = 'See all ' + name + ' →';
      moreWrap.hidden = false;
    } else if (showAll && <?= json_encode($caseStudies->count() + $wmEntries->count() > $feedMax) ?>) {
      moreLink.href = '/our-work';
      moreLink.textContent = 'See all work →';
      moreWrap.hidden = false;
    } else {
      moreWrap.hidden = true;
    }
  }

  // Bring the top of the feed back into view if the reader has scrolled past it
  var shell = document.querySelector('.home-feed__shell');
  function scrollToFeedTop() {
    if (!shell || shell.getBoundingClientRect().top >= 0) return;
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    shell.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
  }

  pills.forEach(function (pill) {
    pill.addEventListener('click', function () {
      pills.forEach(function (p) { p.classList.remove('is-active'); });
      pill.classList.add('is-active');
      applyFilter(pill.getAttribute('data-filter'));
      scrollToFeedTop();
    });
  });

  applyFilter('*');
})();
</script>

<?php snippet('service-page-scripts') ?>
<?php snippet('footer') ?>
