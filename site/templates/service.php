<?php
/**
 * Service page (Labs, Strategy, Brand, Digital), from the Figma
 * "Team · Labs · v5" frame (1260:19288). One template for all four; the
 * services/(:any) route in config.php renders it with $serviceSlug.
 *
 * Header in the service's colour (Figma service openings, 1240:8359 and
 * beside it): the name in grey over a statement in black, then the intro
 * with the page's blocks under "Read more", and a "Currently interested
 * in" card (a topic, linking to its work). Then the service's projects (curated or
 * everything tagged with the service, ten at a time), a What If, "How
 * {service} can help" cards, a contact strip and the previous and next
 * services.
 * Every text has a default (wove_service_defaults(), Labs copy from the
 * Figma); sections without content are left out.
 * Styles: the SERVICE PAGE section of assets/css/pages.css.
 * Behaviour: "Show more" in assets/js/pages.js.
 */

$slug     = $serviceSlug ?? $page->slug();
$labels   = wove_service_labels();
$name     = $labels[$slug] ?? $page->title()->value();
$defaults = wove_service_defaults($slug);
$feed     = wove_feed();

$statement = $page->statement()->or($defaults['statement'])->value();
$lead      = $page->lead()->or($defaults['lead'])->value();
$more      = $page->blocks()->toBlocks();
$offers    = $page->offers()->toStructure();
$offers    = $offers->count()
  ? array_map(fn ($o) => ['title' => $o->title()->value(), 'text' => $o->text()->value()], $offers->values())
  : $defaults['offers'];

$imageOf = fn ($p) => $p->intendedTemplate()->name() === 'case-study'
  ? $p->caseStudyImages()->toFile()
  : $p->content()->get('image')->toFile();

// Currently interested in: a published editorial tag
$interest = null;
$topicSlug = $page->interestTopic()->or($defaults['interestTopic'])->value();
if ($topicSlug && ($tag = $site->tags()->toStructure()->findBy('slug', $topicSlug)) && $tag->active()->toBool() !== false) {
  $interest = [
    'name' => $tag->name()->value(),
    'url'  => url('tag/' . $topicSlug),
  ];
}

// Projects: curated, or every post and case study with this service
$projects = $page->projects()->toPages()->listed()->values();
if (!$projects) {
  $projects = array_values(array_filter($feed['items'], fn ($p) =>
    in_array('service:' . $slug, $feed['keys'][$p->id()] ?? [], true)
    && !in_array($p->format()->value(), ['spark', 'whatif'], true)));
}
$perPage = 10;

// What If: curated, or the newest one with this service
$whatif = $page->whatif()->toPage();
if (!$whatif || !$whatif->isListed()) {
  $whatif = null;
  foreach ($feed['entries'] as $entry) {
    if ($entry->format()->value() === 'whatif' && in_array($slug, $entry->services()->split(','), true)) { $whatif = $entry; break; }
  }
}

// Contact strip
$person = $page->contactPerson()->toUser();
$personName = $person ? ($person->name()->value() ?: $person->email()) : '';
$talk = $page->talkPrompt()->or($defaults['talkPrompt'])->value();

// Previous and next, in the menu order
$order = ['labs', 'strategy', 'brand', 'digital'];
$i     = array_search($slug, $order, true);
$i     = $i === false ? 0 : $i;
$prev  = $order[($i + count($order) - 1) % count($order)];
$next  = $order[($i + 1) % count($order)];

?>
<?php snippet('header', ['css' => ['/assets/css/home.css', '/assets/css/pages.css'], 'nav' => false]) ?>

