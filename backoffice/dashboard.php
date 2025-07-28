<?php
ob_start();
?>ฏ

<h1>📊 Welcome to Admin Dashboard</h1>
<p>ยินดีต้อนรับเข้าสู่ระบบ</p>

<?php
$content = ob_get_clean();
$title = "Dashboard";
include __DIR__ . '/layouts/layout.php';
?>
