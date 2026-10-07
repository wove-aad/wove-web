<?php
/**
 * Sector page (/sector/{slug}): Our work with this sector's filter
 * selected, so the sector panel (its intro) gives the context.
 * File: site/templates/sector.php. Blueprint: site/blueprints/pages/sector.yml
 */
snippet('feed/work-view', ['initial' => 'sector:' . $page->sectorKey()->value()]);
