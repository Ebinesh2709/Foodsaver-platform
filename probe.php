<?php
// FoodSaver Server Probe — deletes itself after running
// Access: https://foodsaver.infinityfreeapp.com/probe.php?k=fs2025
if (($_GET['k'] ?? '') !== 'fs2025') { http_response_code(403); die('Forbidden'); }

$root = $_SERVER['DOCUMENT_ROOT'];

function fs_scan_dir($dir, $base) {
    $result = [];
    $items  = @scandir($dir);
    if (!$items) return $result;
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        $rel  = ltrim(str_replace($base, '', $path), '/\\');
        if (is_dir($path)) {
            $result = array_merge($result, fs_scan_dir($path, $base));
        } else {
            $result[] = ['path' => $rel, 'size' => filesize($path), 'ext' => pathinfo($item, PATHINFO_EXTENSION)];
        }
    }
    return $result;
}

$allFiles = scanDir($root, $root);
$htmlFiles = array_filter($allFiles, fn($f) => $f['ext'] === 'html' || $f['ext'] === 'htm');

// Delete any HTML files found
$deleted = [];
foreach ($htmlFiles as $f) {
    $full = $root . DIRECTORY_SEPARATOR . $f['path'];
    if (@unlink($full)) {
        $deleted[] = $f['path'];
    }
}

// Output
header('Content-Type: text/plain');
echo "=== SERVER DOCUMENT ROOT ===\n";
echo $root . "\n\n";

echo "=== ALL FILES (" . count($allFiles) . " total) ===\n";
foreach ($allFiles as $f) {
    echo sprintf("  %-60s %s\n", $f['path'], $f['ext']);
}

echo "\n=== HTML FILES FOUND AND DELETED ===\n";
if ($deleted) {
    foreach ($deleted as $d) echo "  DELETED: $d\n";
} else {
    echo "  None found — server is clean!\n";
}

// Self-delete
@unlink(__FILE__);
echo "\n[probe.php deleted]\n";
