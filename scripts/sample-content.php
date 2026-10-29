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
$tags = [
    ['Arts, culture & humanities', 'arts-culture-and-humanities', 'Helping organisations evolve their models to better serve audiences in a changing world.'],
    ['Public, social & civic services', 'public-social-and-civic-services', 'Working with leaders to implement change and develop more sustainable and equitable models.'],
    ['Mission-led organisations', 'mission-led-organisations', 'We support founders and businesses dedicated to making people, planet and profit a reality.'],
    ['Education & learning', 'education-and-learning', 'We work with students and educators, helping them explore ways to make learning more accessible, impactful and inclusive.'],
    ['Circular economy', 'circular-economy', 'Helping people and organisations move towards a circular economy.'],
    ['Data and privacy', 'data-and-privacy', 'How our everyday data is tracked and sold.'],
    ['Climate and nature', 'climate-and-nature', 'Work and thinking on climate and nature.'],
];
$yaml = '';
foreach ($tags as [$name, $slug, $intro]) {
    $yaml .= "\n-\n  name: $name\n  slug: $slug\n  intro: $intro\n  active: true";
}
write_page($root . '/site.txt', ['Title' => 'Wove', 'Tags' => $yaml]);

// Case studies (the Work page's children)
write_page($root . '/work/work.txt', ['Title' => 'Our work']);
$cases = [
    ['circular', 'Circular.ie', 'Building Ireland’s national circular economy platform', 'A national campaign and platform helping citizens move towards a circular economy.', 'brand, digital', 'circular-economy, public-social-and-civic-services', 'public-service', '2026-09-20', '#ed8c7c', '#3a3935'],
    ['iaf', 'Irish Architecture Foundation', 'A bold new chapter for IAF’s five-year strategy', 'Shaping a strategy that brings architecture to more people.', 'strategy', 'arts-culture-and-humanities', 'arts-culture-and-humanities', '2026-08-14', '#d5faff', '#2a50f3'],
    ['solace', 'The Solace', 'A new kind of offering helping people navigate death', 'A digital service that helps families through the practical side of loss.', 'digital, labs', 'mission-led-organisations', 'non-profit', '2026-07-02', '#e0bdff', '#3d3b37'],
];
foreach ($cases as $i => [$slug, $name, $title, $summary, $services, $impact, $sector, $date, $c1, $c2]) {
    $dir    = $root . '/work/' . ($i + 1) . '_' . $slug;
    $fields = ['Title' => $name, 'Eyebrow' => $name, 'HeroTitle' => $title, 'Summary' => $summary, 'SubStatement' => $summary, 'Date' => $date, 'Services' => $services, 'ImpactAreas' => $impact, 'Sectors' => $sector];
    @mkdir($dir, 0777, true);
    if (placeholder($dir . '/hero.jpg', 1600, 1000, $c1, $c2)) $fields['CaseStudyImages'] = '- hero.jpg';
    if ($slug === 'circular') {
        // The Figma client page copy
        $fields['HeroTitle'] = 'How do you launch a national campaign for something people have never heard of?';
        $fields['Summary']   = 'Ireland’s shift to a circular economy has plenty of policy activity, but little to help ordinary people take part. With the Rediscovery Centre, we started with the foundations: four award-winning strategic frameworks that turn national ambition into everyday action. Everything since, from the brand and platform to the national campaign, is built on them.';
        $fields['Stats']     = "\n-\n  value: 10 years\n  label: A national behaviour change programme, designed from the citizen up.\n-\n  value: 4 models\n  label: Award-winning strategic frameworks that everything else is built on.\n-\n  value: 12,000\n  label: Visits to Circular.ie in its first year.\n-\n  value: 40\n  label: Community groups engaged through the 2026 campaign.";
    }
    write_page($dir . '/case-study.txt', $fields);
}

