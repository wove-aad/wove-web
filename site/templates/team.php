<?php
/**
 * Our People — team page
 * File: site/templates/team.php
 *
 * A grid of team cards (Kirby users with "Show on the Our People page"
 * on), 4 per row on wide screens. Each card shows photo, name and role;
 * clicking it opens a full-width panel below that row with the bio, a
 * LinkedIn link, their latest Wove Mind entries and a link to Our Work
 * filtered by author.
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
  <div class="team-grid" id="team-grid">
    <?php foreach ($members as $member):
      $slug     = wove_author_slug($member);
      $avatar   = $member->avatar();
      $name     = $member->name()->value() ?: $member->email();
      $initials = Str::upper(implode('', array_map(fn ($w) => mb_substr($w, 0, 1), array_slice(preg_split('/\s+/', trim($name)), 0, 2))));
      $role     = $member->content()->get('role');
      $bio      = $member->content()->get('bio');
      $linkedin = $member->content()->get('linkedin');
      $latest   = wove_author_entries($member)->limit($latestLimit);
    ?>
      <article class="team-card" id="<?= html($slug) ?>">
        <button class="team-card__toggle" type="button" aria-expanded="false" aria-controls="<?= html($slug) ?>-details">
          <span class="team-card__photo">
            <?php if ($avatar): ?>
              <img src="<?= $avatar->crop(600, 600)->url() ?>" alt="" loading="lazy">
            <?php else: ?>
              <span class="team-card__initials"><?= html($initials) ?></span>
            <?php endif ?>
          </span>
          <span class="team-card__summary">
            <span class="team-card__name"><?= html($name) ?></span>
            <?php if ($role->isNotEmpty()): ?>
              <span class="team-card__role"><?= $role->html() ?></span>
            <?php endif ?>
          </span>
          <span class="team-card__icon" aria-hidden="true"></span>
        </button>

        <div class="team-card__details" id="<?= html($slug) ?>-details">
          <?php if ($bio->isNotEmpty()): ?>
            <p class="team-card__bio"><?= $bio->html() ?></p>
          <?php endif ?>
          <?php if ($linkedin->isNotEmpty()): ?>
            <a class="team-card__linkedin" href="<?= $linkedin->html() ?>" target="_blank" rel="noopener">LinkedIn &nearr;</a>
          <?php endif ?>

          <?php if ($latest->count()): ?>
            <div class="team-card__latest">
              <p class="team-card__label">Latest</p>
              <ul class="team-card__articles">
                <?php foreach ($latest as $entry): ?>
                  <li>
                    <a href="<?= $entry->url() ?>"><?= $entry->title()->html() ?></a>
                    <time datetime="<?= $entry->date()->toDate('Y-m-d') ?>"><?= $entry->date()->toDate('j M Y') ?></time>
                  </li>
                <?php endforeach ?>
              </ul>
              <a class="team-card__all" href="<?= url('our-work') . '?filter=' . rawurlencode('author:' . $slug) ?>">
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
/* Team cards — clicking a card opens its details in a full-width panel
   directly below that card's row; one open at a time. Details stay
   visible inline if JS doesn't run. /our-people#{slug} opens that card. */
(function () {
  var grid  = document.getElementById('team-grid');
  var cards = grid ? Array.prototype.slice.call(grid.querySelectorAll('.team-card')) : [];
  if (!cards.length) return;

  var panel = document.createElement('div');
  panel.className = 'team-panel';
  panel.hidden = true;
  var openCard = null;

  cards.forEach(function (card) {
    card.querySelector('.team-card__details').hidden = true;
    card.classList.add('is-enhanced');
  });

  // Last card on the same visual row as the given card
  function rowEnd(card) {
    var top = card.offsetTop, last = card;
    cards.forEach(function (c) { if (c.offsetTop === top) last = c; });
    return last;
  }

  function place() {
    if (!openCard) return;
    rowEnd(openCard).after(panel);
  }

  function close() {
    if (!openCard) return;
    var details = panel.firstElementChild;
    if (details) { details.hidden = true; openCard.appendChild(details); }
    openCard.classList.remove('is-open');
    openCard.querySelector('.team-card__toggle').setAttribute('aria-expanded', 'false');
    panel.hidden = true;
    openCard = null;
  }

  function open(card, scroll) {
    close();
    var details = card.querySelector('.team-card__details');
    panel.appendChild(details);
    details.hidden = false;
    panel.hidden = false;
    card.classList.add('is-open');
    card.querySelector('.team-card__toggle').setAttribute('aria-expanded', 'true');
    openCard = card;
    place();
    if (history.replaceState) history.replaceState(null, '', '#' + card.id);
    if (scroll) {
      var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      panel.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'nearest' });
    }
  }

  cards.forEach(function (card) {
    card.querySelector('.team-card__toggle').addEventListener('click', function () {
      if (openCard === card) {
        close();
        if (history.replaceState) history.replaceState(null, '', window.location.pathname + window.location.search);
      } else {
        open(card, true);
      }
    });
  });

  // Rows change with the viewport, so re-seat the panel after resizing
  var resizeTimer;
  window.addEventListener('resize', function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function () {
      if (!openCard) return;
      panel.remove();
      place();
    }, 100);
  });

  var target = window.location.hash && document.getElementById(decodeURIComponent(window.location.hash.slice(1)));
  if (target && target.classList.contains('team-card')) open(target, true);
})();
</script>

<script>
(function() {
  var h = new Date().getHours();
  if (h >= 7 && h < 19) {
    document.documentElement.setAttribute('data-theme', 'light');
  }
})();
</script>

<?php snippet('footer') ?>
