<?php
/**
 * Our Work — unified feed hub with in-page filtering
 * File: site/templates/work.php
 *
 * All filtering (by service, sector, tag, or case study) happens
 * client-side with smooth transitions. The URL updates via the
 * History API so filtered views are bookmarkable and shareable.
 *
 * Route: /our-work → page('work') (see site/config/config.php)
 */

$caseStudies = kirby()->collection('case-studies');
$wmParent    = $site->find('wove-mind');
$wmEntries   = $wmParent
  ? $wmParent->children()->listed()->sortBy('date', 'desc')
  : new \Kirby\Cms\Pages();

// --- Build feed items with all filter facets ---

$feedItems = [];
foreach ($caseStudies as $cs) {
  $feedItems[] = [
    'entry'       => $cs,
    'date'        => $cs->date()->toDate('U') ?: 0,
    'services'    => implode(',', array_filter(array_map('trim', $cs->services()->split(',')))),
    'sectors'     => implode(',', array_filter(array_map('trim', $cs->sectors()->split(',')))),
    'tags'        => implode(',', array_filter(array_map('trim', $cs->impactAreas()->split(',')))),
    'casestudies' => $cs->slug(),
    'authors'     => '',
  ];
}
foreach ($wmEntries as $e) {
  $linkedCS = [];
  foreach ($e->case_study()->toPages() as $linked) {
    $linkedCS[] = $linked->slug();
  }
  $feedItems[] = [
    'entry'       => $e,
    'date'        => $e->date()->toDate('U') ?: 0,
    'services'    => implode(',', array_filter(array_map('trim', $e->services()->split(',')))),
    'sectors'     => implode(',', array_filter(array_map('trim', $e->sectors()->split(',')))),
    'tags'        => implode(',', array_filter(array_map('trim', $e->content()->get('tags')->split(',')))),
    'casestudies' => implode(',', $linkedCS),
    'authors'     => ($author = wove_entry_author($e)) ? wove_author_slug($author) : '',
  ];
}
usort($feedItems, fn ($a, $b) => $b['date'] <=> $a['date']);

// --- Labels and tag structure ---

$serviceLabels = ['strategy' => 'Strategy', 'labs' => 'Labs', 'digital' => 'Digital', 'brand' => 'Brand'];
$sectorLabels = [
  'arts-and-culture'  => 'Arts and Culture',
  'public-service'    => 'Public Service',
  'higher-education'  => 'Higher Education',
  'non-profit'        => 'Non-profit and Mission-led',
  'founders-ventures' => 'Founders and Ventures',
];
$tagStructure = $site->tags()->toStructure();

$serviceIntros = [
  'strategy' => 'Helping organisations plan, prioritise and change in lasting ways.',
  'labs'     => 'Research and development for a more human future.',
  'digital'  => 'Platforms and innovation that help you deliver and scale impact.',
  'brand'    => 'Identities, content and campaigns that culturally connect.',
];

// --- Collect used facets ---

$usedServices = [];
$usedSectors  = [];
$usedTags     = [];

