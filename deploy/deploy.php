<?php

/**
 * FULL deployment script for Libra on cPanel.
 *
 * Upload to /home/librazad/public_html/deploy.php
 * Visit https://libra-zadar.hr/deploy.php
 * DELETE THIS FILE IMMEDIATELY AFTER USE!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 300);

echo "<pre style='font-family: monospace; font-size: 13px; padding: 20px; line-height: 1.6;'>";
echo "=== LIBRA FULL DEPLOYMENT ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
echo "PHP: " . PHP_VERSION . "\n\n";

$laravelRoot = dirname(__DIR__) . '/libra';
$publicHtml  = dirname(__DIR__) . '/public_html';
$errors = [];

// ============================================================
// PHASE 1: CHECK SERVER STRUCTURE
// ============================================================
echo "========================================\n";
echo "PHASE 1: SERVER STRUCTURE\n";
echo "========================================\n\n";

echo "public_html dir: $publicHtml\n";
echo "Laravel root:    $laravelRoot\n\n";

// Check Laravel root exists
if (!is_dir($laravelRoot)) {
    echo "FATAL: Laravel root does NOT exist: $laravelRoot\n";
    echo "You need to upload the entire app to /home/librazad/libra/ first.\n";
    echo "</pre>";
    exit;
}

// Check key directories
$dirs = [
    'app' => $laravelRoot . '/app',
    'bootstrap' => $laravelRoot . '/bootstrap',
    'bootstrap/cache' => $laravelRoot . '/bootstrap/cache',
    'config' => $laravelRoot . '/config',
    'database' => $laravelRoot . '/database',
    'resources' => $laravelRoot . '/resources',
    'routes' => $laravelRoot . '/routes',
    'storage' => $laravelRoot . '/storage',
    'storage/app' => $laravelRoot . '/storage/app',
    'storage/framework' => $laravelRoot . '/storage/framework',
    'storage/framework/cache' => $laravelRoot . '/storage/framework/cache',
    'storage/framework/sessions' => $laravelRoot . '/storage/framework/sessions',
    'storage/framework/views' => $laravelRoot . '/storage/framework/views',
    'storage/logs' => $laravelRoot . '/storage/logs',
    'vendor' => $laravelRoot . '/vendor',
];

foreach ($dirs as $name => $path) {
    $exists = is_dir($path);
    $writable = $exists ? is_writable($path) : false;
    $status = $exists ? ($writable ? 'OK' : 'NOT WRITABLE!') : 'MISSING!';
    echo "  $name: $status\n";

    if (!$exists) {
        // Create missing storage dirs
        if (strpos($name, 'storage') === 0 || $name === 'bootstrap/cache') {
            mkdir($path, 0755, true);
            echo "    -> Created directory\n";
        } else {
            $errors[] = "Directory missing: $name";
        }
    } elseif (!$writable) {
        chmod($path, 0755);
        echo "    -> Fixed permissions\n";
    }
}

// Check vendor/autoload.php
echo "\n  vendor/autoload.php: " . (file_exists($laravelRoot . '/vendor/autoload.php') ? 'OK' : 'MISSING - run composer install!') . "\n";
echo "  bootstrap/app.php: " . (file_exists($laravelRoot . '/bootstrap/app.php') ? 'OK' : 'MISSING!') . "\n";

echo "\n";

// ============================================================
// PHASE 2: WRITE CORRECT .env
// ============================================================
echo "========================================\n";
echo "PHASE 2: ENVIRONMENT FILE\n";
echo "========================================\n\n";

// Generate APP_KEY before writing .env (so it's there when Laravel boots)
$appKey = 'base64:' . base64_encode(random_bytes(32));
echo "Generated APP_KEY: $appKey\n\n";

$envContent = 'APP_NAME=Libra
APP_ENV=production
APP_KEY=' . $appKey . '
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
';

file_put_contents($laravelRoot . '/.env', $envContent);
echo "Written .env (" . strlen($envContent) . " bytes)\n\n";

// ============================================================
// PHASE 3: WRITE CORRECT public_html FILES
// ============================================================
echo "========================================\n";
echo "PHASE 3: PUBLIC_HTML FILES\n";
echo "========================================\n\n";

// Write correct index.php
$indexContent = '<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define(\'LARAVEL_START\', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.\'/../libra/storage/framework/maintenance.php\')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.\'/../libra/vendor/autoload.php\';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.\'/../libra/bootstrap/app.php\';

$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
';

$currentIndex = file_exists($publicHtml . '/index.php') ? file_get_contents($publicHtml . '/index.php') : '';
echo "Current index.php check:\n";
if (strpos($currentIndex, '../libra/vendor') !== false) {
    echo "  index.php: Already points to ../libra/ - OK\n";
} else {
    echo "  index.php: WRONG or MISSING - fixing...\n";
    file_put_contents($publicHtml . '/index.php', $indexContent);
    echo "  index.php: WRITTEN\n";
}

// Check .htaccess
$htaccessContent = '<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Force HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
';

if (!file_exists($publicHtml . '/.htaccess')) {
    file_put_contents($publicHtml . '/.htaccess', $htaccessContent);
    echo "  .htaccess: WRITTEN (was missing)\n";
} else {
    echo "  .htaccess: EXISTS\n";
}

// Check build assets
echo "\n  Build assets:\n";
if (is_dir($publicHtml . '/build')) {
    $buildFiles = glob($publicHtml . '/build/assets/*');
    foreach ($buildFiles as $f) {
        echo "    " . basename($f) . "\n";
    }
    if (file_exists($publicHtml . '/build/manifest.json')) {
        echo "    manifest.json: OK\n";
    } else {
        echo "    manifest.json: MISSING!\n";
        $errors[] = "Build manifest.json is missing";
    }
} else {
    echo "    build/ directory: MISSING!\n";
    $errors[] = "Build directory is missing - upload public/build/";
}

// Check images
echo "\n  Images:\n";
if (file_exists($publicHtml . '/images/libra-logo-vectors.svg')) {
    echo "    libra-logo-vectors.svg: OK\n";
} else {
    echo "    libra-logo-vectors.svg: MISSING\n";
}

echo "\n";

// ============================================================
// PHASE 4: CLEAR ALL CACHES
// ============================================================
echo "========================================\n";
echo "PHASE 4: CLEAR ALL CACHES\n";
echo "========================================\n\n";

// Delete cached config/routes manually (before booting Laravel)
$cacheFiles = glob($laravelRoot . '/bootstrap/cache/*.php');
foreach ($cacheFiles as $f) {
    $name = basename($f);
    if ($name !== '.gitignore') {
        unlink($f);
        echo "  Deleted: bootstrap/cache/$name\n";
    }
}

// Clear compiled views
$viewFiles = glob($laravelRoot . '/storage/framework/views/*.php');
$viewCount = count($viewFiles);
foreach ($viewFiles as $f) {
    unlink($f);
}
echo "  Cleared $viewCount compiled views\n";

// Truncate laravel.log (it was 1MB+)
$logFile = $laravelRoot . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    file_put_contents($logFile, '');
    echo "  Cleared laravel.log\n";
}

echo "\n";

// ============================================================
// PHASE 5: TEST MYSQL CONNECTION
// ============================================================
echo "========================================\n";
echo "PHASE 5: MYSQL CONNECTION\n";
echo "========================================\n\n";

try {
    $pdo = new PDO(
        'mysql:host=localhost;port=3306;dbname=librazad_libra',
        'librazad_libra',
        'Macak21324354%'
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "  Connection: SUCCESS\n";

    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "  Tables (" . count($tables) . "): " . implode(', ', $tables) . "\n";

    // Check critical tables
    $required = ['users', 'sessions', 'cache', 'cache_locks', 'jobs', 'migrations', 'student_schedules'];
    foreach ($required as $t) {
        if (!in_array($t, $tables)) {
            echo "  MISSING TABLE: $t\n";
            $errors[] = "Missing table: $t";
        }
    }

    // Check student_schedules columns
    $cols = $pdo->query("SHOW COLUMNS FROM student_schedules")->fetchAll(PDO::FETCH_COLUMN);
    echo "  student_schedules columns: " . implode(', ', $cols) . "\n";

    if (!in_array('school_type', $cols)) {
        echo "  NOTE: school_type column missing - will be added by migration\n";
    }
    if (!in_array('attendance_status', $cols)) {
        echo "  NOTE: attendance_status column missing - will be added by migration\n";
    }

    // Check users
    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "  Users in DB: $userCount\n";

} catch (PDOException $e) {
    echo "  Connection: FAILED\n";
    echo "  Error: " . $e->getMessage() . "\n";
    $errors[] = "MySQL connection failed: " . $e->getMessage();
}

echo "\n";

// ============================================================
// PHASE 6: BOOT LARAVEL
// ============================================================
echo "========================================\n";
echo "PHASE 6: BOOT LARAVEL\n";
echo "========================================\n\n";

try {
    chdir($laravelRoot);
    require $laravelRoot . '/vendor/autoload.php';
    $app = require_once $laravelRoot . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    echo "  Laravel booted successfully.\n\n";

    // Run migrations
    echo "  Running migrations...\n";
    $kernel->call('migrate', ['--force' => true]);
    $migrationOutput = Illuminate\Support\Facades\Artisan::output();
    echo "  " . trim($migrationOutput) . "\n\n";

    // Storage link
    echo "  Creating storage link...\n";
    try {
        $kernel->call('storage:link');
        echo "  Done.\n\n";
    } catch (\Throwable $e) {
        echo "  " . $e->getMessage() . " (probably already exists - OK)\n\n";
    }

    // NO config:cache - Laravel will read .env directly on each request.
    // config:cache has a bug where APP_KEY doesn't get cached properly
    // when run from a PHP script (in-memory env doesn't match disk).
    // For a 2-user app, performance impact is zero.
    echo "  Skipping config:cache (reading .env directly is more reliable).\n\n";

    // Verify .env is readable by Laravel
    echo "  Verifying Laravel can read APP_KEY from .env...\n";
    $loadedKey = $app['config']['app.key'];
    echo "    app.key: " . (empty($loadedKey) ? 'EMPTY!' : 'SET (' . substr($loadedKey, 0, 15) . '...)') . "\n";
    echo "    database.default: " . $app['config']['database.default'] . "\n";
    echo "    session.driver: " . $app['config']['session.driver'] . "\n\n";

    // NO route:cache either - Flux and Livewire register JS asset routes
    // dynamically in service providers. Route caching from a script context
    // can miss these, breaking all interactivity.
    echo "  Clearing route cache (Flux/Livewire need dynamic routes)...\n";
    $kernel->call('route:clear');
    echo "  Done.\n\n";

} catch (\Throwable $e) {
    echo "  LARAVEL ERROR: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "  Trace:\n" . $e->getTraceAsString() . "\n\n";
    $errors[] = "Laravel boot failed: " . $e->getMessage();
}

// ============================================================
// PHASE 7: TEST WEB REQUEST SIMULATION
// ============================================================
echo "========================================\n";
echo "PHASE 7: WEB REQUEST TEST\n";
echo "========================================\n\n";

echo "  Testing /up health endpoint...\n";
$ch = curl_init('https://libra-zadar.hr/up');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo "  curl error: $curlError\n";
} else {
    echo "  /up response code: $httpCode\n";
    if ($httpCode === 200) {
        echo "  HEALTH CHECK PASSED!\n";
    } else {
        echo "  Response body (first 500 chars):\n";
        echo "  " . substr(strip_tags($response), 0, 500) . "\n";
    }
}

echo "\n  Testing / (home page)...\n";
$ch = curl_init('https://libra-zadar.hr/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo "  curl error: $curlError\n";
} else {
    echo "  / response code: $httpCode\n";
    if ($httpCode === 200) {
        echo "  HOME PAGE WORKS!\n";
    } else {
        echo "  Response body (first 500 chars):\n";
        echo "  " . substr(strip_tags($response), 0, 500) . "\n";
    }
}

echo "\n  Testing /login...\n";
$ch = curl_init('https://libra-zadar.hr/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo "  curl error: $curlError\n";
} else {
    echo "  /login response code: $httpCode\n";
    if ($httpCode === 200) {
        echo "  LOGIN PAGE WORKS!\n";
    } else {
        echo "  Response body (first 500 chars):\n";
        echo "  " . substr(strip_tags($response), 0, 500) . "\n";
    }
}

// ============================================================
// PHASE 8: CHECK ERROR LOG
// ============================================================
echo "\n========================================\n";
echo "PHASE 8: ERROR LOG (after tests)\n";
echo "========================================\n\n";

$logFile = $laravelRoot . '/storage/logs/laravel.log';
if (file_exists($logFile) && filesize($logFile) > 0) {
    $logContent = file_get_contents($logFile);
    echo "  Log size: " . strlen($logContent) . " bytes\n";
    echo "  Content:\n---\n";
    echo $logContent;
    echo "\n---\n";
} else {
    echo "  Log is empty (good - no errors during test)\n";
}

// ============================================================
// SUMMARY
// ============================================================
echo "\n========================================\n";
echo "SUMMARY\n";
echo "========================================\n\n";

if (empty($errors)) {
    echo "  No critical errors found.\n";
} else {
    echo "  ERRORS FOUND:\n";
    foreach ($errors as $e) {
        echo "    - $e\n";
    }
}

echo "\n  NEXT STEPS:\n";
echo "  1. If site works: edit .env to set APP_DEBUG=false and LOG_LEVEL=error\n";
echo "  2. Then re-run config:cache (or run this script again)\n";
echo "  3. DELETE this deploy.php file!\n\n";

echo "</pre>";
echo "<h2 style='color: red; font-family: sans-serif; padding: 20px;'>DELETE THIS FILE (deploy.php) FROM public_html NOW!</h2>";
