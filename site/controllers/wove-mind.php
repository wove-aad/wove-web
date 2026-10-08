<?php
/**
 * Homepage controller (the homepage is the Wove Mind page; see 'home' in
 * site/config/config.php).
 *
 * Prepares the feed for site/templates/wove-mind.php:
 * - $entries:     listed Wove Mind entries, newest first (case studies are
 *                 not cards here; they appear in the client carousel);
 * - $clients:     case studies for the carousel, each with its post count;
 * - $topics:      services, then editorial tags, in the site-wide tag order,
 *                 limited to those used by at least one entry;
 * - $entryFilters: per entry, the filter keys it matches (cs:, service:, tag:,
 *                 author:);
 * - $sectors:     sectors used by at least one entry (key => label);
 * - $authors:     credited authors (slug => user), by name, for the Team
 *                 filter and its person panels.
 * Filtering happens in the browser (assets/js/home.js) over this markup.
 */

use Kirby\Toolkit\Str;

return function ($page, $site, $kirby) {
    $serviceLabels = ['strategy' => 'Strategy', 'labs' => 'Labs', 'digital' => 'Digital', 'brand' => 'Brand'];
    $siteTags      = $site->tags()->toStructure()->filterBy('active', 'true');

    // Tag field values are slugs; older content may hold names.
    $tagSlug = function (string $value) use ($siteTags): string {
        $value = trim($value);
        $tag   = $siteTags->findBy('slug', $value) ?? $siteTags->findBy('name', $value);
        return $tag ? $tag->slug()->value() : Str::slug($value);
    };

    $entries = $page->children()->listed()
        ->filter(fn ($p) => $p->format()->value() !== 'project-highlight')
        ->sortBy('date', 'desc');

    $caseStudies = $kirby->collection('case-studies');

    $entryFilters = [];
    $usedServices = [];
    $usedTags     = [];
    $clientCounts = [];
    $authors      = [];
    $sectorLabels = wove_sector_labels();
    $usedSectors  = [];

    foreach ($entries as $entry) {
        $keys = [];
        foreach ($entry->case_study()->toPages() as $cs) {
            $keys[] = 'cs:' . $cs->slug();
            $clientCounts[$cs->slug()] = ($clientCounts[$cs->slug()] ?? 0) + 1;
        }
        foreach ($entry->services()->split(',') as $s) {
            if (isset($serviceLabels[$s])) {
                $keys[] = 'service:' . $s;
                $usedServices[$s] = true;
            }
        }
        foreach ($entry->tags()->split(',') as $t) {
            $slug = $tagSlug($t);
            if ($slug === '') continue;
            $keys[] = 'tag:' . $slug;
            $usedTags[$slug] = true;
        }
        foreach ($entry->sectors()->split(',') as $s) {
            if (isset($sectorLabels[$s])) {
                $keys[] = 'sector:' . $s;
                $usedSectors[$s] = true;
            }
        }
        if (function_exists('wove_entry_author') && ($author = wove_entry_author($entry))) {
            $slug = wove_author_slug($author);
            $keys[] = 'author:' . $slug;
            $authors[$slug] = $author;
        }
        $entryFilters[$entry->id()] = array_values(array_unique($keys));
    }

    // Topics in tag order: services, then editorial tags (site order).
    $topics = [];
    foreach ($serviceLabels as $slug => $label) {
        if (isset($usedServices[$slug])) $topics['service:' . $slug] = $label;
    }
    foreach ($siteTags as $tag) {
        $slug = $tag->slug()->value();
        if (isset($usedTags[$slug])) $topics['tag:' . $slug] = $tag->name()->value();
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

    // People for the hero preview: team members with a profile photo first.
    $people = function_exists('wove_team_members') ? wove_team_members() : [];
    usort($people, fn ($a, $b) => ($b->avatar() ? 1 : 0) <=> ($a->avatar() ? 1 : 0));

    uasort($authors, fn ($a, $b) => strcasecmp((string) $a->name(), (string) $b->name()));

    return [
        'entries'      => $entries,
        'entryFilters' => $entryFilters,
        'authors'      => $authors,
        'sectors'      => array_intersect_key($sectorLabels, $usedSectors),
        'clients'      => $clients,
        'topics'       => $topics,
        'people'       => array_slice($people, 0, 4),
        'feedLimit'    => 12,
    ];
};
