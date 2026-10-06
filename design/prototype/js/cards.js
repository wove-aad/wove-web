/* Post cards (the Framed treatment).
   - images keep their own shape (square, portrait, landscape), no cropping;
   - sparks with an image read as a captioned image, with small quiet text;
   - long reads are image-led editorial cards with a reading time;
   - What Ifs lead with the question, set larger, on a pale blue card;
   - threads are the workhorse: compact, title and meta, small thumbnail.
   Cards flow into columns (masonry) so mixed image shapes sit without gaps;
   order runs left to right along each row. */
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

  // Deal cards into columns left to right, so recency reads along each row.
  function masonry(htmlList) {
    var n = columnCount();
    var cols = [];
    for (var c = 0; c < n; c++) cols.push([]);
    htmlList.forEach(function (h, i) { cols[i % n].push(h); });
    return cols.map(function (col) { return '<div class="pc-col">' + col.join('') + '</div>'; }).join('');
  }

  window.WoveCards = { render: render, masonry: masonry, columnCount: columnCount };
})();
