/**
 * Masonry for feeds ([data-masonry] grids; 2026-10-29, every feed): the
 * grid has 4px rows (CSS), and each item spans as many as its height
 * needs plus the gap, so items pack up under each other while staying in
 * date order in the DOM. Re-measured when any item changes size (images
 * loading, filters showing or hiding items, "Show more").
 */
(function () {
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
