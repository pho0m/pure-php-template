<?php
require_once '../../includes/db.php';
header('Content-Type: application/json');

$q = $_GET['q'] ?? '';
$stmt = $pdo->prepare("SELECT id, name FROM customers WHERE name LIKE ? ORDER BY name LIMIT 10");
$stmt->execute(["%$q%"]);
echo json_encode($stmt->fetchAll());
