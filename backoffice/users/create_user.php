<?php
require_once '../../includes/db.php';
require_once '../../includes/config.php';

session_start();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = trim($_POST['role'] ?? 'staff');

    // ตรวจสอบว่ากรอกข้อมูลครบหรือไม่
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

    // ถ้าไม่มี error → บันทึกข้อมูล
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
            <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
        </label>
        <label>Email:
            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </label>
        <label>Role:
            <select name="role">
                <option value="staff" <?= ($_POST['role'] ?? '') === 'staff' ? 'selected' : '' ?>>Staff</option>
                <option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
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
