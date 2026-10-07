<?php

use Kirby\Cms\Page;
use Kirby\Toolkit\Str;

/**
 * Wove Feed: the data behind Our work and the service and tag views.
 *
 * wove_feed() gathers listed Wove Mind entries and case studies and gives
 * each one the filter keys it matches:
 * - cs:{slug}       the case study an entry belongs to (case studies
 *                   themselves don't carry it: the case study panel shows
 *                   them when that client is selected);
 * - service:{slug}  services;
 * - tag:{slug}      editorial tags (entries' `tags`, case studies'
 *                   `impactAreas`);
 * - sector:{slug}   sectors;
 * - author:{slug}   the credited author (entries only).
 * Filtering happens in the browser (assets/js/home.js) over this markup.
 */

function wove_service_labels(): array
{
	return ['strategy' => 'Strategy', 'labs' => 'Labs', 'digital' => 'Digital', 'brand' => 'Brand'];
}

function wove_sector_labels(): array
{
	return [
		'arts-and-culture'  => 'Arts and Culture',
		'public-service'    => 'Public Service',
		'higher-education'  => 'Higher Education',
		'non-profit'        => 'Non-profit and Mission-led',
		'founders-ventures' => 'Founders and Ventures',
	];
}

/**
 * Fallback intros for the services, used when the service page has none.
 */
function wove_service_intro(string $slug): string
{
	$page = page('services/' . $slug);
	if ($page && $page->intro()->isNotEmpty()) return $page->intro()->value();

	return [
		'strategy' => 'Helping organisations plan, prioritise and change in lasting ways.',
		'labs'     => 'Research and development for a more human future.',
		'digital'  => 'Platforms and innovation that help you deliver and scale impact.',
		'brand'    => 'Identities, content and campaigns that culturally connect.',
	][$slug] ?? '';
}

function wove_feed(): array
{
	static $feed = null;
	if ($feed !== null) return $feed;

	$site     = site();
	$services = wove_service_labels();
	$sectors  = wove_sector_labels();
	$siteTags = $site->tags()->toStructure()->filter(fn ($t) => $t->active()->toBool() !== false);

	// Tag field values are slugs; older content may hold names.
	$tagSlug = function (string $value) use ($siteTags): string {
		$value = trim($value);
		$tag   = $siteTags->findBy('slug', $value) ?? $siteTags->findBy('name', $value);
		return $tag ? $tag->slug()->value() : Str::slug($value);
	};

	$parent  = $site->find('wove-mind');
	$entries = $parent
		? $parent->children()->listed()
			->filter(fn ($p) => $p->format()->value() !== 'project-highlight')
			->sortBy('date', 'desc')
		: new \Kirby\Cms\Pages();
	$caseStudies = kirby()->collection('case-studies');

	$used = ['service' => [], 'tag' => [], 'sector' => [], 'author' => []];
	$keys = [];
	$clientCounts = [];

	$common = function (Page $p, string $tagField) use (&$used, $services, $sectors, $tagSlug): array {
		$k = [];
		foreach ($p->services()->split(',') as $s) {
			if (isset($services[$s])) { $k[] = 'service:' . $s; $used['service'][$s] = true; }
		}
		foreach ($p->content()->get($tagField)->split(',') as $t) {
			$slug = $tagSlug($t);
			if ($slug !== '') { $k[] = 'tag:' . $slug; $used['tag'][$slug] = true; }
		}
		foreach ($p->sectors()->split(',') as $s) {
			if (isset($sectors[$s])) { $k[] = 'sector:' . $s; $used['sector'][$s] = true; }
		}
		return $k;
	};

	foreach ($entries as $entry) {
		$k = [];
		foreach ($entry->case_study()->toPages() as $cs) {
			$k[] = 'cs:' . $cs->slug();
			$clientCounts[$cs->slug()] = ($clientCounts[$cs->slug()] ?? 0) + 1;
		}
		$k = array_merge($k, $common($entry, 'tags'));
		if ($author = wove_entry_author($entry)) {
			$slug = wove_author_slug($author);
			$k[] = 'author:' . $slug;
			$used['author'][$slug] = $author;
		}
		$keys[$entry->id()] = array_values(array_unique($k));
	}
	foreach ($caseStudies as $cs) {
		$keys[$cs->id()] = array_values(array_unique($common($cs, 'impactAreas')));
	}

	// Topics in tag order: services, then editorial tags (site order).
	$topics = [];
	foreach ($services as $slug => $label) {
		if (isset($used['service'][$slug])) $topics['service:' . $slug] = $label;
	}
	foreach ($siteTags as $tag) {
		$slug = $tag->slug()->value();
		if (isset($used['tag'][$slug])) $topics['tag:' . $slug] = $tag->name()->value();
	}

	$clients = [];
	foreach ($caseStudies as $cs) {
		$clients[] = [
			'page'  => $cs,
			'slug'  => $cs->slug(),
			'name'  => $cs->eyebrow()->or($cs->title())->value(),
			'count' => $clientCounts[$cs->slug()] ?? 0,
		];
	}

	// Posts and case studies together, newest first.
	$items = array_merge($entries->values(), $caseStudies->values());
	usort($items, fn ($a, $b) => ($b->date()->toTimestamp() ?: 0) <=> ($a->date()->toTimestamp() ?: 0));

	return $feed = [
		'entries'  => $entries,
		'items'    => $items,
		'keys'     => $keys,
		'clients'  => $clients,
		'topics'   => $topics,
		'sectors'  => array_intersect_key($sectors, $used['sector']),
		'authors'  => $used['author'],
	];
}

/**
 * Whether a filter key (service:x, tag:y, sector:z, cs:slug, author:slug)
 * matches anything in the feed. Used to check a filter from a URL.
 */
function wove_feed_has_key(string $key): bool
{
	foreach (wove_feed()['keys'] as $k) {
		if (in_array($key, $k, true)) return true;
	}
	return false;
}
