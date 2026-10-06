<?php
/**
 * Homepage filters: client carousel (one card per case study), topic pills,
 * and a case study panel per client, shown when that client is selected.
 * One filter is active at a time; assets/js/home.js handles selection.
 * Usage: <?php snippet('home/filters', ['clients' => $clients, 'topics' => $topics]) ?>
 */

$entryWord = fn ($n) => $n . ' entr' . ($n === 1 ? 'y' : 'ies');
?>
<div class="filters" id="filters">

  <section class="client-carousel" aria-label="Filter by client">
    <ul class="client-carousel__track" role="list">
      <li class="client-card client-card--all">
        <button type="button" class="client-card__btn" data-filter="*" aria-pressed="true">
          <span class="client-card__logo">All work</span>
          <span class="client-card__meta"><?= $entryWord($totalCount) ?></span>
        </button>
      </li>
      <?php foreach ($clients as $client):
        $cs    = $client['page'];
        $image = $cs->caseStudyImages()->toFile();
        $logo  = $cs->logo()->toFile();
      ?>
        <li class="client-card">
          <button type="button" class="client-card__btn" data-filter="cs:<?= html($client['slug']) ?>" aria-pressed="false">
            <?php if ($image): ?>
              <img class="client-card__img" src="<?= $image->crop(640, 512)->url() ?>" alt="" loading="lazy">
            <?php endif ?>
            <span class="client-card__logo">
              <?php if ($logo): ?>
                <img src="<?= $logo->url() ?>" alt="<?= html($client['name']) ?>">
              <?php else: ?>
                <?= html($client['name']) ?>
              <?php endif ?>
            </span>
            <span class="client-card__meta"><?= html($client['name']) ?> · <?= $entryWord($client['count']) ?></span>
          </button>
        </li>
      <?php endforeach ?>
    </ul>
    <div class="client-carousel__controls">
      <input type="range" class="client-carousel__slider" min="0" max="1000" value="0" aria-label="Scroll clients">
      <div class="client-carousel__arrows">
        <button type="button" class="client-carousel__arrow" data-step="-1" aria-label="Previous clients">&larr;</button>
        <button type="button" class="client-carousel__arrow" data-step="1" aria-label="Next clients">&rarr;</button>
      </div>
    </div>
  </section>

  <div class="topic-pills topic-pills--ruled" role="group" aria-label="Filter by topic">
    <button type="button" class="tag-pill is-active" data-filter="*" aria-pressed="true">All</button>
    <?php foreach ($topics as $key => $label): ?>
      <button type="button" class="tag-pill" data-filter="<?= html($key) ?>" aria-pressed="false"><?= html($label) ?></button>
    <?php endforeach ?>
  </div>

  <?php foreach ($clients as $client):
    $cs    = $client['page'];
    $image = $cs->caseStudyImages()->toFile();
    $logo  = $cs->logo()->toFile();
    $title = $cs->heroTitle()->isNotEmpty() ? strip_tags($cs->heroTitle()->value()) : $cs->title()->value();
    $text  = $cs->summary()->or($cs->subStatement())->value();
    $stats = $cs->stats()->toStructure()->limit(3);
  ?>
    <section class="case-panel" data-panel="cs:<?= html($client['slug']) ?>" aria-label="Case study: <?= html($client['name']) ?>" hidden>
      <div class="case-panel__media">
        <?php if ($image): ?>
          <img src="<?= $image->crop(900, 720)->url() ?>" alt="" loading="lazy">
        <?php endif ?>
        <span class="case-panel__logo">
          <?php if ($logo): ?>
            <img src="<?= $logo->url() ?>" alt="<?= html($client['name']) ?>">
          <?php else: ?>
            <?= html($client['name']) ?>
          <?php endif ?>
        </span>
      </div>
      <div class="case-panel__body">
        <p class="case-panel__eyebrow">Case study · <?= html($client['name']) ?></p>
        <h3 class="case-panel__title"><?= html($title) ?></h3>
        <?php if ($text): ?>
          <p class="case-panel__text"><?= html($text) ?></p>
        <?php endif ?>
        <?php if ($stats->count()): ?>
          <dl class="case-panel__kpis">
            <?php foreach ($stats as $stat): ?>
              <div class="case-panel__kpi"><dt><?= $stat->label()->html() ?></dt><dd><?= $stat->value()->html() ?></dd></div>
            <?php endforeach ?>
          </dl>
        <?php endif ?>
        <a href="<?= $cs->url() ?>" class="case-panel__cta">See case study <span aria-hidden="true">&rarr;</span></a>
      </div>
    </section>
  <?php endforeach ?>

</div>
