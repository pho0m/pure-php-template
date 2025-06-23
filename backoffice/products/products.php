<div class="table-header">
  <h2>📦 รายการสินค้า</h2>
  <a href="create_product.php" class="button">+ สร้างสินค้า</a>
</div>

<?php
$content = ob_get_clean();
$title = "Products";
include __DIR__ . '/../layouts/layout.php';
?>
