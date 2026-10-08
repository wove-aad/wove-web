<?php
/**
 * Homepage hero: logo and contact button, then the statement as the page
 * title, with preview links to Our work and Our people. The description
 * is in home/intro, under the hero.
 * Usage: <?php snippet('home/hero', ['clients' => $clients, 'people' => $people]) ?>
 * Statement and description come from the Wove Mind page's hero fields.
 *
 * Blue hero options, for review: ?hero=a, b or c gives a full-height band
 * in the brand blue with the white squiggle in the space below the text,
 * fading into the feed's grey as you scroll (assets/js/home.js).
 *   a: the squiggle draws once on load
 *   b: the squiggle draws as you scroll
 *   c: the squiggle draws in and out on a slow loop
 * ?hero=3d keeps the blush hero and puts the squiggle beside the title as
 * a blue 3D tube that turns a little back and forth
 * (assets/js/squiggle3d.js).
 * Without ?hero= the blush hero shows as before.
 */

$statement   = $page->headline()->or('Human Centred Transformation')->value();
$thumbs      = array_filter(array_map(fn ($c) => $c['page']->caseStudyImages()->toFile(), array_slice($clients, 0, 3)));
$tones       = ['#ed8c7c', '#d5faff', '#e0bdff', '#f7ecd3'];
$variant     = in_array(get('hero'), ['a', 'b', 'c'], true) ? get('hero') : null;
$shape       = get('hero') === '3d';
?>
<header class="hx hx--home<?= $variant ? ' hx--blue hx--blue-' . $variant : '' ?>"<?= $variant ? ' data-hero="' . $variant . '"' : '' ?>>
  <div class="hx__inner">
    <?php snippet('brand/header-row', ['nav' => false]) ?>
    <div class="hx-body">
      <?php if ($shape): ?>
        <div class="hx-title-row">
          <h1 class="hx-voice__title"><?= html($statement) ?></h1>
          <canvas class="hx-shape" data-squiggle3d aria-hidden="true"></canvas>
        </div>
      <?php else: ?>
        <h1 class="hx-voice__title"><?= html($statement) ?></h1>
      <?php endif ?>
      <div class="hx-voice__links">
        <a class="hx-link" href="<?= wove_work_url() ?>" data-jump-filter="*">
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
    <?php if ($variant): ?>
      <div class="hx-squiggle" aria-hidden="true">
        <svg viewBox="0 0 1200 400" preserveAspectRatio="xMidYMid meet" data-narrow="xMidYMid slice" focusable="false">
          <path pathLength="1" d="M -700 360 C -300 420, 200 400, 470 250 C 640 150, 700 30, 610 20 C 500 10, 470 200, 600 290 C 760 400, 1050 330, 1250 170 C 1450 10, 1700 60, 1950 140"/>
        </svg>
      </div>
    <?php endif ?>
  </div>
</header>
<?php if ($shape): ?><script src="/assets/js/squiggle3d.js" defer></script><?php endif ?>
