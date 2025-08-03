<?php
require_once '../../includes/db.php';
require_once '../../includes/config.php';
?>

<?php ob_start(); ?>

<h2>➕ สร้างคำสั่งซื้อใหม่</h2>

<form id="orderForm">
    <!-- ลูกค้า -->
    <div style="margin-bottom: 1rem;">
        <label><strong>ลูกค้า:</strong></label>
        <div style="display: flex; gap: 8px; align-items: center;">
            <input type="text" id="selectedCustomerName" placeholder="ยังไม่ได้เลือกลูกค้า" readonly style="flex: 1;">
            <button type="button" onclick="openCustomerModal()">เลือก</button>
            <button type="button" onclick="clearCustomer()">❌</button>
        </div>
        <input type="hidden" name="customer_id" id="customerId">
        <input type="hidden" name="customer_email" id="customerEmail">
        <input type="hidden" name="customer_phone" id="customerPhone">
    </div>

    <!-- สินค้า -->
    <h3>🛒 เลือกสินค้า</h3>
    <button type="button" onclick="openProductModal()">เลือกสินค้า</button>

    <table id="productTable" border="1" cellpadding="6" style="margin-top: 10px; width: 100%;">
        <thead>
            <tr>
                <th>ชื่อสินค้า</th>
                <th>จำนวน</th>
                <th>ราคา</th>
                <th>ลบ</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <!-- ส่วนลดและ VAT -->
    <div style="margin-top: 10px;">
        <label>ส่วนลด: <input type="number" id="discount" name="discount" value="0"></label>
        <label>VAT (%): <input type="number" id="vat" name="vat" value="7"></label>
    </div>
    <div style="margin-top: 10px;">
        <strong>รวมทั้งหมด: <span id="totalDisplay">0</span> ฿</strong>
    </div>

    <button type="submit">💾 บันทึกคำสั่งซื้อ</button>
</form>

<!-- Modal ลูกค้า -->
<div id="customerModal" class="modal-backdrop" style="display: none;">
    <div class="modal">
        <h3>เลือกลูกค้า</h3>
        <input type="text" id="customerSearch" placeholder="ค้นหาชื่อลูกค้า..." oninput="filterCustomers()">
        <div id="customerList" style="max-height: 250px; overflow-y: auto;">
            <?php
            $stmt = $pdo->query("SELECT id, name, email, phone FROM customers ORDER BY name ASC LIMIT 100");
            $customers = $stmt->fetchAll();
            foreach ($customers as $c):
            ?>
                <div style="display: flex; flex-direction: column; align-items: flex-start; padding: 6px 0; gap: 2px;">
                    <label style="align-items: flex-start; padding: 6px 0; gap: 2px;">
                        <input type="radio" name="customer" class="customer-radio"
                            data-id="<?= $c['id'] ?>"
                            data-name="<?= htmlspecialchars($c['name']) ?>"
                            data-email="<?= htmlspecialchars($c['email']) ?>"
                            data-phone="<?= htmlspecialchars($c['phone']) ?>">
                        <div>
                            <strong><?= htmlspecialchars($c['name']) ?></strong><br>
                            <small><?= htmlspecialchars($c['email']) ?> / <?= htmlspecialchars($c['phone']) ?></small>
                        </div>
                    </label>
                </div>

            <?php endforeach; ?>
        </div>
        <div style="text-align: right; margin-top: 10px;">
            <button type="button" onclick="closeCustomerModal()">ปิด</button>
            <button type="button" onclick="confirmCustomer()">ยืนยัน</button>
        </div>
    </div>
</div>


