<?php
/**
 * Wove Mind — single post template
 * File: site/templates/wove-mind-entry.php
 * Renamed from wovemind-post.php: Kirby resolves a page's template from
 * its content filename (wove-mind-entry.txt, per the "template:
 * wove-mind-entry" set on the pages section in
 * site/blueprints/sections/wove-mind-entries.yml) unless a Template:
 * field or page model overrides it — neither existed, so every entry
 * was silently falling back to default.php. This name is what makes the
 * template actually load.
 * Used by: Thread, What if, Long read
 * Sparks and Project highlights are not linked to a single page view.
 * Figma: Wove Mind post frame (379:5120)
 *
 * Content model note: `body` (writer) is treated as the short hero dek
 * shown under the title when the entry also has `blocks` content below.
 * If an entry has no `blocks` at all (older/simpler entries), `body` is
 * rendered as the full article instead — so nothing written into `body`
 * is ever silently dropped. Worth confirming with Grace which field
 * editors are actually expected to fill in going forward.
 */

$format = $page->format()->value();
$formatLabels = [
  'thread'   => 'Thread',
  'whatif'   => 'What if',
  'longread' => 'Long read',
];

// $page->image() hits Kirby's built-in HasFiles::image() (first uploaded
// file on the page), not the "Featured image" content field — same
// collision fixed below in wovemind-card.php / wovemind-related-card.php /
// wovemind-highlight-card.php. content()->get() reaches the real field.
$image      = $page->content()->get('image')->toFile();
$showAuthor = $page->show_author()->isTrue();
$author     = $showAuthor ? $page->author()->toUser() : null;
$authorAvatar = $author ? $author->avatar() : null;
$tags       = array_filter($page->tags()->split(','));

$allBlocks       = $page->blocks()->toBlocks();
$hasBlocks       = $allBlocks->count() > 0;
$narrativeBlocks = [];
$wideBlocks      = []; // image + gallery — rendered full width, below
foreach ($allBlocks as $block) {
  if (in_array($block->type(), ['image', 'gallery'])) {
    $wideBlocks[] = $block;
  } else {
    $narrativeBlocks[] = $block;
  }
}

// Only thread/whatif/longread siblings have a single page worth linking to.
$pagerFormats  = ['thread', 'whatif', 'longread'];
$pagerSiblings = $page->parent()->children()->listed()
  ->filter(fn ($p) => in_array($p->format()->value(), $pagerFormats));
$pagerIndex = $pagerSiblings->indexOf($page);
$prev = $pagerIndex > 0 ? $pagerSiblings->nth($pagerIndex - 1) : null;
$next = $pagerSiblings->nth($pagerIndex + 1);
?>

<?php snippet('header') ?>

<main class="wovemind-post wovemind-post--<?= $format ?>">

  <!-- HERO -->
  <header class="case-study-hero">
    <div class="container container--wide">
      <nav class="wovemind-post__breadcrumbs" aria-label="Breadcrumb">
        <ol>
          <li><a href="/wove-mind">Wove Mind</a></li>
          <li aria-current="page"><?= $formatLabels[$format] ?? $format ?></li>
        </ol>
      </nav>

      <?php if ($page->title()->isNotEmpty()): ?>
        <h1 class="wovemind-post__title"><?= $page->title()->html() ?></h1>
      <?php endif ?>

      <?php if ($hasBlocks && $page->body()->isNotEmpty()): ?>
        <div class="wovemind-post__lead"><?= $page->body() ?></div>
      <?php endif ?>

      <div class="wovemind-post__meta">
        <?php if ($author): ?>
          <div class="team-member">
            <?php if ($authorAvatar): ?>
              <img src="<?= $authorAvatar->url() ?>" alt="" class="team-member__avatar" width="48" height="48" loading="lazy">
            <?php endif ?>
            <span class="team-member__name"><?= $author->name()->html() ?></span>
          </div>
        <?php endif ?>
        <time class="wovemind-post__date" datetime="<?= $page->date('Y-m-d') ?>">
          <?= $page->date('j M Y') ?>
        </time>
      </div>
    </div>
  </header>


  <!-- FEATURED IMAGE — dedicated field, left-aligned with the body column -->
  <?php if ($image): ?>
    <section class="section--tight container container--wide" aria-label="Featured image">
      <figure class="wovemind-post-featured">
        <img src="<?= $image->url() ?>" alt="<?= $image->alt()->html() ?>" loading="eager" fetchpriority="high">
      </figure>
    </section>
  <?php endif ?>


  <!-- BODY — centred, snapped to the content-width token -->
  <section class="section container container--wide" aria-label="Article">
    <div class="wovemind-post-blocks">
      <?php if ($hasBlocks): ?>
        <?php foreach ($narrativeBlocks as $block): ?>
          <?= $block ?>
        <?php endforeach ?>
      <?php else: ?>
        <?= $page->body() ?>
      <?php endif ?>
    </div>
  </section>


  <!-- IMAGE / GALLERY BLOCKS — full width, responsive height per breakpoint -->
  <?php foreach ($wideBlocks as $block): ?>
    <section class="section--tight container container--wide" aria-label="<?= $block->type() === 'gallery' ? 'Gallery' : 'Image' ?>">
      <div class="wovemind-post-media wovemind-post-media--<?= $block->type() ?><?= $block->type() === 'gallery' ? ' case-study-gallery' : '' ?>">
        <?= $block ?>
      </div>
    </section>
  <?php endforeach ?>


  <?php if ($tags): ?>
    <section class="section--tight container container--wide" aria-label="Tags">
      <ul class="case-study-tags" role="list">
        <?php foreach ($tags as $tag): ?>
          <li class="tag"><?= html($tag) ?></li>
        <?php endforeach ?>
      </ul>
    </section>
  <?php endif ?>


  <!-- PREV / NEXT -->
  <?php if ($prev || $next): ?>
    <nav class="pager container container--wide" aria-label="Wove Mind entries">
      <?php if ($prev): ?>
        <a href="<?= $prev->url() ?>" class="pager__link pager__link--prev" rel="prev">
          <span class="pager__dir"><span aria-hidden="true">&larr;</span> Previous article</span>
          <span class="pager__name"><?= $prev->title()->html() ?></span>
        </a>
      <?php else: ?>
        <span></span>
      <?php endif ?>
      <?php if ($next): ?>
        <a href="<?= $next->url() ?>" class="pager__link pager__link--next" rel="next">
          <span class="pager__dir">Next article <span aria-hidden="true">&rarr;</span></span>
          <span class="pager__name"><?= $next->title()->html() ?></span>
        </a>
      <?php endif ?>
    </nav>
  <?php endif ?>


  <!-- CONTACT BANNER -->
  <section class="contact" aria-labelledby="contact-heading">
    <div class="contact__inner">
      <div class="contact__avatar">
        <img src="/assets/team/scott.jpg" alt="Scott Burnett, Wove" width="200" height="200" loading="lazy">
      </div>
      <div class="contact__body">
        <p class="contact__text" id="contact-heading">Have a project in mind? Curious about ways the team could bring value to your organisation?</p>
        <a href="/contact" class="btn btn--primary btn--md">Talk to Scott today <span aria-hidden="true">&rarr;</span></a>
      </div>
    </div>
  </section>

</main>

<?php snippet('service-page-scripts') ?>
<?php snippet('footer') ?>
