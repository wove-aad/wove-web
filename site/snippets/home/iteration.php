<?php
/**
 * Homepage iterations for review (2026-10-30), from the Figma frames
 * "Homepage A" (1108:681), "B" (1108:940) and "C: Right now as hero"
 * (1108:1084), without their contact block and with the site's own
 * header and footer. Shown with ?home=1, 2 or 3 (templates/wove-mind.php);
 * the homepage without it is unchanged. A panel in the corner switches.
 *
 * 1: the statement, then a large featured slide (clients' hero images,
 *    turning every few seconds) with the latest news in a small card over
 *    it; then the services, the credibility panel, recent activity and
 *    the newsletter.
 * 2: a full-height slide behind the statement, a "Right now" line under
 *    it (one service at a time); then credibility, recent activity, the
 *    services and the newsletter.
 * 3: the statement, then "Right now": the newest post from each service
 *    as four tall cards; then credibility, recent activity and the
 *    newsletter.
 *
 * Data: the homepage controller ($entries, $entryFilters, $clients).
 * Styles: the HOMEPAGE ITERATIONS section of assets/css/home.css.
 * Behaviour: assets/js/home-iterations.js.
 */

$v        = $v ?? '1';
$services = wove_service_labels();
$order    = ['labs', 'strategy', 'brand', 'digital'];
$posts    = $entries->filter(fn ($p) => $p->format()->value() !== 'spark');

// Slides: clients with a hero image, newest first
$slides = [];
foreach ($clients as $c) {
  $img = $c['page']->caseStudyImages()->toFile();
  if (!$img) continue;
  $slides[] = [
    'page' => $c['page'],
    'name' => $c['name'],
    'img'  => $img,
    'line' => wove_card_excerpt((string) $c['page']->summary()->or($c['page']->subStatement())->value(), 70),
  ];
  if (count($slides) === 5) break;
}

// The latest news (or, without any, the latest post), for the card over
// the slide in iteration 1
$news = $posts->filter(fn ($p) => wove_post_type($p) === 'news')->first() ?? $posts->first();

// Right now: the newest post from each service
$now = [];
foreach ($order as $s) {
  $p = $posts->filter(fn ($p) => in_array($s, $p->services()->split(','), true))->first();
  if ($p) $now[$s] = $p;
}
$month = date('F Y');

// Credibility: client logos (Panel: Client logo), or their names
$logos = array_slice($clients, 0, 6);

// Recent activity: the newest six posts
$recent = $posts->limit(6);

$statement = function () { ?>
  <h1 class="hi-statement"><span class="hi-statement__muted">Strategy, design and technology.</span> Woven together to shape a better Ireland.</h1>
<?php };

$nowLine = function ($p) {
  return $p->title()->value();
};
?>
<?php snippet('header', ['css' => ['/assets/css/home.css', '/assets/css/pages.css'], 'nav' => false]) ?>

