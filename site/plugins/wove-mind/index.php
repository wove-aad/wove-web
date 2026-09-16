<?php

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Form\Form;

/**
 * Wove Mind — custom Panel plugin for authoring team posts.
 *
 * Registers:
 *   - a "wove-mind" Panel area with a list view and an entry editor view
 *     living at /panel/wove-mind
 *   - user role blueprints (admin, contributor)
 *
 * Deliberately does NOT ship page blueprints — those live in
 * site/blueprints/pages/wove-mind{,-entry}.yml and are the single
 * source of truth for the Wove Mind content model, shared with the
 * frontend templates. This plugin adapts to whatever fields those
 * blueprints define.
 *
 * Contributor users are redirected here on login via the "home" option
 * in their role blueprint.
 */

App::plugin('wove/mind', [

	'areas' => [
		'wove-mind' => function ($kirby) {
			return [
				'label'  => 'Wove Mind',
				'icon'   => 'edit',
				'menu'   => true,
				'link'   => 'wove-mind',
				'search' => null,
				'views'  => [

					// Entries list
					[
						'pattern' => 'wove-mind',
						'action'  => function () use ($kirby) {
							$parent = $kirby->page('wove-mind');

							if ($parent === null) {
								return [
									'component' => 'k-mind-entries-view',
									'title'     => 'Wove Mind',
									'props'     => [
										'entries' => [],
										'parent'  => 'wove-mind',
										'error'   => 'The Wove Mind page does not exist on this site yet. Create /wove-mind in the Panel first.',
									],
								];
							}

							$user    = $kirby->user();
							$entries = $parent->children()->add($parent->drafts())->sortBy('modified', 'desc');

							return [
								'component' => 'k-mind-entries-view',
								'title'     => 'Wove Mind',
								'props'     => [
									'parent'  => 'wove-mind',
									'entries' => $entries->values(fn ($entry) => wove_mind_entry_summary($entry, $user)),
								],
							];
						},
					],

					// Entry editor
					[
						'pattern' => 'wove-mind/entry/(:any)',
						'action'  => function ($id) use ($kirby) {
							$page = $kirby->page('wove-mind/' . $id);

							if ($page === null) {
								return [
									'component' => 'k-error-view',
									'title'     => 'Entry not found',
									'props'     => [
										'error' => 'This Mind entry could not be found.',
									],
								];
							}

							// Use the Form's parsed field instances — each field's
							// ->toArray() runs its own type-specific props() method, so
							// specialised fields (blocks, files, tags, structure) return
							// fully-resolved specs (blocks fieldsets, tag options, etc.).
							$form    = Form::for($page);
							$content = $form->values();

							// k-fieldset spreads each field spec as props on the field
							// component, so file/section endpoints must live INSIDE each
							// field definition (Kirby's built-in k-page-view does the same).
							$apiId = str_replace('/', '+', $page->id());
							$fieldsWithEndpoints = [];
							foreach ($form->fields() as $name => $fieldObj) {
								$field = $fieldObj->toArray();
								$field['endpoints'] = [
									'model'   => 'pages/' . $apiId,
									'field'   => 'pages/' . $apiId . '/fields/' . $name,
									'section' => 'pages/' . $apiId . '/sections/' . $name,
								];
								$fieldsWithEndpoints[$name] = $field;
							}

							// Standard page-view scaffolding — the content/changes system
							// uses these props (versions.changes, lock, permissions, api)
							// when fields do things like uploads, autosave, etc.
							$panelPage = $page->panel();
							$standardProps = $panelPage->props();

							return [
								'component' => 'k-mind-editor-view',
								'title'     => $page->title()->value() ?: 'New entry',
								'props'     => [
									'api'         => $standardProps['api']         ?? 'pages/' . $apiId,
									'id'          => $standardProps['id']          ?? $page->id(),
									'lock'        => $standardProps['lock']        ?? null,
									'permissions' => $standardProps['permissions'] ?? null,
									'versions'    => $standardProps['versions']    ?? null,
									'previewUrl'  => $page->previewUrl(),

									'entryId'        => $page->id(),
									'isNew'          => empty(trim((string) $page->content()->get('excerpt')->value())) &&
									                    empty(trim((string) $page->content()->get('blocks')->value())),
									'initialContent' => $content,
									'fields'         => $fieldsWithEndpoints,
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
		'users/admin'       => __DIR__ . '/blueprints/users/admin.yml',
		'users/contributor' => __DIR__ . '/blueprints/users/contributor.yml',
	],

]);

/**
 * Build a compact summary of an entry for the list view.
 *
 * Reads Grace's wove-mind-entry field names (excerpt, blocks) rather
 * than the plugin's original body field.
 */
function wove_mind_entry_summary(Page $entry, ?\Kirby\Cms\User $viewer = null): array
{
	$content = $entry->content();
	$format  = $content->get('format')->value() ?: 'thread';

	// Prefer the intentional excerpt if present, otherwise fall back to
	// stripped blocks content, so the list card has something to show.
	$excerpt = trim((string) $content->get('excerpt')->value());
	if ($excerpt === '') {
		$blocks  = trim(strip_tags((string) $content->get('blocks')->value()));
		$excerpt = $blocks;
	}
	$words = $excerpt === '' ? 0 : count(preg_split('/\s+/u', $excerpt));

	$user   = $entry->createdBy() ?? $entry->authors()->toUsers()->first();
	$author = $user ? ($user->name()->value() ?? $user->email()) : 'Anonymous';

	$tags = $content->get('tags')->split(',');

	return [
		'id'        => $entry->uri(),
		'title'     => $entry->title()->value(),
		'excerpt'   => mb_substr($excerpt, 0, 160),
		'format'    => $format,
		'status'    => $entry->status(),
		'author'    => $author,
		'tags'      => $tags,
		'mine'      => $viewer && $user && $viewer->id() === $user->id(),
		'wordCount' => $words > 0 ? $words : null,
		'dateLabel' => wove_mind_date_label($entry->modified()),
		'editUrl'   => 'wove-mind/entry/' . $entry->slug(),
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
