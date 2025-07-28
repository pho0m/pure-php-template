<?php
require_once '../../includes/db.php';
require_once '../../includes/config.php';

session_start();

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: users.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) {
    echo "ไม่พบข้อมูลผู้ใช้";
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = trim($_POST['role'] ?? '');

    if (!$name || !$email) {
        $error = 'กรุณากรอกข้อมูลให้ครบถ้วน';
    }

    // ตรวจสอบรูปแบบอีเมล
    if (!$error && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'รูปแบบอีเมลไม่ถูกต้อง';
    }

    // ตรวจสอบชื่อซ้ำ
    if (!$error) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE name = ?");
        $stmt->execute([$name]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'ชื่อผู้ใช้นี้ถูกใช้ไปแล้ว กรุณาใช้ชื่ออื่น';
        }
    }

    // ตรวจสอบอีเมลซ้ำ
    if (!$error) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'อีเมลนี้ถูกใช้ไปแล้ว กรุณาใช้อีเมลอื่น';
        }
    }


    if (!$error) {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
        $stmt->execute([$name, $email, $role, $id]);

        $_SESSION['user_updated'] = true;
        header('Location: users.php');
        exit;
    }
}
?>

<?php ob_start(); ?>
<div style="max-width: 600px; margin: auto;">
    <h2>📝 แก้ไขผู้ใช้</h2>
    <?php if ($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST" style="display: flex; flex-direction: column; gap: 16px;">
        <label>ชื่อ:
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
        </label>
        <label>Email:
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </label>
        <label>Role:
            <select name="role">
                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </label>
        <button type="submit">💾 บันทึก</button>
        <a href="users.php">← กลับ</a>
    </form>
</div>
<?php
$content = ob_get_clean();
$title = "Edit User";
include __DIR__ . '/../layouts/layout.php';
?>
