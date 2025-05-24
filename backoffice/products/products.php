<?php
require_once '../../includes/db.php';
require_once '../components/Table.php';
require_once '../components/Pagination.php';
require_once '../components/SearchBox.php';


$perPage = 20;
$page = $_GET['page'] ?? 1;
$start = ($page - 1) * $perPage;

$total = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$stmt = $pdo->prepare("SELECT * FROM products ORDER BY created_at DESC LIMIT :start, :limit");
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();

$headers = ['ชื่อสินค้า', 'ราคา', 'คงเหลือ', 'รูปภาพ', 'จัดการ'];
$rows = array_map(function ($p) {
    return [
        htmlspecialchars($p['name']),
        number_format($p['price'], 2),
        $p['stock'],
        $p['image'] ? "<img src='/uploads/{$p['image']}' width='60'>" : 'ไม่มีรูป',
        "<a href='edit_product.php?id={$p['id']}'>✏️</a> | <a href='#' onclick=\"confirmDelete('delete_product.php?id={$p['id']}')\">🗑️</a>",
    ];
}, $products);

session_start();
$toast = '';
if (isset($_SESSION['product_updated'])) {
    $toast = '✅ แก้ไขสินค้าสำเร็จ';
    unset($_SESSION['product_updated']);
}
if (isset($_SESSION['product_deleted'])) {
    $toast = '🗑️ ลบสินค้าสำเร็จ';
    unset($_SESSION['product_deleted']);
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
  <h2>📦 รายการสินค้า</h2>
  <a href="create_product.php" class="button">+ สร้างสินค้า</a>
</div>

<?php renderSearchBox(); ?>
<?php renderTable($headers, $rows); ?>
<?php renderPagination($page, $total, $perPage); ?>

<!-- Modal ยืนยันการลบ -->
<div id="deleteModal" class="modal-backdrop">
  <div class="modal">
    <h3>ยืนยันการลบสินค้า?</h3>
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

document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
  if (deleteUrl) {
    window.location.href = deleteUrl;
  }
});
</script>


<?php
$content = ob_get_clean();
$title = "Products";
include __DIR__ . '/../layouts/layout.php';
?>
