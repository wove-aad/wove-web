<?php
/**
 * Global site header — doctype/head + skip link + main nav
 * Usage: <?php snippet('header') ?>
 * Lifted from the Labs service-page prototype (Pass 2) so every template
 * shares one nav. `aria-current="page"` is set dynamically by comparing
 * $page's URI against each link, rather than hardcoding it per page.
 * Pairs with footer.php, which closes </body></html> — no template
 * currently opens its own doctype/head, so this snippet owns that shell
 * (matches wovemind.php / wovemind-post.php, which already assumed it).
 */

$uri = '/' . $page->uri();
$isCurrent = fn ($path) => $uri === $path ? ' aria-current="page"' : '';

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

<link rel="stylesheet" href="/assets/css/site.css">
</head>
<body>

<a href="#main" class="skip-link">Skip to main content</a>

<!-- NAV -->
<div class="nav-wrap">
  <nav class="nav" aria-label="Main navigation">
    <button class="nav__toggle" aria-label="Open menu" aria-expanded="false" aria-controls="mobile-menu">
      <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="7" x2="21" y2="7"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="17" x2="21" y2="17"/></svg>
    </button>
    <a href="/" class="nav__logo" aria-label="Wove, go to homepage">wove</a>
    <ul class="nav__links" role="list">
      <li class="nav__has-dropdown">
        <button class="nav__dropbtn" aria-haspopup="true" aria-expanded="false" aria-controls="menu-services">
          Services
          <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <ul class="nav__dropdown" id="menu-services" role="list">
          <li><a href="/services/labs"<?= $isCurrent('/services/labs') ?>>Labs</a></li>
          <li><a href="/services/strategy"<?= $isCurrent('/services/strategy') ?>>Strategy</a></li>
          <li><a href="/services/brand"<?= $isCurrent('/services/brand') ?>>Brand</a></li>
          <li><a href="/services/digital"<?= $isCurrent('/services/digital') ?>>Digital</a></li>
        </ul>
      </li>
      <li><a href="/approach"<?= $isCurrent('/approach') ?>>Approach</a></li>
      <li><a href="/work"<?= $isCurrent('/work') ?>>Work</a></li>
      <li><a href="/about"<?= $isCurrent('/about') ?>>About us</a></li>
      <li><a href="/wove-mind" class="nav__link--icon"<?= $isCurrent('/wove-mind') ?>>Wove Mind <img src="/assets/icons/wove-mind.png" alt="" aria-hidden="true"></a></li>
    </ul>
    <a href="/contact" class="nav__cta">Get in touch</a>
    <a href="/contact" class="nav__cta-icon" aria-label="Get in touch">
      <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 6l10 7 10-7"/></svg>
    </a>
  </nav>
  <div class="nav__menu" id="mobile-menu" hidden>
    <ul role="list">
      <li>
        <button class="nav__menu-sub" aria-expanded="false" aria-controls="mobile-services">
          Services
          <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <ul class="nav__submenu" id="mobile-services" role="list" hidden>
          <li><a href="/services/labs"<?= $isCurrent('/services/labs') ?>>Labs</a></li>
          <li><a href="/services/strategy"<?= $isCurrent('/services/strategy') ?>>Strategy</a></li>
          <li><a href="/services/brand"<?= $isCurrent('/services/brand') ?>>Brand</a></li>
          <li><a href="/services/digital"<?= $isCurrent('/services/digital') ?>>Digital</a></li>
        </ul>
      </li>
      <li><a href="/approach"<?= $isCurrent('/approach') ?>>Approach</a></li>
      <li><a href="/work"<?= $isCurrent('/work') ?>>Work</a></li>
      <li><a href="/about"<?= $isCurrent('/about') ?>>About us</a></li>
      <li><a href="/wove-mind" class="nav__menu-icon"<?= $isCurrent('/wove-mind') ?>>Wove Mind <img src="/assets/icons/wove-mind.png" alt="" aria-hidden="true"></a></li>
      <li><a href="/contact">Get in touch</a></li>
    </ul>
  </div>
</div>

<script>
  /* Hide header on scroll down, reveal on scroll up */
  (function () {
    var nav = document.querySelector('.nav-wrap');
    if (!nav) return;
    var menu = document.getElementById('mobile-menu');
    var toggle = document.querySelector('.nav__toggle');
    var last = window.scrollY;
    var threshold = 8;
    window.addEventListener('scroll', function () {
      var y = window.scrollY;
      if (Math.abs(y - last) < threshold) return;
      if (y > last && y > nav.offsetHeight) {
        nav.classList.add('nav-wrap--hidden');
        if (menu && !menu.hidden) {
          menu.hidden = true;
          if (toggle) { toggle.setAttribute('aria-expanded', 'false'); toggle.setAttribute('aria-label', 'Open menu'); }
        }
      } else {
        nav.classList.remove('nav-wrap--hidden');
      }
      last = y;
    }, { passive: true });
  })();

  /* Mobile menu toggle */
  (function () {
    var toggle = document.querySelector('.nav__toggle');
    var menu = document.getElementById('mobile-menu');
    if (!toggle || !menu) return;
    function setOpen(open) {
      toggle.setAttribute('aria-expanded', String(open));
      toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
      menu.hidden = !open;
    }
    toggle.addEventListener('click', function () {
      setOpen(toggle.getAttribute('aria-expanded') !== 'true');
    });
    menu.addEventListener('click', function (e) {
      if (e.target.closest('a')) setOpen(false);
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
        setOpen(false);
        toggle.focus();
      }
    });
  })();

  /* Desktop Services dropdown */
  (function () {
    var btn = document.querySelector('.nav__dropbtn');
    if (!btn) return;
    btn.addEventListener('click', function () {
      btn.setAttribute('aria-expanded', String(btn.getAttribute('aria-expanded') !== 'true'));
    });
    document.addEventListener('click', function (e) {
      if (!e.target.closest('.nav__has-dropdown')) btn.setAttribute('aria-expanded', 'false');
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') btn.setAttribute('aria-expanded', 'false');
    });
  })();

  /* Mobile Services submenu */
  (function () {
    var sub = document.querySelector('.nav__menu-sub');
    var list = document.getElementById('mobile-services');
    if (!sub || !list) return;
    sub.addEventListener('click', function () {
      var open = sub.getAttribute('aria-expanded') === 'true';
      sub.setAttribute('aria-expanded', String(!open));
      list.hidden = open;
    });
  })();
</script>
