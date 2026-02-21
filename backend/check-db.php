<?php
require_once __DIR__ . '/api/config.php';

echo "=== TimeTracking MM - Database Connection Test ===\n\n";

// 1. Check .env loading
echo "1. Configuration:\n";
echo "   DB_HOST: " . DB_HOST . "\n";
echo "   DB_NAME: " . DB_NAME . "\n";
echo "   DB_USER: " . DB_USER . "\n";
echo "   DB_PASS: " . (DB_PASS ? str_repeat('*', strlen(DB_PASS)) : '(empty)') . "\n\n";

// 2. Test connection
echo "2. Database connection: ";
try {
    $pdo = getDbConnection();
    echo "OK\n\n";
} catch (PDOException $e) {
    echo "FAILED\n";
    echo "   Error: " . $e->getMessage() . "\n";
    exit(1);
}

// 3. Check tables exist
echo "3. Tables:\n";
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach (['users', 'workstations', 'time_entries'] as $expected) {
    $found = in_array($expected, $tables);
    echo "   $expected: " . ($found ? "OK" : "MISSING") . "\n";
}
echo "\n";

// 4. Check workstations data
$count = $pdo->query("SELECT COUNT(*) FROM workstations")->fetchColumn();
echo "4. Workstations: $count rows " . ($count == 17 ? "OK" : "(expected 17)") . "\n\n";

// 5. Check admin user
$stmt = $pdo->prepare("SELECT username, display_name FROM users WHERE username = ?");
$stmt->execute(['admin']);
$admin = $stmt->fetch();
echo "5. Admin user: " . ($admin ? "OK ({$admin['display_name']})" : "MISSING") . "\n\n";

// 6. Test admin password
if ($admin) {
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $hash = $stmt->fetchColumn();
    $valid = password_verify('admin123', $hash);
    echo "6. Admin password (admin123): " . ($valid ? "OK" : "FAILED - hash mismatch") . "\n\n";
}

echo "=== All checks done ===\n";