<!-- Modal สินค้า -->
<div id="productModal" class="modal-backdrop" style="display: none;">
    <div class="modal">
        <h3>เลือกสินค้า</h3>
        <div id="productList" style="max-height: 250px; overflow-y: auto;">
            <?php
            $stmt = $pdo->query("SELECT id, name, price FROM products ORDER BY created_at DESC LIMIT 50");
            $products = $stmt->fetchAll();
            foreach ($products as $p):
            ?>
                <label style="display: flex; justify-content: space-between;">
                    <span><input type="checkbox" class="product-checkbox" value="<?= $p['id'] ?>" data-name="<?= htmlspecialchars($p['name']) ?>" data-price="<?= $p['price'] ?>"> <?= htmlspecialchars($p['name']) ?></span>
                    <span><?= number_format($p['price'], 2) ?> ฿</span>
                </label>
            <?php endforeach; ?>
        </div>
        <div style="text-align: right; margin-top: 10px;">
            <button type="button" onclick="closeProductModal()">ปิด</button>
            <button type="button" onclick="confirmProducts()">ยืนยัน</button>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
    const productTableBody = document.querySelector('#productTable tbody');

    function openCustomerModal() {
        document.getElementById('customerModal').style.display = 'flex';
    }

    function closeCustomerModal() {
        document.getElementById('customerModal').style.display = 'none';
    }

    function confirmCustomer() {
        const selected = document.querySelector('.customer-radio:checked');
        if (!selected) {
            alert("กรุณาเลือกลูกค้า");
            return;
        }
        document.getElementById('selectedCustomerName').value = selected.dataset.name;
        document.getElementById('customerId').value = selected.dataset.id;
        document.getElementById('customerEmail').value = selected.dataset.email;
        document.getElementById('customerPhone').value = selected.dataset.phone;
        closeCustomerModal();
    }

    function clearCustomer() {
        document.getElementById('selectedCustomerName').value = '';
        document.getElementById('customerId').value = '';
        document.getElementById('customerEmail').value = '';
        document.getElementById('customerPhone').value = '';
    }

    function filterCustomers() {
        const input = document.getElementById('customerSearch').value.toLowerCase();
        document.querySelectorAll('#customerList label').forEach(label => {
            const name = label.textContent.toLowerCase();
            label.style.display = name.includes(input) ? 'flex' : 'none';
        });
    }

    function openProductModal() {
        document.getElementById('productModal').style.display = 'flex';
    }

    function closeProductModal() {
        document.getElementById('productModal').style.display = 'none';
    }

    function confirmProducts() {
        const checkboxes = document.querySelectorAll('.product-checkbox:checked');
        checkboxes.forEach(cb => {
            const id = cb.value;
            const name = cb.dataset.name;
            const price = parseFloat(cb.dataset.price);
            if (document.getElementById(`row-${id}`)) return;
            const row = document.createElement('tr');
            row.id = `row-${id}`;
            row.innerHTML = `
                <td>
                    ${name}<input type="hidden" name="product_ids[]" value="${id}">
                    <input type="hidden" name="prices[]" value="${price}">
                </td>
                <td><input type="number" name="quantities[]" value="1" min="1" oninput="calculateTotal()" required></td>
                <td class="product-price">${price.toFixed(2)} ฿</td>
                <td><button type="button" onclick="removeProductRow(${id})">❌</button></td>
            `;
            productTableBody.appendChild(row);
        });
        calculateTotal();
        closeProductModal();
    }

    function removeProductRow(id) {
        const row = document.getElementById(`row-${id}`);
        if (row) row.remove();
        calculateTotal();
        const cb = document.querySelector(`.product-checkbox[value="${id}"]`);
        if (cb) cb.checked = false;
    }

    function calculateTotal() {
        let total = 0;
        productTableBody.querySelectorAll('tr').forEach(row => {
            const qty = parseFloat(row.querySelector('input[type="number"]').value) || 0;
            const price = parseFloat(row.querySelector('input[name="prices[]"]').value);
            total += qty * price;
        });
        const discount = parseFloat(document.getElementById('discount').value) || 0;
        const vatPercent = parseFloat(document.getElementById('vat').value) || 0;
        let discounted = total - discount;
        let vat = (discounted * vatPercent) / 100;
        let finalTotal = discounted + vat;
        document.getElementById('totalDisplay').innerText = finalTotal.toFixed(2);
    }

    document.getElementById('discount').addEventListener('input', calculateTotal);
    document.getElementById('vat').addEventListener('input', calculateTotal);

    document.getElementById('orderForm').addEventListener('submit', async e => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const res = await fetch('store_order.php', {
            method: 'POST',
            body: formData
        });
        const text = await res.text();
        if (text.includes('success')) {
            alert('บันทึกคำสั่งซื้อสำเร็จ');
            window.location.href = 'orders.php';
        } else {
            alert('เกิดข้อผิดพลาด: ' + text);
        }
    });
</script>

<?php
$content = ob_get_clean();
$title = "Create Order";
include __DIR__ . '/../layouts/layout.php';
?>
