<?php

/**
 * Diagnostic script - checks server state before running setup.
 *
 * Upload to /home/librazad/public_html/diagnose.php
 * Visit https://libra-zadar.hr/diagnose.php
 * DELETE THIS FILE IMMEDIATELY AFTER USE!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre style='font-family: monospace; font-size: 14px; padding: 20px;'>";
echo "=== Libra Server Diagnostics ===\n\n";

// 1. Check paths
$laravelRoot = dirname(__DIR__) . '/libra';
echo "1. PATHS\n";
echo "   public_html: " . __DIR__ . "\n";
echo "   Laravel root: " . $laravelRoot . "\n";
echo "   Laravel exists: " . (is_dir($laravelRoot) ? 'YES' : 'NO') . "\n\n";

// 2. Check .env
$envPath = $laravelRoot . '/.env';
echo "2. ENV FILE\n";
echo "   .env exists: " . (file_exists($envPath) ? 'YES' : 'NO') . "\n";
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    // Show key settings (mask passwords)
    foreach (['APP_ENV', 'APP_KEY', 'APP_DEBUG', 'APP_URL', 'DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'SESSION_DRIVER', 'CACHE_STORE', 'QUEUE_CONNECTION'] as $key) {
        if (preg_match('/^' . $key . '=(.*)$/m', $envContent, $m)) {
            $val = trim($m[1]);
            if (in_array($key, ['DB_PASSWORD'])) $val = '***';
            echo "   $key=$val\n";
        } else {
            echo "   $key=(NOT SET)\n";
        }
    }
    // Check APP_KEY specifically
    if (preg_match('/^APP_KEY=\s*$/m', $envContent)) {
        echo "   *** WARNING: APP_KEY is EMPTY! ***\n";
    }
}
echo "\n";

// 3. Check cached config
echo "3. CACHED CONFIG\n";
$cachedConfig = $laravelRoot . '/bootstrap/cache/config.php';
echo "   config.php exists: " . (file_exists($cachedConfig) ? 'YES' : 'NO') . "\n";
if (file_exists($cachedConfig)) {
    $config = require $cachedConfig;
    echo "   Cached DB_CONNECTION: " . ($config['database']['default'] ?? 'NOT SET') . "\n";
    echo "   Cached APP_KEY: " . (empty($config['app']['key']) ? 'EMPTY' : 'SET (' . substr($config['app']['key'], 0, 10) . '...)') . "\n";
    echo "   Cached APP_URL: " . ($config['app']['url'] ?? 'NOT SET') . "\n";
    echo "   Cached SESSION_DRIVER: " . ($config['session']['driver'] ?? 'NOT SET') . "\n";
}
echo "\n";

// 4. Check PHP extensions
echo "4. PHP EXTENSIONS\n";
echo "   PHP version: " . PHP_VERSION . "\n";
echo "   pdo_mysql: " . (extension_loaded('pdo_mysql') ? 'YES' : 'NO - REQUIRED!') . "\n";
echo "   pdo_pgsql: " . (extension_loaded('pdo_pgsql') ? 'YES' : 'NO') . "\n";
echo "   openssl: " . (extension_loaded('openssl') ? 'YES' : 'NO') . "\n";
echo "   mbstring: " . (extension_loaded('mbstring') ? 'YES' : 'NO') . "\n";
echo "   curl: " . (extension_loaded('curl') ? 'YES' : 'NO') . "\n";
echo "\n";

// 5. Check key directories/files
echo "5. KEY FILES\n";
echo "   vendor/autoload.php: " . (file_exists($laravelRoot . '/vendor/autoload.php') ? 'YES' : 'NO') . "\n";
echo "   bootstrap/app.php: " . (file_exists($laravelRoot . '/bootstrap/app.php') ? 'YES' : 'NO') . "\n";
echo "   storage/ writable: " . (is_writable($laravelRoot . '/storage') ? 'YES' : 'NO') . "\n";
echo "   storage/logs/ writable: " . (is_writable($laravelRoot . '/storage/logs') ? 'YES' : 'NO') . "\n";
echo "   bootstrap/cache/ writable: " . (is_writable($laravelRoot . '/bootstrap/cache') ? 'YES' : 'NO') . "\n";
echo "\n";

// 6. Check storage/logs for recent errors
echo "6. RECENT LOG ERRORS\n";
$logFile = $laravelRoot . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    $logSize = filesize($logFile);
    echo "   Log file size: " . round($logSize / 1024) . " KB\n";
    // Show last 30 lines
    $lines = file($logFile);
    $lastLines = array_slice($lines, -30);
    echo "   Last 30 lines:\n";
    echo "   ---\n";
    foreach ($lastLines as $line) {
        echo "   " . rtrim($line) . "\n";
    }
    echo "   ---\n";
} else {
    echo "   No log file found.\n";
}
echo "\n";

// 7. Test MySQL connection directly
echo "7. MYSQL CONNECTION TEST\n";
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    preg_match('/^DB_HOST=(.*)$/m', $envContent, $host);
    preg_match('/^DB_PORT=(.*)$/m', $envContent, $port);
    preg_match('/^DB_DATABASE=(.*)$/m', $envContent, $db);
    preg_match('/^DB_USERNAME=(.*)$/m', $envContent, $user);
    preg_match('/^DB_PASSWORD=(.*)$/m', $envContent, $pass);

    $h = trim($host[1] ?? 'localhost');
    $p = trim($port[1] ?? '3306');
    $d = trim($db[1] ?? '');
    $u = trim($user[1] ?? '');
    $pw = trim($pass[1] ?? '');

    try {
        $pdo = new PDO("mysql:host=$h;port=$p;dbname=$d", $u, $pw);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "   Connection: SUCCESS\n";

        // Check tables
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "   Tables (" . count($tables) . "): " . implode(', ', $tables) . "\n";

        // Check if sessions table exists
        if (in_array('sessions', $tables)) {
            echo "   sessions table: EXISTS\n";
        } else {
            echo "   *** sessions table: MISSING - login will fail! ***\n";
        }
    } catch (PDOException $e) {
        echo "   Connection: FAILED\n";
        echo "   Error: " . $e->getMessage() . "\n";
    }
}
echo "\n";

echo "=== END DIAGNOSTICS ===\n";
echo "</pre>";
echo "<h2 style='color: red; font-family: sans-serif; padding: 20px;'>DELETE THIS FILE (diagnose.php) FROM public_html NOW!</h2>";
