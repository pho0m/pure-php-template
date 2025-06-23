<?php
// โหลด ENV
$env = parse_ini_file(__DIR__ . '/../.env');

$host = $env['DB_HOST'] ?? 'localhost';
$db   = $env['DB_NAME'] ?? 'my_database';
$user = $env['DB_USER'] ?? 'root';
$pass = $env['DB_PASS'] ?? '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // แจ้ง error อย่างชัดเจน
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // คืนค่าแบบ associative array
    PDO::ATTR_EMULATE_PREPARES   => false,                  // ใช้ prepare statement จริง
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage();
    exit;
}
