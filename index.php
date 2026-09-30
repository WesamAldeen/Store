<?php
session_start();
include 'db.php';

$categories_sql = "SELECT * FROM categories";
$categories_result = $conn->query($categories_sql);

$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

if (!empty($category_filter)) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE category = ?");
    $stmt->bind_param("s", $category_filter);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT * FROM products";
    $result = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ماجيك ستور</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- استدعاء خط Zain من Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Zain:ital,wght@0,300;0,400;0,700;0,800;0,900;1,300;1,400&display=swap" rel="stylesheet">
    <style>
        :root {
            --indrive-green: #12A150;
            --indrive-hover: #0e8140;
            --indrive-dark: #0f172a;
        }
        body {
            font-family: 'Zain', sans-serif;
            background-color: #f7f9fa;
            color: #1e293b;
            font-size: 13px; /* تصغير عام لراحة العين على الموبايل */
        }
        .navbar-custom {
            background-color: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            padding: 8px 0;
        }
        /* الأقسام الأفقية */
        .categories-wrapper {
            display: flex;
            gap: 6px;
            overflow-x: auto;
            padding: 4px 2px 10px 2px;
            white-space: nowrap;
            scrollbar-width: none;
        }
        .categories-wrapper::-webkit-scrollbar {
            display: none;
        }
        .cat-chip {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 18px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #64748b;
            transition: all 0.2s ease;
        }
        .cat-chip.active {
            background: var(--indrive-green);
            color: #ffffff;
            border-color: var(--indrive-green);
        }

        /* كرت المنتج الأفقي للهاتف (مريح، بخطوط دقيقة وغير مزعجة) */
        .product-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }
        .product-img-container {
            width: 75px;
            height: 75px;
            border-radius: 9px;
            overflow: hidden;
            background: #f1f5f9;
            flex-shrink: 0;
        }
        .product-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .product-info {
            flex-grow: 1;
            min-width: 0;
        }
        .product-title {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 1px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .product-desc {
            font-size: 11px;
            color: #64748b;
            margin-bottom: 4px;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .price-text {
            font-size: 13px;
            font-weight: 800;
            color: var(--indrive-green);
        }
        .price-curr {
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
        }
        .btn-add {
            background-color: var(--indrive-green);
            color: white;
            border: none;
            border-radius: 7px;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }
        .btn-add:hover {
            background-color: var(--indrive-hover);
            color: white;
        }
        .cart-btn-nav {
            background: #f0fdf4;
            color: var(--indrive-green);
            border: 1px solid #dcfce7;
            font-weight: 700;
            border-radius: 9px;
            padding: 4px 10px;
            font-size: 11px;
        }

        /* تحويل لعرض شبكي على الشاشات الكبيرة */
        @media (min-width: 768px) {
            body {
                font-size: 14px;
            }
            .products-container {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 15px;
            }
            .product-card {
                flex-direction: column;
                align-items: stretch;
                padding: 14px;
                margin-bottom: 0;
            }
            .product-img-container {
                width: 100%;
                height: 150px;
                margin-bottom: 8px;
            }
            .product-desc {
                -webkit-line-clamp: 2;
            }
        }
    </style>
</head>
<body>

    <!-- شريط التنقل العلوي -->
    <nav class="navbar navbar-custom sticky-top mb-3">
        <div class="container px-3 d-flex justify-content-between align-items-center">
            <a class="navbar-brand fw-bold m-0 d-flex align-items-center gap-2 text-dark" style="font-size: 14px;" href="index.php">
                <span class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 22px; height: 22px; font-size: 9px;">
                    <i class="fa-solid fa-paw"></i>
                </span>
                ماجيك ستور
            </a>
            <a href="cart.php" class="btn cart-btn-nav position-relative">
                <i class="fa-solid fa-shopping-bag me-1"></i> السلة
                <?php 
                $cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
                if($cart_count > 0): 
                ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 8px; border: 2px solid #fff;">
                        <?php echo $cart_count; ?>
                    </span>
                <?php endif; ?>
            </a>
        </div>
    </nav>

    <div class="container px-3 pb-5" style="max-width: 900px;">
        
        <!-- الأقسام الأفقية -->
        <div class="mb-3">
            <div class="categories-wrapper">
                <a href="index.php" class="cat-chip <?php echo empty($category_filter) ? 'active' : ''; ?>">
                    الكل
                </a>
                <?php if ($categories_result->num_rows > 0): ?>
                    <?php while($cat = $categories_result->fetch_assoc()): ?>
                        <a href="index.php?category=<?php echo urlencode($cat['name']); ?>" class="cat-chip <?php echo ($category_filter == $cat['name']) ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </a>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- قائمة المنتجات -->
        <div class="products-container">
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="product-card">
                        <div class="product-img-container">
                            <!-- التعديل هنا: إضافة مجلد uploads/ والتحقق من وجود الصورة -->
                            <img src="<?php echo !empty($row['image']) && $row['image'] != 'default.png' ? 'uploads/' . htmlspecialchars($row['image']) : 'https://via.placeholder.com/200'; ?>" class="product-img" alt="">
                        </div>
                        <div class="product-info">
                            <h6 class="product-title"><?php echo htmlspecialchars($row['name']); ?></h6>
                            <p class="product-desc"><?php echo htmlspecialchars($row['description']); ?></p>
                            
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div>
                                    <span class="price-text"><?php echo $row['price']; ?></span>
                                    <span class="price-curr">ر.س</span>
                                </div>
                                <form action="add_to_cart.php" method="POST" class="m-0">
                                    <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn-add">
                                        <i class="fa-solid fa-cart-plus"></i> إضافة
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-5 w-100">
                    <div class="p-4 bg-white rounded-4 border shadow-sm">
                        <i class="fa-solid fa-box-open fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0" style="font-size: 12px;">لا توجد منتجات متاحة حالياً.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>