// Wove Mind posts, newest first
$posts = [
    ['longread', 'How do you launch a national campaign for something people have never heard of?', 'circular', 'brand', 'circular-economy, public-social-and-civic-services', '2026-10-24', [1600, 1100, '#f3eeeb', '#ed8c7c']],
    ['thread', 'Building a future proofed platform for multiple audiences', 'circular', 'digital', 'circular-economy', '2026-10-21', [1200, 800, '#e1f5ee', '#256446']],
    ['thread', 'Circular.ie shortlisted for the Irish Design Awards', 'circular', 'brand', 'circular-economy', '2026-10-20', [1200, 800, '#ed8c7c', '#f3eeeb'], 'news'],
    ['whatif', 'What if libraries were also radio stations?', '', 'labs', 'public-social-and-civic-services, arts-culture-and-humanities', '2026-10-18', null],
    ['longread', 'How citizen workshops helped us understand what circularity really means for people', 'circular', 'labs, strategy', 'circular-economy', '2026-10-15', [1200, 1500, '#d5faff', '#3a3935']],
    ['thread', 'Welcome Phoebe, our new project manager', '', 'strategy', 'education-and-learning', '2026-10-10', [1000, 1000, '#e0bdff', '#85827d']],
    ['spark', 'Spark', '', 'brand', 'arts-culture-and-humanities', '2026-10-07', null],
    ['longread', 'Reshaping how an arts organisation plans for the next five years', 'iaf', 'strategy', 'arts-culture-and-humanities', '2026-10-02', [1600, 1000, '#eae8e3', '#2a50f3']],
    ['thread', 'Designing with families for the hardest moments', 'solace', 'digital, labs', 'mission-led-organisations', '2026-09-27', [1200, 900, '#f3eeeb', '#3d3b37']],
    ['whatif', 'What if every town had a repair café?', '', 'labs', 'circular-economy, climate-and-nature', '2026-09-22', null],
    ['longread', 'Evolving the model for an art and science network', 'iaf', 'labs', 'arts-culture-and-humanities', '2026-09-18', [1600, 1100, '#3a3935', '#d3cae0']],
    ['thread', 'An innovation starter kit for 1,100 staff', '', 'labs', 'education-and-learning', '2026-09-14', [1200, 800, '#f3eeeb', '#d2cec7']],
    ['longread', 'Who Do They Think You Are?', '', 'labs', 'data-and-privacy', '2026-09-10', [1200, 900, '#e0bdff', '#111111']],
    ['thread', 'Models to support co-design in the community', 'solace', 'labs', 'public-social-and-civic-services', '2026-09-06', [1400, 900, '#eae8e3', '#85827d']],
    ['thread', 'Testing a library radio pilot with three branches', '', 'labs', 'arts-culture-and-humanities', '2026-09-02', [1200, 800, '#d5faff', '#256446']],
    ['longread', 'What a decade of futures work taught us', '', 'labs', 'climate-and-nature', '2026-08-28', [1600, 1000, '#cddccb', '#3a3935']],
    ['thread', 'Diaries as a research method', '', 'labs', 'mission-led-organisations', '2026-08-24', [1200, 800, '#ed8c7c', '#f3eeeb']],
    ['thread', 'Mapping who a service leaves out', '', 'labs', 'public-social-and-civic-services', '2026-08-20', [1200, 800, '#e1f5ee', '#3d3b37']],
    ['longread', 'Provotypes: small, rough and on purpose', '', 'labs', 'data-and-privacy', '2026-08-16', [1400, 1000, '#d3cae0', '#3a3935']],
];
$body = 'A short lead that sets out what the piece is about, written long enough to run past a single line on a card.';
write_page($root . '/wove-mind/wove-mind.txt', [
    'Title'    => 'Wove Mind',
    'Subtitle' => 'Strategy, design and technology. Woven together to shape a better Ireland.',
    'Featured' => '- wove-mind/post-1',
]);
foreach ($posts as $i => $row) {
    [$format, $title, $case, $services, $postTags, $date, $img] = $row;
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
    if (isset($row[7])) $fields['PostType'] = $row[7];
    @mkdir($dir, 0777, true);
    if ($img && placeholder($dir . '/image.jpg', ...$img)) $fields['Image'] = '- image.jpg';
    write_page($dir . '/wove-mind-entry.txt', $fields);
}

echo "Sample content written: " . count($cases) . " case studies, " . count($posts) . " posts"
    . (function_exists('imagecreatetruecolor') ? ", with placeholder images" : " (no images: PHP's GD extension isn't enabled)")
    . ".\n";
