<?php
/**
 * Case study card for Our work: one column like the post cards, a generous
 * image with the client logo, then the title, summary and tags.
 * Usage: <?php snippet('home/case-card', ['cs' => $page, 'filters' => [...]]) ?>
 */

$image = $cs->caseStudyImages()->toFile();
$logo  = $cs->logo()->toFile();
$name  = $cs->eyebrow()->or($cs->title())->value();
$title = $cs->heroTitle()->isNotEmpty() ? strip_tags($cs->heroTitle()->value()) : $cs->title()->value();
$text  = $cs->summary()->or($cs->subStatement())->value();
?>
<article class="pc pc--case" data-filters="<?= html(implode(' ', $filters)) ?>">
  <div class="pc__media pc__media--case">
    <?php if ($image): ?>
      <?php snippet('picture', [
        'file'   => $image,
        'widths' => [320, 480, 640, 960],
        'ratio'  => 4 / 3,
        'sizes'  => '(min-width: 960px) 33vw, (min-width: 600px) 50vw, 100vw',
      ]) ?>
    <?php endif ?>
    <span class="pc__logo">
      <?php if ($logo): ?><img src="<?= $logo->url() ?>" alt=""><?php else: ?><?= html($name) ?><?php endif ?>
    </span>
  </div>
  <div class="pc__body">
    <p class="pc__label">Case study · <?= html($name) ?></p>
    <h3 class="pc__title"><a class="pc__link" href="<?= $cs->url() ?>"><?= html($title) ?></a></h3>
    <?php if ($text): ?><p class="pc__excerpt"><?= html($text) ?></p><?php endif ?>
    <?php snippet('card-tags', ['post' => $cs]) ?>
  </div>
</article>
