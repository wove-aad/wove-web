<?php
/**
 * Global site footer, through to the closing </body></html>.
 * Styles: assets/css/footer.css (loaded on every page by header.php).
 *
 * A prompt to get in touch, then four columns: the site links and latest
 * Wove Mind post, the services, the studio's contact details (from the
 * site's Contact / Social tab) and what we stand for, then a bar with the
 * copyright, privacy and social links.
 */

$latest  = ($wm = $site->find('wove-mind'))
  ? $wm->children()->listed()->sortBy('date', 'desc')->first()
  : null;
$address = array_filter([
  $site->addrLine1()->value(),
  $site->addrLine2()->value(),
  trim($site->addrLocality()->value() . ' ' . $site->addrPostalcode()->value()),
  $site->addrCountry()->value(),
]);
$email   = $site->email()->or('hello@wove.group')->value();
$company = $site->companyName()->or($site->title())->value();
?>

<footer class="ft" role="contentinfo">
  <div class="ft__inner">

    <div class="ft__lead">
      <p class="ft__statement">Over 20 years partnering with clients across the civic, cultural and independent business sectors.</p>
      <div class="ft__cta">
        <a class="ft__btn" href="<?= url('contact') ?>">Get in touch <span aria-hidden="true">&rarr;</span></a>
        <a class="ft__email" href="mailto:<?= html($email) ?>"><?= html($email) ?></a>
      </div>
    </div>

    <div class="ft__cols">
      <nav class="ft__col" aria-label="Footer">
        <p class="ft__label">Explore</p>
        <ul role="list">
          <li><a href="<?= wove_work_url() ?>">Our work</a></li>
          <li><a href="<?= url('our-people') ?>">Our people</a></li>
          <li><a href="<?= url('contact') ?>">Get in touch</a></li>
          <?php if ($latest): ?><li><a href="<?= $latest->url() ?>">Latest from Wove Mind</a></li><?php endif ?>
        </ul>
      </nav>

      <nav class="ft__col" aria-label="Services">
        <p class="ft__label">Services</p>
        <ul role="list">
          <?php foreach (wove_service_labels() as $slug => $label): ?>
            <li><a href="<?= url('services/' . $slug) ?>"><?= html($label) ?></a></li>
          <?php endforeach ?>
        </ul>
      </nav>

      <div class="ft__col">
        <p class="ft__label">Studio</p>
        <?php if ($address): ?><address><?= implode('<br>', array_map('html', $address)) ?></address><?php endif ?>
        <?php if ($site->phone()->isNotEmpty()): ?>
          <p><a href="tel:<?= html(preg_replace('/[^0-9+]/', '', $site->phone()->value())) ?>"><?= $site->phone()->html() ?></a></p>
        <?php endif ?>
        <p class="ft__routes">
          <a href="<?= url('contact') ?>#enquiries">Enquiries</a>
          <a href="<?= url('contact') ?>#tenders">Tenders</a>
        </p>
      </div>

      <div class="ft__col ft__col--positions">
        <p class="ft__label">What we stand for</p>
        <ul role="list">
          <li>Designing for a sustainable, equitable future.</li>
          <li>AI as a tool for augmentation, not replacement.</li>
          <li>Investing in our people, and teams that reflect the communities we serve.</li>
        </ul>
        <div class="ft__marks">
          <span class="ft__mark" aria-label="Certified B Corporation">B Corp</span>
          <span class="ft__mark" aria-label="Design Declares">Design Declares</span>
        </div>
      </div>
    </div>

    <div class="ft__bar">
      <a href="<?= url() ?>" class="ft__logo"><?php snippet('brand/logo') ?></a>
      <span>&copy; <?= date('Y') ?> <?= html($company) ?>. Strategic Design &amp; Technology</span>
      <span class="ft__bar-links">
        <?php foreach ($site->social()->toStructure() as $social): ?>
          <a href="<?= $social->url()->html() ?>" rel="noopener"><?= $social->platform()->html() ?></a>
        <?php endforeach ?>
        <a href="<?= url('privacy') ?>">Privacy</a>
      </span>
    </div>

  </div>
</footer>

</body>
</html>
