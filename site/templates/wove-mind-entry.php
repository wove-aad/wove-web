<?php
/**
 * Wove Mind — single entry template (Thread / What if / Long read)
 * File: site/templates/wove-mind-entry.php
 *
 * Uses the feed design system (feed.css) to match the case study page.
 * Includes Schema.org structured data for E-E-A-T.
 */

$format = $page->format()->value();
$formatLabels = [
  'thread'   => 'Thread',
  'whatif'   => 'What if',
  'longread' => 'Long read',
];
$formatLabel = $formatLabels[$format] ?? $format;

$image      = $page->content()->get('image')->toFile();
$showAuthor = $page->show_author()->isTrue();
$author     = $showAuthor ? $page->author()->toUser() : null;
$authorAvatar = $author ? $author->avatar() : null;
$tags       = array_filter($page->tags()->split(','));

$allBlocks  = $page->blocks()->toBlocks();
$hasBlocks  = $allBlocks->count() > 0;

$serviceLabels = ['strategy' => 'Strategy', 'labs' => 'Labs', 'digital' => 'Digital', 'brand' => 'Brand'];
$serviceSlugs  = array_filter($page->services()->split(','));
$serviceTags   = array_map(fn ($slug) => $serviceLabels[$slug] ?? ucfirst($slug), $serviceSlugs);

$wordCount = 0;
if ($hasBlocks) {
  $wordCount += str_word_count(strip_tags((string) $allBlocks));
}
if ($page->body()->isNotEmpty()) {
  $wordCount += str_word_count(strip_tags($page->body()->value()));
}
$readingTime = max(1, (int) ceil($wordCount / 200));

$pagerFormats  = ['thread', 'whatif', 'longread'];
$pagerSiblings = $page->parent()->children()->listed()
  ->filter(fn ($p) => in_array($p->format()->value(), $pagerFormats));
$pagerIndex = $pagerSiblings->indexOf($page);
$prev = $pagerIndex > 0 ? $pagerSiblings->nth($pagerIndex - 1) : null;
$next = $pagerSiblings->nth($pagerIndex + 1);

$relatedEntries = $pagerSiblings->not($page)->sortBy('date', 'desc')->limit(4);

$publishedDate   = $page->date()->toDate('Y-m-d');
$publishedDisplay = $page->date()->toDate('j M Y');
$modifiedDate    = date('Y-m-d', $page->modified());

$description = $page->seoDescription()->isNotEmpty()
  ? $page->seoDescription()->value()
  : ($page->body()->isNotEmpty()
    ? Str::short(strip_tags($page->body()->value()), 160)
    : '');
?>

<?php snippet('header', ['css' => ['/assets/css/feed.css']]) ?>

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

