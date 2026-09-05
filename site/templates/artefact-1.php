<?php

/**
 * /artefact-1 — Wove Mind card variations, wired to real content.
 *
 * Pulls entries from page('wove-mind')->children() and maps the current
 * dev post structure (Format / Title / Cover / Body / Deck / Showbyline /
 * Service / Casestudy / Tags) onto the card variants.
 */

$mind = page('wove-mind');
$children = $mind ? $mind->children()->listed() : new \Kirby\Cms\Pages();

// Normalise a single entry into the shape the JS renderer expects.
$entries = [];
foreach ($children as $entry) {
    $format = (string) $entry->format();
    if ($format === '') { continue; }

    // Featured image (files field, max 1). Must go via content()->get('image')
    // because $entry->image() hits Kirby's built-in HasFiles::image() (first
    // uploaded file on the page), not the content field. Falls back to a
    // legacy `cover` field name for older entries.
    $file = $entry->content()->get('image')->toFile()
        ?? $entry->content()->get('cover')->toFile();
    $imageUrl = null;
    if ($file) {
        try {
            $imageUrl = $file->resize(1200)->url();
        } catch (\Throwable $e) {
            $imageUrl = $file->url();
        }
    }

    // Excerpt: prefer Deck, else strip Body to first ~180 chars.
    $deck = trim((string) $entry->deck());
    if ($deck === '') {
        $bodyPlain = trim(strip_tags((string) $entry->body()));
        $deck = $bodyPlain === '' ? '' : (mb_strlen($bodyPlain) > 180
            ? mb_substr($bodyPlain, 0, 177) . '…'
            : $bodyPlain);
    }

    // Spark uses body as its "quote" — no title.
    $bodyPlain = trim(strip_tags((string) $entry->body()));

    // Contributor — Showbyline toggles it on; resolve the users field.
    $showByline = $entry->showbyline()->toBool(true);
    $authorName = null;
    $authorInitials = null;
    if ($showByline) {
        // Prefer `contributor`, fall back to `author` for older entries.
        $field = $entry->contributor()->isNotEmpty()
            ? $entry->contributor()
            : $entry->author();
        $user = $field->toUser();
        if ($user) {
            $authorName = $user->name()->isNotEmpty()
                ? (string) $user->name()
                : (string) $user->email();
            $parts = preg_split('/\s+/', trim($authorName)) ?: [];
            $authorInitials = strtoupper(
                mb_substr($parts[0] ?? '?', 0, 1)
                . mb_substr($parts[1] ?? '', 0, 1)
            );
        }
    }

    // Date — no explicit field, fall back to page created/modified.
    $dateStr = null;
    if ($entry->date()->exists() && $entry->date()->isNotEmpty()) {
        $dateStr = $entry->date()->toDate('Y-m-d');
    } else {
        $ts = $entry->modified() ?: $entry->created();
        if ($ts) { $dateStr = date('Y-m-d', $ts); }
    }

    // Case study — pages field
    $case = $entry->casestudy()->toPage();
    $caseStudy = $case ? (string) $case->title() : null;

    // Service — single-value on dev; keep as array for filter uniformity
    $service = trim((string) $entry->service());
    $services = $service === '' ? [] : [ucfirst($service)];

    // Tags
    $tags = $entry->tags()->split(',');
    $tags = array_values(array_filter(array_map('trim', $tags)));

    $entries[] = [
        'id'        => (string) $entry->uuid(),
        'url'       => (string) $entry->url(),
        'format'    => $format,
        'title'     => (string) $entry->title(),
        'body'      => $bodyPlain,   // used by spark
        'excerpt'   => $deck,        // used by whatif / longread
        'image'     => $imageUrl,
        'showByline'=> $showByline,
        'author'    => $authorName,
        'initials'  => $authorInitials,
        'date'      => $dateStr,
        'caseStudy' => $caseStudy,
        'services'  => $services,
        'tags'      => $tags,
    ];
}

