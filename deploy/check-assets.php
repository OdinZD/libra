<?php

/**
 * Check why Vite assets aren't loading.
 * Upload to /home/librazad/public_html/check-assets.php
 * DELETE AFTER USE!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre style='font-family: monospace; font-size: 13px; padding: 20px;'>";
echo "=== VITE ASSET DIAGNOSTICS ===\n\n";

$publicHtml = __DIR__;
$laravelRoot = dirname(__DIR__) . '/libra';

// 1. Check for stale 'hot' file (makes Laravel use dev server instead of built files)
echo "1. HOT FILE CHECK\n";
$hotFile = $publicHtml . '/hot';
if (file_exists($hotFile)) {
    echo "   FOUND! This is the problem - Laravel thinks Vite dev server is running.\n";
    echo "   Content: " . file_get_contents($hotFile) . "\n";
    echo "   DELETING...\n";
    unlink($hotFile);
    echo "   Deleted.\n\n";
} else {
    echo "   No hot file (good).\n\n";
}

// 2. Check build directory
echo "2. BUILD DIRECTORY\n";
echo "   public_html/build/: " . (is_dir($publicHtml . '/build') ? 'EXISTS' : 'MISSING!') . "\n";
echo "   manifest.json: " . (file_exists($publicHtml . '/build/manifest.json') ? 'EXISTS' : 'MISSING!') . "\n";
echo "   .vite/manifest.json: " . (file_exists($publicHtml . '/build/.vite/manifest.json') ? 'EXISTS' : 'MISSING') . "\n";

if (file_exists($publicHtml . '/build/manifest.json')) {
    echo "   manifest.json content:\n";
    echo "   " . file_get_contents($publicHtml . '/build/manifest.json') . "\n";
}

// List all files in build/
echo "\n   All files in build/:\n";
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($publicHtml . '/build'));
foreach ($iterator as $file) {
    if ($file->isFile()) {
        echo "   " . str_replace($publicHtml . '/build/', '', $file->getPathname()) . " (" . $file->getSize() . " bytes)\n";
    }
}

// 3. Check Laravel public_path resolution
echo "\n3. LARAVEL PUBLIC_PATH\n";
chdir($laravelRoot);
require $laravelRoot . '/vendor/autoload.php';
$app = require_once $laravelRoot . '/bootstrap/app.php';

// Set public path like index.php does
$app->usePublicPath($publicHtml);

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "   public_path(): " . public_path() . "\n";
echo "   public_path('build/manifest.json'): " . public_path('build/manifest.json') . "\n";
echo "   File exists: " . (file_exists(public_path('build/manifest.json')) ? 'YES' : 'NO') . "\n";

// 4. Test what @vite would generate
echo "\n4. VITE TAG OUTPUT\n";
try {
    $html = Illuminate\Support\Facades\Vite::toHtml();
    echo "   Vite::toHtml():\n";
    echo "   " . htmlspecialchars($html) . "\n";
} catch (\Throwable $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

// Try generating tags manually
echo "\n5. MANUAL VITE TAG TEST\n";
try {
    $tags = app(\Illuminate\Foundation\Vite::class)(['resources/css/app.css', 'resources/js/app.js']);
    echo "   Generated tags:\n";
    echo "   " . htmlspecialchars((string)$tags) . "\n";
} catch (\Throwable $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

// 6. Check if assets are actually accessible via HTTP
echo "\n6. HTTP ACCESS TEST\n";
$files = [
    '/build/manifest.json',
    '/build/assets/app-DFcFPXN6.css',
    '/build/assets/app-CbKOo3wb.js',
];
foreach ($files as $f) {
    $ch = curl_init('https://libra-zadar.hr' . $f);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "   $f -> HTTP $code\n";
}

echo "\n=== END ===\n";
echo "</pre>";
echo "<h2 style='color: red; font-family: sans-serif;'>DELETE THIS FILE NOW!</h2>";
