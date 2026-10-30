<?php
/**
 * Layouts for review on service and impact area pages (2026-10-30), see
 * the "Service and impact area page layouts" styles in pages.css.
 * Usage: <?php snippet('col-layout') ?> as the first thing inside the
 * page's .home element (it sets data-col-layout on it before the first
 * paint), and <?php snippet('col-layout', ['switch' => true]) ?> before
 * the footer for the switcher in the corner.
 */
$options = ['left' => 'All left-aligned', 'feed' => 'Centred feed', 'centre' => 'All centred'];
?>
<?php if (empty($switch)): ?>
  <script>
    (function (el) { try {
      var m = (location.search.match(/[?&]cols=(left|feed|centre)(?![\w-])/) || [])[1];
      if (m) localStorage.setItem('wove.colLayout', m);
      el.setAttribute('data-col-layout', localStorage.getItem('wove.colLayout') || 'left');
    } catch (e) { el.setAttribute('data-col-layout', 'left'); } })(document.currentScript.parentElement);
  </script>
<?php else: ?>
  <aside class="feed-options col-switch" aria-label="Page layouts">
    <p class="feed-options__title">Page layout</p>
    <div class="feed-options__group">
      <?php foreach ($options as $key => $label): ?>
        <a href="?cols=<?= $key ?>" data-col-switch="<?= $key ?>"><?= $label ?></a>
      <?php endforeach ?>
    </div>
  </aside>
  <script>
    (function () { var h = document.querySelector('[data-col-layout]'); if (!h) return;
      var a = document.querySelector('[data-col-switch="' + h.getAttribute('data-col-layout') + '"]'); if (a) a.setAttribute('aria-current', 'true'); })();
  </script>
<?php endif ?>
