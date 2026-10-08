<?php
/**
 * Contact page: three routes, each with its own form
 * File: site/templates/contact.php
 * Blueprint: site/blueprints/pages/contact.yml
 *
 * Discuss a project, Invite us to tender, Book a strategy conversation
 * (with the named strategy partner), then the studio's email, phone and
 * address from the site's contact details. Submissions are checked and
 * emailed by site/controllers/contact.php, which passes $sent (the form just
 * sent), $error and $errorType (a form to show again with its values) and
 * $data (those values).
 */

$partner = $page->strategy_partner()->toUser();

// Hidden fields: which form, Kirby's CSRF token, and a field people leave
// empty (bots fill it in). Shows this form's error, if any.
$hidden = function (string $type) use ($error, $errorType) { ?>
  <input type="hidden" name="form_type" value="<?= $type ?>">
  <input type="hidden" name="csrf" value="<?= csrf() ?>">
  <div class="contact-form__trap" aria-hidden="true">
    <label for="<?= $type ?>-website">Leave this empty</label>
    <input type="text" id="<?= $type ?>-website" name="website" tabindex="-1" autocomplete="off">
  </div>
  <?php if ($errorType === $type && $error): ?>
    <p class="contact-form__error" role="alert"><?= html($error) ?></p>
  <?php endif ?>
<?php };

// A labelled field, keeping what was typed when the form comes back with an error.
$field = function (string $type, string $name, string $label, string $kind = 'text', bool $required = false, string $placeholder = '') use ($errorType, $data) {
  $id    = $type . '-' . str_replace('_', '-', $name);
  $value = $errorType === $type ? ($data[$name] ?? '') : '';
  $attrs = ' id="' . $id . '" name="' . $name . '"' . ($required ? ' required' : '') . ($placeholder ? ' placeholder="' . html($placeholder) . '"' : '');
?>
  <div class="contact-form__group">
    <label class="contact-form__label" for="<?= $id ?>"><?= html($label) ?><?php if ($required): ?> <span class="contact-form__req" aria-hidden="true">*</span><?php endif ?></label>
    <?php if ($kind === 'textarea'): ?>
      <textarea class="contact-form__textarea"<?= $attrs ?> rows="3"><?= html($value) ?></textarea>
    <?php else: ?>
      <input class="contact-form__input" type="<?= $kind ?>"<?= $attrs ?> value="<?= html($value) ?>"<?= $kind === 'email' ? ' autocomplete="email"' : ($name === 'name' ? ' autocomplete="name"' : ($name === 'organisation' ? ' autocomplete="organization"' : '')) ?>>
    <?php endif ?>
  </div>
<?php };
$address = array_filter([
  $site->addrLine1()->value(),
  $site->addrLine2()->value(),
  trim($site->addrLocality()->value() . ' ' . $site->addrPostalcode()->value()),
  $site->addrCountry()->value(),
]);
?>

<?php snippet('header', ['css' => ['/assets/css/home.css', '/assets/css/pages.css'], 'nav' => false]) ?>

