<?php
require_once '../../includes/db.php';
require_once '../components/Table.php';
require_once '../components/Pagination.php';
require_once '../components/SearchBox.php';
require_once '../../includes/config.php';

session_start();

$perPage = 20;
$page = $_GET['page'] ?? 1;
$start = ($page - 1) * $perPage;

// รับคำค้น
$searchCustomer = $_GET['search_customer'] ?? '';
$searchProduct = $_GET['search_product'] ?? '';
$statusFilter = $_GET['status'] ?? ''; // อาจเป็น 'paid', 'pending' ฯลฯ

// เตรียม WHERE เงื่อนไข
$where = "WHERE customers.name LIKE :customer AND p.name LIKE :product";
$params = [
  ':customer' => "%$searchCustomer%",
  ':product' => "%$searchProduct%",
];

if ($statusFilter && in_array($statusFilter, ['pending', 'paid', 'shipped', 'cancelled'])) {
  $where .= " AND orders.status = :status";
  $params[':status'] = $statusFilter;
}

// นับจำนวนรายการ
$countSql = "
  SELECT COUNT(DISTINCT orders.id)
  FROM orders
  LEFT JOIN customers ON orders.customer_id = customers.id
  LEFT JOIN order_details od ON orders.id = od.order_id
  LEFT JOIN products p ON od.product_id = p.id
  $where
";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$total = $countStmt->fetchColumn();

// ดึงข้อมูล
$sql = "
  SELECT DISTINCT orders.*, customers.name AS customer_name
  FROM orders
  LEFT JOIN customers ON orders.customer_id = customers.id
  LEFT JOIN order_details od ON orders.id = od.order_id
  LEFT JOIN products p ON od.product_id = p.id
  $where
  ORDER BY orders.created_at DESC
  LIMIT :start, :limit
";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
  $stmt->bindValue($key, $val, PDO::PARAM_STR);
}
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll();

// สร้างตาราง
$headers = ['รหัสคำสั่งซื้อ', 'ลูกค้า', 'ราคารวม', 'สถานะ', 'วันที่สร้าง', 'จัดการ'];
$rows = array_map(function ($o) {
  return [
    htmlspecialchars($o['order_number']),
    htmlspecialchars($o['customer_name']),
    number_format($o['total_price'], 2) . ' ฿',
    htmlspecialchars($o['status']),
    htmlspecialchars($o['created_at']),
    "<a href='view_order.php?id={$o['id']}'>🔍</a> | <a href='#' onclick=\"confirmDelete('delete_order.php?id={$o['id']}')\">🗑️</a>",
  ];
}, $orders);

// แสดง toast ถ้ามี
$toast = '';
if (isset($_SESSION['order_deleted'])) {
  $toast = '🗑️ ลบคำสั่งซื้อสำเร็จ';
  unset($_SESSION['order_deleted']);
}
?>

<?php ob_start(); ?>

<?php if ($toast): ?>
  <div id="toast-msg" style="position: fixed; top: 20px; right: 20px; background: #333; color: #fff; padding: 12px 20px; border-radius: 6px; z-index: 1000;">
    <?= $toast ?>
  </div>
  <script>
    setTimeout(() => {
      const toast = document.getElementById('toast-msg');
      if (toast) toast.style.display = 'none';
    }, 3000);
  </script>
<?php endif; ?>

<!-- Header -->
<div class="table-header" style="display: flex; justify-content: space-between; align-items: center;">
  <h2>🧾 รายการคำสั่งซื้อ</h2>
  <a href="create_order.php" class="button">+ สร้างคำสั่งซื้อ</a>
</div>

<!-- 🔖 Tabs -->
<?php
$tabs = [
  '' => 'ทั้งหมด',
  'pending' => 'รอชำระ',
  'paid' => 'ชำระแล้ว',
  'shipped' => 'จัดส่งแล้ว',
  'cancelled' => 'ยกเลิกแล้ว'
];
?>
<div style="margin: 16px 0;">
  <?php foreach ($tabs as $key => $label): ?>
    <?php
    $active = ($statusFilter === $key || ($key === '' && !$statusFilter)) ? 'font-weight: bold; text-decoration: underline;' : '';
    $query = http_build_query(array_merge($_GET, ['status' => $key, 'page' => 1]));
    ?>
    <a href="?<?= $query ?>" style="margin-right: 12px; <?= $active ?>"><?= $label ?></a>
  <?php endforeach; ?>
</div>

<!-- 🔍 Search -->
<form method="GET" style="margin: 20px 0; display: flex; gap: 10px; flex-wrap: wrap;">
  <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
  <input type="text" name="search_customer" placeholder="ค้นหาชื่อลูกค้า" value="<?= htmlspecialchars($searchCustomer) ?>" style="padding: 6px;">
  <input type="text" name="search_product" placeholder="ค้นหาชื่อสินค้า" value="<?= htmlspecialchars($searchProduct) ?>" style="padding: 6px;">
  <button type="submit" class="button">ค้นหา</button>
  <a href="orders.php" class="button" style="background-color: #ccc;">รีเซ็ต</a>
</form>

<?php renderTable($headers, $rows); ?>
<?php renderPagination($page, $total, $perPage); ?>

<!-- Modal ยืนยันการลบ -->
<div id="deleteModal" class="modal-backdrop">
  <div class="modal">
    <h3>ยืนยันการลบคำสั่งซื้อ?</h3>
    <div class="modal-actions">
      <button class="modal-cancel" onclick="closeModal()">ยกเลิก</button>
      <button class="modal-confirm" id="confirmDeleteBtn">ลบเลย</button>
    </div>
  </div>
</div>

<script>
  let deleteUrl = '';

  function confirmDelete(url) {
    deleteUrl = url;
    document.getElementById('deleteModal').style.display = 'flex';
  }

  function closeModal() {
    document.getElementById('deleteModal').style.display = 'none';
  }
  document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteUrl) window.location.href = deleteUrl;
  });
</script>

<?php
$content = ob_get_clean();
$title = "Orders";
include __DIR__ . '/../layouts/layout.php';
?>
