<script>
  /* Reveal on scroll */
  (function () {
    var targets = document.querySelectorAll('.offering, .wovemind-related-card, .wovemind-highlight-card, .quote__inner');
    if (!targets.length) return;
    if (!('IntersectionObserver' in window)) {
      targets.forEach(function (el) { el.classList.add('is-visible'); });
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15, rootMargin: '0px 0px -10% 0px' });
    targets.forEach(function (el) { io.observe(el); });
  })();

  /* See more / See less case studies — animated toggle (no-op if not on the page) */
  (function () {
    var btn = document.querySelector('.js-more');
    var features = document.getElementById('more-cases');
    if (!btn || !features) return;
    var label = btn.querySelector('.js-more-label');
    btn.addEventListener('click', function () {
      var expanded = features.classList.toggle('features--expanded');
      btn.setAttribute('aria-expanded', String(expanded));
      if (label) label.textContent = expanded ? 'See less' : 'See more case studies';
    });
  })();

  /* Mobile only: scroll-driven focus on the offerings grid.
     Focuses whichever card sits nearest the vertical centre of the screen. */
  (function () {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    if (!window.matchMedia('(max-width: 599px)').matches) return;
    var grid = document.querySelector('.offerings');
    if (!grid) return;
    var cards = Array.prototype.slice.call(grid.querySelectorAll('.offering'));
    if (!cards.length) return;
    var ticking = false;
    function update() {
      ticking = false;
      var mid = window.innerHeight / 2;
      var gridRect = grid.getBoundingClientRect();
      var active = gridRect.top < window.innerHeight && gridRect.bottom > 0;
      var best = null, bestDist = Infinity;
      cards.forEach(function (c) {
        var r = c.getBoundingClientRect();
        var dist = Math.abs((r.top + r.height / 2) - mid);
        if (dist < bestDist) { bestDist = dist; best = c; }
      });
      cards.forEach(function (c) { c.classList.toggle('is-focus', active && c === best); });
      grid.classList.toggle('has-focus', active);
    }
    function onScroll() {
      if (!ticking) { ticking = true; requestAnimationFrame(update); }
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    update();
  })();
</script>
