<div class="table-header">
  <h2>📦 รายชื่อลูกค้า</h2>
  <a href="create_customer.php" class="button">+ สร้างลูกค้าใหม่</a>
</div>

<?php
$content = ob_get_clean();
$title = "Customers";
include __DIR__ . '/../layouts/layout.php';
?>
