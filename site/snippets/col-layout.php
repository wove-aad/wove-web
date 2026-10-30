<?php
/**
 * Layouts for review on service and impact area pages (2026-10-30), see
 * the "Service and impact area page layouts" styles in pages.css.
 * Usage: <?php snippet('col-layout') ?> as the first thing inside the
 * page's .home element (it sets data-col-layout on it before the first
 * paint), and <?php snippet('col-layout', ['switch' => true]) ?> before
 * the footer for the switcher in the corner.
 * Pass 'key' and 'default' to the first call for a page type with
 * its own default and choice: service pages start on the centred feed
 * (2026-10-30, ['key' => 'service', 'default' => 'feed']).
 * View (?view=2|3): the feeds in 2 columns, or 3 columns at full width.
 */
$key     = 'wove.colLayout' . (empty($key) ? '' : '.' . $key);
$default = $default ?? 'left';
$options = ['left' => 'All left-aligned', 'feed' => 'Centred feed', 'centre' => 'All centred'];
?>
<?php if (empty($switch)): ?>
  <script>
    (function (el) { try {
      var m = (location.search.match(/[?&]cols=(left|feed|centre)(?![\w-])/) || [])[1];
      if (m) localStorage.setItem('<?= $key ?>', m);
      el.setAttribute('data-col-layout', localStorage.getItem('<?= $key ?>') || '<?= $default ?>');
      // View (2026-10-30, ?view=2|3): the feeds in 2 columns, or 3 at full width
      var v = (location.search.match(/[?&]view=(2|3)(?![\w-])/) || [])[1];
      if (v) localStorage.setItem('<?= $key ?>.view', v);
      el.setAttribute('data-col-view', localStorage.getItem('<?= $key ?>.view') || '2');
    } catch (e) { el.setAttribute('data-col-layout', '<?= $default ?>'); el.setAttribute('data-col-view', '2'); } })(document.currentScript.parentElement);
  </script>
<?php else: ?>
  <aside class="feed-options col-switch" aria-label="Page layouts">
    <p class="feed-options__title">Page layout</p>
    <div class="feed-options__group">
      <span class="feed-options__label">Layout</span>
      <?php foreach ($options as $key => $label): ?>
        <a href="?cols=<?= $key ?>" data-col-switch="<?= $key ?>"><?= $label ?></a>
      <?php endforeach ?>
    </div>
    <div class="feed-options__group">
      <span class="feed-options__label">View</span>
      <a href="?view=2" data-col-view-switch="2">2 columns</a>
      <a href="?view=3" data-col-view-switch="3">3 columns, full width</a>
    </div>
  </aside>
  <script>
    (function () { var h = document.querySelector('[data-col-layout]'); if (!h) return;
      var a = document.querySelector('[data-col-switch="' + h.getAttribute('data-col-layout') + '"]'); if (a) a.setAttribute('aria-current', 'true');
      var b = document.querySelector('[data-col-view-switch="' + h.getAttribute('data-col-view') + '"]'); if (b) b.setAttribute('aria-current', 'true'); })();
  </script>
<?php endif ?>
