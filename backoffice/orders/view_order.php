<?php
require_once '../../includes/db.php';
require_once '../../includes/config.php';

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
?>

<?php ob_start(); ?>
<div style="max-width: 800px; margin: 0 auto;">
  <h2>🔍 รายละเอียดคำสั่งซื้อ</h2>
  <p><strong>รหัสคำสั่งซื้อ:</strong> <?= htmlspecialchars($order['order_number']) ?></p>
  <p><strong>ลูกค้า:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
  <p><strong>ราคารวม:</strong> <?= number_format($order['total_price'], 2) ?> ฿</p>
  <p><strong>สถานะ:</strong> <?= htmlspecialchars($order['status']) ?></p>
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
          <td><?= number_format($item['price'], 2) ?></td>
          <td><?= $item['quantity'] ?></td>
          <td><?= number_format($item['price'] * $item['quantity'], 2) ?> ฿</td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <br>
  <a href="orders.php">← กลับ</a>
</div>
<?php
$content = ob_get_clean();
$title = "Order Details";
include __DIR__ . '/../layouts/layout.php';
?>
