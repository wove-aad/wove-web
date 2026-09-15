<?php
/**
 * One-time diagnostic + fix for the duplicate wove-mind directory.
 * Visit: https://staging.wove.group/_fix-content.php
 *
 * GET  → shows what's in content/ (diagnosis only)
 * GET ?fix=1 → removes the empty content/wove-mind/ directory
 *
 * DELETE THIS FILE after the fix is confirmed.
 */

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
    echo "No wove-mind directories found in content/. Something else is going on.\n";
    exit;
}

foreach ($dirs as $name => $info) {
    echo "content/{$name}/  ({$info['count']} items)\n";
    foreach ($info['children'] as $child) {
        echo "  - {$child}\n";
    }
    echo "\n";
}

$emptyDraft = isset($dirs['wove-mind']) && $dirs['wove-mind']['count'] <= 1;
$numbered   = array_filter(array_keys($dirs), fn($d) => preg_match('/^\d+_wove-mind$/', $d));

if ($emptyDraft && !empty($numbered)) {
    $numberedName = reset($numbered);
    echo "DIAGNOSIS: content/wove-mind/ is the empty duplicate (created by deploy).\n";
    echo "           content/{$numberedName}/ is Karl's real page with entries.\n\n";

    if (isset($_GET['fix']) && $_GET['fix'] === '1') {
        $target = $contentDir . '/wove-mind';
        $removed = [];
        foreach (array_diff(scandir($target), ['.', '..']) as $f) {
            unlink($target . '/' . $f);
            $removed[] = $f;
        }
        if (rmdir($target)) {
            echo "FIXED: Removed content/wove-mind/ (deleted files: " . implode(', ', $removed) . ")\n";
            echo "Karl's entries in content/{$numberedName}/ should now be visible.\n";
            echo "\nReload the Panel and check /wove-mind on the public site.\n";
        } else {
            echo "ERROR: Could not remove content/wove-mind/ — it may have subdirectories.\n";
        }
    } else {
        echo "To fix: visit this URL with ?fix=1 appended:\n";
        echo "  " . strtok($_SERVER['REQUEST_URI'], '?') . "?fix=1\n";
    }
} elseif (!$emptyDraft && !empty($numbered)) {
    echo "DIAGNOSIS: content/wove-mind/ has content too — manual inspection needed.\n";
} else {
    echo "DIAGNOSIS: Only one wove-mind directory found. The issue may be something else.\n";
}
