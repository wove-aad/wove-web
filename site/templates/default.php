<?php
/**
 * Default page: any page without its own template (the sector list, and
 * pages made in the Panel such as Privacy). The new design's page header,
 * then the page's text and blocks. For the sector list, links to each sector.
 * File: site/templates/default.php
 */

$intro    = $page->intro()->or($page->headline()->isNotEmpty() && $page->headline()->value() !== $page->title()->value() ? $page->headline() : '')->value();
$blocks   = $page->blocks()->isNotEmpty() ? $page->blocks()->toBlocks() : null;
$children = $page->children()->listed();
?>
<?php snippet('header', ['css' => ['/assets/css/home.css', '/assets/css/pages.css'], 'nav' => false]) ?>

<div class="home" data-theme="light">

  <header class="hx hx--page">
    <div class="hx__inner">
      <?php snippet('brand/header-row') ?>
      <div class="hx-body">
        <h1 class="ph__title"><?= $page->title()->html() ?></h1>
        <?php if ($intro): ?><p class="ph__intro"><?= html($intro) ?></p><?php endif ?>
      </div>
    </div>
  </header>

  <div class="feed-wrap">
    <?php snippet('brand/menu-bar') ?>

    <main class="pg" id="main">
      <?php if ($blocks || $page->text()->isNotEmpty()): ?>
        <section class="pg-section post-body">
          <div class="prose">
            <?= $blocks ? $blocks : $page->text()->kirbytext() ?>
          </div>
        </section>
      <?php endif ?>

      <?php if ($children->count()): ?>
        <section class="pg-section">
          <ul class="link-cards" role="list">
            <?php foreach ($children as $child): ?>
              <li>
                <a class="link-card" href="<?= $child->url() ?>">
                  <span class="link-card__title"><?= $child->headline()->or($child->title())->html() ?></span>
                  <?php if ($child->intro()->isNotEmpty()): ?><span class="link-card__text"><?= $child->intro()->html() ?></span><?php endif ?>
                  <span class="link-card__more" aria-hidden="true">&rarr;</span>
                </a>
              </li>
            <?php endforeach ?>
          </ul>
        </section>
      <?php endif ?>
    </main>
  </div>
</div>

<script src="/assets/js/pages.js" defer></script>
<?php snippet('footer') ?>
