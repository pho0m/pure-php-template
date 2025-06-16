<?php
require_once '../../includes/db.php';
require_once '../../includes/config.php';
?>

<?php ob_start(); ?>
<style>
    h2,
    h3 {
        margin-bottom: 10px;
        color: #333;
    }

    form label {
        display: block;
        margin-top: 10px;
        font-weight: bold;
    }

    input[type="text"],
    input[type="number"] {
        padding: 6px 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        width: 100%;
        max-width: 400px;
    }

    button {
        padding: 6px 12px;
        border: none;
        background-color: #007bff;
        color: white;
        border-radius: 4px;
        cursor: pointer;
    }

    button:hover {
        background-color: #0056b3;
    }

    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 999;
    }

    .modal {
        background: white;
        padding: 20px;
        width: 90%;
        max-width: 500px;
        border-radius: 8px;
        box-shadow: 0 0 12px rgba(0, 0, 0, 0.2);
    }

    .customer-item:hover {
        background-color: #f0f0f0;
        cursor: pointer;
    }

    table {
        border-collapse: collapse;
        width: 100%;
    }

    th,
    td {
        padding: 8px;
        text-align: left;
    }

    th {
        background-color: #f8f8f8;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .suggestion-list {
        position: absolute;
        background: white;
        border: 1px solid #ccc;
        list-style: none;
        padding: 0;
        margin-top: 4px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        width: 300px;
    }

    .suggestion-list li {
        padding: 8px 10px;
        cursor: pointer;
    }

    .suggestion-list li:hover {
        background-color: #eee;
    }
</style>


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
    <label>
        พิมพ์ชื่อสินค้า:
        <input type="text" id="productInput" autocomplete="off">
        <ul id="productSuggestions" class="suggestion-list"></ul>
    </label>

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

    <button type="button" onclick="openProductModal()">เลือกสินค้า</button>
    <button type="submit">💾 บันทึกคำสั่งซื้อ</button>
</form>

<!-- Modal ลูกค้า -->
<div id="customerModal" class="modal-backdrop" style="display: none;">
    <div class="modal">
        <h3>เลือกลูกค้า</h3>
        <input type="text" id="customerSearch" placeholder="ค้นหาด้วยชื่อ / เบอร์ / อีเมล" oninput="filterCustomers()">
        <div id="customerList" style="max-height: 250px; overflow-y: auto;">
            <?php
            $stmt = $pdo->query("SELECT id, name, email, phone FROM customers ORDER BY name ASC LIMIT 100");
            $customers = $stmt->fetchAll();
            foreach ($customers as $c):
            ?>
                <div class="customer-item" data-id="<?= $c['id'] ?>" data-name="<?= htmlspecialchars($c['name']) ?>" data-email="<?= htmlspecialchars($c['email']) ?>" data-phone="<?= htmlspecialchars($c['phone']) ?>" onclick="selectCustomer(this)">
                    <?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['email']) ?> / <?= htmlspecialchars($c['phone']) ?>)
                </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align: right; margin-top: 10px;">
            <button type="button" onclick="closeCustomerModal()">ปิด</button>
        </div>
    </div>
</div>

<!-- Modal สินค้า -->
<div id="productModal" class="modal-backdrop" style="display: none;">
    <div class="modal">
        <h3>เลือกสินค้า</h3>
        <input type="text" id="productSearch" placeholder="ค้นหาด้วยชื่อ" oninput="filterProducts()">
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

<script>
    const productTableBody = document.querySelector('#productTable tbody');

    function openCustomerModal() {
        document.getElementById('customerModal').style.display = 'flex';
    }

    function closeCustomerModal() {
        document.getElementById('customerModal').style.display = 'none';
    }

    function selectCustomer(el) {
        document.getElementById('selectedCustomerName').value = el.dataset.name;
        document.getElementById('customerId').value = el.dataset.id;
        document.getElementById('customerEmail').value = el.dataset.email;
        document.getElementById('customerPhone').value = el.dataset.phone;
        closeCustomerModal();
    }

    function clearCustomer() {
        document.getElementById('selectedCustomerName').value = '';
        document.getElementById('customerId').value = '';
        document.getElementById('customerEmail').value = '';
        document.getElementById('customerPhone').value = '';
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
