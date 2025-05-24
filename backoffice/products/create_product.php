<?php
require_once '../../includes/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);

    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $targetDir = '../../uploads/';
        $imageName = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $targetDir . $imageName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $image = $imageName;
        } else {
            $error = 'ไม่สามารถอัปโหลดรูปภาพได้';
        }
    }

    if (!$name || $price <= 0 || $stock < 0) {
        $error = 'กรุณากรอกข้อมูลให้ครบถ้วนและถูกต้อง';
    }

    if (!$error) {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $desc, $price, $stock, $image]);

        // บันทึกเสร็จ → ตั้ง session flag
        $_SESSION['product_created'] = true;
        header('Location: products.php');
        exit;
    }
}
?>

<?php ob_start(); ?>
<div style="max-width: 600px; margin: 0 auto;">
    <h2>➕ เพิ่มสินค้าใหม่</h2>

    <?php if ($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 16px;">
        <label>
            ชื่อสินค้า:
            <input type="text" name="name" required style="padding: 8px; width: 100%;">
        </label>

        <label>
            รายละเอียด:
            <textarea name="description" rows="4" style="padding: 8px; width: 100%;"></textarea>
        </label>

        <label>
            ราคา (บาท):
            <input type="number" step="0.01" name="price" required style="padding: 8px; width: 100%;">
        </label>

        <label>
            จำนวนคงเหลือ:
            <input type="number" name="stock" required style="padding: 8px; width: 100%;">
        </label>

        <label>
            รูปภาพ:
            <input type="file" name="image" accept="image/*">
            <!-- รูป preview -->
<img id="previewImage" src="#" alt="Preview" style="display: none; margin-top: 10px; max-width: 200px; border-radius: 6px;" />

        </label>

        <button type="submit" class="button">💾 บันทึกสินค้า</button>
        <a href="products.php" style="margin-top: 8px;">← กลับไปหน้ารายการสินค้า</a>
    </form>
</div>


<!-- Toast -->
<div id="toast" style="position: fixed; top: 20px; right: 20px; background-color: #4BB543; color: white; padding: 12px 20px; border-radius: 8px; display: none; box-shadow: 0 2px 8px rgba(0,0,0,0.2); z-index: 9999;">
    ✅ เพิ่มสินค้าสำเร็จ
</div>

<script>
// ✅ Preview Image
document.querySelector('input[name="image"]').addEventListener('change', function (e) {
    const file = e.target.files[0];
    const preview = document.getElementById('previewImage');
    if (file) {
        const reader = new FileReader();
        reader.onload = function (event) {
            preview.src = event.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
});
</script>


<?php
$content = ob_get_clean();
$title = "Create Product";
include __DIR__ . '/../layouts/layout.php';
?>
