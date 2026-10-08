<?php
/**
 * Case study page
 * File: site/templates/case-study.php
 * Blueprint: site/blueprints/pages/case-study.yml
 *
 * Header: "All work" back link and "Case study · client", the title,
 * summary, then services and editorial tags (site-wide tag order; the
 * sector is in the facts). The featured image is pulled up into the blush
 * band. Then three figures, the story (blocks) beside a sticky facts column
 * (client, sector, services, year, project team), the testimonial, posts
 * from this project and the next case study.
 */

$name     = $page->eyebrow()->or($page->title())->value();
$title    = $page->heroTitle()->isNotEmpty() ? strip_tags($page->heroTitle()->value()) : $page->title()->value();
$summary  = $page->summary()->or($page->subStatement())->value();
$image    = $page->caseStudyImages()->toFile();
$stats    = $page->stats()->toStructure()->limit(3);
$quote    = $page->testimonial()->toStructure()->first();
$blocks   = $page->blocks()->toBlocks();
$team     = $page->team()->toStructure();
$services = wove_service_labels();
$sectors  = wove_sector_labels();
$siteTags = $site->tags()->toStructure();
$workUrl  = wove_work_url();
$filter   = fn ($key) => $workUrl . '?filter=' . urlencode($key);

// Services, then editorial tags
$pills = [];
foreach ($page->services()->split(',') as $s) {
  if (isset($services[$s])) $pills[] = ['label' => $services[$s], 'url' => $filter('service:' . $s)];
}
foreach ($page->impactAreas()->split(',') as $t) {
  $tag = $siteTags->findBy('slug', $t) ?? $siteTags->findBy('name', $t);
  if ($tag && $tag->active()->toBool() !== false) $pills[] = ['label' => $tag->name()->value(), 'url' => $filter('tag:' . $tag->slug())];
}

// Team members who have a profile link to it
$members = [];
foreach (wove_team_members() as $m) $members[$m->name()->value()] = $m;
$tones = ['#ed8c7c', '#d5faff', '#e0bdff', '#f7ecd3', '#e1eee7', '#ebf0fe'];

$posts = ($wm = $site->find('wove-mind'))
  ? $wm->children()->listed()
      ->filter(fn ($p) => $p->case_study()->toPages()->has($page))
      ->sortBy('date', 'desc')
  : new \Kirby\Cms\Pages();
$feedKeys = wove_feed()['keys'];

$next = $page->nextListed() ?? $page->siblings()->listed()->not($page)->first();
?>
<?php snippet('header', ['css' => ['/assets/css/home.css', '/assets/css/pages.css'], 'nav' => false]) ?>

