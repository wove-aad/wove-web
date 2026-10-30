<?php
/**
 * Global site header — doctype/head + skip link + main nav
 * Usage: <?php snippet('header') ?>
 * Pass ['nav' => false] to leave out the main nav (the homepage hero carries
 * its own logo, links and contact button).
 * Lifted from the Labs service-page prototype (Pass 2) so every template
 * shares one nav. `aria-current="page"` is set dynamically by comparing
 * $page's id against each link's page id, rather than hardcoding it per
 * page. The ids differ from the hrefs where a route renames a page
 * (/our-work renders page('work')).
 * The current item doubles as the page title (semibold in site.css), and
 * `nav--has-current` mutes the other links.
 * Pairs with footer.php, which closes </body></html> — no template
 * currently opens its own doctype/head, so this snippet owns that shell
 * (matches wovemind.php / wovemind-post.php, which already assumed it).
 */

$navItems = [
  'work'       => ['href' => '/#feed',      'label' => 'Our Work'],
  'our-people' => ['href' => '/our-people', 'label' => 'Our People'],
];
$ctaId     = 'contact';
$currentId = $page->id();
$hasCurrent = isset($navItems[$currentId]) || $currentId === $ctaId;
$isCurrent = fn ($id) => $currentId === $id ? ' aria-current="page"' : '';

// SEO tab fields (site/blueprints/tabs/seo.yml) — all optional, sensible fallbacks.
// A meta title equal to the slug is treated as empty: Wove Mind entries
// used to be created with the `{{ page.title }}` default resolved to it.
// Cast: a page with no stored meta title gives null, which would skip the fallback.
$seoTitle       = (string) $page->seoTitle()->value();
$seoTitle       = $seoTitle === $page->slug() ? '' : $seoTitle;
$seoTitle       = $seoTitle !== '' ? $seoTitle : $page->title()->value();
$seoDescription = $page->seoDescription();
// Wove Mind entries fall back to the start of the post, matching the
// search preview in the Panel editor (site/plugins/wove-mind).
if ($seoDescription->isEmpty() && $page->intendedTemplate()->name() === 'wove-mind-entry') {
    $source = $page->excerpt()->or($page->body());
    $seoDescription = new Kirby\Content\Field($page, 'seoDescription',
        $source->isNotEmpty() ? $source->excerpt(160) : $page->blocks()->toBlocks()->excerpt(160));
}
// Pages rendered for something else through a route (the impact area
// pages render page('tag')) pass their own title, description and address.
if (isset($metaTitle) && $metaTitle !== '') $seoTitle = $metaTitle;
if (isset($metaDescription) && $metaDescription !== '') $seoDescription = new Kirby\Content\Field($page, 'seoDescription', $metaDescription);
$pageUrl = $metaUrl ?? $page->url();
$robotsFlags    = $page->robots()->split(',');
$robotsContent  = implode(', ', array_filter([
  in_array('noindex', $robotsFlags) ? 'noindex' : 'index',
  in_array('nofollow', $robotsFlags) ? 'nofollow' : 'follow',
  in_array('nosnippet', $robotsFlags) ? 'nosnippet' : null,
]));
$ogType = $page->ogtype()->or('website')->value();

// Recently viewed (2026-10-30, assets/js/recent.js): client, service,
// impact area and post pages record themselves in the visitor's browser
// (title, kind, a small image); index pages and the homepage don't.
$recent = null;
$tpl    = $page->intendedTemplate()->name();
if ($tpl === 'case-study') {
  $img    = $page->caseStudyImages()->toFile();
  $recent = ['kind' => 'Client', 'title' => $page->eyebrow()->or($page->title())->value(), 'img' => $img];
} elseif (preg_match('#/services/(labs|strategy|brand|digital)$#', $page->url(), $m)) {
  // Service pages render through the services/(:any) route, sometimes
  // with a stand-in page, so they're known by their address
  $recent = ['kind' => 'Service', 'title' => wove_service_labels()[$m[1]], 'img' => $page->content()->get('image')->toFile()];
} elseif ($tpl === 'tag' && isset($metaTitle) && $metaTitle !== '') {
  $recent = ['kind' => 'Impact area', 'title' => $metaTitle, 'img' => null];
} elseif ($tpl === 'wove-mind-entry') {
  $types  = ['project' => 'Project', 'thinking' => 'Thinking', 'news' => 'News'];
  $recent = ['kind' => $types[wove_post_type($page)] ?? 'Post', 'title' => $page->title()->value(), 'img' => $page->content()->get('image')->toFile()];
}
$recentAttrs = $recent
  ? ' data-rv-title="' . html($recent['title']) . '" data-rv-kind="' . html($recent['kind']) . '" data-rv-url="' . html($pageUrl) . '"'
    . ($recent['img'] ? ' data-rv-img="' . html($recent['img']->crop(96, 96)->url()) . '"' : '')
  : '';
?>
<!DOCTYPE html>
<html lang="en" data-menu="c">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script>document.documentElement.className += ' js';</script>

<title><?= html($seoTitle) ?></title>
<?php if ($seoDescription->isNotEmpty()): ?>
  <meta name="description" content="<?= $seoDescription->html() ?>">
<?php endif ?>
<meta name="robots" content="<?= html($robotsContent) ?>">
<link rel="canonical" href="<?= $pageUrl ?>">

<meta property="og:title" content="<?= html($seoTitle) ?>">
<?php if ($seoDescription->isNotEmpty()): ?>
  <meta property="og:description" content="<?= $seoDescription->html() ?>">
<?php endif ?>
<meta property="og:type" content="<?= html($ogType) ?>">
<meta property="og:url" content="<?= $pageUrl ?>">
<meta property="og:site_name" content="<?= $site->title()->html() ?>">
<meta name="twitter:card" content="summary_large_image">

<link rel="preload" href="/assets/fonts/Ballinger-Regular.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/Ballinger-Bold.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/Ballinger-X-Bold.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= wove_asset('/assets/css/site.css') ?>">
<link rel="stylesheet" href="<?= wove_asset('/assets/css/feed.css') ?>">
<link rel="stylesheet" href="<?= wove_asset('/assets/css/footer.css') ?>">
<?php if (isset($css)): foreach ((array) $css as $href): ?>
  <?php if ($href !== '/assets/css/feed.css'): ?>
    <link rel="stylesheet" href="<?= wove_asset($href) ?>">
  <?php endif ?>
<?php endforeach; endif ?>
<script src="<?= wove_asset('/assets/js/nav.js') ?>" defer></script>
<script src="<?= wove_asset('/assets/js/feed.js') ?>" defer></script>
</head>
<body class="tpl-<?= $page->template()->name() ?>"<?= $recentAttrs ?>>

<a href="#main" class="skip-link">Skip to main content</a>

<?php if ($nav ?? true): ?>
<!-- NAV -->
<nav class="nav<?= $hasCurrent ? ' nav--has-current' : '' ?>" aria-label="Main navigation">
  <a href="/" class="nav__logo" aria-label="Wove, go to homepage">wove</a>
  <ul class="nav__links" role="list">
    <?php foreach ($navItems as $id => $item): ?>
      <li><a href="<?= $item['href'] ?>"<?= $isCurrent($id) ?>><?= $item['label'] ?></a></li>
    <?php endforeach ?>
  </ul>
  <a href="/contact" class="nav__cta"<?= $isCurrent($ctaId) ?>>Get in touch</a>
</nav>
<?php endif ?>