<div class="home hi hi--<?= html($v) ?>" data-theme="light">

  <?php if ($v === '2'): ?>
    <header class="hx hx--page hi-full" data-slides>
      <?php foreach ($slides as $i => $s): ?>
        <div class="hi-full__slide<?= $i === 0 ? ' is-on' : '' ?>" data-slide>
          <?php snippet('picture', ['file' => $s['img'], 'widths' => [960, 1440, 1920, 2560], 'sizes' => '100vw', 'class' => 'hi-full__img', 'loading' => $i === 0 ? 'eager' : 'lazy', 'fetchpriority' => $i === 0 ? 'high' : null]) ?>
        </div>
      <?php endforeach ?>
      <div class="hx__inner hi-full__inner">
        <?php snippet('brand/header-row') ?>
        <div class="hi-full__bottom">
          <?php $statement() ?>
          <?php if ($slides): ?>
            <div class="hi-full__caption">
              <?php foreach ($slides as $i => $s): ?>
                <a class="hi-full__credit<?= $i === 0 ? ' is-on' : '' ?>" href="<?= $s['page']->url() ?>" data-slide-caption>
                  <strong><?= html($s['name']) ?></strong>
                  <?php if ($s['line']): ?><span><?= html($s['line']) ?></span><?php endif ?>
                </a>
              <?php endforeach ?>
              <?php if (count($slides) > 1): ?>
                <div class="hi-dots" role="group" aria-label="Featured work">
                  <?php foreach ($slides as $i => $s): ?>
                    <button type="button" class="hi-dot" data-dot aria-label="<?= html($s['name']) ?>" aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>"></button>
                  <?php endforeach ?>
                </div>
              <?php endif ?>
            </div>
          <?php endif ?>
        </div>
      </div>
    </header>
    <?php if ($now): ?>
      <div class="hi-ticker" data-ticker>
        <div class="hi-ticker__inner">
          <span class="hi-kicker">Right now</span>
          <div class="hi-ticker__lines">
            <?php $i = 0; foreach ($now as $s => $p): ?>
              <a class="hi-ticker__line<?= $i++ === 0 ? ' is-on' : '' ?>" href="<?= $p->url() ?>" data-tick>
                <span class="hi-ticker__team">Wove <?= html($services[$s]) ?></span> <?= $p->title()->html() ?>
              </a>
            <?php endforeach ?>
          </div>
        </div>
      </div>
    <?php endif ?>
  <?php else: ?>
    <header class="hx hx--page hi-head">
      <div class="hx__inner">
        <?php snippet('brand/header-row') ?>
        <?php $statement() ?>
      </div>
    </header>
  <?php endif ?>

  <div class="feed-wrap">
    <?php snippet('brand/menu-bar') ?>

    <main class="pg hi-main" id="main">

      <?php if ($v === '1' && $slides): ?>
        <section class="hi-feature" aria-label="Featured work" data-slides>
          <div class="hi-feature__frame">
            <?php foreach ($slides as $i => $s): ?>
              <a class="hi-feature__slide<?= $i === 0 ? ' is-on' : '' ?>" href="<?= $s['page']->url() ?>" data-slide tabindex="<?= $i === 0 ? '0' : '-1' ?>">
                <?php snippet('picture', ['file' => $s['img'], 'widths' => [960, 1440, 1920, 2560], 'sizes' => '(min-width: 1440px) 85rem, 100vw', 'class' => 'hi-feature__img', 'loading' => $i === 0 ? 'eager' : 'lazy']) ?>
              </a>
            <?php endforeach ?>
            <?php if ($news): $nimg = $news->content()->get('image')->toFile(); ?>
              <a class="hi-news" href="<?= $news->url() ?>">
                <span class="hi-news__meta"><?= $news->date()->toDate('F Y') ?> · <?= html(wove_post_pill($news)) ?></span>
                <span class="hi-news__body">
                  <?php if ($nimg): ?><img class="hi-news__thumb" src="<?= $nimg->crop(112, 112)->url() ?>" alt="" width="56" height="56"><?php endif ?>
                  <span class="hi-news__title"><?= $news->title()->html() ?></span>
                </span>
              </a>
            <?php endif ?>
          </div>
          <div class="hi-feature__foot">
            <?php foreach ($slides as $i => $s): ?>
              <p class="hi-feature__caption<?= $i === 0 ? ' is-on' : '' ?>" data-slide-caption><strong><?= html($s['name']) ?></strong> <?php if ($s['line']): ?><span><?= html($s['line']) ?></span><?php endif ?></p>
            <?php endforeach ?>
            <?php if (count($slides) > 1): ?>
              <div class="hi-dots" role="group" aria-label="Featured work">
                <?php foreach ($slides as $i => $s): ?>
                  <button type="button" class="hi-dot" data-dot aria-label="<?= html($s['name']) ?>" aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>"></button>
                <?php endforeach ?>
              </div>
            <?php endif ?>
          </div>
        </section>
      <?php endif ?>

      <?php if ($v === '3' && $now): ?>
        <section class="hi-now" aria-labelledby="hi-now">
          <h2 class="hi-kicker" id="hi-now">Right now · <?= html($month) ?></h2>
          <ul class="hi-now__list" role="list">
            <?php foreach ($now as $s => $p): $img = $p->content()->get('image')->toFile(); ?>
              <li class="hi-team">
                <a class="hi-team__media" href="<?= $p->url() ?>" tabindex="-1" aria-hidden="true">
                  <?php if ($img): ?>
                    <?php snippet('picture', ['file' => $img, 'widths' => [320, 480, 640, 960], 'sizes' => '(min-width: 961px) 25vw, (min-width: 601px) 50vw, 100vw', 'ratio' => 3 / 4, 'class' => 'hi-team__img']) ?>
                  <?php endif ?>
                  <span class="hi-team__tag"><span>Wove</span> <?= html($services[$s]) ?></span>
                </a>
                <a class="hi-team__title" href="<?= $p->url() ?>"><?= $p->title()->html() ?></a>
                <a class="hi-team__visit" href="<?= url('services/' . $s) ?>">Visit Wove <?= html($services[$s]) ?> <span aria-hidden="true">&rarr;</span></a>
              </li>
            <?php endforeach ?>
          </ul>
        </section>
      <?php endif ?>

      <?php if ($v === '1'): ?><div class="hi-services"><?php snippet('home/intro') ?></div><?php endif ?>

      <section class="hi-cred" aria-labelledby="hi-cred">
        <div class="hi-cred__text">
          <h2 class="hi-cred__title" id="hi-cred">Better for people, better for planet</h2>
          <p class="hi-cred__lead">For over 20 years, we’ve used human-centred design to help public institutions, cultural organisations and mission-led companies turn ambition into action.</p>
        </div>
        <?php if ($logos): ?>
          <ul class="hi-cred__logos" role="list">
            <?php foreach ($logos as $c): $logo = $c['page']->logo()->toFile(); ?>
              <li>
                <a class="hi-cred__logo" href="<?= $c['page']->url() ?>">
                  <?php if ($logo): ?><img src="<?= $logo->url() ?>" alt="<?= html($c['name']) ?>"><?php else: ?><span><?= html($c['name']) ?></span><?php endif ?>
                </a>
              </li>
            <?php endforeach ?>
          </ul>
        <?php endif ?>
      </section>

      <section class="hi-recent" aria-labelledby="hi-recent" data-recent-feed>
        <div class="hi-recent__head">
          <h2 class="hi-recent__title" id="hi-recent">Recent activity</h2>
          <div class="feed-types" role="group" aria-label="Filter by service">
            <button type="button" class="feed-type" data-filter="*" aria-pressed="true">All</button>
            <?php foreach ($order as $s): ?>
              <button type="button" class="feed-type" data-filter="service:<?= $s ?>" aria-pressed="false"><?= html($services[$s]) ?></button>
            <?php endforeach ?>
          </div>
        </div>
        <div class="feed-cards pc-grid pc-grid--cols-2 pc-grid--grid pc-grid--heights-set pc-grid--card-compact pc-grid--card-open" data-heights>
          <?php foreach ($posts->limit(18) as $i => $p): ?>
            <?php snippet('home/post-card', ['post' => $p, 'filters' => $entryFilters[$p->id()] ?? [], 'hidden' => false]) ?>
          <?php endforeach ?>
          <p class="feed-empty" data-recent-empty hidden>Nothing from this team yet.</p>
        </div>
        <a class="feed-more__btn hi-recent__all" href="<?= url('work') ?>">Explore all our work <span aria-hidden="true">&rarr;</span></a>
      </section>

      <?php if ($v === '2'): ?><div class="hi-services"><?php snippet('home/intro') ?></div><?php endif ?>

      <section class="hi-news-signup" aria-labelledby="hi-letter">
        <div>
          <h2 class="hi-letter__title" id="hi-letter">The Wove newsletter</h2>
          <p class="hi-letter__text">What’s popping up in our research, plus updates from our projects. Every month or so.</p>
        </div>
        <?php $signup = $site->newsletterUrl()->value(); ?>
        <form class="hi-letter__form" <?= $signup ? 'action="' . html($signup) . '" method="post" target="_blank"' : 'action="mailto:' . html($site->email()->or('hello@wove.group')->value()) . '?subject=Newsletter%20sign-up" method="get"' ?>>
          <label class="visually-hidden" for="hi-email">Your email</label>
          <input class="hi-letter__input" id="hi-email" type="email" name="<?= $signup ? 'EMAIL' : 'body' ?>" placeholder="Your email" required autocomplete="email">
          <button class="hi-letter__btn" type="submit">Sign up</button>
        </form>
      </section>

    </main>
  </div>
</div>

<aside class="feed-options hi-switch" aria-label="Homepage iterations">
  <p class="feed-options__title">Homepage</p>
  <div class="feed-options__group">
    <a href="<?= url() ?>">Current</a>
    <?php foreach (['1' => 'A Featured', '2' => 'B Full image', '3' => 'C Right now'] as $k => $label): ?>
      <a href="<?= url() ?>/?home=<?= $k ?>"<?= $v === $k ? ' aria-current="true"' : '' ?>><?= $label ?></a>
    <?php endforeach ?>
  </div>
</aside>

<script src="<?= wove_asset('/assets/js/home-iterations.js') ?>" defer></script>
<?php snippet('footer') ?>
