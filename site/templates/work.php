<?php
/**
 * Our work: every post and case study, filtered in place.
 * File: site/templates/work.php. Route: /our-work (see config.php).
 * The view is snippets/feed/work-view.php, shared with service.php and
 * tag.php. Filters can come in from the URL: /our-work?filter=cs:dcu,
 * service:strategy, tag:climate, sector:public-service or author:{slug}.
 */
snippet('feed/work-view', ['initial' => '*']);
