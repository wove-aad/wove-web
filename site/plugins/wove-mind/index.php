<?php

use Kirby\Cms\App;
use Kirby\Cms\Page;

/**
 * Wove Mind — custom Panel plugin for authoring team posts.
 *
 * Registers:
 *   - a "mind" Panel area with a list view and an entry editor view
 *   - blueprint bindings for the mind_entry template
 *   - user role blueprints (admin, contributor)
 *
 * Contributor users are redirected here on login via the "home" option
 * in their role blueprint.
 */

App::plugin('wove/mind', [

	'areas' => [
		'mind' => function ($kirby) {
			return [
				'label'  => 'Wove Mind',
				'icon'   => 'edit',
				'menu'   => true,
				'link'   => 'mind',
				'search' => null,
				'views'  => [

					// Entries list
					[
						'pattern' => 'mind',
						'action'  => function () use ($kirby) {
							$parent = $kirby->page('mind');

							if ($parent === null) {
								return [
									'component' => 'k-mind-entries-view',
									'title'     => 'Wove Mind',
									'props'     => [
										'entries' => [],
										'parent'  => 'mind',
									],
								];
							}

							$user    = $kirby->user();
							$entries = $parent->children()->add($parent->drafts())->sortBy('modified', 'desc');

							return [
								'component' => 'k-mind-entries-view',
								'title'     => 'Wove Mind',
								'props'     => [
									'parent'  => 'mind',
									'entries' => $entries->values(fn ($entry) => wove_mind_entry_summary($entry, $user)),
								],
							];
						},
					],

					// New entry — the format is chosen client-side, then a POST creates the page.
					// We land people directly on the editor by way of the list view's chooser,
					// so no distinct new-view route is needed.

					// Entry editor
					[
						'pattern' => 'mind/entry/(:any)',
						'action'  => function ($id) use ($kirby) {
							$page = $kirby->page('mind/' . $id);

							if ($page === null) {
								return [
									'component' => 'k-error-view',
									'title'     => 'Entry not found',
									'props'     => [
										'error' => 'This Mind entry could not be found.',
									],
								];
							}

							$blueprintFields = $page->blueprint()->fields();
							$content         = $page->content()->toArray();

							return [
								'component' => 'k-mind-editor-view',
								'title'     => $page->title()->value() ?: 'New entry',
								'props'     => [
									'entryId'        => $page->id(),
									'isNew'          => empty(trim($page->content()->body()->value() ?? '')),
									'initialContent' => $content,
									'fields'         => $blueprintFields,
									'status'         => $page->status(),
								],
							];
						},
					],
				],
			];
		},
	],

	'blueprints' => [
		'pages/mind'       => __DIR__ . '/blueprints/pages/mind.yml',
		'pages/mind_entry' => __DIR__ . '/blueprints/pages/mind_entry.yml',
		'users/admin'      => __DIR__ . '/blueprints/users/admin.yml',
		'users/contributor'=> __DIR__ . '/blueprints/users/contributor.yml',
	],

]);

/**
 * Build a compact summary of an entry for the list view.
 */
function wove_mind_entry_summary(Page $entry, ?\Kirby\Cms\User $viewer = null): array
{
	$format = $entry->content()->get('format')->value() ?: 'thread';
	$body   = trim(strip_tags((string) $entry->content()->get('body')->value()));
	$words  = $body === '' ? 0 : count(preg_split('/\s+/u', $body));

	$user   = $entry->createdBy() ?? $entry->authors()->toUsers()->first();
	$author = $user ? ($user->name()->value() ?? $user->email()) : 'Anonymous';

	return [
		'id'        => $entry->uri(),
		'title'     => $entry->title()->value(),
		'excerpt'   => mb_substr($body, 0, 160),
		'format'    => $format,
		'status'    => $entry->status(),
		'author'    => $author,
		'mine'      => $viewer && $user && $viewer->id() === $user->id(),
		'wordCount' => $words > 0 ? $words : null,
		'dateLabel' => wove_mind_date_label($entry->modified()),
		'editUrl'   => 'mind/entry/' . $entry->slug(),
	];
}

/**
 * Human-friendly date label. Same-day => "Today, HH:MM"; recent days => "N days ago";
 * older => "MMM D" or "MMM D, YYYY" for other years.
 */
function wove_mind_date_label(int $timestamp): string
{
	$now  = time();
	$diff = $now - $timestamp;

	if ($diff < 60) {
		return 'Just now';
	}
	if ($diff < 3600) {
		$m = (int) floor($diff / 60);
		return $m . ' min ago';
	}
	if ($diff < 86400 && date('Y-m-d', $timestamp) === date('Y-m-d', $now)) {
		return 'Today, ' . date('H:i', $timestamp);
	}
	if ($diff < 86400 * 2) {
		return 'Yesterday';
	}
	if ($diff < 86400 * 7) {
		return floor($diff / 86400) . ' days ago';
	}
	if (date('Y', $timestamp) === date('Y', $now)) {
		return date('M j', $timestamp);
	}
	return date('M j, Y', $timestamp);
}
