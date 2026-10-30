<?php
/**
 * One-off restore (2026-10-30) for ten Wove Mind entries whose title,
 * text and image were cleared on staging on 30 Oct. Open /wove-restore
 * while signed in to the Panel: it lists what it will fill, and the
 * button fills it. It only fills fields that are empty, never replaces
 * anything, and is safe to run twice.
 * - Titles for all ten; the full text for the two whose text survived.
 * - The featured image, when the entry has exactly one image file (the
 *   others list their images to pick from in the Panel).
 * Remove this plugin once it has been run.
 */

use Kirby\Cms\App as Kirby;

function wove_restore_data(): array
{
	return [
		'longread-2026-09-03-tysq' => ['title' => 'What Irish design students told us about the future of education'],
		'longread-2026-09-29-0mzd' => ['title' => 'How we used strategic design to help Irish citizens transition towards a circular economy'],
		'longread-2026-09-29-loby' => ['title' => 'Building a future proofed platform for multiple audiences'],
		'longread-2026-09-29-rj4p' => ['title' => 'How do you build a brand that makes circular living feel desirable and accessible?'],
		'longread-2026-10-07-tdu1' => ['title' => 'Who Do They Think You Are?', 'body' => '<p>Most Irish citizens are unaware of how their daily digital activities generate data that is tracked, consolidated and sold by private data brokers. These hidden algorithmic categorisations can have real-world impacts on access to information, opportunities and resources. With funding from Research Ireland and working with partners at the ADAPT Centre, we developed an engaging way to foster dialogue about data profiling and empower the public with enhanced digital literacy during Science Week 2025.</p><p>Rather than lectures or reports, we created "Who Do They Think You Are?"—a playful, multi-channel initiative making the invisible data economy visible. We designed participatory experiences where people could discover how their digital choices contribute to hidden algorithmic categorisations, balancing education with entertainment through interactive exhibitions, speculative design, game design and social events.</p><p>The campaign included a pop-up exhibition at the Douglas Hyde Gallery inviting passersby to engage with interactive boards exploring data tracking, a speculative design project with NCAD students imagining future data tracking scenarios (exhibited at Beta Festival), an educational table quiz night and an online game created using insights from the exhibition. All materials were designed with accessibility and universal design principles at their core.</p><p>The partnership model—bringing together research institutions, art colleges, festivals and galleries—demonstrated how design and creative engagement can make complex technical topics accessible and culturally relevant, helping people understand the data systems shaping their digital lives.</p>'],
		'thread-2026-09-03-1qd4' => ['title' => 'Public Services conference ❤️', 'body' => '<p>Head spinning in a great way after the Eolas Magazine Public Services conference yesterday. Was particularly excited at just how central design was to every conversation at all levels.</p><p>Core to every talk and panel was a sense that change is now the most persistent factor in making a system and services work for people in the 21st century. That every department, unit, team, project is having to transform their ways of working to be ready to meet and successfully navigate growing complexity and challenges.</p><p>Which I think makes sense of why design was so central. It’s an approach geared for change - for understanding the true nature of a problem space and working to transform it to an opportunity space with solutions and ways forward focused on real world impact.</p><p>So much deeply inspiring work from so many people dedicated to putting real people, contexts and lived experiences at the heart of system transformation that’s worth seeking out!</p>'],
		'thread-2026-09-03-497o' => ['title' => 'IDI Awards 2026'],
		'thread-2026-09-03-nlcw' => ['title' => 'Nice coverage of our data privacy game ❤️'],
		'thread-2026-09-03-wuda' => ['title' => 'Shaping Ireland’s Creative Future'],
		'whatif-2026-10-07-kutq' => ['title' => '31 Days of Being More Human'],
	];
}

/** What would be filled for each entry */
function wove_restore_plan(): array
{
	$plan = [];
	foreach (wove_restore_data() as $slug => $fields) {
		$page = kirby()->page('wove-mind/' . $slug);
		if (!$page) { $plan[$slug] = ['missing' => true]; continue; }
		$content = $page->content();
		$fill = [];
		$title = trim((string) $content->get('title')->value());
		if ($title === '' || $title === 'Untitled') $fill['title'] = $fields['title'];
		if (!empty($fields['body']) && $content->get('body')->isEmpty() && $content->get('blocks')->isEmpty()) $fill['body'] = $fields['body'];
		$images = $page->images();
		if ($content->get('image')->isEmpty() && $images->count() === 1) {
			$file = $images->first();
			$fill['image'] = [$file->uuid() ? $file->uuid()->toString() : $file->filename()];
		}
		$plan[$slug] = ['page' => $page, 'fill' => $fill, 'images' => $images->pluck('filename')];
	}
	return $plan;
}

Kirby::plugin('wove/restore', [
	'routes' => [
		[
			'pattern' => 'wove-restore',
			'method'  => 'GET|POST',
			'action'  => function () {
				$kirby = kirby();
				if (!$kirby->user()) return go('panel/login');
				$done = [];
				if ($kirby->request()->is('POST')) {
					if (csrf(get('csrf')) !== true) return new Kirby\Http\Response('The form had expired. Go back, reload and try again.', 'text/plain', 403);
					foreach (wove_restore_plan() as $slug => $item) {
						if (empty($item['fill'])) continue;
						$item['page']->update($item['fill']);
						$done[$slug] = array_keys($item['fill']);
					}
				}
				$plan = wove_restore_plan();
				$h = fn ($s) => htmlspecialchars((string) $s, ENT_QUOTES);
				$out = '<!doctype html><meta charset="utf-8"><title>Restore Wove Mind entries</title><body style="font:16px/1.5 system-ui;max-width:52rem;margin:3rem auto;padding:0 1rem">';
				$out .= '<h1>Restore Wove Mind entries</h1>';
				if ($done) $out .= '<p style="background:#e1f5ee;padding:1rem;border-radius:8px">Filled: ' . $h(implode('; ', array_map(fn ($s, $f) => $s . ' (' . implode(', ', $f) . ')', array_keys($done), $done))) . '</p>';
				$out .= '<p>Only empty fields are filled. Nothing is replaced.</p><ul>';
				foreach ($plan as $slug => $item) {
					if (!empty($item['missing'])) { $out .= '<li><b>' . $h($slug) . '</b>: not found</li>'; continue; }
					$fill = $item['fill'];
					$out .= '<li style="margin-bottom:.75rem"><b>' . $h(wove_restore_data()[$slug]['title']) . '</b> <small>(' . $h($slug) . ')</small><br>';
					$out .= $fill ? 'Will fill: ' . $h(implode(', ', array_keys($fill))) : 'Nothing to fill';
					if ($item['page']->content()->get('image')->isEmpty() && empty($fill['image'])) {
						$out .= '<br>Image: ' . ($item['images'] ? 'pick one in the Panel from ' . $h(implode(', ', $item['images'])) : 'no image files on this entry');
					}
					$out .= ' · <a href="' . $h($item['page']->panel()->url()) . '">Open in Panel</a></li>';
				}
				$out .= '</ul>';
				if (array_filter($plan, fn ($i) => !empty($i['fill']))) {
					$out .= '<form method="post"><input type="hidden" name="csrf" value="' . csrf() . '"><button style="font:inherit;padding:.6rem 1.2rem;border-radius:99px;border:0;background:#111;color:#fff;cursor:pointer">Fill these fields</button></form>';
				}
				return $out . '</body>';
			},
		],
	],
]);
