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

echo "<pre style='font-family: monospace; font-size: 14px; padding: 20px;'>";
echo "=== Libra cPanel Setup ===\n\n";

try {
    // Step 0: Ensure APP_KEY exists in .env BEFORE booting Laravel
    echo "0. Checking APP_KEY in .env...\n";
    $envPath = $laravelRoot . '/.env';
    $envContent = file_get_contents($envPath);

    if (preg_match('/^APP_KEY=\s*$/m', $envContent) || !preg_match('/^APP_KEY=/m', $envContent)) {
        // Generate a key and write it directly to .env
        $key = 'base64:' . base64_encode(random_bytes(32));
        if (preg_match('/^APP_KEY=/m', $envContent)) {
            $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $envContent);
        } else {
            $envContent .= "\nAPP_KEY=" . $key . "\n";
        }
        file_put_contents($envPath, $envContent);
        echo "   Generated new APP_KEY.\n\n";
    } else {
        echo "   APP_KEY already set.\n\n";
    }

    // Step 1: Delete cached config file directly (avoid booting Laravel with stale cache)
    echo "1. Removing stale config cache...\n";
    $cachedConfig = $laravelRoot . '/bootstrap/cache/config.php';
    if (file_exists($cachedConfig)) {
        unlink($cachedConfig);
        echo "   Deleted bootstrap/cache/config.php\n\n";
    } else {
        echo "   No cached config found.\n\n";
    }

    // Now boot Laravel with fresh .env
    require $laravelRoot . '/vendor/autoload.php';
    $app = require_once $laravelRoot . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

    echo "2. Clearing route/view caches...\n";
    $kernel->call('route:clear');
    $kernel->call('view:clear');
    echo "   Done.\n\n";

    echo "3. Running database migrations...\n";
    $kernel->call('migrate', ['--force' => true]);
    echo "   Done.\n\n";

    echo "4. Creating storage symlink...\n";
    $kernel->call('storage:link');
    echo "   Done.\n\n";

    echo "5. Caching configuration...\n";
    $kernel->call('config:cache');
    echo "   Done.\n\n";

    echo "6. Caching routes...\n";
    $kernel->call('route:cache');
    echo "   Done.\n\n";

    echo "7. Caching views...\n";
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
