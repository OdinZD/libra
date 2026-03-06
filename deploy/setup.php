<?php

/**
 * Temporary setup script for cPanel deployment.
 *
 * Upload to /home/librazad/public_html/setup.php
 * Visit https://libra-zadar.hr/setup.php
 * DELETE THIS FILE IMMEDIATELY AFTER USE!
 */

// Change to the Laravel project root
$laravelRoot = dirname(__DIR__) . '/libra';
chdir($laravelRoot);

require $laravelRoot . '/vendor/autoload.php';
$app = require_once $laravelRoot . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<pre style='font-family: monospace; font-size: 14px; padding: 20px;'>";
echo "=== Libra cPanel Setup ===\n\n";

try {
    echo "1. Generating application key...\n";
    $kernel->call('key:generate', ['--force' => true]);
    echo "   Done.\n\n";

    echo "2. Running database migrations...\n";
    $kernel->call('migrate', ['--force' => true]);
    echo "   Done.\n\n";

    echo "3. Creating storage symlink...\n";
    $kernel->call('storage:link');
    echo "   Done.\n\n";

    echo "4. Caching configuration...\n";
    $kernel->call('config:cache');
    echo "   Done.\n\n";

    echo "5. Caching routes...\n";
    $kernel->call('route:cache');
    echo "   Done.\n\n";

    echo "6. Caching views...\n";
    $kernel->call('view:cache');
    echo "   Done.\n\n";

    echo "=========================\n";
    echo "ALL DONE!\n";
    echo "=========================\n\n";
} catch (\Throwable $e) {
    echo "\n\nERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";
echo "<h2 style='color: red; font-family: sans-serif; padding: 20px;'>DELETE THIS FILE (setup.php) FROM public_html NOW!</h2>";
