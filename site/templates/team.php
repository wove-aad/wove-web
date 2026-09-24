<?php
/**
 * Our People — team page
 * File: site/templates/team.php
 *
 * One profile per team member (Kirby users with "Show on the Our People
 * page" on): avatar, name, role, bio, a LinkedIn link, their latest
 * Wove Mind entries, and a link to Our Work filtered by author.
 *
 * Route: /our-people (see site/plugins/wove-team/index.php)
 */

$members     = wove_team_members();
$latestLimit = 3;
$intro       = $page->intro()->or('The people behind the work.')->value();
?>

<?php snippet('header', ['css' => ['/assets/css/feed.css']]) ?>

<div class="feed-wrap">

  <div class="tag-hero">
    <h1 class="tag-hero__name"><?= $page->title()->html() ?></h1>
    <p class="tag-hero__intro"><?= html($intro) ?></p>
  </div>

  <?php if ($members): ?>
  <div class="team-grid">
    <?php foreach ($members as $member):
      $slug     = wove_author_slug($member);
      $avatar   = $member->avatar();
      $name     = $member->name()->value() ?: $member->email();
      $initials = Str::upper(implode('', array_map(fn ($w) => mb_substr($w, 0, 1), array_slice(preg_split('/\s+/', trim($name)), 0, 2))));
      $entries  = wove_author_entries($member);
      $latest   = $entries->limit($latestLimit);
    ?>
      <article class="team-profile" id="<?= html($slug) ?>">
        <div class="team-profile__photo">
          <?php if ($avatar): ?>
            <img src="<?= $avatar->crop(480, 480)->url() ?>" alt="" loading="lazy">
          <?php else: ?>
            <span class="team-profile__initials"><?= html($initials) ?></span>
          <?php endif ?>
        </div>

        <div class="team-profile__body">
          <h2 class="team-profile__name"><?= html($name) ?></h2>
          <?php if ($member->content()->get('role')->isNotEmpty()): ?>
            <p class="team-profile__role"><?= $member->content()->get('role')->html() ?></p>
          <?php endif ?>
          <?php if ($member->content()->get('bio')->isNotEmpty()): ?>
            <p class="team-profile__bio"><?= $member->content()->get('bio')->html() ?></p>
          <?php endif ?>
          <?php if ($member->content()->get('linkedin')->isNotEmpty()): ?>
            <a class="team-profile__linkedin" href="<?= $member->content()->get('linkedin')->html() ?>" target="_blank" rel="noopener">LinkedIn &nearr;</a>
          <?php endif ?>

          <?php if ($latest->count()): ?>
            <div class="team-profile__latest">
              <p class="team-profile__label">Latest</p>
              <ul class="team-profile__articles">
                <?php foreach ($latest as $entry): ?>
                  <li>
                    <a href="<?= $entry->url() ?>"><?= $entry->title()->html() ?></a>
                    <time datetime="<?= $entry->date()->toDate('Y-m-d') ?>"><?= $entry->date()->toDate('j M Y') ?></time>
                  </li>
                <?php endforeach ?>
              </ul>
              <a class="team-profile__all" href="<?= url('our-work') . '?filter=' . rawurlencode('author:' . $slug) ?>">
                See all <?= html(explode(' ', $name)[0]) ?>'s posts &rarr;
              </a>
            </div>
          <?php endif ?>
        </div>
      </article>
    <?php endforeach ?>
  </div>
  <?php else: ?>
    <p class="our-work-empty">No team members to show yet.</p>
  <?php endif ?>

</div>

<script>
(function() {
  var h = new Date().getHours();
  if (h >= 7 && h < 19) {
    document.documentElement.setAttribute('data-theme', 'light');
  }
})();
</script>

<?php snippet('footer') ?>
