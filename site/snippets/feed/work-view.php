<?php
/**
 * Our work: every post and case study, filtered in place by client, topic,
 * sector or person, with the homepage's carousel, pills, pinned bar and
 * context panels. Used by work.php, service.php and tag.php, so a service
 * or tag URL opens Our work with that filter selected and its panel giving
 * the context.
 * Usage: <?php snippet('feed/work-view', ['initial' => 'service:strategy']) ?>
 *
 * `initial` is the filter to start with ('*' for all). A ?filter= in the
 * URL (from "See all" on the homepage, card tags or profiles) wins. Unknown
 * filters fall back to all work.
 */

$feed    = wove_feed();
$initial = get('filter') ?: ($initial ?? '*');
if ($initial !== '*' && !wove_feed_has_key($initial)) $initial = '*';
$total   = count($feed['items']);
$intro   = $kirby->page('work')?->intro()->or('')->value()
  ?: 'Case studies, and the thinking behind them. Choose a client to see the project and everything we have written about it, or a topic to see work across clients.';
?>
<?php snippet('header', ['css' => ['/assets/css/home.css', '/assets/css/pages.css'], 'nav' => false]) ?>

<div class="home" data-theme="light">

  <header class="hx hx--page">
    <div class="hx__inner">
      <?php snippet('brand/header-row', ['current' => 'work']) ?>
      <div class="hx-body">
        <h1 class="ph__title">Our work</h1>
        <p class="ph__intro"><?= html($intro) ?></p>
      </div>
    </div>
  </header>

  <div class="feed-wrap">

    <?php snippet('home/filter-bar', ['clients' => $feed['clients'], 'topics' => $feed['topics'], 'totalCount' => $total]) ?>

    <main class="feed feed--home" id="main">

      <div class="feed__header" id="feed">
        <h2 class="feed__title">All work</h2>
        <span class="feed__showing label" id="feed-count" role="status" aria-live="polite"><?= $total ?> entr<?= $total === 1 ? 'y' : 'ies' ?></span>
      </div>

      <?php snippet('home/filters', [
        'clients'    => $feed['clients'],
        'topics'     => $feed['topics'],
        'sectors'    => $feed['sectors'],
        'authors'    => $feed['authors'],
        'totalCount' => $total,
      ]) ?>

      <div class="feed-cards pc-grid" id="feed-grid" tabindex="-1" data-initial="<?= html($initial) ?>" data-url="<?= url('our-work') ?>" data-page="12">
        <?php foreach ($feed['items'] as $item): ?>
          <?php if ($item->intendedTemplate()->name() === 'case-study'): ?>
            <?php snippet('home/case-card', ['cs' => $item, 'filters' => $feed['keys'][$item->id()] ?? []]) ?>
          <?php else: ?>
            <?php snippet('home/post-card', ['post' => $item, 'filters' => $feed['keys'][$item->id()] ?? [], 'hidden' => false]) ?>
          <?php endif ?>
        <?php endforeach ?>
        <p class="feed-empty" id="feed-empty" hidden>No entries match this filter yet.</p>
      </div>

      <div class="feed-more" id="feed-more" hidden>
        <span class="feed-more__count" id="feed-more-count"></span>
        <button type="button" class="feed-more__btn" id="feed-load-more">Load more</button>
      </div>

    </main>
  </div>
</div>

<script src="/assets/js/home.js" defer></script>
<?php snippet('service-page-scripts') ?>
<?php snippet('footer') ?>
