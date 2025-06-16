<?php
require_once '../../includes/db.php';
require_once '../../includes/config.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: products.php');
    exit;
}

// ดึงข้อมูลสินค้าเดิม
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
    echo "ไม่พบข้อมูลสินค้า";
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);

    $image = $product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $targetDir = '../../uploads/';
        $imageName = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $targetDir . $imageName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $image = $imageName;
        } else {
            $error = 'ไม่สามารถอัปโหลดรูปภาพใหม่ได้';
        }
    }

    if (!$name || $price <= 0 || $stock < 0) {
        $error = 'กรุณากรอกข้อมูลให้ครบถ้วนและถูกต้อง';
    }

    if (!$error) {
        $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, image=? WHERE id=?");
        $stmt->execute([$name, $desc, $price, $stock, $image, $id]);
        $_SESSION['product_updated'] = true;
        header('Location: products.php');
        exit;
    }
}
?>

<?php ob_start(); ?>
<div style="max-width: 600px; margin: 0 auto;">
    <h2>📝 แก้ไขสินค้า</h2>
    <?php if ($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 16px;">
        <label>
            ชื่อสินค้า:
            <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
        </label>
        <label>
            รายละเอียด:
            <textarea name="description"><?= htmlspecialchars($product['description']) ?></textarea>
        </label>
        <label>
            ราคา (บาท):
            <input type="number" name="price" step="0.01" value="<?= $product['price'] ?>" required>
        </label>
        <label>
            จำนวนคงเหลือ:
            <input type="number" name="stock" value="<?= $product['stock'] ?>" required>
        </label>
        <label>
            รูปภาพ:
            <?php if ($product['image']): ?>
                <img src="<?= BASE_PATH ?>/uploads/<?= htmlspecialchars($product['image']) ?>" width="60">
            <?php endif; ?>
            <input type="file" name="image">
        </label>
        <button type="button" onclick="confirmEdit()">💾 บันทึก</button>
        <a href="products.php">← กลับ</a>
    </form>
</div>

<div id="editModal" class="modal-backdrop">
    <div class="modal">
        <h3>ยืนยันการบันทึกข้อมูลสินค้า?</h3>
        <div class="modal-actions">
            <button class="modal-cancel" onclick="closeModal()">ยกเลิก</button>
            <button class="modal-confirm" onclick="document.forms[0].submit()">ตกลง</button>
        </div>
    </div>
</div>

<script>
    function confirmEdit() {
        document.getElementById('editModal').style.display = 'flex';
    }
</script>


<?php
$content = ob_get_clean();
$title = "Edit Product";
include __DIR__ . '/../layouts/layout.php';
?>
