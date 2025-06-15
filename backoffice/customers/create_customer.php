<?php
require_once '../../includes/db.php';
require_once '../../includes/config.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');

  if (!$name || !$email) {
    $error = 'กรุณากรอกชื่อและอีเมล';
  } else {
    $stmt = $pdo->prepare("INSERT INTO customers (name, email, phone) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $phone]);
    $_SESSION['customer_created'] = true;
    header('Location: customers.php');
    exit;
  }
}
?>

<?php ob_start(); ?>
<div style="max-width: 600px; margin: 0 auto;">
  <h2>➕ เพิ่มลูกค้า</h2>
  <?php if ($error): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>
  <form method="POST" style="display: flex; flex-direction: column; gap: 16px;">
    <label>
      ชื่อลูกค้า:
      <input type="text" name="name" required>
    </label>
    <label>
      อีเมล:
      <input type="email" name="email" required>
    </label>
    <label>
      เบอร์โทร:
      <input type="text" name="phone">
    </label>
    <button type="submit">💾 บันทึก</button>
    <a href="customers.php">← กลับ</a>
  </form>
</div>
<?php
$content = ob_get_clean();
$title = "Create Customer";
include __DIR__ . '/../layouts/layout.php';
?>
