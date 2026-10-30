/**
 * Work page (templates/work.php): theme and discipline chips (any number,
 * combined with AND), a searchable client dropdown (one client), "Load
 * more" (12 at a time) and the address kept in step
 * (?theme=a,b&discipline=labs&client=x). With a client selected, its units
 * show oldest first and a line above the grid links to the client page.
 */
(function () {
  var grid = document.querySelector('[data-work-grid]');
  var bar = document.querySelector('[data-work-filters]');
  if (!grid || !bar) return;

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

  var picker = document.querySelector('[data-client-picker]');
  var toggle = picker && picker.querySelector('.wk-client__toggle');
  var panel = picker && picker.querySelector('.wk-client__panel');
  var search = picker && picker.querySelector('[data-client-search]');
  var current = picker && picker.querySelector('[data-client-current]');
  var picks = picker ? [].slice.call(picker.querySelectorAll('[data-client]')) : [];
  var rows = picker ? [].slice.call(picker.querySelectorAll('[data-client-row]')) : [];

  // JS on: the client names become buttons, with the search above them
  picks.forEach(function (b) { b.hidden = false; });
  if (search) search.hidden = false;

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

    var any = state.theme.length || state.discipline.length || state.client;
    clearBtn.hidden = !any;

    [].forEach.call(bar.querySelectorAll('.wk-chip'), function (b) {
      b.setAttribute('aria-pressed', String(state[b.getAttribute('data-group')].indexOf(b.getAttribute('data-value')) !== -1));
    });

    var pick = null;
    picks.forEach(function (b) {
      var on = b.getAttribute('data-client') === state.client;
      b.setAttribute('aria-pressed', String(on));
      if (on) pick = b;
    });
    if (current) current.textContent = pick ? pick.getAttribute('data-label') : 'All clients';
    if (toggle) toggle.classList.toggle('is-set', !!pick);
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
      if (v && bar.querySelector('.wk-chip[data-group="' + group + '"][data-value="' + CSS.escape(v) + '"]')) state[group].push(v);
    });
  });
  var c = params.get('client');
  if (c && picks.some(function (b) { return b.getAttribute('data-client') === c; })) state.client = c;

  bar.addEventListener('click', function (ev) {
    var chip = ev.target.closest('.wk-chip');
    if (!chip) return;
    var list = state[chip.getAttribute('data-group')];
    var v = chip.getAttribute('data-value');
    var i = list.indexOf(v);
    if (i === -1) list.push(v); else list.splice(i, 1);
    change();
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

  // Client dropdown
  if (picker) {
    var open = function (on) {
      panel.classList.toggle('is-open', on);
      toggle.setAttribute('aria-expanded', String(on));
      if (on) search.focus();
    };
    toggle.addEventListener('click', function () { open(!panel.classList.contains('is-open')); });
    search.addEventListener('input', function () {
      var q = search.value.trim().toLowerCase();
      var n = 0;
      rows.forEach(function (r) {
        r.hidden = q && r.getAttribute('data-name').indexOf(q) === -1;
        if (!r.hidden) n++;
      });
      picker.querySelector('[data-client-none]').hidden = n > 0;
    });
    picks.forEach(function (b) {
      b.addEventListener('click', function () {
        var v = b.getAttribute('data-client');
        state.client = state.client === v ? '' : v;
        open(false);
        toggle.focus();
        change();
      });
    });
    document.addEventListener('click', function (ev) {
      if (!picker.contains(ev.target)) open(false);
    });
    picker.addEventListener('keydown', function (ev) {
      if (ev.key === 'Escape' && panel.classList.contains('is-open')) { open(false); toggle.focus(); }
    });
  }

  render();
})();
