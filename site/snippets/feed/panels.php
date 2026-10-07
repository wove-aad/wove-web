<?php
/**
 * Context panels for the feed filters. One shows above the cards when its
 * filter is selected (assets/js/home.js matches data-panel), in the same
 * place as the case study panel for a client:
 * - services: the service page's featured image and intro, and a link to
 *   get in touch;
 * - editorial tags: the tag's intro from the site's tag list;
 * - sectors: the sector page's intro;
 * - people: who wrote the posts, with a link to their profile.
 * Usage: <?php snippet('feed/panels', ['topics' => $topics, 'sectors' => [...], 'authors' => [...]]) ?>
 */

$siteTags = $site->tags()->toStructure();
$sectors  = $sectors ?? [];
$authors  = $authors ?? [];
$tones    = ['#ed8c7c', '#d5faff', '#e0bdff', '#f7ecd3', '#e1eee7', '#ebf0fe'];
?>
<?php foreach ($topics as $key => $label):
  [$type, $slug] = explode(':', $key, 2);
  $image = null;
  if ($type === 'service') {
    $servicePage = page('services/' . $slug);
    $image       = $servicePage ? $servicePage->content()->get('image')->toFile() : null;
    $eyebrow     = 'Service';
    $text        = wove_service_intro($slug);
  } else {
    $tag     = $siteTags->findBy('slug', $slug);
    $eyebrow = 'Topic';
    $text    = $tag ? $tag->intro()->or($tag->subtitle())->value() : '';
  }
?>
  <section class="topic-panel<?= $image ? ' has-image' : '' ?>" data-panel="<?= html($key) ?>" data-label="<?= html($label) ?>" aria-label="<?= html($eyebrow . ': ' . $label) ?>" hidden>
    <?php if ($image): ?>
      <div class="topic-panel__media">
        <?php snippet('picture', ['file' => $image, 'widths' => [480, 640, 900], 'ratio' => 5 / 4, 'sizes' => '(min-width: 961px) 30vw, 100vw']) ?>
      </div>
    <?php endif ?>
    <div class="topic-panel__body">
      <p class="topic-panel__eyebrow"><?= $eyebrow ?></p>
      <h3 class="topic-panel__title"><?= html($label) ?></h3>
      <?php if ($text): ?><p class="topic-panel__text"><?= html($text) ?></p><?php endif ?>
      <?php if ($type === 'service'): ?>
        <a class="pg-btn" href="<?= url('contact') ?>">Talk to us about <?= html($label) ?> <span aria-hidden="true">&rarr;</span></a>
      <?php endif ?>
    </div>
  </section>
<?php endforeach ?>

<?php foreach ($sectors as $slug => $label):
  $sectorPage = $site->find('sector')?->children()->findBy('sectorkey', $slug);
  $text = $sectorPage ? $sectorPage->intro()->value() : '';
?>
  <section class="topic-panel" data-panel="sector:<?= html($slug) ?>" data-label="<?= html($label) ?>" aria-label="Sector: <?= html($label) ?>" hidden>
    <div class="topic-panel__body">
      <p class="topic-panel__eyebrow">Sector</p>
      <h3 class="topic-panel__title"><?= html($label) ?></h3>
      <?php if ($text): ?><p class="topic-panel__text"><?= html($text) ?></p><?php endif ?>
      <button type="button" class="pg-btn pg-btn--ghost" data-clear>Clear <span aria-hidden="true">&times;</span></button>
    </div>
  </section>
<?php endforeach ?>

<?php $i = 0; foreach ($authors as $slug => $member):
  $name   = $member->name()->value();
  $avatar = $member->avatar();
  $role   = wove_member_role($member);
?>
  <section class="person-panel" data-panel="author:<?= html($slug) ?>" data-label="<?= html($name) ?>" aria-label="Posts by <?= html($name) ?>" hidden>
    <span class="person-panel__avatar" style="--tone: <?= $tones[$i++ % count($tones)] ?>" aria-hidden="true">
      <?php if ($avatar): ?><img src="<?= $avatar->crop(160, 160)->url() ?>" alt=""><?php else: ?><?= html(wove_initials($name)) ?><?php endif ?>
    </span>
    <div class="person-panel__body">
      <p class="person-panel__eyebrow">Posts by</p>
      <h3 class="person-panel__name"><?= html($name) ?></h3>
      <?php if ($role): ?><p class="person-panel__role"><?= html($role) ?></p><?php endif ?>
    </div>
    <div class="person-panel__actions">
      <a class="person-panel__link" href="<?= url('our-people') ?>#<?= html($slug) ?>">View profile <span aria-hidden="true">&rarr;</span></a>
      <button type="button" class="person-panel__clear" data-clear>Clear <span aria-hidden="true">&times;</span></button>
    </div>
  </section>
<?php endforeach ?>
