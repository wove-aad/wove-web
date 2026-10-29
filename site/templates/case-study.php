<?php
/**
 * Client page (a case study)
 * File: site/templates/case-study.php
 * Blueprint: site/blueprints/pages/case-study.yml
 *
 * From the Figma "Client page" frame (1267:13489), 2026-10-29:
 * - Intro on a mint band: the hero title in grey with the client name in
 *   black under it, then the summary and a meta row (date, client,
 *   services, impact areas).
 * - The hero image, then "The headlines" (Impact Stats, up to four).
 * - Activity: the client's posts (related to this case study), the pinned
 *   one large beside the next two, then rows of three. All / Projects /
 *   Thinking / News filter them in place (pages.js); the type filters
 *   only show the types this client has.
 * - "Explore more {service} work" goes to the first service's page.
 * The story blocks, facts, team, testimonial and next case study from the
 * previous layout aren't in this design; their fields stay in the Panel.
 * Styles: the CLIENT PAGE section of assets/css/pages.css.
 */

$name     = $page->eyebrow()->or($page->title())->value();
$headline = $page->heroTitle()->isNotEmpty() ? trim(strip_tags($page->heroTitle()->value())) : '';
$headline = $headline === $name ? '' : $headline;
$summary  = $page->summary()->or($page->subStatement())->value();
$image    = $page->caseStudyImages()->toFile();
$stats    = $page->stats()->toStructure()->limit(4);
$services = wove_service_labels();
$siteTags = $site->tags()->toStructure();

// Meta row: date, client, services, impact areas
$meta = [];
if ($page->date()->isNotEmpty()) $meta[] = html($page->date()->toDate('j M Y'));
$meta[] = html($name);
$firstService = null;
foreach ($page->services()->split(',') as $s) {
  if (!isset($services[$s])) continue;
  $firstService ??= $s;
  $meta[] = '<a href="' . url('services/' . $s) . '">' . html($services[$s]) . '</a>';
}
foreach ($page->impactAreas()->split(',') as $t) {
  $tag = $siteTags->findBy('slug', $t) ?? $siteTags->findBy('name', $t);
  if ($tag && $tag->active()->toBool() !== false) $meta[] = '<a href="' . url('tag/' . $tag->slug()) . '">' . $tag->name()->html() . '</a>';
}

// Activity: this client's posts, the pinned one first
$posts = ($wm = $site->find('wove-mind'))
  ? $wm->children()->listed()
      ->filter(fn ($p) => $p->case_study()->toPages()->has($page) && $p->format()->value() !== 'spark')
      ->sortBy('date', 'desc')
  : new \Kirby\Cms\Pages();
$pinned = $page->pinnedPost()->toPage();
$pinned = $pinned && $posts->has($pinned) ? $pinned : $posts->first();
$posts  = $pinned ? array_merge([$pinned], $posts->not($pinned)->values()) : [];

$typeLabels = ['project' => 'Project', 'thinking' => 'Thinking', 'news' => 'News'];
$typeCounts = [];
foreach ($posts as $p) $typeCounts[wove_post_type($p)] = true;
?>
<?php snippet('header', ['css' => ['/assets/css/home.css', '/assets/css/pages.css'], 'nav' => false]) ?>

<div class="home cp" data-theme="light">

  <header class="hx hx--page cp-head">
    <div class="hx__inner">
      <?php snippet('brand/header-row') ?>
      <h1 class="cp-head__title">
        <?php if ($headline): ?><span class="cp-head__question"><?= html($headline) ?></span><?php endif ?>
        <span class="cp-head__name"><?= html($name) ?></span>
      </h1>
      <div class="cp-head__lead">
        <?php if ($summary): ?><p class="cp-head__summary"><?= html($summary) ?></p><?php endif ?>
        <p class="cp-head__meta"><?= implode(' ', array_map(fn ($m) => '<span>' . $m . '</span>', $meta)) ?></p>
      </div>
    </div>
  </header>

  <div class="feed-wrap">
    <?php snippet('brand/menu-bar') ?>

    <main class="pg cp-main" id="main">

      <?php if ($image): ?>
        <div class="cp-hero">
          <?php snippet('picture', [
            'file'          => $image,
            'widths'        => [640, 960, 1280, 1600, 2000, 2560],
            'ratio'         => 11 / 5,
            'sizes'         => '(min-width: 85rem) 79rem, calc(100vw - 2rem)',
            'alt'           => $image->alt()->or($name)->value(),
            'loading'       => 'eager',
            'fetchpriority' => 'high',
            'class'         => 'cp-hero__image',
          ]) ?>
        </div>
      <?php endif ?>

      <?php if ($stats->count()): ?>
        <section class="cp-section" aria-labelledby="cp-headlines">
          <h2 class="cp-heading" id="cp-headlines">The headlines</h2>
          <dl class="cp-stats">
            <?php foreach ($stats as $stat): ?>
              <div class="cp-stat"><dt><?= $stat->value()->html() ?></dt><dd><?= $stat->label()->html() ?></dd></div>
            <?php endforeach ?>
          </dl>
        </section>
      <?php endif ?>

      <?php if ($posts): ?>
        <section class="cp-section cp-activity" aria-labelledby="cp-activity">
          <div class="cp-activity__head">
            <h2 class="cp-activity__title" id="cp-activity">Activity</h2>
            <?php if (count($typeCounts) > 1): ?>
              <div class="feed-types" role="group" aria-label="Filter by type" data-activity-filters>
                <button type="button" class="feed-type" data-type="*" aria-pressed="true">All</button>
                <?php foreach ($typeLabels as $type => $label): if (empty($typeCounts[$type])) continue ?>
                  <button type="button" class="feed-type" data-type="<?= $type ?>" aria-pressed="false"><?= wove_post_type_labels()[$type] ?></button>
                <?php endforeach ?>
              </div>
            <?php endif ?>
          </div>
          <ul class="cp-posts" role="list" data-activity>
            <?php foreach ($posts as $i => $post):
              $type  = wove_post_type($post);
              $postImage = $post->content()->get('image')->toFile();
              $line  = $post->body()->isNotEmpty() ? wove_first_sentence($post->body()->value()) : '';
            ?>
              <li class="cp-post" data-type="<?= $type ?>">
                <a class="cp-post__link" href="<?= $post->url() ?>">
                  <span class="cp-post__media">
                    <?php if ($postImage): ?>
                      <?php snippet('picture', [
                        'file'   => $postImage,
                        'widths' => [480, 720, 960, 1440],
                        'sizes'  => $i === 0 ? '(min-width: 960px) 60vw, 100vw' : '(min-width: 960px) 30vw, 100vw',
                        'class'  => 'cp-post__image',
                      ]) ?>
                    <?php endif ?>
                    <span class="pc__pill"><?= $i === 0 ? 'Pinned · ' : '' ?><?= $typeLabels[$type] ?></span>
                  </span>
                  <span class="cp-post__title"><?= $post->title()->html() ?></span>
                  <?php if ($line): ?><span class="cp-post__line"><?= html($line) ?></span><?php endif ?>
                </a>
              </li>
            <?php endforeach ?>
          </ul>
          <a class="cp-explore" href="<?= $firstService ? url('services/' . $firstService) : wove_work_url() ?>">Explore more <?= $firstService ? html($services[$firstService]) . ' ' : '' ?>work <span aria-hidden="true">&rarr;</span></a>
        </section>
      <?php endif ?>

    </main>
  </div>
</div>

<script src="<?= wove_asset('/assets/js/pages.js') ?>" defer></script>
<?php snippet('footer') ?>
