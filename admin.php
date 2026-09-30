<?php
session_start();
include 'db.php';

// إضافة صنف جديد بشكل آمن
if (isset($_POST['add_category'])) {
    $cat_name = trim($_POST['category_name']);
    if (!empty($cat_name)) {
        $stmt = $conn->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $cat_name);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: admin.php");
    exit();
}

// حذف صنف بشكل آمن
if (isset($_GET['delete_cat'])) {
    $cat_id = intval($_GET['delete_cat']);
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $cat_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// إضافة منتج جديد (الوصف أصبح اختيارياً)
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $description = !empty($_POST['description']) ? trim($_POST['description']) : NULL; // جعل الوصف اختيارياً
    $price = $_POST['price'];
    $category = $_POST['category'];
    
    $image = 'default.png';
    if (!empty($_FILES['image']['name'])) {
        $image = time() . '_' . basename($_FILES['image']['name']);
        $target = "uploads/" . $image;
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
    }
    
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, category, image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdss", $name, $description, $price, $category, $image);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// تعديل منتج (الوصف أصبح اختيارياً)
if (isset($_POST['edit_product'])) {
    $id = intval($_POST['id']);
    $name = $_POST['name'];
    $description = !empty($_POST['description']) ? trim($_POST['description']) : NULL; // جعل الوصف اختيارياً
    $price = $_POST['price'];
    $category = $_POST['category'];
    
    if (!empty($_FILES['image']['name'])) {
        $image = time() . '_' . basename($_FILES['image']['name']);
        $target = "uploads/" . $image;
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
        
        $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, category=?, image=? WHERE id=?");
        $stmt->bind_param("ssdssi", $name, $description, $price, $category, $image, $id);
    } else {
        $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, category=? WHERE id=?");
        $stmt->bind_param("ssdsi", $name, $description, $price, $category, $id);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// حذف منتج
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

$categories = $conn->query("SELECT * FROM categories");
$search = $_GET['search'] ?? '';

if (!empty($search)) {
    $search_param = "%" . $search . "%";
    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ?");
    $stmt->bind_param("s", $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    $result = $conn->query("SELECT * FROM products");
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم الشاملة - متجر الأليفة</title>
    <!-- استدعاء خط زين (Zain) الشهير -->
    <link href="https://fonts.googleapis.com/css2?family=Zain:ital,wght@0,300;0,400;0,700;0,800;0,900;1,300;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --indrive-green: #12A150;
            --indrive-hover: #0e8140;
            --bg: #f7f9fa; 
            --card: #ffffff; 
            --text: #1e293b; 
            --table-header: #f1f5f9;
        }
        [data-theme="dark"] { 
            --bg: #0f172a; 
            --card: #1e293b; 
            --text: #f8fafc; 
            --table-header: #334155; 
        }
        body { 
            background-color: var(--bg); 
            color: var(--text); 
            font-family: 'Zain', sans-serif !important; 
            font-size: 13px;
            transition: background 0.3s, color 0.3s; 
        }
        .card { 
            background-color: var(--card); 
            border: 1px solid rgba(0,0,0,0.05); 
            border-radius: 12px; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.02); 
            color: var(--text); 
        }
        .table-img { width: 45px; height: 45px; object-fit: cover; border-radius: 8px; }
        .theme-toggle-nav { cursor: pointer; font-size: 1.1rem; background: none; border: none; }
        
        .table { color: var(--text) !important; margin-bottom: 0; }
        .table th { background-color: var(--table-header) !important; color: var(--text) !important; border-bottom: 1px solid rgba(0,0,0,0.05); font-size: 12px; }
        .table td { background-color: var(--card) !important; color: var(--text) !important; border-bottom: 1px solid rgba(0,0,0,0.05); font-size: 12px; }
        
        .btn-success { background-color: var(--indrive-green) !important; border-color: var(--indrive-green) !important; }
        .btn-success:hover { background-color: var(--indrive-hover) !important; }
        
        @media (min-width: 768px) {
            body { font-size: 14px; }
            .table th, .table td { font-size: 13px; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark mb-3 py-2">
    <div class="container px-3">
        <a class="navbar-brand fw-bold m-0" style="font-size: 14px;" href="admin.php">⚙️ لوحة التحكم بمتجر الأليفة</a>
        <div class="d-flex align-items-center gap-2">
            <a href="index.php" class="text-white text-decoration-none small" style="font-size: 12px;"><i class="fa-solid fa-store me-1"></i> المتجر</a>
            <button class="theme-toggle-nav text-white" id="adminThemeToggle" title="تغيير الوضع">🌙</button>
        </div>
    </div>
</nav>

<div class="container px-3 pb-5" style="max-width: 1000px;">
    <div class="row g-3">
        <!-- قسم الأصناف -->
        <div class="col-md-4">
            <div class="card p-3 mb-3">
                <h6 class="fw-bold mb-2" style="font-size: 14px;">📁 إضافة صنف جديد</h6>
                <form action="admin.php" method="POST" class="d-flex gap-2">
                    <input type="text" name="category_name" class="form-control form-control-sm" placeholder="اسم الصنف..." required style="font-size: 12px;">
                    <button type="submit" name="add_category" class="btn btn-sm btn-success px-3 fw-bold" style="font-size: 12px;">إضافة</button>
                </form>
            </div>
            
            <div class="card p-3">
                <h6 class="fw-bold mb-2" style="font-size: 14px;">🗂️ الأصناف الحالية</h6>
                <ul class="list-group p-0">
                    <?php if($categories && $categories->num_rows > 0): ?>
                        <?php $categories->data_seek(0); while($cat = $categories->fetch_assoc()): ?>
                            <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center border-0 border-bottom text-reset p-2" style="font-size: 12px;">
                                <span>🔹 <?php echo htmlspecialchars($cat['name']); ?></span>
                                <a href="admin.php?delete_cat=<?php echo $cat['id']; ?>" class="btn btn-sm text-danger text-decoration-none p-0 fw-bold" style="font-size: 11px;" onclick="return confirm('هل أنت متأكد من حذف الصنف؟')">حذف</a>
                            </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li class="list-group-item bg-transparent border-0 text-muted text-center small p-2">لا توجد أصناف</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- قسم إدارة المنتجات -->
        <div class="col-md-8">
            <div class="card p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold m-0" style="font-size: 15px;">📦 إدارة المنتجات</h5>
                    <button class="btn btn-sm btn-success fw-bold" style="font-size: 12px;" data-bs-toggle="modal" data-bs-target="#addProductModal">إضافة منتج +</button>
                </div>
                
                <form class="mb-3" method="GET">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="ابحث عن منتج..." value="<?php echo htmlspecialchars($search); ?>" style="font-size: 12px;">
                        <button class="btn btn-outline-secondary" type="submit" style="font-size: 12px;">بحث</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>الصورة</th>
                                <th>الاسم</th>
                                <th>السعر</th>
                                <th>الصنف</th>
                                <th>العمليات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($result && $result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><img src="uploads/<?php echo !empty($row['image']) ? $row['image'] : 'default.png'; ?>" class="table-img" alt=""></td>
                                    <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                    <td class="fw-bold" style="color: var(--indrive-green);"><?php echo number_format($row['price'], 2); ?> ر.س</td>
                                    <td><span class="badge bg-secondary" style="font-size: 10px;"><?php echo htmlspecialchars($row['category']); ?></span></td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-warning btn-sm edit-btn px-2 py-1" style="font-size: 11px;" 
                                                data-id="<?php echo $row['id']; ?>" 
                                                data-name="<?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?>" 
                                                data-desc="<?php echo htmlspecialchars($row['description'] ?? '', ENT_QUOTES); ?>" 
                                                data-price="<?php echo $row['price']; ?>" 
                                                data-cat="<?php echo htmlspecialchars($row['category'], ENT_QUOTES); ?>">تعديل</button>
                                            <a href="admin.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm px-2 py-1" style="font-size: 11px;" onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">لا توجد منتجات مسجلة.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal إضافة منتج -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form action="admin.php" method="POST" enctype="multipart/form-data" class="modal-content card border">
      <div class="modal-header py-2">
        <h5 class="modal-title fw-bold" style="font-size: 14px;">إضافة منتج جديد</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body py-2" style="font-size: 12px;">
        <div class="mb-2"><label class="form-label mb-1">اسم المنتج</label><input type="text" name="name" class="form-control form-control-sm" required style="font-size: 12px;"></div>
        <!-- تمت إزالة required من الوصف ليكون اختيارياً -->
        <div class="mb-2"><label class="form-label mb-1">الوصف (اختياري)</label><textarea name="description" class="form-control form-control-sm" rows="2" style="font-size: 12px;"></textarea></div>
        <div class="mb-2"><label class="form-label mb-1">السعر (ر.س)</label><input type="number" step="0.01" name="price" class="form-control form-control-sm" required style="font-size: 12px;"></div>
        <div class="mb-2"><label class="form-label mb-1">الصنف</label><select name="category" class="form-select form-select-sm" required style="font-size: 12px;">
            <?php if($categories): $categories->data_seek(0); while($cat = $categories->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($cat['name']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
            <?php endwhile; endif; ?>
        </select></div>
        <div class="mb-2"><label class="form-label mb-1">الصورة</label><input type="file" name="image" class="form-control form-control-sm" style="font-size: 12px;"></div>
      </div>
      <div class="modal-footer py-2">
        <button type="submit" name="add_product" class="btn btn-success btn-sm fw-bold px-3" style="font-size: 12px;">حفظ المنتج</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal تعديل منتج -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form action="admin.php" method="POST" enctype="multipart/form-data" class="modal-content card border">
      <input type="hidden" name="id" id="edit-id">
      <div class="modal-header py-2">
        <h5 class="modal-title fw-bold" style="font-size: 14px;">تعديل بيانات المنتج</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body py-2" style="font-size: 12px;">
        <div class="mb-2"><label class="form-label mb-1">اسم المنتج</label><input type="text" name="name" id="edit-name" class="form-control form-control-sm" required style="font-size: 12px;"></div>
        <!-- تمت إزالة required من الوصف للتعديل أيضاً -->
        <div class="mb-2"><label class="form-label mb-1">الوصف (اختياري)</label><textarea name="description" id="edit-desc" class="form-control form-control-sm" rows="2" style="font-size: 12px;"></textarea></div>
        <div class="mb-2"><label class="form-label mb-1">السعر (ر.س)</label><input type="number" step="0.01" name="price" id="edit-price" class="form-control form-control-sm" required style="font-size: 12px;"></div>
        <div class="mb-2"><label class="form-label mb-1">الصنف</label><select name="category" id="edit-cat" class="form-select form-select-sm" required style="font-size: 12px;">
            <?php if($categories): $categories->data_seek(0); while($cat = $categories->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($cat['name']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
            <?php endwhile; endif; ?>
        </select></div>
        <div class="mb-2"><label class="form-label mb-1">تغيير الصورة (اختياري)</label><input type="file" name="image" class="form-control form-control-sm" style="font-size: 12px;"></div>
      </div>
      <div class="modal-footer py-2">
        <button type="submit" name="edit_product" class="btn btn-warning btn-sm fw-bold px-3 text-white" style="font-size: 12px;">تحديث البيانات</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const adminThemeToggle = document.getElementById('adminThemeToggle');
    if (localStorage.getItem('admin_theme') === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
        adminThemeToggle.textContent = '☀️';
    }
    
    adminThemeToggle.addEventListener('click', () => {
        if (document.documentElement.getAttribute('data-theme') === 'dark') {
            document.documentElement.setAttribute('data-theme', 'light');
            localStorage.setItem('admin_theme', 'light');
            adminThemeToggle.textContent = '🌙';
        } else {
            document.documentElement.setAttribute('data-theme', 'dark');
            localStorage.setItem('admin_theme', 'dark');
            adminThemeToggle.textContent = '☀️';
        }
    });

    // ربط أزرار التعديل بالنافذة المنبثقة بشكل صحيح وآمن
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('edit-id').value = this.dataset.id;
            document.getElementById('edit-name').value = this.dataset.name;
            document.getElementById('edit-desc').value = this.dataset.desc;
            document.getElementById('edit-price').value = this.dataset.price;
            document.getElementById('edit-cat').value = this.dataset.cat;
            new bootstrap.Modal(document.getElementById('editProductModal')).show();
        });
    });
</script>
</body>
</html>