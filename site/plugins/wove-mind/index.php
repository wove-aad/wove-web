<?php

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Form\Form;
use Kirby\Toolkit\Str;

/**
 * Wove Mind — custom Panel plugin for authoring team posts.
 *
 * Registers:
 *   - a "wove-mind" Panel area with a list view and an entry editor view
 *     living at /panel/wove-mind
 *   - user role blueprints (admin, contributor), with shared team profile fields
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
										'viewer'  => wove_mind_user_summary($kirby->user()),
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
									'viewer'  => wove_mind_user_summary($user),
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
		'sections/team-profile' => __DIR__ . '/blueprints/sections/team-profile.yml',
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

	// Plain text of the post: the optional excerpt, the writer `body`,
	// and the text of any long read blocks.
	$excerpt = wove_mind_plain_text((string) $content->get('excerpt')->value());
	$body    = wove_mind_plain_text((string) $content->get('body')->value());
	$blocks  = wove_mind_blocks_text((string) $content->get('blocks')->value());
	$summary = $excerpt ?: ($body ?: $blocks);
	$full    = trim($body . ' ' . $blocks);
	$words   = $full === '' ? 0 : count(preg_split('/\s+/u', $full));

	// The blueprint's `author` users field. createdBy()/authors() are not Page
	// methods, so Kirby returned them as Field objects and the fallback never ran.
	$user   = $content->get('author')->toUser();
	$author = $user ? ($user->name()->value() ?? $user->email()) : 'Anonymous';
	$avatar = wove_mind_avatar_url($user);

	// Featured image. Read through content() because Page::image() is
	// Kirby's "first image of the page" method, not the field.
	$image = $content->get('image')->toFile();
	$thumb = $image ? $image->crop(112, 112)->url() : null;

	// Published entries sort and group by their date field, falling
	// back to the last edit; drafts always use the last edit.
	$modified  = $entry->modified();
	$date      = $content->get('date')->isNotEmpty() ? $content->get('date')->toDate() : null;
	$timestamp = $entry->isDraft() ? $modified : ($date ?: $modified);

	// Sparks have no title of their own: show the start of the text.
	$sparkText = null;
	if ($format === 'spark') {
		$sparkText = $body !== '' ? Str::short($body, 140) : ($image ? 'Image spark' : 'Empty spark');
	}

	$tags = $content->get('tags')->split(',');

	return [
		'id'        => $entry->uri(),
		'title'     => $entry->title()->value(),
		'excerpt'   => Str::short($summary, 160),
		'sparkText' => $sparkText,
		'format'    => $format,
		'status'    => $entry->status(),
		'author'    => $author,
		'avatar'    => $avatar,
		'thumb'     => $thumb,
		'tags'      => $tags,
		'mine'      => $viewer !== null && $user !== null && $viewer->id() === $user->id(),
		'wordCount' => $words > 0 ? $words : null,
		'timestamp' => $timestamp,
		// A date field has no time, so show it by day
		'dateLabel' => wove_mind_date_label($timestamp, $timestamp !== $modified),
		'monthLabel' => date('F Y', $timestamp),
		'editUrl'   => 'wove-mind/entry/' . $entry->slug(),
		'viewUrl'   => $entry->previewUrl(),
	];
}

/**
 * HTML to single-spaced plain text. Block-level tags become spaces so
 * paragraphs don't run together.
 */
function wove_mind_plain_text(string $html): string
{
	$text = strip_tags(preg_replace('/<(br|\/p|\/li|\/h[1-6]|\/blockquote)[^>]*>/i', ' ', $html));
	return trim(preg_replace('/\s+/u', ' ', html_entity_decode($text, ENT_QUOTES | ENT_HTML5)));
}

/**
 * Text of a blocks field (stored as JSON). Only the `text` of each
 * block is read, which covers text, heading, quote and list blocks.
 */
function wove_mind_blocks_text(string $json): string
{
	$blocks = json_decode($json, true);
	if (!is_array($blocks)) {
		return '';
	}
	$parts = [];
	foreach ($blocks as $block) {
		$text = $block['content']['text'] ?? null;
		if (is_string($text)) {
			$parts[] = wove_mind_plain_text($text);
		}
	}
	return trim(implode(' ', array_filter($parts)));
}

/**
 * Name and avatar of the logged-in user, for the top bar.
 */
function wove_mind_user_summary(?\Kirby\Cms\User $user): array
{
	return [
		'name'   => $user ? ($user->name()->value() ?? $user->email()) : '',
		'avatar' => wove_mind_avatar_url($user),
	];
}

/**
 * URL of a square thumbnail of the user's Panel profile image, or null if none is set.
 */
function wove_mind_avatar_url(?\Kirby\Cms\User $user): ?string
{
	$avatar = $user?->avatar();

	return $avatar ? $avatar->crop(96, 96)->url() : null;
}

/**
 * Human-friendly date label: "Just now", "5 min ago", "Today, 12:05",
 * "Yesterday", "3 days ago", then "3 Sep 2026".
 */
function wove_mind_date_label(int $timestamp, bool $dayOnly = false): string
{
	$now  = time();
	$diff = $now - $timestamp;

	if ($dayOnly) {
		if (date('Y-m-d', $timestamp) === date('Y-m-d', $now)) {
			return 'Today';
		}
		if (date('Y-m-d', $timestamp) === date('Y-m-d', $now - 86400)) {
			return 'Yesterday';
		}
		return date('j M Y', $timestamp);
	}

	if ($diff >= 0 && $diff < 60) {
		return 'Just now';
	}
	if ($diff >= 0 && $diff < 3600) {
		return (int) floor($diff / 60) . ' min ago';
	}
	if (date('Y-m-d', $timestamp) === date('Y-m-d', $now)) {
		return 'Today, ' . date('H:i', $timestamp);
	}
	if (date('Y-m-d', $timestamp) === date('Y-m-d', $now - 86400)) {
		return 'Yesterday';
	}
	if ($diff > 0 && $diff < 86400 * 7) {
		return (int) floor($diff / 86400) . ' days ago';
	}
	return date('j M Y', $timestamp);
}