// Build filter option lists from real content, alphabetised.
$allTags     = [];
$allCases    = [];
$allServices = [];
foreach ($entries as $e) {
    foreach ($e['tags']     as $t) { $allTags[$t] = true; }
    foreach ($e['services'] as $s) { $allServices[$s] = true; }
    if ($e['caseStudy']) { $allCases[$e['caseStudy']] = true; }
}
$allTags     = array_keys($allTags);     sort($allTags);
$allCases    = array_keys($allCases);    sort($allCases);
$allServices = array_keys($allServices); sort($allServices);

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $page->title()->html() ?> — Wove</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
<style>
  /* Ballinger — real weights on disk are 400 / 700 / 800 (+ italic 400).
     Any other weight resolves to the nearest registered one via CSS
     font-matching, keeping text in Ballinger rather than falling back. */
  @font-face {
    font-family: 'Ballinger';
    src: url('<?= url('assets/fonts/Ballinger-Regular.woff2') ?>') format('woff2'),
         url('<?= url('assets/fonts/Ballinger-Regular.woff') ?>') format('woff');
    font-weight: 400; font-style: normal; font-display: optional;
  }
  @font-face {
    font-family: 'Ballinger';
    src: url('<?= url('assets/fonts/Ballinger-Italic.woff2') ?>') format('woff2'),
         url('<?= url('assets/fonts/Ballinger-Italic.woff') ?>') format('woff');
    font-weight: 400; font-style: italic; font-display: optional;
  }
  @font-face {
    font-family: 'Ballinger';
    src: url('<?= url('assets/fonts/Ballinger-Bold.woff2') ?>') format('woff2'),
         url('<?= url('assets/fonts/Ballinger-Bold.woff') ?>') format('woff');
    font-weight: 700; font-style: normal; font-display: optional;
  }
  @font-face {
    font-family: 'Ballinger';
    src: url('<?= url('assets/fonts/Ballinger-XBold.woff2') ?>') format('woff2'),
         url('<?= url('assets/fonts/Ballinger-XBold.woff') ?>') format('woff');
    font-weight: 800; font-style: normal; font-display: optional;
  }

  :root {
    --ground: #FFFFFF;
    --surface: #FFFFFF;
    --surface-2: #F7F6F2;
    --ink: #111111;
    --ink-2: #3D3D3B;
    --muted: #6B6B67;
    --faint: #A6A49E;
    --rule: #EAEAE4;
    --rule-2: #D9D6CC;

    --accent: #2A50F3;
    --accent-tint: #EBF0FE;
    --accent-ink: #1535A8;

    --spark:         #C6841E; --spark-tint:    #F7ECD3; --spark-ink:     #6C4310;
    --thread:        #2F7A57; --thread-tint:   #E1EEE7; --thread-ink:    #1A4531;
    --whatif:        #2A50F3; --whatif-tint:   #EBF0FE; --whatif-ink:    #1535A8;
    --longread:      #6B3EC2; --longread-tint: #EDE6F8; --longread-ink:  #3E2273;
    --highlight:     #B45D0B; --highlight-tint:#FBEDD6; --highlight-ink: #6E3A08;

    --sans: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    --display: 'Ballinger', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;

    --radius: 8px;
    --radius-sm: 4px;
    --shadow: 0 1px 2px rgba(20,18,10,0.04);
  }
  @media (prefers-color-scheme: dark) {
    :root:not([data-theme="light"]) {
      --ground: #131310; --surface: #1B1B17; --surface-2: #22221D;
      --ink: #F1EFE8; --ink-2: #D7D3C7; --muted: #9A968A; --faint: #6E6A5F;
      --rule: #2A2A22; --rule-2: #3A3A31;
      --accent: #7C93F9; --accent-tint: #1F2647; --accent-ink: #C7D0FA;
      --spark-tint: #2E2412; --spark-ink: #E8C888;
      --thread-tint: #17281E; --thread-ink: #9CD3B7;
      --whatif-tint: #1F2647; --whatif-ink: #C7D0FA;
      --longread-tint: #241A36; --longread-ink: #C7B4EE;
      --highlight-tint: #2E1F0D; --highlight-ink: #E6B379;
      --shadow: 0 1px 2px rgba(0,0,0,0.35);
    }
  }
  :root[data-theme="dark"] {
    --ground: #131310; --surface: #1B1B17; --surface-2: #22221D;
    --ink: #F1EFE8; --ink-2: #D7D3C7; --muted: #9A968A; --faint: #6E6A5F;
    --rule: #2A2A22; --rule-2: #3A3A31;
    --accent: #7C93F9; --accent-tint: #1F2647; --accent-ink: #C7D0FA;
    --spark-tint: #2E2412; --spark-ink: #E8C888;
    --thread-tint: #17281E; --thread-ink: #9CD3B7;
    --whatif-tint: #1F2647; --whatif-ink: #C7D0FA;
    --longread-tint: #241A36; --longread-ink: #C7B4EE;
    --highlight-tint: #2E1F0D; --highlight-ink: #E6B379;
    --shadow: 0 1px 2px rgba(0,0,0,0.35);
  }

  * { box-sizing: border-box; }
  body {
    margin: 0; font-family: var(--sans); color: var(--ink);
    background: var(--ground); font-size: 14px; line-height: 1.5;
    -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;
  }
  button, input, select { font: inherit; color: inherit; }
  a { color: inherit; text-decoration: none; }
  img { display: block; }
  @media (prefers-reduced-motion: reduce) { * { transition: none !important; animation: none !important; } }

  .top {
    position: sticky; top: 0; z-index: 30;
    display: flex; align-items: center; gap: 16px;
    padding: 12px 32px;
    background: color-mix(in oklab, var(--ground) 92%, transparent);
    backdrop-filter: saturate(140%) blur(8px);
    border-bottom: 1px solid var(--rule);
  }
  .brand { font-weight: 700; letter-spacing: -0.01em; font-size: 14px; display: flex; align-items: baseline; gap: 8px; }
  .brand .dot { color: var(--faint); }
  .brand .crumb { color: var(--muted); font-weight: 500; }
  .brand .path { color: var(--faint); font-weight: 400; }
  .top__right { margin-left: auto; display: flex; align-items: center; gap: 12px; }

  .switch { display: inline-flex; align-items: center; gap: 8px; font-size: 12px; color: var(--muted); cursor: pointer; user-select: none; }
  .switch input { position: absolute; opacity: 0; pointer-events: none; }
  .switch .track { width: 30px; height: 18px; border-radius: 999px; background: var(--rule-2); position: relative; transition: background .15s ease; }
  .switch .track::after {
    content: ""; position: absolute; top: 2px; left: 2px;
    width: 14px; height: 14px; border-radius: 50%;
    background: var(--surface); box-shadow: 0 1px 2px rgba(0,0,0,.2);
    transition: transform .15s ease;
  }
  .switch input:checked + .track { background: var(--accent); }
  .switch input:checked + .track::after { transform: translateX(12px); }
  .switch:focus-within .track { outline: 2px solid var(--accent-tint); outline-offset: 2px; }

  .icon-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 30px; height: 30px; border-radius: 6px;
    background: transparent; border: 1px solid var(--rule);
    color: var(--ink-2); cursor: pointer;
  }
  .icon-btn:hover { background: var(--surface-2); }

  main { max-width: 1200px; margin: 0 auto; padding: 40px 32px 96px; }
  .page-head { margin-bottom: 40px; }
  .page-head h1 {
    font-family: var(--display); font-weight: 400; font-size: 32px;
    letter-spacing: -0.02em; line-height: 1.15; margin: 0 0 8px; text-wrap: balance;
  }
  .page-head p { color: var(--muted); max-width: 62ch; margin: 0; font-size: 14px; }

  section.block { margin-bottom: 56px; }
  .block__head {
    display: flex; align-items: baseline; justify-content: space-between; gap: 24px;
    padding-bottom: 12px; margin-bottom: 20px; border-bottom: 1px solid var(--rule);
  }
  .eyebrow { font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; color: var(--muted); font-weight: 600; }
  .block__title { font-family: var(--display); font-weight: 400; font-size: 20px; letter-spacing: -0.01em; margin: 4px 0 0; color: var(--ink); }
  .block__meta { color: var(--faint); font-size: 12px; }

  .carousel {
    display: grid; grid-auto-flow: column;
    grid-auto-columns: minmax(280px, 320px);
    gap: 16px; overflow-x: auto; scroll-snap-type: x mandatory;
    padding-bottom: 16px;
    margin: 0 -32px; padding-left: 32px; padding-right: 32px;
    scrollbar-width: thin;
  }
  .carousel > * { scroll-snap-align: start; }
  .carousel::-webkit-scrollbar { height: 8px; }
  .carousel::-webkit-scrollbar-thumb { background: var(--rule-2); border-radius: 4px; }
  .carousel::-webkit-scrollbar-track { background: transparent; }

  .filters { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; }
  .filters__group { display: inline-flex; align-items: center; gap: 6px; padding-right: 10px; margin-right: 4px; border-right: 1px solid var(--rule); }
  .filters__group:last-of-type { border-right: none; }
  .filters__label { font-size: 11px; letter-spacing: 0.06em; text-transform: uppercase; color: var(--faint); font-weight: 600; margin-right: 4px; }
  .chip {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 4px 10px; border-radius: 999px;
    font-size: 12px; font-weight: 500;
    background: var(--surface-2); color: var(--ink-2);
    border: 1px solid transparent; cursor: pointer;
    transition: background .12s ease, color .12s ease, border-color .12s ease;
  }
  .chip:hover { background: color-mix(in oklab, var(--surface-2) 60%, var(--rule) 40%); }
  .chip[aria-pressed="true"] { background: var(--ink); color: var(--ground); border-color: var(--ink); }

  .grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; }
  @media (max-width: 900px) { .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
  @media (max-width: 600px) {
    .grid { grid-template-columns: 1fr; }
    main { padding: 24px 20px 64px; }
    .top { padding: 10px 20px; }
    .carousel { margin: 0 -20px; padding-left: 20px; padding-right: 20px; }
  }

  .card {
    display: flex; flex-direction: column;
    background: var(--surface); border: 1px solid var(--rule);
    border-radius: var(--radius); overflow: hidden;
    transition: border-color .15s ease, transform .15s ease;
    cursor: pointer; min-height: 240px;
  }
  .card:hover { border-color: var(--rule-2); }
  .card:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }

  .card__media { aspect-ratio: 16 / 10; background: var(--surface-2); overflow: hidden; position: relative; }
  .card__media img { width: 100%; height: 100%; object-fit: cover; transition: transform .3s ease; }
  .card:hover .card__media img { transform: scale(1.02); }

  .card__body { padding: 14px 16px 16px; display: flex; flex-direction: column; gap: 8px; flex: 1; }

  .card__format { display: inline-flex; align-items: center; gap: 6px; font-size: 10px; letter-spacing: 0.08em; text-transform: uppercase; font-weight: 600; color: var(--muted); }
  .card__format .swatch { display: inline-block; width: 6px; height: 6px; border-radius: 50%; }
  .card--spark    .swatch { background: var(--spark); }
  .card--thread   .swatch { background: var(--thread); }
  .card--whatif   .swatch { background: var(--whatif); }
  .card--longread .swatch { background: var(--longread); }
  .card--highlight .swatch { background: var(--highlight); }

  .card__title { font-family: var(--display); font-weight: 400; font-size: 17px; line-height: 1.25; letter-spacing: -0.01em; color: var(--ink); margin: 0; text-wrap: balance; }
  .card__excerpt {
    font-size: 13px; line-height: 1.5; color: var(--muted); margin: 0;
    display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
  }

  .card__byline { display: flex; align-items: center; gap: 8px; margin-top: auto; padding-top: 8px; font-size: 12px; color: var(--muted); }
  .card__byline .avatar {
    width: 20px; height: 20px; border-radius: 50%;
    background: var(--accent-tint); color: var(--accent-ink);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 10px; font-weight: 700; flex-shrink: 0;
  }
  .card__byline .who { color: var(--ink-2); font-weight: 500; }
  .card__byline .sep { color: var(--faint); }
  body[data-byline="off"] .card__byline { display: none; }

  .card--spark { min-height: 260px; }
  .card--spark-image { position: relative; }
  .card--spark-image .card__media { aspect-ratio: auto; position: absolute; inset: 0; }
  .card--spark-image .card__media::after {
    content: ""; position: absolute; inset: 0;
    background: linear-gradient(180deg, rgba(0,0,0,0) 40%, rgba(0,0,0,0.72) 100%);
  }
  .card--spark-image .card__body { position: relative; color: #FDFCF7; background: transparent; padding: 16px; z-index: 1; justify-content: flex-end; }
  .card--spark-image .card__format { color: rgba(255,255,255,0.85); }
  .card--spark-image .card__format .swatch { background: rgba(255,255,255,0.9); }
  .card--spark-image .card__quote { font-family: var(--display); font-weight: 400; font-size: 17px; line-height: 1.3; letter-spacing: -0.005em; margin: 0; text-wrap: balance; color: #FDFCF7; }
  .card--spark-image .card__byline { color: rgba(255,255,255,0.8); }
  .card--spark-image .card__byline .who { color: #fff; }
  .card--spark-image .card__byline .avatar { background: rgba(255,255,255,0.16); color: #fff; }
  .card--spark-image .card__byline .sep { color: rgba(255,255,255,0.5); }

  .card--spark-block { background: var(--spark-tint); border-color: transparent; }
  .card--spark-block .card__format { color: var(--spark-ink); }
  .card--spark-block .card__body { padding: 20px; justify-content: space-between; gap: 16px; }
  .card--spark-block .card__quote {
    font-family: var(--display); font-weight: 400; font-size: 22px; line-height: 1.22;
    letter-spacing: -0.015em; margin: 0; color: var(--spark-ink); text-wrap: balance;
  }
  .card--spark-block .card__byline { color: color-mix(in oklab, var(--spark-ink) 65%, transparent); }
  .card--spark-block .card__byline .who { color: var(--spark-ink); }
  .card--spark-block .card__byline .avatar { background: color-mix(in oklab, var(--spark-ink) 15%, transparent); color: var(--spark-ink); }

  .card--thread .card__format { color: var(--thread-ink); }

  .card--whatif-block { background: var(--whatif-tint); border-color: transparent; min-height: 260px; }
  .card--whatif-block .card__body { padding: 20px; justify-content: space-between; gap: 16px; }
  .card--whatif-block .card__format { color: var(--whatif-ink); }
  .card--whatif-block .card__title { font-size: 22px; line-height: 1.2; color: var(--whatif-ink); }
  .card--whatif-block .card__excerpt { color: color-mix(in oklab, var(--whatif-ink) 78%, transparent); }
  .card--whatif-block .card__byline { color: color-mix(in oklab, var(--whatif-ink) 65%, transparent); }
  .card--whatif-block .card__byline .who { color: var(--whatif-ink); }
  .card--whatif-block .card__byline .avatar { background: color-mix(in oklab, var(--whatif-ink) 15%, transparent); color: var(--whatif-ink); }

  .card--longread-block { background: var(--longread-tint); border-color: transparent; min-height: 260px; }
  .card--longread-block .card__body { padding: 20px; justify-content: space-between; gap: 16px; }
  .card--longread-block .card__format { color: var(--longread-ink); }
  .card--longread-block .card__title { font-size: 22px; line-height: 1.2; color: var(--longread-ink); }
  .card--longread-block .card__excerpt { color: color-mix(in oklab, var(--longread-ink) 78%, transparent); }
  .card--longread-block .card__byline { color: color-mix(in oklab, var(--longread-ink) 65%, transparent); }
  .card--longread-block .card__byline .who { color: var(--longread-ink); }
  .card--longread-block .card__byline .avatar { background: color-mix(in oklab, var(--longread-ink) 15%, transparent); color: var(--longread-ink); }

  .card--whatif .card__format { color: var(--whatif-ink); }
  .card--longread .card__format { color: var(--longread-ink); }
  .card--highlight .card__format { color: var(--highlight-ink); }

  .empty {
    grid-column: 1 / -1; padding: 40px 20px; text-align: center;
    color: var(--muted); font-size: 13px;
    border: 1px dashed var(--rule); border-radius: var(--radius);
  }
  .carousel .empty { grid-column: auto; min-width: 320px; }

  .url-badge {
    display: inline-flex; align-items: center; gap: 6px;
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    font-size: 11px; color: var(--muted);
    padding: 3px 8px; background: var(--surface-2);
    border: 1px solid var(--rule); border-radius: 4px;
  }

  .note { font-size: 12px; color: var(--faint); margin-top: -12px; margin-bottom: 16px; max-width: 62ch; }

  .stat {
    display: inline-flex; align-items: baseline; gap: 6px;
    font-size: 12px; color: var(--muted);
  }
  .stat b { color: var(--ink); font-weight: 600; font-variant-numeric: tabular-nums; }
</style>
</head>
<body>

<div class="top">
  <div class="brand">
    <span>Wove</span>
    <span class="dot">·</span>
    <span class="crumb">Mind</span>
    <span class="dot">·</span>
    <span class="path">/artefact-1</span>
  </div>
  <div class="top__right">
    <label class="switch" title="Show author &amp; date on every card">
      <input type="checkbox" id="bylineToggle" checked>
      <span class="track"></span>
      <span>Show author &amp; date</span>
    </label>
    <button class="icon-btn" id="themeToggle" title="Toggle theme" aria-label="Toggle theme">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
    </button>
  </div>
</div>

<main>
  <header class="page-head">
    <div class="eyebrow" style="margin-bottom: 10px;">Component preview · wired to /wove-mind</div>
    <h1>Wove Mind — card variations</h1>
    <p>Sample layouts of the four Mind formats — Spark, Thread, What if, Long read — pulled live from the CMS. Six sections below: three carousels and two filterable grids. Toggle bylines at the top; theme follows the OS unless overridden.</p>
    <p class="note" style="margin-top: 12px;">
      <span class="stat"><b><?= count($entries) ?></b> entries loaded</span>
      &nbsp;·&nbsp;
      <span class="stat"><b><?= count(array_filter($entries, fn($e) => $e['format'] === 'spark')) ?></b> sparks</span>
      &nbsp;·&nbsp;
      <span class="stat"><b><?= count(array_filter($entries, fn($e) => $e['format'] === 'thread')) ?></b> threads</span>
      &nbsp;·&nbsp;
      <span class="stat"><b><?= count(array_filter($entries, fn($e) => $e['format'] === 'whatif')) ?></b> what ifs</span>
      &nbsp;·&nbsp;
      <span class="stat"><b><?= count(array_filter($entries, fn($e) => $e['format'] === 'longread')) ?></b> long reads</span>
    </p>
  </header>

  <section class="block">
    <div class="block__head">
      <div><div class="eyebrow">01 · Carousel</div><h2 class="block__title">Sparks</h2></div>
      <div class="block__meta">Short-form · image overlay or coloured block</div>
    </div>
    <p class="note">Sparks have no title on the front end — the body <em>is</em> the card. Either text sits over the image with a gradient scrim, or it fills a tinted block when there's no image.</p>
    <div class="carousel" id="sparks-carousel"></div>
  </section>

  <section class="block">
    <div class="block__head">
      <div><div class="eyebrow">02 · Carousel</div><h2 class="block__title">Threads</h2></div>
      <div class="block__meta">Image + title, no excerpt</div>
    </div>
    <div class="carousel" id="threads-carousel"></div>
  </section>

  <section class="block">
    <div class="block__head">
      <div><div class="eyebrow">03 · Carousel</div><h2 class="block__title">Everything, mixed</h2></div>
      <div class="block__meta">All formats together, most recent first</div>
    </div>
    <p class="note">Same row, different card shapes. Format is marked by a small coloured dot and label; the shape does the rest of the work.</p>
    <div class="carousel" id="mixed-carousel"></div>
  </section>

  <section class="block">
    <div class="block__head">
      <div><div class="eyebrow">04 · Grid</div><h2 class="block__title">All formats, filterable</h2></div>
      <div class="block__meta"><span class="url-badge">/wove-mind</span></div>
    </div>
    <div class="filters" id="filters-all"></div>
    <div class="grid" id="grid-all"></div>
  </section>

  <section class="block">
    <div class="block__head">
      <div><div class="eyebrow">05 · Grid</div><h2 class="block__title">Threads only, filterable</h2></div>
      <div class="block__meta"><span class="url-badge">/wove-mind/threads</span></div>
    </div>
    <div class="filters" id="filters-threads"></div>
    <div class="grid" id="grid-threads"></div>
  </section>
</main>

<script>
  window.__ENTRIES__      = <?= json_encode($entries, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  window.__FILTER_TAGS__  = <?= json_encode($allTags, JSON_UNESCAPED_UNICODE) ?>;
  window.__FILTER_CASES__ = <?= json_encode($allCases, JSON_UNESCAPED_UNICODE) ?>;
  window.__FILTER_SVCS__  = <?= json_encode($allServices, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script>
  const entries = window.__ENTRIES__ || [];
  const TAGS          = window.__FILTER_TAGS__  || [];
  const CASE_STUDIES  = window.__FILTER_CASES__ || [];
  const SERVICES      = window.__FILTER_SVCS__  || [];

  function fmtDate(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    if (isNaN(d)) return '';
    return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
  }

  function esc(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  function byline(entry) {
    if (!entry.showByline) return '';
    if (!entry.author && !entry.date) return '';
    const initials = entry.initials || (entry.author ? entry.author.slice(0,1).toUpperCase() : '·');
    const parts = [];
    if (entry.author) parts.push(`<span class="who">${esc(entry.author)}</span>`);
    if (entry.author && entry.date) parts.push(`<span class="sep">·</span>`);
    if (entry.date) parts.push(`<span>${esc(fmtDate(entry.date))}</span>`);
    return `
      <div class="card__byline">
        <span class="avatar" aria-hidden="true">${esc(initials)}</span>
        ${parts.join('')}
      </div>`;
  }

  function formatLabel(entry) {
    const labels = {
      spark: 'Spark', thread: 'Thread',
      whatif: 'What if', longread: 'Long read',
      'project-highlight': 'Project'
    };
    return `<div class="card__format"><span class="swatch"></span>${esc(labels[entry.format] || entry.format)}</div>`;
  }

  function cardAttrs(entry, extraClass = '') {
    const cls = `card card--${entry.format}${entry.format === 'project-highlight' ? ' card--highlight' : ''} ${extraClass}`.trim();
    const href = entry.url ? ` onclick="window.location='${esc(entry.url)}'"` : '';
    return `class="${cls}" tabindex="0"${href} data-id="${esc(entry.id)}"`;
  }

  function renderCard(entry) {
    if (entry.format === 'spark') {
      const text = entry.body || entry.title || '';
      if (entry.image) {
        return `
          <article ${cardAttrs(entry, 'card--spark-image')}>
            <div class="card__media"><img src="${esc(entry.image)}" alt="" loading="lazy"></div>
            <div class="card__body">
              ${formatLabel(entry)}
              <p class="card__quote">${esc(text)}</p>
              ${byline(entry)}
            </div>
          </article>`;
      }
      return `
        <article ${cardAttrs(entry, 'card--spark-block')}>
          <div class="card__body">
            ${formatLabel(entry)}
            <p class="card__quote">${esc(text)}</p>
            ${byline(entry)}
          </div>
        </article>`;
    }

    if (entry.format === 'thread') {
      return `
        <article ${cardAttrs(entry)}>
          ${entry.image ? `<div class="card__media"><img src="${esc(entry.image)}" alt="" loading="lazy"></div>` : ''}
          <div class="card__body">
            ${formatLabel(entry)}
            <h3 class="card__title">${esc(entry.title)}</h3>
            <div style="flex:1"></div>
            ${byline(entry)}
          </div>
        </article>`;
    }

    // whatif / longread / project-highlight
    if (entry.image) {
      return `
        <article ${cardAttrs(entry)}>
          <div class="card__media"><img src="${esc(entry.image)}" alt="" loading="lazy"></div>
          <div class="card__body">
            ${formatLabel(entry)}
            <h3 class="card__title">${esc(entry.title)}</h3>
            ${entry.excerpt ? `<p class="card__excerpt">${esc(entry.excerpt)}</p>` : ''}
            <div style="flex:1"></div>
            ${byline(entry)}
          </div>
        </article>`;
    }
    const blockClass = entry.format === 'longread' ? 'card--longread-block' : 'card--whatif-block';
    return `
      <article ${cardAttrs(entry, blockClass)}>
        <div class="card__body">
          ${formatLabel(entry)}
          <h3 class="card__title">${esc(entry.title)}</h3>
          ${entry.excerpt ? `<p class="card__excerpt">${esc(entry.excerpt)}</p>` : ''}
          ${byline(entry)}
        </div>
      </article>`;
  }

  function emptyState(msg) {
    return `<div class="empty">${esc(msg)}</div>`;
  }

  // Carousels
  const sparks  = entries.filter(e => e.format === 'spark');
  const threads = entries.filter(e => e.format === 'thread');
  const mixed   = [...entries].sort((a, b) => (b.date || '').localeCompare(a.date || ''));

  document.getElementById('sparks-carousel').innerHTML  = sparks.length
    ? sparks.map(renderCard).join('') : emptyState('No sparks published yet.');
  document.getElementById('threads-carousel').innerHTML = threads.length
    ? threads.map(renderCard).join('') : emptyState('No threads published yet.');
  document.getElementById('mixed-carousel').innerHTML   = mixed.length
    ? mixed.map(renderCard).join('') : emptyState('No entries published yet.');

  // Filters + grids
  const state = {
    all:     { tag: null, caseStudy: null, service: null },
    threads: { tag: null, caseStudy: null, service: null },
  };

  function chipGroup(label, options, groupKey, stateKey) {
    if (!options.length) return '';
    return `
      <div class="filters__group">
        <span class="filters__label">${esc(label)}</span>
        <button class="chip" data-group="${groupKey}" data-state="${stateKey}" data-value="" aria-pressed="true">All</button>
        ${options.map(o => `<button class="chip" data-group="${groupKey}" data-state="${stateKey}" data-value="${esc(o)}" aria-pressed="false">${esc(o)}</button>`).join('')}
      </div>`;
  }

  function renderFilters(containerId, stateKey) {
    const el = document.getElementById(containerId);
    const html =
      chipGroup('Tag',        TAGS,         'tag',       stateKey) +
      chipGroup('Case study', CASE_STUDIES, 'caseStudy', stateKey) +
      chipGroup('Service',    SERVICES,     'service',   stateKey);
    el.innerHTML = html || '<div class="filters__label" style="color:var(--faint)">No filters available yet — add tags, case studies, or services to entries.</div>';

    el.querySelectorAll('.chip').forEach(chip => {
      chip.addEventListener('click', () => {
        const group = chip.dataset.group;
        const value = chip.dataset.value || null;
        state[stateKey][group] = value;
        el.querySelectorAll(`.chip[data-group="${group}"]`).forEach(c => {
          c.setAttribute('aria-pressed', (c.dataset.value || null) === value ? 'true' : 'false');
        });
        renderGrid(stateKey);
      });
    });
  }

  function matchesFilters(entry, s) {
    if (s.tag       && !(entry.tags || []).includes(s.tag)) return false;
    if (s.caseStudy && entry.caseStudy !== s.caseStudy) return false;
    if (s.service   && !(entry.services || []).includes(s.service)) return false;
    return true;
  }

  function renderGrid(stateKey) {
    const s = state[stateKey];
    const source = stateKey === 'threads' ? threads : entries;
    const list = source.filter(e => matchesFilters(e, s));
    const target = document.getElementById(stateKey === 'threads' ? 'grid-threads' : 'grid-all');
    target.innerHTML = list.length
      ? list.map(renderCard).join('')
      : `<div class="empty">No entries match these filters.</div>`;
  }

  renderFilters('filters-all',     'all');
  renderFilters('filters-threads', 'threads');
  renderGrid('all');
  renderGrid('threads');

  // Byline toggle
  const bylineToggle = document.getElementById('bylineToggle');
  bylineToggle.addEventListener('change', () => {
    document.body.dataset.byline = bylineToggle.checked ? 'on' : 'off';
  });
  document.body.dataset.byline = 'on';

  // Theme toggle
  document.getElementById('themeToggle').addEventListener('click', () => {
    const root = document.documentElement;
    const current = root.getAttribute('data-theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    let next;
    if (!current) next = prefersDark ? 'light' : 'dark';
    else if (current === 'dark') next = 'light';
    else next = 'dark';
    root.setAttribute('data-theme', next);
  });
</script>
</body>
</html>