<div class="home" data-theme="light">

  <header class="hx hx--page<?= $image ? ' hx--lead' : '' ?>">
    <div class="hx__inner">
      <?php snippet('brand/header-row', ['current' => 'work']) ?>
      <div class="hx-body">
        <div class="ph__top">
          <a class="ph__back" href="<?= $workUrl ?>"><span aria-hidden="true">&larr;</span> All work</a>
          <p class="ph__kicker">Case study · <?= html($name) ?></p>
        </div>
        <h1 class="ph__title"><?= html($title) ?></h1>
        <?php if ($summary): ?><p class="ph__intro"><?= html($summary) ?></p><?php endif ?>
        <?php if ($pills): ?>
          <div class="ph__pills">
            <?php foreach ($pills as $pill): ?>
              <a class="ph__pill" href="<?= $pill['url'] ?>"><?= html($pill['label']) ?></a>
            <?php endforeach ?>
          </div>
        <?php endif ?>
      </div>
    </div>
  </header>

  <div class="feed-wrap">
    <?php snippet('brand/menu-bar') ?>

    <?php if ($image): ?>
      <div class="pg-lead-image">
        <?php snippet('picture', [
          'file'          => $image,
          'widths'        => [640, 960, 1280, 1600, 2000, 2560],
          'ratio'         => 21 / 9,
          'sizes'         => '(min-width: 85rem) 79rem, calc(100vw - 2rem)',
          'alt'           => $image->alt()->or($name)->value(),
          'loading'       => 'eager',
          'fetchpriority' => 'high',
        ]) ?>
      </div>
    <?php endif ?>

    <main class="pg" id="main">

      <?php if ($stats->count()): ?>
        <section class="pg-section" aria-label="Key figures">
          <dl class="csp-stats">
            <?php foreach ($stats as $stat): ?>
              <div class="csp-stat"><dt><?= $stat->label()->html() ?></dt><dd><?= $stat->value()->html() ?></dd></div>
            <?php endforeach ?>
          </dl>
        </section>
      <?php endif ?>

      <section class="pg-section cs-main" aria-label="Case study">
        <aside class="cs-aside">
          <dl class="cs-facts">
            <div><dt>Client</dt><dd><?= html($name) ?></dd></div>
            <?php if ($sec = array_filter(array_map(fn ($s) => isset($sectors[$s]) ? '<a href="' . $filter('sector:' . $s) . '">' . html($sectors[$s]) . '</a>' : null, $page->sectors()->split(',')))): ?>
              <div><dt>Sector</dt><dd><?= implode(', ', $sec) ?></dd></div>
            <?php endif ?>
            <?php if ($srv = array_filter(array_map(fn ($s) => isset($services[$s]) ? '<a href="' . $filter('service:' . $s) . '">' . $services[$s] . '</a>' : null, $page->services()->split(',')))): ?>
              <div><dt>Services</dt><dd><?= implode(', ', $srv) ?></dd></div>
            <?php endif ?>
            <?php if ($page->date()->isNotEmpty()): ?>
              <div><dt>Year</dt><dd><?= $page->date()->toDate('Y') ?></dd></div>
            <?php endif ?>
            <?php if ($team->count()): ?>
              <div><dt>Project team</dt>
                <dd class="csp-team">
                  <?php foreach ($team as $i => $member):
                    $memberName = $member->name()->value();
                    $user   = $members[$memberName] ?? null;
                    $avatar = $member->avatar()->toFile() ?? $user?->avatar();
                    $face   = '<span class="avatar" style="--tone: ' . $tones[$i % count($tones)] . '" aria-hidden="true">'
                      . ($avatar ? '<img src="' . $avatar->crop(80, 80)->url() . '" alt="">' : html(wove_initials($memberName))) . '</span>';
                  ?>
                    <?php if ($user): ?>
                      <a href="<?= url('our-people') ?>#<?= html(wove_author_slug($user)) ?>"><?= $face ?><span><?= html($memberName) ?></span></a>
                    <?php else: ?>
                      <span class="csp-team__person"><?= $face ?><span><?= html($memberName) ?></span></span>
                    <?php endif ?>
                  <?php endforeach ?>
                </dd>
              </div>
            <?php endif ?>
          </dl>
        </aside>
        <div class="prose">
          <?php foreach ($blocks as $block): ?>
            <?= $block ?>
          <?php endforeach ?>
        </div>
      </section>

      <?php if ($quote && $quote->quote()->isNotEmpty()): ?>
        <section class="pg-section" aria-label="Testimonial">
          <figure class="cs-quote">
            <blockquote><?= $quote->quote()->html() ?></blockquote>
            <figcaption>
              <strong><?= $quote->name()->html() ?></strong>
              <?= html(implode(', ', array_filter([$quote->role()->value(), $quote->organisation()->value()]))) ?>
              <?php if ($quote->reference_available()->toBool()): ?><br>Available as a reference on request<?php endif ?>
            </figcaption>
          </figure>
        </section>
      <?php endif ?>

      <?php if ($posts->count()): ?>
        <section class="pg-section" aria-labelledby="cs-posts-title">
          <div class="pg-section__head">
            <h2 class="pg-section__title" id="cs-posts-title">In this collection</h2>
            <a class="pg-section__link" href="<?= $filter('cs:' . $page->slug()) ?>">See all <span aria-hidden="true">&rarr;</span></a>
          </div>
          <div class="feed-cards pc-grid">
            <?php foreach ($posts as $post): ?>
              <?php snippet('home/post-card', ['post' => $post, 'filters' => $feedKeys[$post->id()] ?? [], 'hidden' => false]) ?>
            <?php endforeach ?>
          </div>
        </section>
      <?php endif ?>

      <?php if ($next):
        $nextName  = $next->eyebrow()->or($next->title())->value();
        $nextImage = $next->caseStudyImages()->toFile();
        $nextLogo  = $next->logo()->toFile();
      ?>
        <section class="pg-section" aria-label="Next case study">
          <article class="cs-next">
            <div class="cs-next__media">
              <?php if ($nextImage): ?>
                <?php snippet('picture', ['file' => $nextImage, 'widths' => [480, 640, 900], 'ratio' => 5 / 4, 'sizes' => '(min-width: 961px) 40vw, 100vw']) ?>
              <?php endif ?>
              <span class="pc__logo"><?php if ($nextLogo): ?><img src="<?= $nextLogo->url() ?>" alt=""><?php else: ?><?= html($nextName) ?><?php endif ?></span>
            </div>
            <div class="cs-next__body">
              <p class="cs-next__eyebrow">Next case study · <?= html($nextName) ?></p>
              <h2 class="cs-next__title"><?= html($next->heroTitle()->isNotEmpty() ? strip_tags($next->heroTitle()->value()) : $next->title()->value()) ?></h2>
              <a class="pg-btn cs-next__link" href="<?= $next->url() ?>">See case study <span aria-hidden="true">&rarr;</span></a>
            </div>
          </article>
        </section>
      <?php endif ?>

    </main>
  </div>
</div>

<script src="/assets/js/pages.js" defer></script>
<?php snippet('footer') ?>
