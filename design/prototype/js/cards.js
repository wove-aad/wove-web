/* Post cards (the Framed treatment).
   - images keep their own shape (square, portrait, landscape), no cropping;
   - sparks with an image read as a captioned image, with small quiet text;
   - long reads are image-led editorial cards with a reading time;
   - What Ifs lead with the question, set larger, on a pale blue card;
   - threads are the workhorse: compact, title and meta, small thumbnail.
   Cards sit in a grid in recency order (so reading and tab order match),
   each spanning rows by its height, so mixed image shapes pack without
   gaps. */
(function () {
  var LABELS = { spark: 'Spark', thread: 'Thread', whatif: 'What If', longread: 'Long Read' };

  function esc(s) {
    return String(s).replace(/[&<>"]/g, function (ch) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[ch];
    });
  }

  // ctx: { image(e) -> src, tags(e) -> html, date(e) -> label }
  function render(e, ctx) {
    var ratio = e.ratio || 3 / 2;
    var img = e.image
      ? '<div class="pc__media" style="aspect-ratio:' + ratio + '"><img src="' + ctx.image(e) + '" alt=""></div>'
      : '';
    var meta = '<p class="pc__meta">' +
      (e.author ? '<span>' + esc(e.author) + '</span><span aria-hidden="true"> &middot; </span>' : '') +
      '<time>' + ctx.date(e) + '</time></p>';
    var tags = ctx.tags(e);

    if (e.format === 'spark') {
      if (e.image) {
        // Captioned image: the picture leads, the words are a quiet caption.
        return '<article class="pc pc--spark pc--spark-image">' + img +
          '<div class="pc__body"><p class="pc__caption">' + esc(e.quote) + '</p>' + meta + tags + '</div></article>';
      }
      return '<article class="pc pc--spark pc--spark-text"><div class="pc__body">' +
        '<p class="pc__quote">' + esc(e.quote) + '</p>' + meta + tags + '</div></article>';
    }

    if (e.format === 'thread') {
      var thumb = e.image
        ? '<img class="pc__thumb" src="' + ctx.image(e) + '" alt="" style="aspect-ratio:' + ratio + '">'
        : '';
      return '<article class="pc pc--thread' + (e.image ? ' has-thumb' : '') + '"><div class="pc__body">' +
        '<p class="pc__label">' + LABELS.thread + '</p>' +
        '<div class="pc__row"><h3 class="pc__title">' + esc(e.title) + '</h3>' + thumb + '</div>' +
        meta + tags + '</div></article>';
    }

    if (e.format === 'whatif') {
      return '<article class="pc pc--whatif' + (e.image ? ' has-image' : '') + '">' +
        '<div class="pc__body">' +
          '<p class="pc__label">' + LABELS.whatif + '</p>' +
          '<h3 class="pc__title">' + esc(e.title) + '</h3>' +
          (e.excerpt ? '<p class="pc__excerpt">' + esc(e.excerpt) + '</p>' : '') +
        '</div>' + img +
        '<div class="pc__foot"><span class="pc__cta">Explore the idea <span aria-hidden="true">&rarr;</span></span>' + meta + tags + '</div>' +
      '</article>';
    }

    // Long read
    return '<article class="pc pc--longread' + (e.image ? ' has-image' : '') + '">' + img +
      '<div class="pc__body">' +
        '<p class="pc__label">' + LABELS.longread + (e.minutes ? ' <span class="pc__time">' + e.minutes + ' min read</span>' : '') + '</p>' +
        '<h3 class="pc__title">' + esc(e.title) + '</h3>' +
        (e.excerpt ? '<p class="pc__excerpt">' + esc(e.excerpt) + '</p>' : '') +
        meta + tags +
      '</div></article>';
  }

  function columnCount() {
    var w = window.innerWidth;
    return w >= 960 ? 3 : w >= 600 ? 2 : 1;
  }

  // Cards in DOM order; layout() sizes each one's row span.
  function masonry(htmlList) { return htmlList.join(''); }

  var ROW = 4;   // px, matches grid-auto-rows
  var GAP = 24;  // px, vertical space between cards
  function layout(grid) {
    [].forEach.call(grid.querySelectorAll('.pc'), function (el) {
      el.style.gridRowEnd = 'span ' + Math.ceil((el.offsetHeight + GAP) / ROW);
    });
  }

  window.WoveCards = { render: render, masonry: masonry, layout: layout, columnCount: columnCount };
})();
