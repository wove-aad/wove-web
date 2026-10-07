<?php
/**
 * Homepage hero: logo, nav, contact button, then the statement and
 * description as one paragraph, with preview links to Our work and Our people.
 * Usage: <?php snippet('home/hero', ['clients' => $clients, 'people' => $people]) ?>
 * Statement and description come from the Wove Mind page's hero fields.
 */

$statement   = $page->headline()->or('Shaping a better Ireland through strategic design and technology')->value();
$description = $page->intro()->or('We help the people running Ireland’s public services, cultural institutions and mission-led organisations move from strategy to delivery.')->value();
$thumbs      = array_filter(array_map(fn ($c) => $c['page']->caseStudyImages()->toFile(), array_slice($clients, 0, 3)));
$tones       = ['#ed8c7c', '#d5faff', '#e0bdff', '#f7ecd3'];
?>
<header class="hx">
  <div class="hx__inner">
    <?php snippet('brand/header-row') ?>
    <div class="hx-body">
      <h1 class="hx-voice__text"><span class="hx-voice__statement"><?= html(rtrim($statement, '.')) ?>.</span> <?= html($description) ?></h1>
      <div class="hx-voice__links">
        <a class="hx-link" href="<?= url('our-work') ?>">
          <?php if ($thumbs): ?>
            <span class="hx-thumbs" aria-hidden="true">
              <?php foreach ($thumbs as $img): ?>
                <img class="hx-thumb" src="<?= $img->crop(80, 80)->url() ?>" alt="" width="40" height="40">
              <?php endforeach ?>
            </span>
          <?php endif ?>
          <span>See our work <span aria-hidden="true">&rarr;</span></span>
        </a>
        <a class="hx-link" href="<?= url('our-people') ?>">
          <?php if ($people): ?>
            <span class="hx-avatars" aria-hidden="true">
              <?php foreach ($people as $i => $person): ?>
                <?php if ($avatar = $person->avatar()): ?>
                  <img class="hx-avatar" src="<?= $avatar->crop(80, 80)->url() ?>" alt="" width="40" height="40">
                <?php else: ?>
                  <span class="hx-avatar" style="--tone: <?= $tones[$i % count($tones)] ?>"><?= html(Str::upper(implode('', array_map(fn ($w) => mb_substr($w, 0, 1), array_slice(preg_split('/\s+/', trim((string) $person->name()->value())), 0, 2))))) ?></span>
                <?php endif ?>
              <?php endforeach ?>
            </span>
          <?php endif ?>
          <span>Meet our people <span aria-hidden="true">&rarr;</span></span>
        </a>
      </div>
    </div>
  </div>
</header>
