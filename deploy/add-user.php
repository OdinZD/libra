<?php

/**
 * One-time script to add a user.
 * Upload to /home/librazad/public_html/add-user.php
 * Visit https://libra-zadar.hr/add-user.php
 * DELETE THIS FILE IMMEDIATELY AFTER USE!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre style='font-family: monospace; font-size: 14px; padding: 20px;'>";

try {
    $pdo = new PDO(
        'mysql:host=localhost;port=3306;dbname=librazad_libra',
        'librazad_libra',
        'Macak21324354%'
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $email = 'mblazi042@gmail.com';
    $name = 'Marina Blaži';
    // Random password - user will set their own via "Forgot Password"
    $password = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);

    // Check if already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        echo "User $email already exists.\n";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
        $stmt->execute([$name, $email, $password]);
        echo "User added successfully!\n";
        echo "Name: $name\n";
        echo "Email: $email\n";
        echo "Password: (random - use 'Forgot Password' to set it)\n";
    }

    // Show all users
    echo "\nAll users:\n";
    foreach ($pdo->query("SELECT id, name, email FROM users") as $row) {
        echo "  #{$row['id']} - {$row['name']} ({$row['email']})\n";
    }

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
echo "<h2 style='color: red; font-family: sans-serif; padding: 20px;'>DELETE THIS FILE (add-user.php) FROM public_html NOW!</h2>";
