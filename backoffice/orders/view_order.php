<?php
require_once '../../includes/db.php';
require_once '../../includes/config.php';
session_start();

$id = $_GET['id'] ?? null;
if (!$id) {
  echo "ไม่พบคำสั่งซื้อ";
  exit;
}

// ดึงข้อมูลคำสั่งซื้อ
$stmt = $pdo->prepare("
    SELECT orders.*, customers.name AS customer_name
    FROM orders
    LEFT JOIN customers ON orders.customer_id = customers.id
    WHERE orders.id = ?
");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
  echo "ไม่พบคำสั่งซื้อ";
  exit;
}

// ดึงรายการสินค้าในคำสั่งซื้อ
$stmtItems = $pdo->prepare("
    SELECT od.*, p.name AS product_name
    FROM order_details od
    LEFT JOIN products p ON od.product_id = p.id
    WHERE od.order_id = ?
");
$stmtItems->execute([$id]);
$items = $stmtItems->fetchAll();

// Flow สถานะ
$statusFlow = [
  'pending'   => 'paid',
  'paid'      => 'shipped',
  'shipped'   => null,
  'cancelled' => null,
];

$currentStatus = $order['status'];
$nextStatus = $statusFlow[$currentStatus];
?>

<?php ob_start(); ?>
<div style="max-width: 800px; margin: 0 auto;">
  <h2>🔍 รายละเอียดคำสั่งซื้อ</h2>

  <?php if (isset($_SESSION['message'])): ?>
    <div style="color: green; margin-bottom: 10px;">
      <?= $_SESSION['message'];
      unset($_SESSION['message']); ?>
    </div>
  <?php endif; ?>

  <p><strong>รหัสคำสั่งซื้อ:</strong> <?= htmlspecialchars($order['order_number']) ?></p>
  <p><strong>ลูกค้า:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
  <p><strong>รวมก่อนส่วนลด:</strong> <?= number_format($order['total_price'], 2) ?> ฿</p>

  <?php if (isset($order['discount'])): ?>
    <p><strong>ส่วนลด:</strong> <?= number_format($order['discount'], 2) ?> ฿</p>
  <?php endif; ?>
  <?php if (isset($order['vat'])): ?>
    <p><strong>VAT:</strong> <?= number_format($order['vat'], 2) ?> ฿</p>
  <?php endif; ?>
  <?php if (isset($order['final_total'])): ?>
    <p><strong>ราคารวมสุทธิ:</strong> <?= number_format($order['final_total'], 2) ?> ฿</p>
  <?php endif; ?>

  <p><strong>สถานะ:</strong> <strong><?= strtoupper($currentStatus) ?></strong></p>
  <p><strong>วันที่สร้าง:</strong> <?= htmlspecialchars($order['created_at']) ?></p>

  <h3>📦 รายการสินค้า</h3>
  <table border="1" cellpadding="8" cellspacing="0" style="width: 100%; border-collapse: collapse;">
    <thead>
      <tr>
        <th>สินค้า</th>
        <th>ราคา/ชิ้น</th>
        <th>จำนวน</th>
        <th>รวม</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $item): ?>
        <tr>
          <td><?= htmlspecialchars($item['product_name']) ?></td>
          <td><?= number_format($item['price'], 2) ?> ฿</td>
          <td><?= $item['quantity'] ?></td>
          <td><?= number_format($item['price'] * $item['quantity'], 2) ?> ฿</td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- ปุ่มเปลี่ยนสถานะ -->
  <form method="POST" action="update_order_status.php" style="margin-top: 20px;">
    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

    <?php if ($nextStatus): ?>
      <input type="hidden" name="new_status" value="<?= $nextStatus ?>">
      <button type="submit" style="margin-right: 10px;">📤 ผลักไปสถานะถัดไป (<?= strtoupper($nextStatus) ?>)</button>
    <?php endif; ?>

    <?php if (!in_array($currentStatus, ['cancelled', 'shipped'])): ?>
      <button type="submit" name="cancel" value="1" style="background-color: red; color: white;">❌ ยกเลิกคำสั่งซื้อ</button>
    <?php endif; ?>
  </form>

  <br>
  <a href="orders.php">← กลับ</a>
</div>
<?php
$content = ob_get_clean();
$title = "Order Details";
include __DIR__ . '/../layouts/layout.php';
?>
