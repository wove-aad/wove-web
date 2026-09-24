<?php
/**
 * Global site footer — 4-column info footer + legal bar
 * Uses the feed design system (dark-first, time-of-day light mode).
 *
 * Col 1: Company statement, Wove wordmark, tagline
 * Col 2: Position statements (sustainability, AI, employees)
 * Col 3: Navigation links
 * Col 4: Trust logos + latest Wove Mind link
 */

$wmParent = $site->find('wove-mind');
$latestWm = $wmParent
  ? $wmParent->children()->listed()->sortBy('date', 'desc')->first()
  : null;
?>

<footer class="site-footer" role="contentinfo">
  <div class="site-footer__main">

    <div class="site-footer__col site-footer__col--about">
      <p class="site-footer__statement">Over 20 years partnering with clients across the civic, cultural and independent business sectors</p>
      <a href="/" class="site-footer__wordmark" aria-label="Wove, go to homepage">wove</a>
      <p class="site-footer__sub">Strategic Design &amp; Technology</p>
    </div>

    <div class="site-footer__col site-footer__col--positions">
      <p class="site-footer__position">We believe in designing for a sustainable, equitable future.</p>
      <p class="site-footer__position">We use AI as a tool for augmentation, not replacement.</p>
      <p class="site-footer__position">We invest in our people and build teams that reflect the communities we serve.</p>
    </div>

    <nav class="site-footer__col site-footer__col--nav" aria-label="Footer navigation">
      <a href="/our-work" class="site-footer__nav-link">Our Work</a>
      <a href="/our-people" class="site-footer__nav-link">Our Team</a>
      <a href="/contact" class="site-footer__nav-link">Get in Touch</a>
    </nav>

    <div class="site-footer__col site-footer__col--trust">
      <div class="site-footer__trust-logos">
        <div class="site-footer__trust-logo" aria-label="Certified B Corporation">
          <span class="site-footer__trust-placeholder">B Corp</span>
        </div>
        <div class="site-footer__trust-logo" aria-label="Design Declares">
          <span class="site-footer__trust-placeholder">Design Declares</span>
        </div>
      </div>
      <?php if ($latestWm): ?>
        <a href="<?= $latestWm->url() ?>" class="site-footer__wm-link">Latest from Wove Mind &rarr;</a>
      <?php endif ?>
    </div>

  </div>

  <div class="site-footer__bar">
    <span class="site-footer__brand"><?= $site->title()->html() ?> &mdash; Strategic Design &amp; Technology</span>
    <div class="site-footer__links">
      <a href="/contact#enquiries" class="site-footer__link">Enquiries</a>
      <a href="/contact#tenders" class="site-footer__link">Tenders</a>
      <a href="/contact#strategy" class="site-footer__link">Strategy</a>
      <a href="/privacy" class="site-footer__link">Privacy</a>
    </div>
    <span>&copy; <?= date('Y') ?></span>
  </div>
</footer>

</body>
</html>
