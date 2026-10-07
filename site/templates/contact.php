<?php
/**
 * Contact page: three routes, each with its own form
 * File: site/templates/contact.php
 * Blueprint: site/blueprints/pages/contact.yml
 *
 * Discuss a project, Invite us to tender, Book a strategy conversation
 * (with the named strategy partner), then the studio's email, phone and
 * address from the site's contact details. The forms post to /contact as
 * before; nothing handles them yet.
 */

$partner = $page->strategy_partner()->toUser();
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

        <!-- CARD 1: Project Enquiry -->
        <div class="contact-route" id="enquiries">
          <h2 class="contact-card__heading"><?= $page->enquiry_heading()->or('Discuss a project')->html() ?></h2>
          <p class="contact-card__desc"><?= $page->enquiry_desc()->or('Tell us about what you\'re working on and we\'ll get back to you.')->html() ?></p>

          <form class="contact-form" method="post" action="/contact" data-route="enquiry">
            <input type="hidden" name="form_type" value="enquiry">

            <div class="contact-form__group">
              <label class="contact-form__label" for="enquiry-name">Your name</label>
              <input class="contact-form__input" type="text" id="enquiry-name" name="name" required>
            </div>

            <div class="contact-form__group">
              <label class="contact-form__label" for="enquiry-org">Organisation</label>
              <input class="contact-form__input" type="text" id="enquiry-org" name="organisation">
            </div>

            <div class="contact-form__group">
              <label class="contact-form__label" for="enquiry-scope">Scope note</label>
              <textarea class="contact-form__textarea" id="enquiry-scope" name="scope" rows="3"></textarea>
            </div>

            <div class="contact-form__group">
              <label class="contact-form__label" for="enquiry-timeline">Timeline</label>
              <input class="contact-form__input" type="text" id="enquiry-timeline" name="timeline" placeholder="e.g. Q1 2027">
            </div>

            <button class="pg-btn contact-form__submit" type="submit">Send enquiry</button>
          </form>
        </div>

        <!-- CARD 2: Tender Invite -->
        <div class="contact-route" id="tenders">
          <h2 class="contact-card__heading"><?= $page->tender_heading()->or('Invite us to tender')->html() ?></h2>
          <p class="contact-card__desc"><?= $page->tender_desc()->or('Share the details of your RFT and we\'ll confirm receipt within one working day.')->html() ?></p>

          <form class="contact-form" method="post" action="/contact" data-route="tender">
            <input type="hidden" name="form_type" value="tender">

            <div class="contact-form__group">
              <label class="contact-form__label" for="tender-ref">RFT reference</label>
              <input class="contact-form__input" type="text" id="tender-ref" name="rft_reference">
            </div>

            <div class="contact-form__group">
              <label class="contact-form__label" for="tender-portal">Portal</label>
              <select class="contact-form__select" id="tender-portal" name="portal">
                <option value="">Select portal</option>
                <option value="etenders">eTenders</option>
                <option value="supplygov">SupplyGov</option>
                <option value="ojeu">OJEU</option>
                <option value="other">Other</option>
              </select>
            </div>

            <div class="contact-form__group">
              <label class="contact-form__label" for="tender-closing">Closing date</label>
              <input class="contact-form__input" type="date" id="tender-closing" name="closing_date">
            </div>

            <div class="contact-form__group">
              <label class="contact-form__label" for="tender-framework">Framework</label>
              <input class="contact-form__input" type="text" id="tender-framework" name="framework">
            </div>

            <div class="contact-form__group">
              <label class="contact-form__label" for="tender-note">Note</label>
              <textarea class="contact-form__textarea" id="tender-note" name="note" rows="3"></textarea>
            </div>

            <button class="pg-btn contact-form__submit" type="submit">Submit tender invite</button>
            <p class="contact-form__ack"><?= $page->tender_ack()->or('We\'ll confirm receipt within one working day.')->html() ?></p>
          </form>
        </div>

        <!-- CARD 3: Strategy Conversation -->
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
            <p class="contact-partner__intro"><?= $page->strategy_partner_intro()->html() ?></p>
          <?php endif ?>

          <form class="contact-form" method="post" action="/contact" data-route="strategy">
            <input type="hidden" name="form_type" value="strategy">

            <div class="contact-form__group">
              <label class="contact-form__label" for="strategy-name">Your name</label>
              <input class="contact-form__input" type="text" id="strategy-name" name="name" required>
            </div>

            <div class="contact-form__group">
              <label class="contact-form__label" for="strategy-org">Organisation</label>
              <input class="contact-form__input" type="text" id="strategy-org" name="organisation">
            </div>

            <button class="pg-btn contact-form__submit" type="submit">Request a conversation</button>
          </form>

          <p class="contact-partner__response"><?= $page->strategy_response()->or('We\'ll be in touch within 48 hours.')->html() ?></p>
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

<script src="/assets/js/pages.js" defer></script>
<?php snippet('footer') ?>
