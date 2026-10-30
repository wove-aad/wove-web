/**
 * Work page (templates/work.php): a client carousel (one client; choosing
 * it again, or All work, clears it), service chips and impact area chips
 * (any number, combined with AND; in a dropdown in the top layout),
 * "Load more" (12 at a time) and the address kept in step
 * (?theme=a,b&discipline=labs&client=x). With a client selected, its units
 * show oldest first and a line above the grid links to the client page.
 * Two layouts for review (?filters=top|side, kept per browser; set by the
 * inline script in work.php), with a switcher in the corner.
 */
(function () {
  var grid = document.querySelector('[data-work-grid]');
  var bar = document.querySelector('[data-work-filters]');
  if (!grid || !bar) return;

  function reduceMotion() { return window.matchMedia('(prefers-reduced-motion: reduce)').matches; }
  function behaviour() { return reduceMotion() ? 'auto' : 'smooth'; }

  var units = [].slice.call(grid.children);
  var step = parseInt(grid.getAttribute('data-page'), 10) || 12;
  var shown = step;
  var state = { theme: [], discipline: [], client: '' };

  var countEl = document.querySelector('[data-work-count]');
  var clearBtn = document.querySelector('[data-work-clear]');
  var emptyEl = document.querySelector('[data-work-empty]');
  var more = document.querySelector('[data-work-more]');
  var moreCount = document.querySelector('[data-work-more-count]');
  var active = document.querySelector('[data-client-active]');
  var cards = [].slice.call(bar.querySelectorAll('.client-card__btn'));
  var chips = [].slice.call(document.querySelectorAll('.wk-chip[data-group]'));

  var words = function (el, attr) { return (el.getAttribute(attr) || '').split(' ').filter(Boolean); };
  var hasAll = function (have, want) { return want.every(function (w) { return have.indexOf(w) !== -1; }); };

  function matches(el) {
    return hasAll(words(el, 'data-theme-values'), state.theme)
      && hasAll(words(el, 'data-discipline-values'), state.discipline)
      && (!state.client || words(el, 'data-client').indexOf(state.client) !== -1);
  }

  function render() {
    // Newest first, or oldest first for one client
    var sorted = units.slice().sort(function (a, b) {
      var d = (+a.getAttribute('data-date')) - (+b.getAttribute('data-date'));
      return state.client ? d : -d;
    });
    sorted.forEach(function (el) { grid.appendChild(el); });

    var hits = sorted.filter(matches);
    sorted.forEach(function (el) {
      el.removeAttribute('data-later');
      el.hidden = hits.indexOf(el) === -1;
    });
    hits.forEach(function (el, i) { if (i >= shown) el.setAttribute('data-later', ''); });

    var total = hits.length;
    countEl.textContent = total + (total === 1 ? ' project' : ' projects');
    emptyEl.hidden = total > 0;
    more.hidden = total <= shown;
    moreCount.textContent = 'Showing ' + Math.min(shown, total) + ' of ' + total;
    clearBtn.hidden = !(state.theme.length || state.discipline.length || state.client);

    chips.forEach(function (b) {
      b.setAttribute('aria-pressed', String(state[b.getAttribute('data-group')].indexOf(b.getAttribute('data-value')) !== -1));
    });
    [].forEach.call(document.querySelectorAll('[data-group-count]'), function (el) {
      var n = state[el.getAttribute('data-group-count')].length;
      el.textContent = n ? String(n) : '';
    });

    var pick = null;
    cards.forEach(function (b) {
      var on = b.getAttribute('data-client') === state.client;
      b.setAttribute('aria-pressed', String(on));
      if (on && state.client) pick = b;
    });
    if (active) {
      active.hidden = !pick;
      if (pick) {
        var name = pick.getAttribute('data-label');
        active.querySelector('[data-client-active-name]').textContent = name;
        active.querySelector('[data-client-active-label]').textContent = name;
        active.querySelector('[data-client-active-link]').href = pick.getAttribute('data-url');
      }
    }
  }

  function writeUrl() {
    var q = [];
    if (state.theme.length) q.push('theme=' + state.theme.map(encodeURIComponent).join(','));
    if (state.discipline.length) q.push('discipline=' + state.discipline.map(encodeURIComponent).join(','));
    if (state.client) q.push('client=' + encodeURIComponent(state.client));
    history.replaceState(null, '', location.pathname + (q.length ? '?' + q.join('&') : ''));
  }

  function change() { shown = step; render(); writeUrl(); }

  // From the address, keeping only values the page has
  var params = new URLSearchParams(location.search);
  ['theme', 'discipline'].forEach(function (group) {
    (params.get(group) || '').split(',').forEach(function (v) {
      if (v && document.querySelector('.wk-chip[data-group="' + group + '"][data-value="' + CSS.escape(v) + '"]') && state[group].indexOf(v) === -1) state[group].push(v);
    });
  });
  var c = params.get('client');
  if (c && cards.some(function (b) { return b.getAttribute('data-client') === c; })) state.client = c;

  document.addEventListener('click', function (ev) {
    var chip = ev.target.closest('.wk-chip[data-group]');
    if (chip) {
      var list = state[chip.getAttribute('data-group')];
      var v = chip.getAttribute('data-value');
      var i = list.indexOf(v);
      if (i === -1) list.push(v); else list.splice(i, 1);
      change();
      return;
    }
    var card = ev.target.closest('.client-card__btn');
    if (card && bar.contains(card)) {
      var slug = card.getAttribute('data-client');
      state.client = state.client === slug ? '' : slug;
      change();
    }
  });

  clearBtn.addEventListener('click', function () {
    state = { theme: [], discipline: [], client: '' };
    change();
  });

  document.querySelector('[data-work-load]').addEventListener('click', function () {
    var first = grid.querySelector('[data-later]:not([hidden])');
    shown += step;
    render();
    if (first) first.querySelector('.wk-unit__link').focus({ preventScroll: true });
  });

  /* ---------- Impact areas dropdown (top layout) ---------- */

  var drop = document.querySelector('[data-drop]');
  if (drop) {
    var toggle = drop.querySelector('.wk-drop__toggle');
    var open = function (on) {
      drop.classList.toggle('is-open', on);
      toggle.setAttribute('aria-expanded', String(on));
    };
    toggle.addEventListener('click', function () { open(!drop.classList.contains('is-open')); });
    document.addEventListener('click', function (ev) { if (!drop.contains(ev.target)) open(false); });
    drop.addEventListener('keydown', function (ev) {
      if (ev.key === 'Escape' && drop.classList.contains('is-open')) { open(false); toggle.focus(); }
    });
  }

  /* ---------- Client carousel ---------- */

  var carousel = bar.querySelector('[data-carousel]');
  if (carousel) {
    var track = carousel.querySelector('.client-carousel__track');
    var controls = carousel.querySelector('.client-carousel__controls');
    var thumb = carousel.querySelector('.client-carousel__bar > span');
    var arrows = [].slice.call(carousel.querySelectorAll('.client-carousel__arrow'));
    var maxScroll = function () { return track.scrollWidth - track.clientWidth; };
    var stepWidth = function () {
      var card = track.querySelector('.client-card');
      return card.offsetWidth + (parseFloat(getComputedStyle(track).columnGap) || 0);
    };
    var sync = function () {
      var max = maxScroll();
      controls.hidden = max <= 2;
      var total = track.scrollWidth || 1;
      thumb.style.left = (track.scrollLeft / total * 100) + '%';
      thumb.style.width = (track.clientWidth / total * 100) + '%';
      arrows.forEach(function (b) {
        b.disabled = +b.getAttribute('data-step') < 0 ? track.scrollLeft <= 2 : track.scrollLeft >= max - 2;
      });
    };
    track.addEventListener('scroll', sync, { passive: true });
    window.addEventListener('resize', sync);
    arrows.forEach(function (b) {
      b.addEventListener('click', function () {
        track.scrollBy({ left: +b.getAttribute('data-step') * stepWidth(), behavior: behaviour() });
      });
    });

    // Click and drag with a mouse (touch screens swipe natively). A drag of
    // more than a few pixels doesn't count as a click on the card under it.
    var drag = null, dragged = false;
    track.addEventListener('pointerdown', function (ev) {
      if (ev.pointerType !== 'mouse' || ev.button !== 0) return;
      drag = { x: ev.clientX, left: track.scrollLeft, id: ev.pointerId };
      dragged = false;
    });
    track.addEventListener('pointermove', function (ev) {
      if (!drag || ev.pointerId !== drag.id) return;
      var dx = ev.clientX - drag.x;
      if (!dragged && Math.abs(dx) > 5) {
        dragged = true;
        track.setPointerCapture(drag.id);
        track.classList.add('is-dragging');
      }
      if (dragged) track.scrollLeft = drag.left - dx;
    });
    var endDrag = function () {
      if (!drag) return;
      drag = null;
      if (!dragged) return;
      track.classList.remove('is-dragging');
      var s = stepWidth();
      track.scrollTo({ left: Math.round(track.scrollLeft / s) * s, behavior: behaviour() });
    };
    track.addEventListener('pointerup', endDrag);
    track.addEventListener('pointercancel', endDrag);
    track.addEventListener('click', function (ev) {
      if (dragged) { ev.preventDefault(); ev.stopPropagation(); dragged = false; }
    }, true);
    track.addEventListener('dragstart', function (ev) { ev.preventDefault(); });

    // A client from the address: bring its card into view
    if (state.client) {
      var on = track.querySelector('[data-client="' + CSS.escape(state.client) + '"]');
      if (on) track.scrollLeft = on.parentElement.offsetLeft - track.offsetLeft;
    }
    sync();
  }

  /* ---------- Layout switcher, for review ---------- */

  var root = document.querySelector('.wk');
  var layout = root ? root.getAttribute('data-filter-layout') : 'top';
  var panel = document.createElement('aside');
  panel.className = 'feed-options menu-options menu-options--left';
  panel.setAttribute('aria-label', 'Filter layouts');
  panel.innerHTML = '<p class="feed-options__title">Filter layouts</p><div class="feed-options__group">'
    + [['top', 'Top'], ['side', 'Sidebar']].map(function (o) {
      return '<a href="' + location.pathname + '?filters=' + o[0] + '"' + (o[0] === layout ? ' aria-current="true"' : '') + '>' + o[1] + '</a>';
    }).join('') + '</div>';
  document.body.appendChild(panel);

  render();
})();
