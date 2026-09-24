<?php

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Cms\User;
use Kirby\Toolkit\Str;

/**
 * Wove Team — helpers for the Our People page and author filtering.
 *
 * Team members are Kirby user accounts. Their profile fields (job title, bio,
 * LinkedIn, show on team page) come from the team-profile section in the
 * wove-mind plugin's user blueprints; the profile picture is the avatar.
 *
 * /our-people renders page('our-people') if it exists, otherwise a virtual
 * page using the team template, so the page works before it's created
 * in the Panel.
 */

App::plugin('wove/team', [
	'routes' => [
		[
			'pattern' => 'our-people',
			'action'  => function () {
				return page('our-people') ?? Page::factory([
					'slug'     => 'our-people',
					'template' => 'team',
					'model'    => 'default',
					'content'  => ['title' => 'Our People'],
				]);
			},
		],
	],
]);

/**
 * Stable slug for a user, used in `author:{slug}` filters and page anchors.
 */
function wove_author_slug(User $user): string
{
	return Str::slug($user->name()->value() ?: $user->email());
}

/**
 * Users shown on the Our People page, sorted by name.
 */
function wove_team_members(): array
{
	$members = kirby()->users()
		->filter(fn ($u) => $u->content()->get('showOnTeam')->toBool(true))
		->sortBy('name', 'asc');

	return $members->values();
}

/**
 * Listed Wove Mind entries credited to a user (author set and "Show author" on), newest first.
 */
function wove_author_entries(User $user): Pages
{
	$parent = site()->find('wove-mind');
	if (!$parent) return new Pages();

	return $parent->children()->listed()
		->filter(fn ($p) => wove_entry_author($p)?->id() === $user->id())
		->sortBy('date', 'desc');
}

/**
 * The credited author of a Wove Mind entry, or null when none is shown.
 */
function wove_entry_author(Page $entry): ?User
{
	if (!$entry->show_author()->toBool() || $entry->author()->isEmpty()) return null;
	return $entry->author()->toUser();
}

/**
 * A team member's role: the "Role" profile field (stored as `jobTitle`,
 * since Kirby reserves `role` on users), or failing that the
 * "Author role" on their most recent credited Wove Mind entry.
 */
function wove_member_role(User $user): string
{
	$role = trim((string) $user->content()->get('jobTitle')->value());
	if ($role !== '') return $role;

	foreach (wove_author_entries($user) as $entry) {
		$entryRole = trim((string) $entry->author_role()->value());
		if ($entryRole !== '') return $entryRole;
	}

	return '';
}