<div class="home sv sv--<?= html($slug) ?>" data-theme="light">
  <?php snippet('col-layout', ['key' => 'service', 'default' => 'feed']) ?>

  <header class="hx hx--page sv-head">
    <div class="hx__inner">
      <?php snippet('brand/header-row', ['current' => 'service:' . $slug]) ?>
      <h1 class="sv-head__title">
        <span class="sv-head__name"><?= html($name) ?></span>
        <?php if ($statement): ?><span class="sv-head__statement"><?= html($statement) ?></span><?php endif ?>
      </h1>
      <div class="sv-head__lower">
        <div class="sv-head__lead">
          <?php if ($lead): ?><p class="sv-head__text"><?= html($lead) ?></p><?php endif ?>
          <?php if ($more->isNotEmpty()): ?>
            <details class="sv-intro__more">
              <summary>Read more <span class="sv-intro__plus" aria-hidden="true"></span></summary>
              <div class="sv-intro__blocks"><?= $more ?></div>
            </details>
          <?php endif ?>
        </div>
        <?php if ($interest): ?>
          <a class="sv-interest" href="<?= $interest['url'] ?>">
            <span class="sv-interest__kicker">Currently interested in</span>
            <span class="sv-interest__name"><?= html($interest['name']) ?></span>
          </a>
        <?php endif ?>
      </div>
    </div>
  </header>

  <div class="feed-wrap">
    <?php snippet('brand/menu-bar', ['current' => 'service:' . $slug]) ?>

    <main class="pg sv-main" id="main">

      <?php if ($projects): ?>
        <section class="sv-section" aria-labelledby="sv-projects">
          <h2 class="sv-heading" id="sv-projects"><?= html($name) ?> projects</h2>
          <ul class="sv-projects feed-varied" role="list" data-projects data-heights data-page="<?= $perPage ?>">
            <?php foreach ($projects as $n => $p):
              $isCase = $p->intendedTemplate()->name() === 'case-study';
              $image  = $imageOf($p);
              $title  = $isCase && $p->heroTitle()->isNotEmpty() ? strip_tags($p->heroTitle()->value()) : $p->title()->value();
              $client = $isCase ? $p : $p->case_study()->toPages()->first();
              $client = $client ? $client->eyebrow()->or($client->title())->value() : '';
              $line   = wove_card_excerpt($isCase ? (string) $p->summary()->or($p->subStatement())->value() : (string) $p->body()->value());
            ?>
              <li class="sv-project"<?= $n >= $perPage ? ' data-later' : '' ?>>
                <a class="sv-project__link" href="<?= $p->url() ?>">
                  <span class="sv-project__media"<?= $image && $image->height() ? ' style="--ratio: ' . $image->width() . ' / ' . $image->height() . '"' : '' ?>>
                    <?php if ($image): ?>
                      <?php snippet('picture', [
                        'file'   => $image,
                        'widths' => [480, 720, 960, 1440],
                        'sizes'  => '(min-width: 960px) 60vw, 100vw',
                        'class'  => 'sv-project__image',
                      ]) ?>
                    <?php endif ?>
                    <span class="pc__pill"><?= html(wove_post_pill($p)) ?></span>
                  </span>
                  <span class="sv-project__title"><?= html($title) ?></span>
                  <?php if ($client): ?><span class="sv-project__client"><?= html($client) ?></span><?php endif ?>
                  <?php if ($line): ?><span class="sv-project__line"><?= html($line) ?></span><?php endif ?>
                </a>
              </li>
            <?php endforeach ?>
          </ul>
          <?php if (count($projects) > $perPage): ?>
            <div class="sv-more" data-more-wrap hidden>
              <button type="button" class="sv-btn" data-show-more>Show more</button>
              <span class="sv-more__count" data-count aria-live="polite">Showing <?= $perPage ?> of <?= count($projects) ?></span>
            </div>
          <?php endif ?>
        </section>
      <?php endif ?>

      <?php if ($whatif): ?>
        <section class="sv-whatif" aria-label="What If">
          <h2 class="sv-whatif__question"><?= $whatif->title()->html() ?></h2>
          <?php if ($whatif->body()->isNotEmpty()): ?><p class="sv-whatif__line"><?= html(wove_first_sentence($whatif->body()->value())) ?></p><?php endif ?>
          <a class="sv-btn" href="<?= $whatif->url() ?>">Read the experiment <span aria-hidden="true">&rarr;</span></a>
        </section>
      <?php endif ?>

      <?php if ($offers): ?>
        <section class="sv-section" aria-labelledby="sv-help">
          <h2 class="sv-heading" id="sv-help">How <?= html($name) ?> can help</h2>
          <ul class="sv-offers" role="list">
            <?php foreach ($offers as $offer): ?>
              <li class="sv-offer">
                <h3 class="sv-offer__title"><?= html($offer['title']) ?></h3>
                <?php if ($offer['text']): ?><p class="sv-offer__text"><?= html($offer['text']) ?></p><?php endif ?>
              </li>
            <?php endforeach ?>
            <li class="sv-offer sv-offer--start">
              <h3 class="sv-offer__title">Not sure where to start?</h3>
              <p class="sv-offer__text">Tell us what you are trying to change and we will say which team, and which first step, fits.</p>
              <a class="sv-btn sv-btn--white" href="<?= url('contact') ?>">Where to start <span aria-hidden="true">&rarr;</span></a>
            </li>
          </ul>
        </section>
      <?php endif ?>

      <section class="sv-section sv-talk-wrap" aria-label="Contact">
        <div class="sv-talk">
          <div class="sv-talk__person">
            <?php if ($person): ?>
              <?php if ($avatar = $person->avatar()): ?>
                <img class="sv-talk__face" src="<?= $avatar->crop(144, 144)->url() ?>" alt="" width="72" height="72">
              <?php else: ?>
                <span class="sv-talk__face sv-talk__face--initials" aria-hidden="true"><?= html(wove_initials($personName)) ?></span>
              <?php endif ?>
            <?php endif ?>
            <div>
              <p class="sv-talk__prompt"><?= html($talk) ?></p>
              <p class="sv-talk__who"><?= $person ? html($personName . (($role = wove_member_role($person)) ? ', ' . $role : '')) : 'Wove ' . html($name) ?></p>
            </div>
          </div>
          <a class="sv-btn sv-btn--dark" href="<?= url('contact') ?>"><?= $person ? 'Talk to ' . html(explode(' ', trim($personName))[0]) : 'Get in touch' ?> <span aria-hidden="true">&rarr;</span></a>
        </div>

        <nav class="sv-pager" aria-label="Other services">
          <a class="sv-pager__link sv--<?= $prev ?>" href="<?= url('services/' . $prev) ?>" rel="prev">
            <span class="sv-pager__swatch" aria-hidden="true"></span>
            <span><span class="sv-pager__label">Previous</span><span class="sv-pager__name"><?= html($labels[$prev]) ?></span></span>
          </a>
          <a class="sv-pager__link sv-pager__link--next sv--<?= $next ?>" href="<?= url('services/' . $next) ?>" rel="next">
            <span><span class="sv-pager__label">Next</span><span class="sv-pager__name"><?= html($labels[$next]) ?></span></span>
            <span class="sv-pager__swatch" aria-hidden="true"></span>
          </a>
        </nav>
      </section>

    </main>
  </div>
</div>

<?php snippet('col-layout', ['switch' => true]) ?>
<script src="<?= wove_asset('/assets/js/pages.js') ?>" defer></script>
<?php snippet('footer') ?>
