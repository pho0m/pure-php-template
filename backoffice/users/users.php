<?php
require_once '../../includes/db.php';
session_start();

$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll();
?>

<?php ob_start(); ?>

<div class="table-header">
  <h2>👥 รายชื่อผู้ใช้</h2>
  <a href="create_user.php" class="button">+ สร้างผู้ใช้</a>
</div>

<!-- กล่องค้นหา -->
<div class="search-box">
  <input type="text" placeholder="ค้นหา..." />
  <button>🔍</button>
</div>

<!-- ตารางรายชื่อผู้ใช้ -->
<table border="1" cellpadding="10" cellspacing="0" style="width:100%; margin-top: 20px;">
  <thead>
    <tr>
      <th>ชื่อผู้ใช้</th>
      <th>อีเมล</th>
      <th>จัดการ</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($users as $user): ?>
      <tr>
        <td><?= htmlspecialchars($user['name']) ?></td>
        <td><?= htmlspecialchars($user['email']) ?></td>
        <td>
          <a href="edit_user.php?id=<?= $user['id'] ?>">✏️</a> |
          <a href="javascript:void(0);" onclick="confirmDelete('delete_user.php?id=<?= $user['id'] ?>')">🗑️</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php if (!empty($_SESSION['user_deleted'])): ?>
  <p style="color: green;">✅ ลบผู้ใช้เรียบร้อยแล้ว</p>
  <?php unset($_SESSION['user_deleted']); ?>
<?php endif; ?>

<script>
  function confirmDelete(url) {
    if (confirm('คุณแน่ใจหรือไม่ว่าต้องการลบผู้ใช้นี้?')) {
      window.location.href = url;
    }
  }
</script>

<!-- Pagination Mock -->
<div style="margin-top: 20px;">
  <a href="?page=1">1</a>
  <a href="?page=2">2</a>
  <a href="?page=3">3</a>
</div>

<?php
$content = ob_get_clean();
$title = "Users";
include __DIR__ . '/../layouts/layout.php';
?>
