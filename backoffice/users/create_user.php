<?php
require_once '../../includes/db.php';
require_once '../../includes/config.php';

session_start();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = trim($_POST['role'] ?? 'user');

    if (!$name || !$email) {
        $error = 'กรุณากรอกข้อมูลให้ครบถ้วน';
    }

    if (!$error) {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, role) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $role]);

        $_SESSION['user_created'] = true;
        header('Location: users.php');
        exit;
    }
}
?>

<?php ob_start(); ?>
<div style="max-width: 600px; margin: auto;">
    <h2>➕ เพิ่มผู้ใช้ใหม่</h2>
    <?php if ($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST" style="display: flex; flex-direction: column; gap: 16px;">
        <label>ชื่อ:
            <input type="text" name="name" required>
        </label>
        <label>Email:
            <input type="email" name="email" required>
        </label>
        <label>Role:
            <select name="role">
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
        </label>
        <button type="submit">💾 บันทึก</button>
        <a href="users.php">← กลับ</a>
    </form>
</div>
<?php
$content = ob_get_clean();
$title = "Create User";
include __DIR__ . '/../layouts/layout.php';
?>
