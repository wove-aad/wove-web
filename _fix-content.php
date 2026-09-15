<?php
/**
 * One-time fix for the duplicate wove-mind directory.
 * Visit: https://staging.wove.group/_fix-content.php
 *
 * GET       → diagnosis
 * GET ?fix=1 → removes content/wove-mind/ (the git-deployed duplicate)
 *
 * DELETE THIS FILE after the fix is confirmed.
 */

function rrmdir($dir) {
    $removed = [];
    foreach (array_diff(scandir($dir), ['.', '..']) as $item) {
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            $removed = array_merge($removed, rrmdir($path));
        } else {
            unlink($path);
            $removed[] = $path;
        }
    }
    rmdir($dir);
    $removed[] = $dir;
    return $removed;
}

$contentDir = __DIR__ . '/content';
$dirs = [];

foreach (scandir($contentDir) as $item) {
    if ($item === '.' || $item === '..') continue;
    if (strpos($item, 'wove-mind') !== false && is_dir($contentDir . '/' . $item)) {
        $children = array_diff(scandir($contentDir . '/' . $item), ['.', '..']);
        $dirs[$item] = [
            'path'     => $contentDir . '/' . $item,
            'children' => array_values($children),
            'count'    => count($children),
        ];
    }
}

header('Content-Type: text/plain; charset=utf-8');

echo "=== Wove Mind directory diagnosis ===\n\n";

if (empty($dirs)) {
    echo "No wove-mind directories found in content/.\n";
    exit;
}

foreach ($dirs as $name => $info) {
    echo "content/{$name}/  ({$info['count']} items)\n";
    foreach ($info['children'] as $child) {
        echo "  - {$child}\n";
    }
    echo "\n";
}

$numbered = array_filter(array_keys($dirs), fn($d) => preg_match('/^\d+_wove-mind$/', $d));
$hasPlain = isset($dirs['wove-mind']);

if ($hasPlain && !empty($numbered)) {
    $numberedName = reset($numbered);
    echo "DIAGNOSIS: content/{$numberedName}/ is Karl's real page ({$dirs[$numberedName]['count']} entries).\n";
    echo "           content/wove-mind/ is the git-deployed duplicate ({$dirs['wove-mind']['count']} items — test entries).\n";
    echo "           Removing content/wove-mind/ will let Kirby resolve to Karl's page.\n\n";

    if (isset($_GET['fix']) && $_GET['fix'] === '1') {
        $target = $contentDir . '/wove-mind';
        $removed = rrmdir($target);
        echo "FIXED: Removed content/wove-mind/ (" . count($removed) . " files/dirs deleted)\n\n";
        echo "Karl's entries in content/{$numberedName}/ should now be visible.\n";
        echo "Reload the Panel and check /wove-mind on the public site.\n";
    } else {
        echo "To fix: visit this URL with ?fix=1\n";
    }
} elseif (!$hasPlain && !empty($numbered)) {
    echo "Only the numbered directory exists — no duplicate. Entries should be working.\n";
} else {
    echo "Unexpected state — manual inspection needed.\n";
}
