<?php
/**
 * Contact page — three routed pathways
 * File: site/templates/contact.php
 *
 * Card 1: Discuss a project → enquiry email
 * Card 2: Invite us to tender → tender email
 * Card 3: Book a strategy conversation → strategy email / named partner
 *
 * Forms use PHP mail() for now — swap to a form handler plugin later.
 * Blueprint: site/blueprints/pages/contact.yml
 */

$partner = $page->strategy_partner()->toUser();
?>

<?php snippet('header', ['css' => ['/assets/css/feed.css']]) ?>

<div class="feed-wrap">

  <div class="contact-page">

    <header class="contact-page__header">
      <h1 class="contact-page__title"><?= $page->headline()->or('Get in touch')->html() ?></h1>
      <p class="contact-page__intro"><?= $page->intro()->or('Whether you\'re exploring a project, responding to a tender, or looking for strategic advice — start here.')->html() ?></p>
    </header>

    <div class="contact-cards">

      <!-- CARD 1: Project Enquiry -->
      <div class="contact-card" id="enquiries">
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

          <button class="contact-form__submit" type="submit">Send enquiry</button>
        </form>
      </div>

      <!-- CARD 2: Tender Invite -->
      <div class="contact-card" id="tenders">
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

          <button class="contact-form__submit" type="submit">Submit tender invite</button>
          <p class="contact-form__ack"><?= $page->tender_ack()->or('We\'ll confirm receipt within one working day.')->html() ?></p>
        </form>
      </div>

      <!-- CARD 3: Strategy Conversation -->
      <div class="contact-card" id="strategy">
        <h2 class="contact-card__heading"><?= $page->strategy_heading()->or('Book a strategy conversation')->html() ?></h2>
        <p class="contact-card__desc"><?= $page->strategy_desc()->or('A 30-minute conversation with our strategy lead to explore how we might help.')->html() ?></p>

        <?php if ($partner): ?>
          <div class="contact-partner">
            <div class="contact-partner__avatar">
              <?php if ($avatar = $partner->avatar()): ?>
                <img src="<?= $avatar->url() ?>" alt="" loading="lazy">
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

          <button class="contact-form__submit" type="submit">Request a conversation</button>
        </form>

        <p class="contact-partner__response"><?= $page->strategy_response()->or('We\'ll be in touch within 48 hours.')->html() ?></p>
      </div>

    </div>

  </div>

</div>

<script>
(function() {
  var h = new Date().getHours();
  if (h >= 7 && h < 19) {
    document.documentElement.setAttribute('data-theme', 'light');
  }
})();
</script>

<?php snippet('footer') ?>
