<?php
/**
 * Our work, service, tag and sector pages: since 2026-10-08 the homepage
 * feed is the only work feed, so these redirect to it with their filter
 * selected (wove_work_url()). Used by work.php, service.php, tag.php and
 * sector.php. A ?filter= in the URL wins, so old shared links keep working.
 * Usage: <?php snippet('feed/work-view', ['initial' => 'service:strategy']) ?>
 */

$filter = get('filter') ?: ($initial ?? '*');
go(wove_work_url($filter === '*' ? null : $filter), 302);
