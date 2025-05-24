<?php
$host = 'localhost';
$db = 'l3en_commerce_db';
$user = 'superadmin';
$pass = 'password123';
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
?>
