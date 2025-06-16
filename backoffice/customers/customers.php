<?php
require_once '../../includes/db.php';
require_once '../components/Table.php';
require_once '../components/Pagination.php';
require_once '../components/SearchBox.php';
require_once '../../includes/config.php';

$perPage = 20;
$page = $_GET['page'] ?? 1;
$start = ($page - 1) * $perPage;

$total = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$stmt = $pdo->prepare("SELECT * FROM customers ORDER BY created_at DESC LIMIT :start, :limit");
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->execute();
$customers = $stmt->fetchAll();

$headers = ['ชื่อลูกค้า', 'อีเมล', 'เบอร์โทร', 'วันที่สร้าง', 'จัดการ'];
$rows = array_map(function ($c) {
  return [
    htmlspecialchars($c['name']),
    htmlspecialchars($c['email']),
    htmlspecialchars($c['phone']),
    htmlspecialchars($c['created_at']),
    "<a href='edit_customer.php?id={$c['id']}'>✏️</a> | <a href='#' onclick=\"confirmDelete('delete_customer.php?id={$c['id']}')\">🗑️</a>",
  ];
}, $customers);

session_start();
$toast = '';
if (isset($_SESSION['customer_updated'])) {
  $toast = '✅ แก้ไขลูกค้าสำเร็จ';
  unset($_SESSION['customer_updated']);
}
if (isset($_SESSION['customer_deleted'])) {
  $toast = '🗑️ ลบลูกค้าสำเร็จ';
  unset($_SESSION['customer_deleted']);
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

<div class="table-header">
  <h2>🧑‍💼 รายชื่อลูกค้า</h2>
  <a href="create_customer.php" class="button">+ เพิ่มลูกค้า</a>
</div>

<?php renderSearchBox(); ?>
<?php renderTable($headers, $rows); ?>
<?php renderPagination($page, $total, $perPage); ?>

<!-- Modal ยืนยันการลบ -->
<div id="deleteModal" class="modal-backdrop">
  <div class="modal">
    <h3>ยืนยันการลบลูกค้า?</h3>
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
    if (deleteUrl) {
      window.location.href = deleteUrl;
    }
  });
</script>

<?php
$content = ob_get_clean();
$title = "Customers";
include __DIR__ . '/../layouts/layout.php';
?>
