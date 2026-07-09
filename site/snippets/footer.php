<?php
/**
 * Global site footer — footer markup + closing tags
 * Usage: <?php snippet('footer') ?>
 * Lifted from the Labs service-page prototype (Pass 2) so every template
 * shares one footer. Only the copyright year is dynamic; everything else
 * matches the prototype markup. Closes the <body>/<html> opened in
 * header.php.
 */
?>

<footer class="footer">
  <div class="footer__inner container container--wide">
    <div class="footer__brand">
      <a href="/" class="footer__logo" aria-label="Wove, go to homepage">wove</a>
      <p class="footer__tagline">Strategic design that makes things work better, for everyone.</p>
      <img class="footer__bcorp" src="/assets/logos/bcorp.svg" alt="Certified B Corporation" width="80" height="72" loading="lazy">
    </div>
    <div class="footer__nav">
      <nav class="footer__col" aria-label="Services">
        <h2 class="footer__heading">Services</h2>
        <ul role="list">
          <li><a href="/services/strategy">Strategy</a></li>
          <li><a href="/services/digital">Digital</a></li>
          <li><a href="/services/labs">Labs</a></li>
          <li><a href="/services/brand">Brand</a></li>
        </ul>
      </nav>
      <nav class="footer__col" aria-label="Company">
        <h2 class="footer__heading">Company</h2>
        <ul role="list">
          <li><a href="/approach">Approach</a></li>
          <li><a href="/work">Work</a></li>
          <li><a href="/about">About us</a></li>
          <li><a href="/contact">Get in touch</a></li>
        </ul>
      </nav>
      <nav class="footer__col" aria-label="Connect">
        <h2 class="footer__heading">Connect</h2>
        <ul role="list" class="footer__social">
          <li><a href="https://www.linkedin.com/company/wove" target="_blank" rel="noopener noreferrer" aria-label="Wove on LinkedIn">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.45 20.45h-3.56v-5.57c0-1.33-.03-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.35V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.45v6.29zM5.34 7.43a2.06 2.06 0 1 1 0-4.13 2.06 2.06 0 0 1 0 4.13zM7.12 20.45H3.56V9h3.56v11.45zM22.22 0H1.77C.79 0 0 .77 0 1.72v20.56C0 23.23.79 24 1.77 24h20.45c.98 0 1.78-.77 1.78-1.72V1.72C24 .77 23.2 0 22.22 0z"/></svg>
          </a></li>
          <li><a href="https://www.instagram.com/wove" target="_blank" rel="noopener noreferrer" aria-label="Wove on Instagram">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41-.56-.22-.96-.48-1.38-.9-.42-.42-.68-.82-.9-1.38-.16-.42-.36-1.06-.41-2.23-.06-1.27-.07-1.65-.07-4.85s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.06-.36 2.23-.41 1.27-.06 1.65-.07 4.85-.07M12 0C8.74 0 8.33.01 7.05.07 5.78.13 4.9.33 4.14.63c-.79.3-1.46.72-2.13 1.38C1.35 2.68.93 3.35.63 4.14.33 4.9.13 5.78.07 7.05.01 8.33 0 8.74 0 12s.01 3.67.07 4.95c.06 1.27.26 2.15.56 2.91.3.79.72 1.46 1.38 2.13.67.66 1.34 1.08 2.13 1.38.76.3 1.64.5 2.91.56C8.33 23.99 8.74 24 12 24s3.67-.01 4.95-.07c1.27-.06 2.15-.26 2.91-.56.79-.3 1.46-.72 2.13-1.38.66-.67 1.08-1.34 1.38-2.13.3-.76.5-1.64.56-2.91.06-1.28.07-1.69.07-4.95s-.01-3.67-.07-4.95c-.06-1.27-.26-2.15-.56-2.91-.3-.79-.72-1.46-1.38-2.13-.67-.66-1.34-1.08-2.13-1.38-.76-.3-1.64-.5-2.91-.56C15.67.01 15.26 0 12 0z"/><path d="M12 5.84A6.16 6.16 0 1 0 18.16 12 6.16 6.16 0 0 0 12 5.84zM12 16a4 4 0 1 1 4-4 4 4 0 0 1-4 4z"/><circle cx="18.41" cy="5.59" r="1.44"/></svg>
          </a></li>
        </ul>
        <a href="mailto:hello@wove.group" class="footer__email">hello@wove.group</a>
      </nav>
    </div>
  </div>
  <div class="footer__bar container container--wide">
    <p class="footer__legal">&copy; <?= date('Y') ?> Wove. Ireland's first BCorp design agency.</p>
    <ul class="footer__meta" role="list">
      <li><a href="/privacy">Privacy</a></li>
      <li><a href="/terms">Terms</a></li>
    </ul>
  </div>
</footer>

</body>
</html>
