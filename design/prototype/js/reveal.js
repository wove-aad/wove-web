/* Scroll reveal for [data-reveal] paragraphs.
   Words go from faint to full opacity, in reading order, as the paragraph
   rises from the bottom of the viewport until its top is 20% of the way
   down. On most screens the paragraph loads partly revealed and completes
   over the first few hundred pixels of scroll. The text
   is in the HTML and fully visible without JS; with reduced motion, or when
   the prototype switcher turns it off, it stays fully visible. */
(function () {
  var FAINT = 0.15;
  var els = [].slice.call(document.querySelectorAll('[data-reveal]'));
  if (!els.length) return;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)');

  // Wrap each word in a span, keeping the original spacing.
  els.forEach(function (el) {
    el.innerHTML = el.textContent.split(/(\s+)/).map(function (part) {
      return /^\s+$/.test(part) || !part ? part : '<span class="reveal-word">' + part + '</span>';
    }).join('');
    el._words = [].slice.call(el.querySelectorAll('.reveal-word'));
  });

  function off() { return reduce.matches || document.documentElement.hasAttribute('data-reveal-off'); }

  function update() {
    var vh = window.innerHeight;
    els.forEach(function (el) {
      var words = el._words;
      if (off()) { words.forEach(function (w) { w.style.removeProperty('--o'); }); return; }
      var r = el.getBoundingClientRect();
      // 0 when the top of the paragraph reaches the bottom of the screen,
      // 1 when its top reaches 20% of the way down.
      var endTop = vh * 0.2;
      var p = Math.min(1, Math.max(0, (vh - r.top) / (vh - endTop)));
      // Spread the words across the progress, with a little overlap.
      var n = words.length;
      words.forEach(function (w, i) {
        var t = Math.min(1, Math.max(0, p * (n + 3) - i) / 3);
        w.style.setProperty('--o', (FAINT + (1 - FAINT) * t).toFixed(3));
      });
    });
  }

  var ticking = false;
  function onScroll() {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(function () { ticking = false; update(); });
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onScroll);
  if (reduce.addEventListener) reduce.addEventListener('change', update);
  update();
})();
