<?php
/**
 * Our People
 * File: site/templates/team.php. Route: /our-people
 * (see site/plugins/wove-team/index.php).
 *
 * A grid of team members (Kirby users with "Show on the Our People page"
 * on), 4 per row on wide screens: photo (or initials on a colour), name and
 * role. Choosing one opens a panel under that row with the bio, their
 * latest posts, "See all {name}'s posts" (Our work filtered by author) and
 * LinkedIn; the other cards step back. /our-people#{slug} opens that
 * person. Without JavaScript the details show inside each card.
 * Behaviour: assets/js/pages.js.
 */

$members = wove_team_members();
$intro   = $page->intro()->or('Strategists, designers, researchers and developers, working as one team. Choose a person to read about them and what they have written.')->value();
$tones   = ['#ed8c7c', '#d5faff', '#e0bdff', '#f7ecd3', '#e1eee7', '#ebf0fe'];
?>
<?php snippet('header', ['css' => ['/assets/css/home.css', '/assets/css/pages.css'], 'nav' => false]) ?>

<div class="home" data-theme="light">

  <header class="hx hx--page">
    <div class="hx__inner">
      <?php snippet('brand/header-row', ['current' => 'people']) ?>
      <div class="hx-body">
        <h1 class="ph__title"><?= $page->title()->or('Our people')->html() ?></h1>
        <p class="ph__intro"><?= html($intro) ?></p>
      </div>
    </div>
  </header>

  <div class="feed-wrap">
    <?php snippet('brand/menu-bar') ?>

    <main class="pg" id="main">
      <?php if ($members): ?>
        <section class="pg-section" aria-label="Team">
          <ul class="people-grid" id="people-grid" role="list">
            <?php foreach ($members as $i => $member):
              $slug     = wove_author_slug($member);
              $name     = $member->name()->value() ?: $member->email();
              $first    = explode(' ', trim($name))[0];
              $avatar   = $member->avatar();
              $role     = wove_member_role($member);
              $bio      = $member->content()->get('bio');
              $linkedin = $member->content()->get('linkedin');
              $entries  = wove_author_entries($member);
              $latest   = $entries->filter(fn ($e) => $e->format()->value() !== 'spark')->limit(3);
            ?>
              <li class="person-card" id="<?= html($slug) ?>">
                <button type="button" class="person-card__btn" aria-expanded="false" aria-controls="<?= html($slug) ?>-details" data-person="<?= html($slug) ?>">
                  <span class="person-card__photo" style="--tone: <?= $tones[$i % count($tones)] ?>" aria-hidden="true">
                    <?php if ($avatar): ?>
                      <?php snippet('picture', [
                        'file'   => $avatar,
                        'widths' => [300, 450, 600, 900],
                        'ratio'  => 4 / 5,
                        'sizes'  => '(min-width: 1280px) 20rem, (min-width: 960px) 33vw, 50vw',
                      ]) ?>
                    <?php else: ?>
                      <?= html(wove_initials($name)) ?>
                    <?php endif ?>
                  </span>
                  <span class="person-card__name"><?= html($name) ?></span>
                  <?php if ($role !== ''): ?><span class="person-card__role"><?= html($role) ?></span><?php endif ?>
                </button>

                <div class="person-card__details" id="<?= html($slug) ?>-details" role="region" aria-label="<?= html($name) ?>" tabindex="-1">
                  <div class="person-detail__head">
                    <div>
                      <p class="person-detail__name"><?= html($name) ?></p>
                      <?php if ($role !== ''): ?><p class="person-detail__role"><?= html($role) ?></p><?php endif ?>
                    </div>
                    <button type="button" class="person-detail__close" aria-label="Close">&times;</button>
                  </div>
                  <?php if ($bio->isNotEmpty()): ?>
                    <p class="person-detail__bio"><?= $bio->html() ?></p>
                  <?php else: ?>
                    <div></div>
                  <?php endif ?>
                  <?php if ($latest->count()): ?>
                    <div>
                      <p class="person-detail__label">Latest</p>
                      <div class="person-detail__posts">
                        <?php foreach ($latest as $entry): ?>
                          <a href="<?= $entry->url() ?>"><strong><?= $entry->title()->html() ?></strong><span><?= $entry->date()->toDate('j M Y') ?></span></a>
                        <?php endforeach ?>
                      </div>
                    </div>
                  <?php endif ?>
                  <?php if ($entries->count() || $linkedin->isNotEmpty()): ?>
                    <div class="person-detail__foot">
                      <?php if ($entries->count()): ?>
                        <a class="pg-btn" href="<?= url('our-work') ?>?filter=author:<?= html($slug) ?>">See all <?= html($first) ?>’s posts (<?= $entries->count() ?>) <span aria-hidden="true">&rarr;</span></a>
                      <?php endif ?>
                      <?php if ($linkedin->isNotEmpty()): ?>
                        <a class="pg-btn pg-btn--ghost" href="<?= $linkedin->html() ?>" target="_blank" rel="noopener">LinkedIn <span aria-hidden="true">&nearr;</span></a>
                      <?php endif ?>
                    </div>
                  <?php endif ?>
                </div>
              </li>
            <?php endforeach ?>
          </ul>
        </section>
      <?php endif ?>

      <section class="pg-section" aria-label="Work with us">
        <div class="people-join">
          <div>
            <h2>Want to work with us?</h2>
            <p>We are always glad to hear from people who care about public services, culture and climate.</p>
          </div>
          <a class="pg-btn" href="<?= url('contact') ?>">Get in touch <span aria-hidden="true">&rarr;</span></a>
        </div>
      </section>
    </main>
  </div>
</div>

<script src="/assets/js/pages.js" defer></script>
<?php snippet('footer') ?>
