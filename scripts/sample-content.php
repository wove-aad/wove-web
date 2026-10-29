<?php
/**
 * Sample content for local development: site tags, three case studies and
 * a mix of Wove Mind posts with placeholder images, so the homepage, menu
 * and feed have something to show.
 *
 * Run from the repo root:  php scripts/sample-content.php
 *
 * Writes only gitignored folders (content/site.txt, content/wove-mind,
 * content/work) and overwrites them each run. Never run it on a server:
 * it would replace real content, so it refuses to run outside the command
 * line. Placeholder images need PHP's GD extension; without it the posts
 * are created without images.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$root = dirname(__DIR__) . '/content';
if (!is_dir($root . '/services')) {
    exit("Run this from the wove-web repo (content/services not found).\n");
}

function remove_dir(string $dir): void
{
    if (!is_dir($dir)) return;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $f) {
        $f->isDir() ? rmdir($f->getPathname()) : unlink($f->getPathname());
    }
    rmdir($dir);
}

function write_page(string $file, array $fields): void
{
    @mkdir(dirname($file), 0777, true);
    $parts = [];
    foreach ($fields as $key => $value) $parts[] = $key . ': ' . $value;
    file_put_contents($file, implode("\n\n----\n\n", $parts) . "\n");
}

// A soft two-colour gradient, as a stand-in photo
function placeholder(string $file, int $w, int $h, string $from, string $to): bool
{
    if (!function_exists('imagecreatetruecolor')) return false;
    $im = imagecreatetruecolor($w, $h);
    [$r1, $g1, $b1] = sscanf($from, '#%02x%02x%02x');
    [$r2, $g2, $b2] = sscanf($to, '#%02x%02x%02x');
    for ($y = 0; $y < $h; $y++) {
        $t = $y / max(1, $h - 1);
        $c = imagecolorallocate($im, (int) ($r1 + ($r2 - $r1) * $t), (int) ($g1 + ($g2 - $g1) * $t), (int) ($b1 + ($b2 - $b1) * $t));
        imageline($im, 0, $y, $w, $y, $c);
    }
    imagejpeg($im, $file, 85);
    return true;
}

remove_dir($root . '/wove-mind');
remove_dir($root . '/work');

// Site: editorial tags (impact areas)
$tags = ['Circular economy', 'Community engagement', 'Climate and nature', 'Health and wellbeing', 'Arts and culture', 'Education and learning'];
$yaml = '';
foreach ($tags as $name) {
    $slug  = strtolower(str_replace(' ', '-', $name));
    $yaml .= "\n-\n  name: $name\n  slug: $slug\n  intro: Work and thinking on " . strtolower($name) . ".\n  active: true";
}
write_page($root . '/site.txt', ['Title' => 'Wove', 'Tags' => $yaml]);

// Case studies (the Work page's children)
write_page($root . '/work/work.txt', ['Title' => 'Our work']);
$cases = [
    ['circular', 'Circular.ie', 'Building Ireland’s national circular economy platform', 'A national campaign and platform helping citizens move towards a circular economy.', 'brand, digital', 'circular-economy, community-engagement', 'public-service', '2026-09-20', '#ed8c7c', '#3a3935'],
    ['iaf', 'Irish Architecture Foundation', 'A bold new chapter for IAF’s five-year strategy', 'Shaping a strategy that brings architecture to more people.', 'strategy', 'arts-and-culture', 'arts-and-culture', '2026-08-14', '#d5faff', '#2a50f3'],
    ['solace', 'The Solace', 'A new kind of offering helping people navigate death', 'A digital service that helps families through the practical side of loss.', 'digital, labs', 'health-and-wellbeing', 'non-profit', '2026-07-02', '#e0bdff', '#3d3b37'],
];
foreach ($cases as $i => [$slug, $name, $title, $summary, $services, $impact, $sector, $date, $c1, $c2]) {
    $dir    = $root . '/work/' . ($i + 1) . '_' . $slug;
    $fields = ['Title' => $name, 'Eyebrow' => $name, 'HeroTitle' => $title, 'Summary' => $summary, 'SubStatement' => $summary, 'Date' => $date, 'Services' => $services, 'ImpactAreas' => $impact, 'Sectors' => $sector];
    @mkdir($dir, 0777, true);
    if (placeholder($dir . '/hero.jpg', 1600, 1000, $c1, $c2)) $fields['CaseStudyImages'] = '- hero.jpg';
    write_page($dir . '/case-study.txt', $fields);
}

// Wove Mind posts, newest first
$posts = [
    ['longread', 'How do you launch a national campaign for something people have never heard of?', 'circular', 'brand', 'circular-economy, community-engagement', '2026-10-24', [1600, 1100, '#f3eeeb', '#ed8c7c']],
    ['thread', 'Building a future proofed platform for multiple audiences', 'circular', 'digital', 'circular-economy', '2026-10-21', [1200, 800, '#e1f5ee', '#256446']],
    ['whatif', 'What if libraries were also radio stations?', '', 'labs', 'community-engagement, arts-and-culture', '2026-10-18', null],
    ['longread', 'How citizen workshops helped us understand what circularity really means for people', 'circular', 'labs, strategy', 'circular-economy', '2026-10-15', [1200, 1500, '#d5faff', '#3a3935']],
    ['thread', 'Welcome Phoebe, our new project manager', '', 'strategy', 'education-and-learning', '2026-10-10', [1000, 1000, '#e0bdff', '#85827d']],
    ['spark', 'Spark', '', 'brand', 'arts-and-culture', '2026-10-07', null],
    ['longread', 'Reshaping how an arts organisation plans for the next five years', 'iaf', 'strategy', 'arts-and-culture', '2026-10-02', [1600, 1000, '#eae8e3', '#2a50f3']],
    ['thread', 'Designing with families for the hardest moments', 'solace', 'digital, labs', 'health-and-wellbeing', '2026-09-27', [1200, 900, '#f3eeeb', '#3d3b37']],
    ['whatif', 'What if every town had a repair café?', '', 'labs', 'circular-economy, climate-and-nature', '2026-09-22', null],
];
$body = 'A short lead that sets out what the piece is about, written long enough to run past a single line on a card.';
write_page($root . '/wove-mind/wove-mind.txt', [
    'Title'    => 'Wove Mind',
    'Subtitle' => 'Strategy, design and technology. Woven together to shape a better Ireland.',
    'Featured' => '- wove-mind/post-1',
]);
foreach ($posts as $i => [$format, $title, $case, $services, $postTags, $date, $img]) {
    $n      = $i + 1;
    $dir    = $root . '/wove-mind/' . $n . '_post-' . $n;
    $fields = [
        'Title'    => $title,
        'Format'   => $format,
        'Date'     => $date,
        'Body'     => $format === 'spark' ? 'Small ideas, made together, add up to lasting change.' : $body,
        'Services' => $services,
        'Tags'     => $postTags,
    ];
    if ($case) $fields['Case_study'] = '- work/' . $case;
    @mkdir($dir, 0777, true);
    if ($img && placeholder($dir . '/image.jpg', ...$img)) $fields['Image'] = '- image.jpg';
    write_page($dir . '/wove-mind-entry.txt', $fields);
}

echo "Sample content written: " . count($cases) . " case studies, " . count($posts) . " posts"
    . (function_exists('imagecreatetruecolor') ? ", with placeholder images" : " (no images: PHP's GD extension isn't enabled)")
    . ".\n";
