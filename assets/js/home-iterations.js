/**
 * Homepage iterations (snippets/home/iteration.php): the featured slides
 * (turning every six seconds, paused on hover, focus or with reduced
 * motion; the dashes choose one), the "Right now" line (one team at a
 * time) and the Recent activity filters (one service at a time; All shows
 * everything; six cards show).
 */
(function () {
  var still = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  [].forEach.call(document.querySelectorAll('[data-slides]'), function (box) {
    var slides = [].slice.call(box.querySelectorAll('[data-slide]'));
    var captions = [].slice.call(box.querySelectorAll('[data-slide-caption]'));
    var dots = [].slice.call(box.querySelectorAll('[data-dot]'));
    if (slides.length < 2) return;
    var i = 0, timer = null, held = false;
    var show = function (n) {
      i = (n + slides.length) % slides.length;
      slides.forEach(function (s, k) { s.classList.toggle('is-on', k === i); if (s.tagName === 'A') s.tabIndex = k === i ? 0 : -1; });
      captions.forEach(function (c, k) { c.classList.toggle('is-on', k === i); });
      dots.forEach(function (d, k) { d.setAttribute('aria-pressed', String(k === i)); });
    };
    var start = function () { if (still || held) return; clearInterval(timer); timer = setInterval(function () { show(i + 1); }, 6000); };
    dots.forEach(function (d, k) { d.addEventListener('click', function () { show(k); start(); }); });
    box.addEventListener('mouseenter', function () { held = true; clearInterval(timer); });
    box.addEventListener('mouseleave', function () { held = false; start(); });
    box.addEventListener('focusin', function () { held = true; clearInterval(timer); });
    box.addEventListener('focusout', function () { held = false; start(); });
    start();
  });

  var ticker = document.querySelector('[data-ticker]');
  if (ticker && !still) {
    var lines = [].slice.call(ticker.querySelectorAll('[data-tick]'));
    var t = 0, hold = false;
    ticker.addEventListener('mouseenter', function () { hold = true; });
    ticker.addEventListener('mouseleave', function () { hold = false; });
    if (lines.length > 1) setInterval(function () {
      if (hold) return;
      lines[t].classList.remove('is-on');
      t = (t + 1) % lines.length;
      lines[t].classList.add('is-on');
    }, 4500);
  }

  var feed = document.querySelector('[data-recent-feed]');
  if (feed) {
    var cards = [].slice.call(feed.querySelectorAll('.pc'));
    var empty = feed.querySelector('[data-recent-empty]');
    var apply = function (key) {
      var shown = 0;
      cards.forEach(function (c) {
        var ok = key === '*' || (' ' + c.getAttribute('data-filters') + ' ').indexOf(' ' + key + ' ') !== -1;
        c.hidden = !ok || shown >= 6;
        if (ok && shown < 6) shown++;
      });
      empty.hidden = shown > 0;
      [].forEach.call(feed.querySelectorAll('[data-filter]'), function (b) {
        b.setAttribute('aria-pressed', String(b.getAttribute('data-filter') === key));
      });
    };
    feed.addEventListener('click', function (ev) {
      var b = ev.target.closest('[data-filter]');
      if (b) apply(b.getAttribute('aria-pressed') === 'true' && b.getAttribute('data-filter') !== '*' ? '*' : b.getAttribute('data-filter'));
    });
    apply('*');
  }
})();
