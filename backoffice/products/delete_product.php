<?php
require_once '../../includes/db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: products.php');
    exit;
}

// ลบสินค้า
$stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
$stmt->execute([$id]);

$_SESSION['product_deleted'] = true;
header('Location: products.php');
exit;
