<?php
require_once '../../includes/db.php';
session_start();

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: customers.php');
    exit;
}

$stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
$stmt->execute([$id]);

$_SESSION['customer_deleted'] = true;
header('Location: customers.php');
exit;
