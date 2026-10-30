/**
 * Recently viewed (2026-10-30): a quiet row above the footer with the last
 * three client, service, impact area and post pages this visitor opened,
 * so they can get back to them. Pages record themselves from the
 * data-rv-* attributes on <body> (snippets/header.php). The list lives
 * only in this browser (localStorage, eight at most) and the row shows
 * once there are at least two other pages to offer. Without storage it
 * simply stays hidden.
 */
(function () {
  var KEY = 'wove.recent';
  var section = document.querySelector('[data-recent]');
  var list = [];
  try { list = JSON.parse(localStorage.getItem(KEY)) || []; } catch (e) { return; }

  var body = document.body;
  var here = body.getAttribute('data-rv-url') || location.href.split('#')[0].split('?')[0];
  var same = function (a, b) { return a.replace(/\/$/, '') === b.replace(/\/$/, ''); };

  // Show the others, newest first
  var others = list.filter(function (r) { return !same(r.u, here); }).slice(0, 3);
  if (section && others.length >= 2) {
    var ul = section.querySelector('.rv__list');
    others.forEach(function (r) {
      var li = document.createElement('li');
      var a = document.createElement('a');
      a.className = 'rv__item';
      a.href = r.u;
      var thumb;
      if (r.i) {
        thumb = document.createElement('img');
        thumb.src = r.i; thumb.alt = ''; thumb.width = 48; thumb.height = 48; thumb.loading = 'lazy';
      } else {
        thumb = document.createElement('span');
        thumb.setAttribute('aria-hidden', 'true');
        thumb.textContent = (r.t || '?').trim().charAt(0);
      }
      thumb.className = 'rv__thumb';
      var text = document.createElement('span');
      text.className = 'rv__text';
      var kind = document.createElement('span');
      kind.className = 'rv__kind';
      kind.textContent = r.k;
      var title = document.createElement('span');
      title.className = 'rv__name';
      title.textContent = r.t;
      text.appendChild(kind); text.appendChild(title);
      a.appendChild(thumb); a.appendChild(text);
      li.appendChild(a); ul.appendChild(li);
    });
    section.hidden = false;
  }

  // Record this page
  var title = body.getAttribute('data-rv-title');
  if (!title) return;
  list = list.filter(function (r) { return !same(r.u, here); });
  list.unshift({ u: here, t: title, k: body.getAttribute('data-rv-kind') || '', i: body.getAttribute('data-rv-img') || '' });
  try { localStorage.setItem(KEY, JSON.stringify(list.slice(0, 8))); } catch (e) {}
})();
