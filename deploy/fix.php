<?php

/**
 * Emergency fix script - writes .env and runs full setup.
 *
 * Upload to /home/librazad/public_html/fix.php
 * Visit https://libra-zadar.hr/fix.php
 * DELETE THIS FILE IMMEDIATELY AFTER USE!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$laravelRoot = dirname(__DIR__) . '/libra';

echo "<pre style='font-family: monospace; font-size: 14px; padding: 20px;'>";
echo "=== Libra Emergency Fix ===\n\n";

try {
    // Step 1: Write the complete .env file
    echo "1. Writing complete .env file...\n";
    $envContent = <<<'ENV'
APP_NAME=Libra
APP_ENV=production
APP_KEY=base64:8nwAlElPTArk7lOe1EihtrgMV+4kXfhyyHbVEg18adA=
APP_DEBUG=false
APP_URL=https://libra-zadar.hr

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=librazad_libra
DB_USERNAME=librazad_libra
DB_PASSWORD=Macak21324354%

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database

MAIL_MAILER=smtp
MAIL_HOST=mail.libra-zadar.hr
MAIL_PORT=465
MAIL_USERNAME=info@libra-zadar.hr
MAIL_PASSWORD=Macak21324354
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=info@libra-zadar.hr
MAIL_FROM_NAME=Libra

VITE_APP_NAME="${APP_NAME}"

GOOGLE_CALENDAR_CLIENT_ID=947780080625-ih51iot57krgn2i9hb4cfe126otbun74.apps.googleusercontent.com
GOOGLE_CALENDAR_CLIENT_SECRET=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
GOOGLE_CALENDAR_REDIRECT_URI=https://libra-zadar.hr/google/callback
GOOGLE_CALENDAR_ID=primary
TUTOR_MARINA_USER_ID=1
TUTOR_VALENTINA_USER_ID=2
ENV;

    file_put_contents($laravelRoot . '/.env', $envContent);
    echo "   Written to: $laravelRoot/.env\n";
    echo "   Size: " . filesize($laravelRoot . '/.env') . " bytes\n\n";

    // Step 2: Delete ALL cached files
    echo "2. Deleting all cache files...\n";
    $cacheFiles = [
        '/bootstrap/cache/config.php',
        '/bootstrap/cache/routes-v7.php',
        '/bootstrap/cache/services.php',
        '/bootstrap/cache/packages.php',
        '/bootstrap/cache/events.php',
    ];
    foreach ($cacheFiles as $file) {
        $path = $laravelRoot . $file;
        if (file_exists($path)) {
            unlink($path);
            echo "   Deleted: $file\n";
        }
    }
    // Clear compiled views
    $viewCache = $laravelRoot . '/storage/framework/views';
    if (is_dir($viewCache)) {
        foreach (glob($viewCache . '/*.php') as $f) {
            unlink($f);
        }
        echo "   Cleared compiled views\n";
    }
    echo "   Done.\n\n";

    // Step 3: Verify .env was written correctly
    echo "3. Verifying .env...\n";
    $verify = file_get_contents($laravelRoot . '/.env');
    $checks = [
        'DB_CONNECTION=mysql',
        'DB_DATABASE=librazad_libra',
        'APP_KEY=base64:8nw',
        'SESSION_DRIVER=database',
    ];
    $allGood = true;
    foreach ($checks as $check) {
        if (strpos($verify, $check) !== false) {
            echo "   OK: $check\n";
        } else {
            echo "   FAIL: $check NOT FOUND!\n";
            $allGood = false;
        }
    }
    if (!$allGood) {
        echo "\n   *** .env verification failed. Cannot continue. ***\n";
        echo "</pre>";
        exit;
    }
    echo "\n";

    // Step 4: Test MySQL connection directly
    echo "4. Testing MySQL connection...\n";
    try {
        $pdo = new PDO(
            'mysql:host=localhost;port=3306;dbname=librazad_libra',
            'librazad_libra',
            'Macak21324354%'
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "   Connection: SUCCESS\n";
        echo "   Tables: " . implode(', ', $tables) . "\n\n";
    } catch (PDOException $e) {
        echo "   Connection: FAILED - " . $e->getMessage() . "\n";
        echo "   *** Fix your DB credentials and re-run this script ***\n";
        echo "</pre>";
        exit;
    }

    // Step 5: Boot Laravel
    echo "5. Booting Laravel...\n";
    chdir($laravelRoot);
    require $laravelRoot . '/vendor/autoload.php';
    $app = require_once $laravelRoot . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    echo "   Booted successfully.\n\n";

    // Step 6: Run migrations
    echo "6. Running migrations...\n";
    $kernel->call('migrate', ['--force' => true]);
    echo Illuminate\Support\Facades\Artisan::output();
    echo "   Done.\n\n";

    // Step 7: Create storage symlink
    echo "7. Creating storage symlink...\n";
    $kernel->call('storage:link');
    echo "   Done.\n\n";

    // Step 8: Cache everything
    echo "8. Caching config...\n";
    $kernel->call('config:cache');
    echo "   Done.\n\n";

    echo "9. Caching routes...\n";
    $kernel->call('route:cache');
    echo "   Done.\n\n";

    echo "10. Caching views...\n";
    $kernel->call('view:cache');
    echo "   Done.\n\n";

    // Step 9: Final verification
    echo "11. Final verification...\n";
    $cachedConfig = $laravelRoot . '/bootstrap/cache/config.php';
    if (file_exists($cachedConfig)) {
        $config = require $cachedConfig;
        echo "    Cached DB: " . ($config['database']['default'] ?? '?') . "\n";
        echo "    Cached URL: " . ($config['app']['url'] ?? '?') . "\n";
        echo "    Cached Session: " . ($config['session']['driver'] ?? '?') . "\n";
    }
    echo "\n";

    echo "============================\n";
    echo "ALL DONE! Site should work now.\n";
    echo "============================\n\n";

} catch (\Throwable $e) {
    echo "\n\nERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";
echo "<h2 style='color: red; font-family: sans-serif; padding: 20px;'>DELETE THIS FILE (fix.php) FROM public_html NOW!</h2>";
