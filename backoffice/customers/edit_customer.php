<?php
require_once '../../includes/db.php';
require_once '../../includes/config.php';
session_start();

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: customers.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$id]);
$customer = $stmt->fetch();
if (!$customer) {
    echo "ไม่พบข้อมูลลูกค้า";
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (!$name || !$email) {
        $error = 'กรุณากรอกชื่อและอีเมล';
    }

    if (!$error) {
        $stmt = $pdo->prepare("UPDATE customers SET name=?, email=?, phone=? WHERE id=?");
        $stmt->execute([$name, $email, $phone, $id]);
        $_SESSION['customer_updated'] = true;
        header('Location: customers.php');
        exit;
    }
}
?>

<?php ob_start(); ?>
<div style="max-width: 600px; margin: 0 auto;">
    <h2>📝 แก้ไขลูกค้า</h2>
    <?php if ($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST" style="display: flex; flex-direction: column; gap: 16px;">
        <label>
            ชื่อลูกค้า:
            <input type="text" name="name" value="<?= htmlspecialchars($customer['name']) ?>" required>
        </label>
        <label>
            อีเมล:
            <input type="email" name="email" value="<?= htmlspecialchars($customer['email']) ?>" required>
        </label>
        <label>
            เบอร์โทร:
            <input type="text" name="phone" value="<?= htmlspecialchars($customer['phone']) ?>">
        </label>
        <button type="submit">💾 บันทึก</button>
        <a href="customers.php">← กลับ</a>
    </form>
</div>
<?php
$content = ob_get_clean();
$title = "Edit Customer";
include __DIR__ . '/../layouts/layout.php';
?>
