/**
 * Work page (templates/work.php): clients (one at a time; choosing it
 * again clears it), service and impact area chips (any number, combined
 * with AND), "Load more" (12 at a time) and the address kept in step
 * (?theme=a,b&discipline=labs&client=x). With a client chosen, its units
 * show oldest first and a card in second place in the grid names it and
 * links to its client page. A change scrolls back up to the top of the
 * results.
 */
(function () {
  var grid = document.querySelector('[data-work-grid]');
  var bar = document.querySelector('[data-work-filters]');
  if (!grid || !bar) return;


  var showing = grid.querySelector('[data-client-active]');
  var units = [].slice.call(grid.children).filter(function (el) { return el !== showing; });
  var step = parseInt(grid.getAttribute('data-page'), 10) || 12;
  var shown = step;
  var state = { theme: [], discipline: [], client: '' };

  var countEl = document.querySelector('[data-work-count]');
  var clearBtn = document.querySelector('[data-work-clear]');
  var emptyEl = document.querySelector('[data-work-empty]');
  var more = document.querySelector('[data-work-more]');
  var moreCount = document.querySelector('[data-work-more-count]');
    var cards = [].slice.call(bar.querySelectorAll('.wk-cl'));
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
    // The client card: second in the grid (first when only one unit shows)
    if (showing) {
      showing.hidden = !pick || !hits.length;
      if (!showing.hidden) {
        showing.querySelector('[data-client-active-name]').textContent = pick.getAttribute('data-label');
        showing.querySelector('[data-client-active-link]').href = pick.getAttribute('data-url');
        var img = showing.querySelector('[data-client-active-img]');
        var src = pick.getAttribute('data-img');
        img.hidden = !src;
        if (src) img.src = src;
        var after = hits[0];
        grid.insertBefore(showing, after.nextSibling);
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

  // After a filter change, back to the top of the results if the page is
  // scrolled past them (under the pinned header)
  var top = document.querySelector('.wk-status') || grid;
  function toTop() {
    var y = top.getBoundingClientRect().top + window.scrollY - 112;
    if (window.scrollY > y) {
      window.scrollTo({ top: Math.max(0, y), behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
    }
  }

  function change() { shown = step; render(); writeUrl(); toTop(); }

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
    var card = ev.target.closest('.wk-cl');
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

  /* ---------- Clients: eight, then "Show all" ---------- */

  var moreClients = bar.querySelector('[data-more-clients]');
  if (moreClients) {
    var hiddenClients = [].slice.call(bar.querySelectorAll('[data-more-client]'));
    var chosen = state.client && bar.querySelector('[data-more-client] [data-client="' + CSS.escape(state.client) + '"]');
    if (!chosen) {
      hiddenClients.forEach(function (li) { li.hidden = true; });
      moreClients.hidden = false;
    }
    moreClients.addEventListener('click', function () {
      hiddenClients.forEach(function (li) { li.hidden = false; });
      moreClients.hidden = true;
      var first = hiddenClients[0] && hiddenClients[0].querySelector('button');
      if (first) first.focus();
    });
  }

  render();
})();
