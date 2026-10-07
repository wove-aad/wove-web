<?php
/**
 * Tag and sector pages (/tag/{slug}, see the route in config.php): Our work
 * with that filter selected, so its panel gives the context.
 * File: site/templates/tag.php
 */
$filterField = $filterField ?? 'tags';
$filterValue = $filterValue ?? '';
snippet('feed/work-view', ['initial' => ($filterField === 'sectors' ? 'sector:' : 'tag:') . $filterValue]);
