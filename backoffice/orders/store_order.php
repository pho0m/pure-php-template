<?php
require_once '../../includes/db.php';
session_start();

$customer_id = $_POST['customer_id'] ?? null;
$product_ids = $_POST['product_ids'] ?? [];
$prices = $_POST['prices'] ?? [];
$quantities = $_POST['quantities'] ?? [];
$discount = floatval($_POST['discount'] ?? 0);
$vat = floatval($_POST['vat'] ?? 0);

if (!$customer_id || empty($product_ids)) {
  echo "กรุณาเลือกลูกค้าและสินค้าอย่างน้อย 1 รายการ";
  exit;
}

try {
  $pdo->beginTransaction();

  // สร้าง order เปล่า เพื่อให้ได้ ID ก่อน
  $stmt = $pdo->prepare("INSERT INTO orders (customer_id, total_price, discount, vat, status) VALUES (?, 0, ?, ?, 'pending')");
  $stmt->execute([$customer_id, $discount, $vat]);
  $order_id = $pdo->lastInsertId();

  // สร้างเลข order_number จาก order_id
  $order_number = 'ORD-' . str_pad($order_id, 5, '0', STR_PAD_LEFT);
  $stmt = $pdo->prepare("UPDATE orders SET order_number = ? WHERE id = ?");
  $stmt->execute([$order_number, $order_id]);

  $total = 0;
  $stmtDetail = $pdo->prepare("INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");

  for ($i = 0; $i < count($product_ids); $i++) {
    $pid = $product_ids[$i];
    $qty = floatval($quantities[$i]);
    $price = floatval($prices[$i]);
    $subtotal = $qty * $price;
    $total += $subtotal;
    $stmtDetail->execute([$order_id, $pid, $qty, $price]);
  }

  $total_after_discount = $total - $discount;
  $vat_amount = ($total_after_discount * $vat) / 100;
  $final_total = $total_after_discount + $vat_amount;

  $stmt = $pdo->prepare("UPDATE orders SET total_price = ? WHERE id = ?");
  $stmt->execute([$final_total, $order_id]);

  $pdo->commit();
  echo "success";
} catch (Exception $e) {
  $pdo->rollBack();
  echo "เกิดข้อผิดพลาด: " . $e->getMessage();
}
