<?php
require_once '../../includes/db.php';
session_start();

$id = $_GET['id'] ?? null;

if ($id) {
  // ตรวจสอบสถานะก่อนลบ
  $stmt = $pdo->prepare("SELECT status FROM orders WHERE id = ? AND deleted_at IS NULL");
  $stmt->execute([$id]);
  $order = $stmt->fetch();

  if ($order && $order['status'] === 'cancelled') {
    // ทำ soft delete ได้เฉพาะถ้าเป็นสถานะ 'cancelled'
    $del = $pdo->prepare("UPDATE orders SET deleted_at = NOW() WHERE id = ?");
    $del->execute([$id]);

    $_SESSION['order_deleted'] = '🗑️ ลบคำสั่งซื้อสำเร็จ';
  } else {
    $_SESSION['order_deleted'] = '❌ ลบไม่ได้: ต้องเป็นคำสั่งซื้อที่ถูกยกเลิกก่อน';
  }
}

header("Location: orders.php");
exit;
