<?php
/**
 * Global site header — doctype/head + skip link + main nav
 * Usage: <?php snippet('header') ?>
 * Lifted from the Labs service-page prototype (Pass 2) so every template
 * shares one nav. `aria-current="page"` is set dynamically by comparing
 * $page's id against each link's page id, rather than hardcoding it per
 * page. The ids differ from the hrefs where a route renames a page
 * (/our-work renders page('work')).
 * The current item doubles as the page title (styled larger in site.css),
 * and `nav--has-current` mutes the other links. Each item has a
 * view-transition-name, so browsers with cross-document view transitions
 * animate it between the link size and the title size on navigation.
 * Pairs with footer.php, which closes </body></html> — no template
 * currently opens its own doctype/head, so this snippet owns that shell
 * (matches wovemind.php / wovemind-post.php, which already assumed it).
 */

$navItems = [
  'work'       => ['href' => '/our-work',   'label' => 'Our Work'],
  'our-people' => ['href' => '/our-people', 'label' => 'Our People'],
];
$ctaId     = 'contact';
$currentId = $page->id();
$hasCurrent = isset($navItems[$currentId]) || $currentId === $ctaId;
$isCurrent = fn ($id) => $currentId === $id ? ' aria-current="page"' : '';

// SEO tab fields (site/blueprints/tabs/seo.yml) — all optional, sensible fallbacks.
$seoTitle       = $page->seoTitle()->or($page->title())->value();
$seoDescription = $page->seoDescription();
$robotsFlags    = $page->robots()->split(',');
$robotsContent  = implode(', ', array_filter([
  in_array('noindex', $robotsFlags) ? 'noindex' : 'index',
  in_array('nofollow', $robotsFlags) ? 'nofollow' : 'follow',
  in_array('nosnippet', $robotsFlags) ? 'nosnippet' : null,
]));
$ogType = $page->ogtype()->or('website')->value();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script>document.documentElement.className += ' js';</script>

<title><?= html($seoTitle) ?></title>
<?php if ($seoDescription->isNotEmpty()): ?>
  <meta name="description" content="<?= $seoDescription->html() ?>">
<?php endif ?>
<meta name="robots" content="<?= html($robotsContent) ?>">
<link rel="canonical" href="<?= $page->url() ?>">

<meta property="og:title" content="<?= html($seoTitle) ?>">
<?php if ($seoDescription->isNotEmpty()): ?>
  <meta property="og:description" content="<?= $seoDescription->html() ?>">
<?php endif ?>
<meta property="og:type" content="<?= html($ogType) ?>">
<meta property="og:url" content="<?= $page->url() ?>">
<meta property="og:site_name" content="<?= $site->title()->html() ?>">
<meta name="twitter:card" content="summary_large_image">

<link rel="preload" href="/assets/fonts/Ballinger-Regular.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/Ballinger-X-Bold.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/assets/css/site.css">
<link rel="stylesheet" href="/assets/css/feed.css">
<?php if (isset($css)): foreach ((array) $css as $href): ?>
  <?php if ($href !== '/assets/css/feed.css'): ?>
    <link rel="stylesheet" href="<?= $href ?>">
  <?php endif ?>
<?php endforeach; endif ?>
</head>
<body class="tpl-<?= $page->template()->name() ?>">

<a href="#main" class="skip-link">Skip to main content</a>

<!-- NAV -->
<nav class="nav<?= $hasCurrent ? ' nav--has-current' : '' ?>" aria-label="Main navigation">
  <a href="/" class="nav__logo" aria-label="Wove, go to homepage">wove</a>
  <ul class="nav__links" role="list">
    <?php foreach ($navItems as $id => $item): ?>
      <li><a href="<?= $item['href'] ?>" style="view-transition-name: nav-<?= $id ?>"<?= $isCurrent($id) ?>><?= $item['label'] ?></a></li>
    <?php endforeach ?>
  </ul>
  <a href="/contact" class="nav__cta" style="view-transition-name: nav-<?= $ctaId ?>"<?= $isCurrent($ctaId) ?>>Get in touch</a>
</nav>
