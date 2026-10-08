<?php
/**
 * Wove Mind post page (Thread / What If / Long Read, and sparks)
 * File: site/templates/wove-mind-entry.php
 *
 * Header: "All work" back link and the format chip, the title, the lead
 * (the "Excerpt / Lead" field, when the post has blocks) and the byline.
 * What Ifs use their pale blue in place of blush, like their cards. Wide
 * images (3:2 and wider) lead under the header; square and portrait ones
 * sit in the text column. Then the body, tags (site-wide order), an author
 * card, the case study this post belongs to, and more posts (the same
 * project first). Includes Schema.org structured data.
 */

$format = $page->format()->value() ?: 'thread';
$formatLabels = [
  'spark'    => 'Spark',
  'thread'   => 'Thread',
  'whatif'   => 'What If',
  'longread' => 'Long Read',
];
$formatLabel = $formatLabels[$format] ?? $format;

$image  = $page->content()->get('image')->toFile();
$author = wove_entry_author($page);
$ratio  = $image && $image->height() ? $image->width() / $image->height() : null;
$wide   = $ratio && $ratio >= 1.4;

$allBlocks = $page->blocks()->toBlocks();
$hasBlocks = $allBlocks->count() > 0;

$wordCount = 0;
if ($hasBlocks) {
  $wordCount += str_word_count(strip_tags((string) $allBlocks));
}
if ($page->body()->isNotEmpty()) {
  $wordCount += str_word_count(strip_tags($page->body()->value()));
}
$readingTime = max(1, (int) ceil($wordCount / 200));

$publishedDate    = $page->date()->toDate('Y-m-d');
$publishedDisplay = $page->date()->toDate('j F Y');
$modifiedDate     = date('Y-m-d', $page->modified());

$description = $page->seoDescription()->isNotEmpty()
  ? $page->seoDescription()->value()
  : ($page->body()->isNotEmpty()
    ? Str::short(strip_tags($page->body()->value()), 160)
    : '');

// Tags in the site-wide order: case study, services, editorial tags, sectors
$workUrl  = wove_work_url();
$filter   = fn ($key) => $workUrl . '?filter=' . urlencode($key);
$services = wove_service_labels();
$sectors  = wove_sector_labels();
$siteTags = $site->tags()->toStructure();
$caseStudy = $page->case_study()->toPages()->first();
$pills = [];
foreach ($page->case_study()->toPages() as $cs) {
  $pills[] = ['label' => $cs->eyebrow()->or($cs->title())->value(), 'url' => $filter('cs:' . $cs->slug())];
}
foreach ($page->services()->split(',') as $s) {
  if (isset($services[$s])) $pills[] = ['label' => $services[$s], 'url' => $filter('service:' . $s)];
}
foreach ($page->tags()->split(',') as $t) {
  $tag = $siteTags->findBy('slug', Str::slug($t)) ?? $siteTags->findBy('name', $t);
  if ($tag && $tag->active()->toBool() !== false) $pills[] = ['label' => $tag->name()->value(), 'url' => $filter('tag:' . $tag->slug())];
}
foreach ($page->sectors()->split(',') as $s) {
  if (isset($sectors[$s])) $pills[] = ['label' => $sectors[$s], 'url' => $filter('sector:' . $s)];
}

// More posts: the same project first, then the newest others
$others = $page->siblings()->listed()->not($page)
  ->filter(fn ($p) => $p->format()->value() !== 'project-highlight')
  ->sortBy('date', 'desc');
$same = $caseStudy ? $others->filter(fn ($p) => $p->case_study()->toPages()->has($caseStudy)) : new \Kirby\Cms\Pages();
$more = $same->merge($others->not($same))->limit(6);
$feedKeys = wove_feed()['keys'];

$avatarHtml = function ($user, $size) {
  $avatar = $user->avatar();
  return '<span class="avatar" style="--tone: #e0bdff" aria-hidden="true">'
    . ($avatar ? '<img src="' . $avatar->crop($size * 2, $size * 2)->url() . '" alt="">' : html(wove_initials($user->name()->value())))
    . '</span>';
};
?>

<?php snippet('header', ['css' => ['/assets/css/home.css', '/assets/css/pages.css'], 'nav' => false]) ?>