foreach ($feedItems as $item) {
  $e = $item['entry'];
  foreach (explode(',', $item['services']) as $s) {
    $s = trim($s);
    if ($s && isset($serviceLabels[$s])) $usedServices[$s] = $serviceLabels[$s];
  }
  foreach (explode(',', $item['sectors']) as $s) {
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

$usedCS = [];
foreach ($caseStudies as $cs) {
  $name = $cs->eyebrow()->value();
  if ($name) $usedCS[$cs->slug()] = $name;
}

// --- Hero data payload for JS ---

// Team members with at least one credited entry, for the Team dropdown
$usedAuthors = [];
foreach (wove_team_members() as $member) {
  $slug = wove_author_slug($member);
  foreach ($feedItems as $item) {
    if ($item['authors'] === $slug) {
      $usedAuthors[$slug] = $member;
      break;
    }
  }
}

$heroData = ['services' => [], 'caseStudies' => [], 'sectors' => [], 'tags' => [], 'authors' => []];
foreach ($usedAuthors as $slug => $member) {
  $heroData['authors'][$slug] = [
    'label' => $member->name()->value(),
    'role'  => $member->content()->get('role')->value() ?: '',
    'url'   => url('our-people') . '#' . $slug,
  ];
}
foreach ($usedServices as $slug => $label) {
  $heroData['services'][$slug] = ['label' => $label, 'intro' => $serviceIntros[$slug] ?? ''];
}
foreach ($usedSectors as $slug => $label) {
  $heroData['sectors'][$slug] = ['label' => $label];
}
foreach ($usedTags as $slug => $label) {
  $heroData['tags'][$slug] = ['label' => $label];
}
foreach ($caseStudies as $cs) {
  $heroImg = $cs->caseStudyImages()->toFile();
  $heroData['caseStudies'][$cs->slug()] = [
    'eyebrow'  => $cs->eyebrow()->value() ?: '',
    'title'    => strip_tags($cs->heroTitle()->value() ?: $cs->title()->value()),
    'subtitle' => $cs->subStatement()->value() ?: '',
    'image'    => $heroImg ? $heroImg->url() : null,
    'url'      => $cs->url(),
  ];
}

$totalCount = count($feedItems);
?>

<?php snippet('header') ?>

<div class="feed-wrap">

  <div class="tag-hero" id="feed-hero">
    <div id="feed-hero-content">
      <h1 class="tag-hero__name" id="feed-hero-title">Our Work</h1>
      <div class="tag-hero__meta" id="feed-hero-meta">
        <?= $totalCount ?> entr<?= $totalCount === 1 ? 'y' : 'ies' ?>
      </div>
    </div>
  </div>

  <div class="feed-hero-image" id="feed-hero-image" hidden>
    <div class="feed-hero-image__inner">
      <img id="feed-hero-img" src="" alt="">
    </div>
  </div>

  <?php if ($usedServices || $usedSectors || $usedTags || $usedCS || $usedAuthors): ?>
  <nav class="tag-cloud" aria-label="Filter by tag">
    <button class="tag-pill is-active" data-filter="*" type="button">All</button>
    <?php foreach ($usedCS as $slug => $label): ?>
      <button class="tag-pill" data-filter="cs:<?= html($slug) ?>" type="button"><?= html($label) ?></button>
    <?php endforeach ?>
    <?php foreach ($usedServices as $slug => $label): ?>
      <button class="tag-pill" data-filter="service:<?= $slug ?>" type="button"><?= html($label) ?></button>
    <?php endforeach ?>
    <?php foreach ($usedTags as $slug => $label): ?>
      <button class="tag-pill" data-filter="tag:<?= html($slug) ?>" type="button"><?= html($label) ?></button>
    <?php endforeach ?>
    <?php foreach ($usedSectors as $slug => $label): ?>
      <button class="tag-pill" data-filter="sector:<?= $slug ?>" type="button"><?= html($label) ?></button>
    <?php endforeach ?>
    <?php if ($usedAuthors): ?>
      <label class="tag-pill tag-pill--select">
        <span class="visually-hidden">Filter by team member</span>
        <select id="feed-team-select">
          <option value="">Team</option>
          <?php foreach ($usedAuthors as $slug => $member): ?>
            <option value="<?= html($slug) ?>"><?= $member->name()->html() ?></option>
          <?php endforeach ?>
        </select>
      </label>
    <?php endif ?>
  </nav>
  <?php endif ?>

  <div class="tag-grid">
    <div class="stream-grid" id="feed-grid">
      <?php foreach ($feedItems as $item):
        $e = $item['entry'];
      ?>
        <div class="work-feed-item"
             data-services="<?= html($item['services']) ?>"
             data-sectors="<?= html($item['sectors']) ?>"
             data-tags="<?= html($item['tags']) ?>"
             data-casestudies="<?= html($item['casestudies']) ?>"
             data-authors="<?= html($item['authors']) ?>">
          <?php snippet('stream-card', ['post' => $e]) ?>
        </div>
      <?php endforeach ?>
    </div>

    <?php if ($totalCount === 0): ?>
      <p class="our-work-empty">No entries published yet.</p>
    <?php endif ?>
  </div>

</div>

<script>window.__feedHeroData = <?= json_encode($heroData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;</script>

<script>
(function() {
  var h = new Date().getHours();
  if (h >= 7 && h < 19) {
    document.documentElement.setAttribute('data-theme', 'light');
  }
})();
</script>

<script>
(function () {
  var data  = window.__feedHeroData || {};
  var pills = document.querySelectorAll('.tag-pill[data-filter]');
  var teamSelect = document.getElementById('feed-team-select');
  var items = document.querySelectorAll('.work-feed-item');
  var grid  = document.getElementById('feed-grid');
  var heroContent = document.getElementById('feed-hero-content');
  var heroImageWrap = document.getElementById('feed-hero-image');
  var heroImg = document.getElementById('feed-hero-img');

  if (!pills.length || !items.length) return;

  var totalCount = items.length;
  var FADE = 180;

  function esc(str) {
    var d = document.createElement('div');
    d.textContent = str || '';
    return d.innerHTML;
  }

  function countVisible() {
    var n = 0;
    items.forEach(function (el) { if (!el.hidden) n++; });
    return n;
  }

  function entryWord(n) {
    return n + ' entr' + (n === 1 ? 'y' : 'ies');
  }

  function heroHTML(filter) {
    if (filter === '*') {
      return '<h1 class="tag-hero__name">Our Work</h1>' +
             '<div class="tag-hero__meta">' + entryWord(totalCount) + '</div>';
    }
    var parts = filter.split(':');
    var type  = parts[0];
    var slug  = parts.slice(1).join(':');

    if (type === 'service' && data.services && data.services[slug]) {
      var s = data.services[slug];
      var h = '<h1 class="tag-hero__name">' + esc(s.label) + '</h1>';
      if (s.intro) h += '<p class="tag-hero__intro">' + esc(s.intro) + '</p>';
      h += '<div class="tag-hero__meta" id="feed-hero-meta"></div>';
      return h;
    }

    if (type === 'cs' && data.caseStudies && data.caseStudies[slug]) {
      var c = data.caseStudies[slug];
      var h = '';
      if (c.eyebrow) h += '<div class="tag-hero__eyebrow">' + esc(c.eyebrow) + '</div>';
      h += '<h1 class="tag-hero__name">' + esc(c.title) + '</h1>';
      if (c.subtitle) h += '<p class="tag-hero__intro">' + esc(c.subtitle) + '</p>';
      h += '<a href="' + esc(c.url) + '" class="tag-hero__cta">View case study &rarr;</a>';
      h += '<div class="tag-hero__meta" id="feed-hero-meta"></div>';
      return h;
    }

    if (type === 'sector' && data.sectors && data.sectors[slug]) {
      return '<div class="tag-hero__label">Sector</div>' +
             '<h1 class="tag-hero__name">' + esc(data.sectors[slug].label) + '</h1>' +
             '<div class="tag-hero__meta" id="feed-hero-meta"></div>';
    }

    if (type === 'author' && data.authors && data.authors[slug]) {
      var a = data.authors[slug];
      var h = '<div class="tag-hero__label">Team</div>' +
              '<h1 class="tag-hero__name">' + esc(a.label) + '</h1>';
      if (a.role) h += '<p class="tag-hero__intro">' + esc(a.role) + '</p>';
      h += '<a href="' + esc(a.url) + '" class="tag-hero__cta">View profile &rarr;</a>';
      h += '<div class="tag-hero__meta" id="feed-hero-meta"></div>';
      return h;
    }

    if (type === 'tag' && data.tags && data.tags[slug]) {
      return '<h1 class="tag-hero__name">' + esc(data.tags[slug].label) + '</h1>' +
             '<div class="tag-hero__meta" id="feed-hero-meta"></div>';
    }

    return '<h1 class="tag-hero__name">Our Work</h1>' +
           '<div class="tag-hero__meta">' + entryWord(totalCount) + '</div>';
  }

  function applyFilter(filter, push) {
    pills.forEach(function (p) {
      p.classList.toggle('is-active', p.getAttribute('data-filter') === filter);
    });
    if (teamSelect) {
      var isAuthor = filter.indexOf('author:') === 0;
      teamSelect.value = isAuthor ? filter.slice(7) : '';
      teamSelect.parentNode.classList.toggle('is-active', isAuthor && teamSelect.value !== '');
    }

    heroContent.classList.add('is-fading');
    grid.classList.add('is-fading');

    setTimeout(function () {
      // Filter items
      var attrMap = { service: 'data-services', sector: 'data-sectors', tag: 'data-tags', cs: 'data-casestudies', author: 'data-authors' };
      if (filter === '*') {
        items.forEach(function (el) { el.hidden = false; });
      } else {
        var parts = filter.split(':');
        var type  = parts[0];
        var value = parts.slice(1).join(':');
        var attr  = attrMap[type];
        if (attr) {
          items.forEach(function (el) {
            el.hidden = (el.getAttribute(attr) || '').split(',').indexOf(value) === -1;
          });
        }
      }

      // Swap hero content
      heroContent.innerHTML = heroHTML(filter);
      var meta = document.getElementById('feed-hero-meta');
      if (meta) meta.textContent = entryWord(countVisible());

      // Case study hero image
      var parts2 = filter.split(':');
      var isCS = parts2[0] === 'cs';
      var csSlug = parts2.slice(1).join(':');
      if (isCS && data.caseStudies && data.caseStudies[csSlug] && data.caseStudies[csSlug].image) {
        heroImg.src = data.caseStudies[csSlug].image;
        heroImg.alt = data.caseStudies[csSlug].eyebrow || '';
        heroImageWrap.hidden = false;
      } else {
        heroImageWrap.hidden = true;
      }

      // Fade in
      heroContent.classList.remove('is-fading');
      grid.classList.remove('is-fading');
    }, FADE);

    if (push !== false) {
      var url = filter === '*'
        ? window.location.pathname
        : window.location.pathname + '?filter=' + encodeURIComponent(filter);
      history.pushState({ filter: filter }, '', url);
    }
  }

  pills.forEach(function (pill) {
    pill.addEventListener('click', function () {
      applyFilter(pill.getAttribute('data-filter'), true);
    });
  });

  if (teamSelect) {
    teamSelect.addEventListener('change', function () {
      applyFilter(teamSelect.value ? 'author:' + teamSelect.value : '*', true);
    });
  }

  window.addEventListener('popstate', function (e) {
    applyFilter(e.state && e.state.filter ? e.state.filter : filterFromURL(), false);
  });

  function filterFromURL() {
    var p = new URLSearchParams(window.location.search);
    return p.get('filter') || '*';
  }

  // Apply filter from URL on load
  var init = filterFromURL();
  if (init !== '*') {
    applyFilter(init, false);
  }
  history.replaceState({ filter: init }, '', window.location.href);
})();
</script>

<?php snippet('service-page-scripts') ?>
<?php snippet('footer') ?>
