## 📦 Pure PHP Template - วิธีการติดตั้งและใช้งาน

## โปรเจกต์นี้เป็นโครงสร้างพื้นฐานของระบบ PHP (ไม่ใช้ Framework) ที่เหมาะสำหรับเริ่มพัฒนาเว็บแอปพลิเคชันแบบง่าย รองรับ MVC, Routing, Pagination, Components, Layout และมีโครงสร้างโฟลเดอร์ที่ชัดเจน

### ✅ ความต้องการระบบ

- PHP >= 7.4
- MySQL / MariaDB
- Apache / Nginx (หรือใช้ PHP built-in server ได้)
- Composer

---

### 🧑‍💻 ขั้นตอนติดตั้ง

#### 1. Clone Repo

```bash
git clone https://github.com/pho0m/pure-php-template.git myproject
cd myproject
```

---

#### 2. คัดลอก `.env.example` ไปเป็น `.env`

```bash
cp .env.example .env
```

จากนั้นตั้งค่าข้อมูลฐานข้อมูลของคุณใน `.env` เช่น:

```env
DB_HOST=localhost
DB_NAME=my_database
DB_USER=root
DB_PASS=
```

---

#### 3. สร้างฐานข้อมูล

สร้างฐานข้อมูลตามชื่อใน `.env` เช่น `my_database` (ใช้ phpMyAdmin หรือ MySQL CLI ก็ได้):

```sql
CREATE DATABASE my_database CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
```

จากนั้น import ไฟล์ SQL ที่ใช้เป็น schema ได้ เช่น:

```bash
mysql -u root -p my_database < schema.sql
```

---

#### 4. รันเว็บเซิร์ฟเวอร์ (แบบ local)

**กรณีใช้ PHP Built-in Server:**

```bash
php -S localhost:8000 -t public
```

แล้วเปิดเบราว์เซอร์ไปที่:

```
http://localhost:8000
```

**หรือกรณีใช้ XAMPP/Apache:**
ให้วางโปรเจกต์ไว้ที่ `htdocs/myproject` แล้วเข้าผ่าน:

```
http://localhost/myproject/public
```

---

### 📁 โครงสร้างโปรเจกต์โดยย่อ

```bash
├── public/               # Root สำหรับ Web Server
│   └── index.php         # Entry point
├── includes/             # DB connection, config, helper
├── layouts/              # Template layout หลัก
├── pages/                # ไฟล์ PHP แต่ละหน้า
├── components/           # Component UI เช่น Table, Form
├── .env                  # ไฟล์ตั้งค่าเชื่อมต่อ DB
└── README.md
```

---

### 🚀 เริ่มพัฒนา

- เพิ่มหน้าใหม่ได้ที่โฟลเดอร์ `pages/`
- เพิ่มคอมโพเนนต์ UI ที่ใช้ซ้ำใน `components/`
- Layout หลักอยู่ที่ `layouts/layout.php`
- ระบบ Routing เรียบง่าย: ใช้ `$_GET['page']` เพื่อเปลี่ยนหน้า เช่น `index.php?page=orders`

---

### 🧪 ทดสอบระบบ

เมื่อเปิดผ่านเบราว์เซอร์แล้ว หากเชื่อมต่อฐานข้อมูลได้สำเร็จและไม่มีข้อผิดพลาดแสดงว่าเซ็ตอัพเสร็จสมบูรณ์ 🎉

---

หากต้องการให้ผมเขียนไฟล์ `schema.sql`, seed ข้อมูลเริ่มต้น, หรือ deploy จริงบน server เช่น VPS / cPanel ก็บอกได้นะครับ!
