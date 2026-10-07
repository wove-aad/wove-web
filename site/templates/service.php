<?php
/**
 * Service page: Our work with this service's filter selected, so the
 * service panel (featured image, intro, a link to get in touch) gives the
 * context above the matching posts and case studies.
 * File: site/templates/service.php. The slug comes from the services/(:any)
 * route in config.php, or from the page.
 */
snippet('feed/work-view', ['initial' => 'service:' . ($serviceSlug ?? $page->slug())]);
