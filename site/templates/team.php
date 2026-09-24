<?php
/**
 * Our People — team page
 * File: site/templates/team.php
 *
 * A grid of team cards (Kirby users with "Show on the Our People page"
 * on), 4 per row on wide screens. Each card shows photo, name and role;
 * clicking it opens a compact panel below that row, centred under the
 * card, with the bio, their latest Wove Mind entries, a LinkedIn link and
 * a link to Our Work filtered by author. The other cards fade back.
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
      $role     = wove_member_role($member);
      $bio      = $member->content()->get('bio');
      $linkedin = $member->content()->get('linkedin');
      $entries   = wove_author_entries($member);
      $postCount = $entries->count();
      $latest    = $entries->limit($latestLimit);
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
            <?php if ($role !== ''): ?>
              <span class="team-card__role"><?= html($role) ?></span>
            <?php endif ?>
          </span>
          <span class="team-card__icon" aria-hidden="true"></span>
        </button>

        <div class="team-card__details" id="<?= html($slug) ?>-details" aria-label="<?= html($name) ?>">
          <div class="team-card__head">
            <p class="team-card__head-title">
              <span class="team-card__head-name"><?= html($name) ?></span>
              <?php if ($role !== ''): ?><span class="team-card__role"><?= html($role) ?></span><?php endif ?>
            </p>
            <button class="team-card__close" type="button" aria-label="Close">&times;</button>
          </div>

          <div class="team-card__body">
            <?php if ($bio->isNotEmpty()): ?>
              <p class="team-card__bio"><?= $bio->html() ?></p>
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
              </div>
            <?php endif ?>
          </div>

          <?php if ($linkedin->isNotEmpty() || $postCount): ?>
            <div class="team-card__foot">
              <?php if ($linkedin->isNotEmpty()): ?>
                <a class="team-card__linkedin" href="<?= $linkedin->html() ?>" target="_blank" rel="noopener">LinkedIn &nearr;</a>
              <?php endif ?>
              <?php if ($postCount): ?>
                <a class="team-card__all" href="<?= url('our-work') . '?filter=' . rawurlencode('author:' . $slug) ?>">See all <?= html(explode(' ', $name)[0]) ?>'s posts (<?= $postCount ?>) &rarr;</a>
              <?php endif ?>
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
/* Team cards — clicking a card opens its details in a panel below that
   card's row. The panel is centred under the card (kept inside the grid)
   with a notch pointing at it; the other cards fade back. One open at a
   time; Esc or the close button closes it and returns focus to the card.
   Details stay inline in each card if JS doesn't run.
   /our-people#{slug} opens that card on load. */
(function () {
  var grid  = document.getElementById('team-grid');
  var cards = grid ? Array.prototype.slice.call(grid.querySelectorAll('.team-card')) : [];
  if (!cards.length) return;

  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var panel  = document.createElement('div');
  panel.className = 'team-panel';
  panel.setAttribute('role', 'region');
  panel.innerHTML = '<div class="team-panel__clip"><div class="team-panel__box"><span class="team-panel__notch" aria-hidden="true"></span></div></div>';
  var box = panel.querySelector('.team-panel__box'), notch = panel.querySelector('.team-panel__notch');
  var openCard = null, closeTimer;

  cards.forEach(function (card) {
    card.querySelector('.team-card__details').hidden = true;
    card.classList.add('is-enhanced');
  });

  // Last card on the same visual row as the given card
  function rowEnd(card) {
    var last = card;
    cards.forEach(function (c) { if (c.offsetTop === card.offsetTop) last = c; });
    return last;
  }

  // Put the panel after the row, centre the box under the card, point the notch at it
  function place() {
    if (!openCard) return;
    rowEnd(openCard).after(panel);
    // Measure against the panel itself: the grid has page padding
    var g = panel.getBoundingClientRect(), c = openCard.getBoundingClientRect();
    var mid = c.left - g.left + c.width / 2, bw = box.offsetWidth;
    var left = Math.max(0, Math.min(mid - bw / 2, g.width - bw));
    box.style.marginLeft = left + 'px';
    notch.style.left = (mid - left) + 'px';
  }

  function detach(card) {
    var details = box.querySelector('.team-card__details');
    if (details) { details.hidden = true; card.appendChild(details); }
    card.classList.remove('is-open');
    card.querySelector('.team-card__toggle').setAttribute('aria-expanded', 'false');
  }

  function close(returnFocus) {
    if (!openCard) return;
    var card = openCard;
    openCard = null;
    detach(card);
    grid.classList.remove('has-open');
    panel.classList.remove('is-in');
    clearTimeout(closeTimer);
    closeTimer = setTimeout(function () { if (!openCard) panel.remove(); }, reduce ? 0 : 300);
    if (history.replaceState) history.replaceState(null, '', window.location.pathname + window.location.search);
    if (returnFocus) card.querySelector('.team-card__toggle').focus();
  }

  function open(card, scroll) {
    var sameRow = openCard && rowEnd(openCard) === rowEnd(card);
    if (openCard) detach(openCard);
    clearTimeout(closeTimer);

    var details = card.querySelector('.team-card__details');
    box.appendChild(details);
    details.hidden = false;
    panel.setAttribute('aria-label', details.getAttribute('aria-label'));
    card.classList.add('is-open');
    card.querySelector('.team-card__toggle').setAttribute('aria-expanded', 'true');
    grid.classList.add('has-open');
    openCard = card;

    if (!sameRow) panel.classList.remove('is-in');
    place();
    if (history.replaceState) history.replaceState(null, '', '#' + card.id);

    requestAnimationFrame(function () { requestAnimationFrame(function () {
      panel.classList.add('is-in');
      setTimeout(function () {
        if (scroll) panel.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'nearest' });
        var closeBtn = details.querySelector('.team-card__close');
        if (closeBtn) closeBtn.focus({ preventScroll: true });
      }, reduce ? 0 : 200);
    }); });
  }

  cards.forEach(function (card) {
    card.querySelector('.team-card__toggle').addEventListener('click', function () {
      openCard === card ? close(true) : open(card, true);
    });
    var closeBtn = card.querySelector('.team-card__close');
    if (closeBtn) closeBtn.addEventListener('click', function () { close(true); });
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') close(true);
  });

  // Rows change with the viewport, so re-seat the panel after resizing
  var resizeTimer;
  window.addEventListener('resize', function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(place, 100);
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
