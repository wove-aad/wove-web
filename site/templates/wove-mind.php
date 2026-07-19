<?php
/**
 * Wove Mind — feed template
 * File: site/templates/wove-mind.php
 * Renamed from wovemind.php: Kirby resolves a page's template purely from
 * its content filename (wove-mind.txt), never from the "Template:" field
 * that was sitting in that file — that field does nothing on its own.
 * Same class of bug as wove-mind-entry.php: this page was silently
 * falling back to default.php both locally and (presumably) on staging.
 * Figma: Wove mind frame (165:568)
 *
 * The format filter tabs (All/Spark/Thread/What if/Long read) are instant
 * client-side JS — no reload — but sync to a ?format= query param via
 * history.pushState so each filtered view still has its own shareable URL.
 *
 * The newsletter form is a static placeholder (per Grace, 2026-07-19) —
 * it doesn't submit anywhere yet.
 */

$posts = $page->children()->listed()->sortBy('date', 'desc')
  ->filter(fn ($p) => $p->format()->value() !== 'project-highlight');

$formats = [
  'all'      => 'All',
  'spark'    => 'Spark',
  'thread'   => 'Thread',
  'whatif'   => 'What if',
  'longread' => 'Long read',
];
?>

<?php snippet('header') ?>

<main class="wovemind-feed">

  <!-- HERO -->
  <header class="page-head">
    <div class="page-head__inner">
      <h1 class="page-head__title"><?= $page->title()->html() ?></h1>
      <p class="page-head__tagline"><?= $page->tagline()->or('The studio thinking out loud')->html() ?></p>
    </div>
  </header>

  <section class="section--flush-top container" aria-label="Introduction">
    <p class="lead indent">A living feed of what we're reading, questioning, making and noticing. Not polished opinions — just honest thinking from the people in the studio, as it happens.</p>
    <div class="wovemind-hero-cta indent">
      <a href="#newsletter" class="btn btn--primary btn--md">Get the newsletter</a>
    </div>
  </section>


  <!-- FILTER TOOLBAR -->
  <nav class="wovemind-filter" aria-label="Filter by format">
    <div class="wovemind-filter__inner">
      <?php foreach ($formats as $value => $label): ?>
        <button type="button" class="wovemind-filter__btn<?= $value === 'all' ? ' is-active' : '' ?>" data-filter="<?= $value ?>" aria-pressed="<?= $value === 'all' ? 'true' : 'false' ?>">
          <?= html($label) ?>
        </button>
      <?php endforeach ?>
    </div>
  </nav>


  <!-- CARD GRID -->
  <section class="section container" aria-label="Entries">
    <div class="wovemind-feed__grid">
      <?php foreach ($posts as $post): ?>
        <?php snippet('wovemind-card', ['post' => $post]) ?>
      <?php endforeach ?>
    </div>
    <p class="wovemind-feed__empty"<?= $posts->count() ? ' hidden' : '' ?>>No entries match this filter yet.</p>
  </section>


  <!-- NEWSLETTER (static placeholder — not wired up yet) -->
  <section class="newsletter" id="newsletter" aria-labelledby="newsletter-heading">
    <div class="container">
      <div class="newsletter__content">
        <h2 class="newsletter__heading" id="newsletter-heading">Get the thinking in your inbox</h2>
        <p class="newsletter__desc">We send a curated selection of Wove Mind entries every few weeks. No schedule, no filler — just when there's something worth sharing.</p>
        <form class="newsletter__form" aria-label="Newsletter signup">
          <label class="visually-hidden" for="newsletter-email">Email address</label>
          <input type="email" id="newsletter-email" name="email" placeholder="your@email.com" class="newsletter__input" required>
          <button type="submit" class="btn btn--primary btn--sm">Subscribe</button>
        </form>
      </div>
    </div>
  </section>

</main>

<?php snippet('service-page-scripts') ?>
<script>
  /* Format filter — instant client-side, URL-synced (no reload) */
  (function () {
    var toolbar = document.querySelector('.wovemind-filter');
    var grid = document.querySelector('.wovemind-feed__grid');
    var empty = document.querySelector('.wovemind-feed__empty');
    if (!toolbar || !grid) return;
    var buttons = Array.prototype.slice.call(toolbar.querySelectorAll('[data-filter]'));
    var cards = Array.prototype.slice.call(grid.querySelectorAll('[data-format]'));

    function applyFilter(format, pushState) {
      var visibleCount = 0;
      cards.forEach(function (card) {
        var show = format === 'all' || card.dataset.format === format;
        card.hidden = !show;
        if (show) visibleCount++;
      });
      buttons.forEach(function (btn) {
        var active = btn.dataset.filter === format;
        btn.classList.toggle('is-active', active);
        btn.setAttribute('aria-pressed', String(active));
      });
      if (empty) empty.hidden = visibleCount > 0;
      if (pushState) {
        var url = new URL(window.location.href);
        if (format === 'all') {
          url.searchParams.delete('format');
        } else {
          url.searchParams.set('format', format);
        }
        history.pushState({ format: format }, '', url);
      }
    }

    buttons.forEach(function (btn) {
      btn.addEventListener('click', function () { applyFilter(btn.dataset.filter, true); });
    });
    window.addEventListener('popstate', function () {
      applyFilter(new URLSearchParams(window.location.search).get('format') || 'all', false);
    });

    // Always run once on load (not just when a ?format= is present) so the
    // empty-state message is correctly shown/hidden even with zero entries.
    applyFilter(new URLSearchParams(window.location.search).get('format') || 'all', false);
  })();

  /* Newsletter form — static placeholder, not wired up to a service yet */
  (function () {
    var form = document.querySelector('.newsletter__form');
    if (!form) return;
    form.addEventListener('submit', function (e) { e.preventDefault(); });
  })();
</script>
<?php snippet('footer') ?>
