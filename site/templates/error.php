<?php
/**
 * Error page (404), in the new design: what happened, ways back (home,
 * Our work, Our people, contact), and the services and topics as links
 * into Our work.
 * File: site/templates/error.php
 */

$topics = wove_feed()['topics'];
?>
<?php snippet('header', ['css' => ['/assets/css/home.css', '/assets/css/pages.css'], 'nav' => false]) ?>

<div class="home" data-theme="light">

  <header class="hx hx--page">
    <div class="hx__inner">
      <?php snippet('brand/header-row') ?>
      <div class="hx-body">
        <div class="ph__top"><p class="ph__kicker">Error 404</p></div>
        <h1 class="ph__title">We can’t find that page</h1>
        <p class="ph__intro">It may have moved, or the link may be out of date. These will get you back on track.</p>
        <div class="error-links">
          <a class="pg-btn" href="<?= url() ?>">Go to the homepage <span aria-hidden="true">&rarr;</span></a>
          <a class="pg-btn pg-btn--ghost" href="<?= wove_work_url() ?>">Our work</a>
          <a class="pg-btn pg-btn--ghost" href="<?= url('our-people') ?>">Our people</a>
          <a class="pg-btn pg-btn--ghost" href="<?= url('contact') ?>">Get in touch</a>
        </div>
      </div>
    </div>
  </header>

  <div class="feed-wrap">
    <?php snippet('brand/menu-bar') ?>

    <main class="pg" id="main">
      <?php if ($topics): ?>
        <section class="pg-section" aria-labelledby="error-topics-title">
          <div class="pg-section__head"><h2 class="pg-section__title" id="error-topics-title">Browse our work by topic</h2></div>
          <nav class="ph__pills" aria-labelledby="error-topics-title">
            <?php foreach ($topics as $key => $label): ?>
              <a class="ph__pill" href="<?= wove_work_url($key) ?>"><?= html($label) ?></a>
            <?php endforeach ?>
          </nav>
        </section>
      <?php endif ?>
    </main>
  </div>
</div>

<script src="/assets/js/pages.js" defer></script>
<?php snippet('footer') ?>
