<?php
/**
 * "Explore by topic" band, from the Figma frame (873:6039), 2026-10-30:
 * every impact area (wove_impact_areas()), in three rows of large links that drift
 * sideways (alternate rows the other way; paused on hover or focus, still
 * and wrapped with reduced motion). Each topic has a small square with
 * the image of its newest post, or a colour swatch. Links go to the
 * impact area pages (templates/tag.php).
 * Usage: <?php snippet('home/topics') ?> — above the footer on the
 * homepage and at the end of posts.
 * Styles: the EXPLORE BY TOPIC section of assets/css/home.css.
 */

$feed     = wove_feed();
$swatches = ['coral', 'aqua', 'lilac', 'mint', 'white'];

$topics = [];
foreach (wove_impact_areas() as $areaSlug => $label) {
  $key   = 'tag:' . $areaSlug;
  $image = null;
  foreach ($feed['items'] as $p) {
    if (!in_array($key, $feed['keys'][$p->id()] ?? [], true)) continue;
    $image = $p->intendedTemplate()->name() === 'case-study' ? $p->caseStudyImages()->toFile() : $p->content()->get('image')->toFile();
    if ($image) break;
  }
  $topics[] = [
    'label'  => $label,
    'url'    => url('tag/' . substr($key, 4)),
    'image'  => $image,
    'swatch' => $swatches[count($topics) % count($swatches)],
  ];
}
if (!$topics) return;

// Three rows, each the full list starting at a different topic
$n    = count($topics);
$rows = [];
for ($r = 0; $r < 3; $r++) {
  $start  = (int) round($r * $n / 3);
  $rows[] = array_merge(array_slice($topics, $start), array_slice($topics, 0, $start));
}

$item = function (array $t, bool $copy) { ?>
  <li class="tp-item"<?= $copy ? ' aria-hidden="true"' : '' ?>>
    <a class="tp-link" href="<?= $t['url'] ?>"<?= $copy ? ' tabindex="-1"' : '' ?>>
      <span class="tp-swatch" style="--swatch: var(--color-<?= $t['swatch'] ?>)">
        <?php if ($t['image']): ?><img src="<?= $t['image']->crop(88, 88)->url() ?>" alt="" width="44" height="44" loading="lazy"><?php endif ?>
      </span>
      <?= html($t['label']) ?>
    </a>
    <span class="tp-sep" aria-hidden="true">/</span>
  </li>
<?php };
?>
<section class="tp" aria-labelledby="tp-title">
  <h2 class="tp__title" id="tp-title">Explore by topic</h2>
  <div class="tp__rows">
    <?php foreach ($rows as $r => $row): ?>
      <div class="tp-row tp-row--<?= $r + 1 ?>"<?= $r ? ' aria-hidden="true"' : '' ?>>
        <ul class="tp-track" role="list">
          <?php foreach ($row as $t) $item($t, $r > 0) ?>
          <?php foreach ($row as $t) $item($t, true) ?>
          <?php if ($n < 6) foreach ($row as $t) { $item($t, true); } ?>
          <?php if ($n < 6) foreach ($row as $t) { $item($t, true); } ?>
        </ul>
      </div>
    <?php endforeach ?>
  </div>
</section>
