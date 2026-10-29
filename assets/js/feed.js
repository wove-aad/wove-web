/**
 * Feed layouts, shared by every feed (loaded from snippets/header.php).
 *
 * Varied image heights ([data-heights] grids, 2026-10-29, the default on
 * every feed): each visible item gets data-size tall, short or medium by
 * its place in the grid, so every row of three has one of each and the
 * order shifts row by row ((column + row) % 3). Short and tall images
 * never bunch up in one row. CSS sets the image ratio for each size
 * (home.css). Re-run when filters hide or show items, "Load more" or
 * "Show more" reveal them, or the column count changes.
 *
 * Masonry ([data-masonry] grids; the homepage ?layout=masonry option):
 * the grid has 4px rows (CSS), and each item spans as many as its height
 * needs plus the gap, so items pack up under each other while staying in
 * date order in the DOM.
 */
(function () {
  var SIZES = ['tall', 'short', 'medium'];

  [].forEach.call(document.querySelectorAll('[data-heights]'), function (grid) {
    var queued = false;
    var size = function () {
      queued = false;
      var cols = getComputedStyle(grid).gridTemplateColumns.split(' ').filter(Boolean).length || 1;
      var items = [].filter.call(grid.children, function (el) {
        return !el.hidden && el.offsetParent !== null && !el.classList.contains('feed-empty');
      });
      items.forEach(function (el, i) {
        el.setAttribute('data-size', SIZES[(i % cols + Math.floor(i / cols)) % 3]);
      });
    };
    var queue = function () {
      if (queued) return;
      queued = true;
      requestAnimationFrame(size);
    };
    size();
    if (window.MutationObserver) {
      new MutationObserver(queue).observe(grid, { attributes: true, subtree: true, attributeFilter: ['hidden', 'data-later', 'class'] });
    }
    window.addEventListener('resize', queue);
  });

  var ROW = 4, GAP = 48;
  [].forEach.call(document.querySelectorAll('[data-masonry]'), function (grid) {
    var layout = function () {
      [].forEach.call(grid.children, function (el) {
        if (el.hidden || !el.offsetHeight) return;
        el.style.gridRowEnd = 'span ' + Math.ceil((el.offsetHeight + GAP) / ROW);
      });
    };
    layout();
    if (window.ResizeObserver) {
      var ro = new ResizeObserver(layout);
      ro.observe(grid);
      [].forEach.call(grid.children, function (el) { ro.observe(el); });
    }
    window.addEventListener('load', layout);
  });
})();