<script type="application/ld+json">
<?php
$articleData = [
  '@context' => 'https://schema.org',
  '@type' => 'Article',
  'headline' => $page->title()->value(),
  'datePublished' => $publishedDate,
  'dateModified' => $modifiedDate,
  'articleSection' => $formatLabel,
  'wordCount' => $wordCount,
  'publisher' => [
    '@type' => 'Organization',
    'name' => 'Wove',
    'url' => $site->url(),
  ],
  'mainEntityOfPage' => [
    '@type' => 'WebPage',
    '@id' => $page->url(),
  ],
];
if ($description) {
  $articleData['description'] = $description;
}
if ($image) {
  $articleData['image'] = $image->url();
}
if ($author) {
  $authorData = [
    '@type' => 'Person',
    'name' => $author->name()->value(),
  ];
  if ($page->author_role()->isNotEmpty()) {
    $authorData['jobTitle'] = $page->author_role()->value();
  }
  $articleData['author'] = $authorData;
} else {
  $articleData['author'] = [
    '@type' => 'Organization',
    'name' => 'Wove',
    'url' => $site->url(),
  ];
}
echo json_encode($articleData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
?>
</script>

<script type="application/ld+json">
<?= json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'BreadcrumbList',
  'itemListElement' => [
    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Feed', 'item' => $site->url()],
    ['@type' => 'ListItem', 'position' => 2, 'name' => $formatLabel],
    ['@type' => 'ListItem', 'position' => 3, 'name' => $page->title()->value(), 'item' => $page->url()],
  ],
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>


<div class="home" data-theme="light">

  <header class="hx hx--page<?= $format === 'whatif' ? ' hx--whatif' : '' ?><?= $wide ? ' hx--lead' : '' ?>">
    <div class="hx__inner">
      <?php snippet('brand/header-row') ?>
      <div class="hx-body">
        <div class="ph__top">
          <a class="ph__back" href="<?= $workUrl ?>"><span aria-hidden="true">&larr;</span> All work</a>
          <p class="post-chip post-chip--<?= html($format) ?>"><?= html($formatLabel) ?></p>
        </div>
        <h1 class="ph__title"><?= $page->title()->html() ?></h1>
        <?php if ($hasBlocks && $page->body()->isNotEmpty()): ?>
          <div class="ph__intro"><?= $page->body() ?></div>
        <?php endif ?>
        <div class="byline">
          <?php if ($author): ?>
            <span class="byline__who">
              <a href="<?= url('our-people') ?>#<?= html(wove_author_slug($author)) ?>">
                <?= $avatarHtml($author, 40) ?>
                <span>
                  <span class="byline__name"><?= $author->name()->html() ?></span>
                  <?php if ($page->author_role()->isNotEmpty()): ?><span class="byline__role"><?= $page->author_role()->html() ?></span><?php endif ?>
                </span>
              </a>
            </span>
          <?php endif ?>
          <span class="byline__meta"><time datetime="<?= $publishedDate ?>"><?= $publishedDisplay ?></time> · <?= $readingTime ?> min read</span>
        </div>
      </div>
    </div>
  </header>

  <div class="feed-wrap">
    <?php snippet('brand/menu-bar') ?>

    <?php if ($wide): ?>
      <div class="pg-lead-image">
        <?php snippet('picture', [
          'file'          => $image,
          'widths'        => [640, 960, 1280, 1600, 2000, 2560],
          'sizes'         => '(min-width: 85rem) 79rem, calc(100vw - 2rem)',
          'alt'           => $image->alt()->value(),
          'loading'       => 'eager',
          'fetchpriority' => 'high',
        ]) ?>
      </div>
    <?php endif ?>

    <main class="pg" id="main">
      <article class="pg-section post-body">
        <div class="prose">
          <?php if ($image && !$wide): ?>
            <figure>
              <?php snippet('picture', ['file' => $image, 'widths' => [480, 640, 960, 1280], 'sizes' => '(min-width: 960px) 40rem, 100vw', 'alt' => $image->alt()->value()]) ?>
            </figure>
          <?php endif ?>
          <?php if ($hasBlocks): ?>
            <?php foreach ($allBlocks as $block): ?>
              <?= $block ?>
            <?php endforeach ?>
          <?php else: ?>
            <?= $page->body() ?>
          <?php endif ?>
        </div>

        <?php if ($pills): ?>
          <nav class="post-tags" aria-label="Tags">
            <?php foreach ($pills as $pill): ?>
              <a href="<?= $pill['url'] ?>"><?= html($pill['label']) ?></a>
            <?php endforeach ?>
          </nav>
        <?php endif ?>

        <?php if ($author):
          $bio   = $page->author_bio()->or($author->content()->get('bio'))->value();
          $count = wove_author_entries($author)->count();
          $first = explode(' ', trim($author->name()->value()))[0];
          $slug  = wove_author_slug($author);
        ?>
          <aside class="author-card" aria-label="About the author">
            <?= $avatarHtml($author, 64) ?>
            <div>
              <p class="author-card__label">Written by</p>
              <p class="author-card__name"><?= $author->name()->html() ?></p>
              <?php if ($page->author_role()->isNotEmpty()): ?><p class="author-card__role"><?= $page->author_role()->html() ?></p><?php endif ?>
              <?php if ($bio): ?><p class="author-card__bio"><?= html($bio) ?></p><?php endif ?>
              <p class="author-card__links">
                <a href="<?= url('our-people') ?>#<?= html($slug) ?>">View profile <span aria-hidden="true">&rarr;</span></a>
                <a href="<?= $filter('author:' . $slug) ?>">See all <?= html($first) ?>’s posts (<?= $count ?>) <span aria-hidden="true">&rarr;</span></a>
              </p>
            </div>
          </aside>
        <?php endif ?>
      </article>

      <?php if ($caseStudy): ?>
        <section class="pg-section" aria-labelledby="post-case-title">
          <div class="pg-section__head"><h2 class="pg-section__title" id="post-case-title">From the project</h2></div>
          <?php snippet('home/case-panel', ['cs' => $caseStudy]) ?>
        </section>
      <?php endif ?>

      <?php if ($more->count()): ?>
        <section class="pg-section" aria-labelledby="post-more-title">
          <div class="pg-section__head">
            <h2 class="pg-section__title" id="post-more-title">More from Wove</h2>
            <a class="pg-section__link" href="<?= $workUrl ?>">See all work <span aria-hidden="true">&rarr;</span></a>
          </div>
          <div class="feed-cards pc-grid">
            <?php foreach ($more as $post): ?>
              <?php snippet('home/post-card', ['post' => $post, 'filters' => $feedKeys[$post->id()] ?? [], 'hidden' => false]) ?>
            <?php endforeach ?>
          </div>
        </section>
      <?php endif ?>
    </main>
  </div>
</div>

<script src="/assets/js/pages.js" defer></script>
<?php snippet('footer') ?>
