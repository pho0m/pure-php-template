<?php
require_once '../../includes/db.php';
require_once '../components/Table.php';
require_once '../components/Pagination.php';
require_once '../components/SearchBox.php';
require_once '../../includes/config.php';

$perPage = 20;
$page = $_GET['page'] ?? 1;
$start = ($page - 1) * $perPage;

$total = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$stmt = $pdo->prepare("SELECT * FROM users ORDER BY created_at DESC LIMIT :start, :limit");
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll();

$headers = ['ชื่อผู้ใช้', 'อีเมล', 'จัดการ'];
$rows = array_map(function ($u) {
  return [
    htmlspecialchars($u['username']),
    htmlspecialchars($u['email']),
    "<a href='edit_user.php?id={$u['id']}'>✏️</a> | <a href='#' onclick=\"confirmDelete('delete_user.php?id={$u['id']}')\">🗑️</a>",
  ];
}, $users);

session_start();
$toast = '';
if (isset($_SESSION['user_updated'])) {
  $toast = '✅ แก้ไขผู้ใช้สำเร็จ';
  unset($_SESSION['user_updated']);
}
if (isset($_SESSION['user_deleted'])) {
  $toast = '🗑️ ลบผู้ใช้สำเร็จ';
  unset($_SESSION['user_deleted']);
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
  <h2>👥 รายชื่อผู้ใช้</h2>
  <a href="create_user.php" class="button">+ สร้างผู้ใช้</a>
</div>

<?php renderSearchBox(); ?>
<?php renderTable($headers, $rows); ?>
<?php renderPagination($page, $total, $perPage); ?>

<div id="deleteModal" class="modal-backdrop">
  <div class="modal">
    <h3>ยืนยันการลบผู้ใช้?</h3>
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
$title = "Users";
include __DIR__ . '/../layouts/layout.php';
?>
