<?php
/**
 * Homepage filter panels: a case study panel per client and the context
 * panels for services, tags, sectors and people (feed/panels), shown when
 * that filter is active (from a ?filter= link, an author link on a card or
 * the Impact areas menu). The client carousel and topic pills were
 * replaced on 2026-10-29 by the Projects / Thinking / News filters beside
 * the feed heading (templates/wove-mind.php).
 * One filter is active at a time; assets/js/home.js handles selection.
 * Usage: <?php snippet('home/filters', ['clients' => $clients, 'topics' => $topics,
 *        'sectors' => [...], 'authors' => [...]]) ?>
 */
?>
<div class="filters" id="filters">

  <?php foreach ($clients as $client): ?>
    <?php snippet('home/case-panel', ['cs' => $client['page'], 'hidden' => true]) ?>
  <?php endforeach ?>

  <?php snippet('feed/panels', ['topics' => $topics, 'sectors' => $sectors ?? [], 'authors' => $authors ?? []]) ?>

</div>
