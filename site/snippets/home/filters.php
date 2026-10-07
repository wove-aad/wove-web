<?php
/**
 * Homepage filters: client carousel (one card per case study), topic pills,
 * and a case study panel per client, shown when that client is selected.
 * One filter is active at a time; assets/js/home.js handles selection.
 * Context panels for services and tags (and, on Our work, sectors and
 * people) come from feed/panels.
 * Usage: <?php snippet('home/filters', ['clients' => $clients, 'topics' => $topics,
 *        'sectors' => [...], 'authors' => [...]]) ?>
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
              <?php snippet('picture', [
                'file'   => $image,
                'widths' => [320, 480, 640, 960],
                'ratio'  => 5 / 4,
                'sizes'  => '(min-width: 961px) 20vw, (min-width: 601px) 40vw, 70vw',
                'class'  => 'client-card__img',
              ]) ?>
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

  <?php foreach ($clients as $client): ?>
    <?php snippet('home/case-panel', ['cs' => $client['page'], 'hidden' => true]) ?>
  <?php endforeach ?>

  <?php snippet('feed/panels', ['topics' => $topics, 'sectors' => $sectors ?? [], 'authors' => $authors ?? []]) ?>

</div>