<div class="feed-wrap">

  <nav class="cs-breadcrumb" aria-label="Breadcrumb">
    <a href="/">Feed</a>
    <span class="cs-breadcrumb__sep">/</span>
    <span><?= html($formatLabel) ?></span>
  </nav>

  <header class="cs-hero">
    <div class="entry-format entry-format--<?= $format ?>"><?= html($formatLabel) ?></div>

    <?php if ($page->title()->isNotEmpty()): ?>
      <h1 class="cs-hero__title"><?= $page->title()->html() ?></h1>
    <?php endif ?>

    <?php if ($hasBlocks && $page->body()->isNotEmpty()): ?>
      <div class="entry-lead"><?= $page->body() ?></div>
    <?php endif ?>

    <div class="entry-byline">
      <?php if ($author): ?>
        <div class="entry-byline__author">
          <?php if ($authorAvatar): ?>
            <img src="<?= $authorAvatar->url() ?>" alt="" class="entry-byline__avatar" width="40" height="40" loading="lazy">
          <?php endif ?>
          <div class="entry-byline__info">
            <span class="entry-byline__name"><?= $author->name()->html() ?></span>
            <?php if ($page->author_role()->isNotEmpty()): ?>
              <span class="entry-byline__role"><?= $page->author_role()->html() ?></span>
            <?php endif ?>
          </div>
        </div>
      <?php endif ?>
      <div class="entry-byline__meta">
        <time datetime="<?= $publishedDate ?>"><?= $publishedDisplay ?></time>
        <span class="entry-byline__dot">&middot;</span>
        <span><?= $readingTime ?> min read</span>
      </div>
    </div>
  </header>

  <?php if ($image): ?>
    <div class="cs-hero-image">
      <div class="cs-hero-image__inner">
        <img src="<?= $image->url() ?>" alt="<?= $image->alt()->html() ?>" loading="eager" fetchpriority="high">
      </div>
    </div>
  <?php endif ?>

  <article class="cs-body">
    <div class="cs-body__inner">
      <?php if ($hasBlocks): ?>
        <?php foreach ($allBlocks as $block): ?>
          <?= $block ?>
        <?php endforeach ?>
      <?php else: ?>
        <?= $page->body() ?>
      <?php endif ?>
    </div>
  </article>

  <?php
    $cloudPills = [];
    foreach ($serviceSlugs as $sSlug) {
      $cloudPills[] = ['label' => $serviceLabels[$sSlug] ?? ucfirst($sSlug), 'url' => '/our-work?filter=service:' . $sSlug];
    }
    $sectorLabels = [
      'arts-and-culture'  => 'Arts and Culture',
      'public-service'    => 'Public Service',
      'higher-education'  => 'Higher Education',
      'non-profit'        => 'Non-profit and Mission-led',
      'founders-ventures' => 'Founders and Ventures',
    ];
    foreach ($page->sectors()->split(',') as $sec) {
      $sec = trim($sec);
      if ($sec && isset($sectorLabels[$sec])) {
        $cloudPills[] = ['label' => $sectorLabels[$sec], 'url' => '/our-work?filter=sector:' . $sec];
      }
    }
    $tagStructureCloud = $site->tags()->toStructure();
    foreach ($tags as $t) {
      $tagData = $tagStructureCloud->findBy('slug', Str::slug($t)) ?: $tagStructureCloud->findBy('name', $t);
      $tagSlug = $tagData ? $tagData->slug()->value() : Str::slug($t);
      $tagLabel = $tagData ? $tagData->name()->value() : $t;
      $cloudPills[] = ['label' => $tagLabel, 'url' => '/our-work?filter=tag:' . $tagSlug];
    }
  ?>
  <?php if ($cloudPills): ?>
    <div class="entry-tags">
      <nav class="tag-cloud" aria-label="Tags">
        <?php foreach ($cloudPills as $pill): ?>
          <a href="<?= $pill['url'] ?>" class="tag-pill"><?= html($pill['label']) ?></a>
        <?php endforeach ?>
      </nav>
    </div>
  <?php endif ?>

  <?php if ($author): ?>
    <div class="entry-bio">
      <div class="entry-bio__label">About the author</div>
      <div class="entry-bio__card">
        <?php if ($authorAvatar): ?>
          <img src="<?= $authorAvatar->url() ?>" alt="" class="entry-bio__avatar" width="56" height="56" loading="lazy">
        <?php endif ?>
        <div class="entry-bio__content">
          <div class="entry-bio__name"><?= $author->name()->html() ?></div>
          <?php if ($page->author_role()->isNotEmpty()): ?>
            <div class="entry-bio__role"><?= $page->author_role()->html() ?></div>
          <?php endif ?>
          <?php if ($page->author_bio()->isNotEmpty()): ?>
            <p class="entry-bio__text"><?= $page->author_bio()->html() ?></p>
          <?php endif ?>
        </div>
      </div>
    </div>
  <?php endif ?>

  <?php if ($prev || $next): ?>
    <nav class="entry-pager" aria-label="More articles">
      <?php if ($prev): ?>
        <a href="<?= $prev->url() ?>" class="entry-pager__link entry-pager__link--prev" rel="prev">
          <span class="entry-pager__dir">&larr; Previous</span>
          <span class="entry-pager__name"><?= $prev->title()->html() ?></span>
        </a>
      <?php else: ?>
        <span></span>
      <?php endif ?>
      <?php if ($next): ?>
        <a href="<?= $next->url() ?>" class="entry-pager__link entry-pager__link--next" rel="next">
          <span class="entry-pager__dir">Next &rarr;</span>
          <span class="entry-pager__name"><?= $next->title()->html() ?></span>
        </a>
      <?php endif ?>
    </nav>
  <?php endif ?>

  <?php if ($relatedEntries->count()): ?>
    <div class="cs-related">
      <div class="cs-related__header">
        <div>
          <div class="cs-related__label">Keep reading</div>
          <h2 class="cs-related__title">More from Wove Mind</h2>
        </div>
        <a href="/" class="cs-related__link">View all &rarr;</a>
      </div>
      <div class="feed-cards">
        <?php foreach ($relatedEntries as $entry): ?>
          <?php snippet('feed-card', ['post' => $entry]) ?>
        <?php endforeach ?>
      </div>
    </div>
  <?php endif ?>

</div>

<script>
(function() {
  var h = new Date().getHours();
  if (h >= 7 && h < 19) {
    document.documentElement.setAttribute('data-theme', 'light');
  }
})();
</script>

<?php snippet('footer') ?>