<div class="home" data-theme="light">

  <header class="hx hx--page">
    <div class="hx__inner">
      <?php snippet('brand/header-row', ['current' => 'contact']) ?>
      <div class="hx-body">
        <h1 class="ph__title"><?= $page->headline()->or('Get in touch')->html() ?></h1>
        <p class="ph__intro"><?= $page->intro()->or('Whether you\'re exploring a project, responding to a tender, or looking for strategic advice, start here.')->html() ?></p>
      </div>
    </div>
  </header>

  <div class="feed-wrap">
    <?php snippet('brand/menu-bar') ?>

    <main class="pg" id="main">

      <section class="pg-section" aria-label="Ways to get in touch">
        <div class="contact-routes">

          <!-- Project enquiry -->
          <div class="contact-route" id="enquiries">
            <h2 class="contact-card__heading"><?= $page->enquiry_heading()->or('Discuss a project')->html() ?></h2>
            <p class="contact-card__desc"><?= $page->enquiry_desc()->or('Tell us about what you\'re working on and we\'ll get back to you.')->html() ?></p>
            <?php if ($sent === 'enquiry'): ?>
              <p class="contact-form__done" role="status">Thanks, your enquiry is with us. We'll get back to you soon.</p>
            <?php else: ?>
              <form class="contact-form" method="post" action="<?= $page->url() ?>#enquiries">
                <?php $hidden('enquiry') ?>
                <?php $field('enquiry', 'name', 'Your name', 'text', true) ?>
                <?php $field('enquiry', 'email', 'Your email', 'email', true) ?>
                <?php $field('enquiry', 'organisation', 'Organisation') ?>
                <?php $field('enquiry', 'scope', 'Scope note', 'textarea') ?>
                <?php $field('enquiry', 'timeline', 'Timeline', 'text', false, 'e.g. Q1 2027') ?>
                <button class="pg-btn contact-form__submit" type="submit">Send enquiry</button>
              </form>
            <?php endif ?>
          </div>

          <!-- Tender invite -->
          <div class="contact-route" id="tenders">
            <h2 class="contact-card__heading"><?= $page->tender_heading()->or('Invite us to tender')->html() ?></h2>
            <p class="contact-card__desc"><?= $page->tender_desc()->or('Share the details of your RFT and we\'ll confirm receipt within one working day.')->html() ?></p>
            <?php if ($sent === 'tender'): ?>
              <p class="contact-form__done" role="status">Thanks, your tender invite is with us. <?= $page->tender_ack()->or('We\'ll confirm receipt within one working day.')->html() ?></p>
            <?php else: ?>
              <form class="contact-form" method="post" action="<?= $page->url() ?>#tenders">
                <?php $hidden('tender') ?>
                <?php $field('tender', 'email', 'Your email', 'email', true) ?>
                <?php $field('tender', 'rft_reference', 'RFT reference') ?>
                <div class="contact-form__group">
                  <label class="contact-form__label" for="tender-portal">Portal</label>
                  <select class="contact-form__select" id="tender-portal" name="portal">
                    <?php foreach (['' => 'Select portal', 'etenders' => 'eTenders', 'supplygov' => 'SupplyGov', 'ojeu' => 'OJEU', 'other' => 'Other'] as $value => $label): ?>
                      <option value="<?= $value ?>"<?= $errorType === 'tender' && ($data['portal'] ?? '') === $value ? ' selected' : '' ?>><?= $label ?></option>
                    <?php endforeach ?>
                  </select>
                </div>
                <?php $field('tender', 'closing_date', 'Closing date', 'date') ?>
                <?php $field('tender', 'framework', 'Framework') ?>
                <?php $field('tender', 'note', 'Note', 'textarea') ?>
                <button class="pg-btn contact-form__submit" type="submit">Submit tender invite</button>
                <p class="contact-form__ack"><?= $page->tender_ack()->or('We\'ll confirm receipt within one working day.')->html() ?></p>
              </form>
            <?php endif ?>
          </div>

          <!-- Strategy conversation -->
          <div class="contact-route" id="strategy">
            <h2 class="contact-card__heading"><?= $page->strategy_heading()->or('Book a strategy conversation')->html() ?></h2>
            <p class="contact-card__desc"><?= $page->strategy_desc()->or('A 30-minute conversation with our strategy lead to explore how we might help.')->html() ?></p>
            <?php if ($partner): ?>
              <div class="contact-partner">
                <div class="contact-partner__avatar">
                  <?php if ($avatar = $partner->avatar()): ?>
                    <?php snippet('picture', ['file' => $avatar, 'widths' => [48, 96], 'ratio' => 1, 'sizes' => '48px']) ?>
                  <?php endif ?>
                </div>
                <div>
                  <div class="contact-partner__name"><?= $partner->name()->html() ?></div>
                  <div class="contact-partner__role">Strategy Lead</div>
                </div>
              </div>
              <?php if ($page->strategy_partner_intro()->isNotEmpty()): ?>
                <p class="contact-partner__intro"><?= $page->strategy_partner_intro()->html() ?></p>
              <?php endif ?>
            <?php endif ?>
            <?php if ($sent === 'strategy'): ?>
              <p class="contact-form__done" role="status">Thanks, we have your request. <?= $page->strategy_response()->or('We\'ll be in touch within 48 hours.')->html() ?></p>
            <?php else: ?>
              <form class="contact-form" method="post" action="<?= $page->url() ?>#strategy">
                <?php $hidden('strategy') ?>
                <?php $field('strategy', 'name', 'Your name', 'text', true) ?>
                <?php $field('strategy', 'email', 'Your email', 'email', true) ?>
                <?php $field('strategy', 'organisation', 'Organisation') ?>
                <button class="pg-btn contact-form__submit" type="submit">Request a conversation</button>
              </form>
              <p class="contact-partner__response"><?= $page->strategy_response()->or('We\'ll be in touch within 48 hours.')->html() ?></p>
            <?php endif ?>
          </div>

        </div>
      </section>

      <?php if ($site->email()->isNotEmpty() || $site->phone()->isNotEmpty() || $address): ?>
        <section class="pg-section contact-direct" aria-label="Contact details">
          <?php if ($site->email()->isNotEmpty()): ?>
            <div><p class="contact-direct__label">Email</p><a href="mailto:<?= $site->email()->html() ?>"><?= $site->email()->html() ?></a></div>
          <?php endif ?>
          <?php if ($site->phone()->isNotEmpty()): ?>
            <div><p class="contact-direct__label">Phone</p><a href="tel:<?= html(preg_replace('/[^0-9+]/', '', $site->phone()->value())) ?>"><?= $site->phone()->html() ?></a></div>
          <?php endif ?>
          <?php if ($address): ?>
            <div><p class="contact-direct__label">Studio</p><address><?= implode('<br>', array_map('html', $address)) ?></address></div>
          <?php endif ?>
        </section>
      <?php endif ?>

    </main>
  </div>
</div>

<script src="<?= wove_asset('/assets/js/pages.js') ?>" defer></script>
<?php snippet('footer') ?>
