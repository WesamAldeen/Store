<?php
session_start();
include 'db.php';

if (isset($_GET['action']) && $_GET['action'] == 'clear') {
    unset($_SESSION['cart']);
    header("Location: cart.php");
    exit();
}

if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $id_to_remove = $_GET['id'];
    unset($_SESSION['cart'][$id_to_remove]);
    header("Location: cart.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سلة مشتريات- ماجيك ستور</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- استدعاء خط Zain المريح -->
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
            font-size: 13px; /* تصغير القياس العام لراحة العين على الموبايل */
        }
        .navbar-custom {
            background-color: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            padding: 8px 0;
        }
        /* كرت المنتج داخل السلة بتصميم مرتب وصغير */
        .cart-item-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px;
            margin-bottom: 8px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .item-img {
            width: 55px;
            height: 55px;
            object-fit: cover;
            border-radius: 9px;
            background: #f1f5f9;
            flex-shrink: 0;
        }
        .btn-whatsapp {
            background-color: var(--indrive-green);
            color: white;
            font-weight: 700;
            border-radius: 10px;
            border: none;
            box-shadow: 0 3px 10px rgba(18, 161, 80, 0.2);
            transition: background 0.2s;
            font-size: 12px;
        }
        .btn-whatsapp:hover {
            background-color: var(--indrive-hover);
            color: white;
        }
        .summary-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        }

        @media (min-width: 768px) {
            body {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

    <!-- شريط التنقل العلوي -->
    <nav class="navbar navbar-custom sticky-top mb-3">
        <div class="container px-3 d-flex justify-content-between align-items-center" style="max-width: 700px;">
            <a class="navbar-brand fw-bold m-0 d-flex align-items-center gap-2 text-dark" style="font-size: 14px;" href="index.php">
                <span class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 22px; height: 22px; font-size: 9px;">
                    <i class="fa-solid fa-paw"></i>
                </span>
                ماجيك ستور
            </a>
            <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3" style="font-size: 11px; font-weight: 700;">
                <i class="fa-solid fa-arrow-right me-1"></i> متابعة التسوق
            </a>
        </div>
    </nav>

    <div class="container px-3 pb-5" style="max-width: 700px;">
        
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold m-0 text-dark" style="font-size: 15px;">سلة المشتريات</h5>
            <span class="badge bg-light text-secondary border px-2.5 py-1" style="font-size: 11px; font-weight: 700;">
                <?php echo !empty($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?> منتجات
            </span>
        </div>

        <?php if (!empty($_SESSION['cart'])): ?>
            
            <div class="mb-3">
                <?php 
                $total_price = 0;
                $whatsapp_message = "مرحبا اريد الطلب التالي من متجر ماجيك ستور ";
                
                foreach ($_SESSION['cart'] as $key => $item): 
                    $item_total = $item['price'] * $item['quantity'];
                    $total_price += $item_total;
                    
                    $whatsapp_message .= "- " . urlencode($item['name']) . " (الكمية: " . $item['quantity'] . ") - الإجمالي: " . $item_total . " ر.س%0A";
                ?>
                    <!-- كرت المنتج المتجاوب داخل السلة -->
                    <div class="cart-item-card">
                        <div class="d-flex align-items-center gap-2 overflow-hidden flex-grow-1">
                            <!-- التعديل هنا: إضافة مجلد uploads/ والتحقق من وجود الصورة -->
                            <img src="<?php echo !empty($item['image']) && $item['image'] != 'default.png' ? 'uploads/' . htmlspecialchars($item['image']) : 'https://via.placeholder.com/65'; ?>" class="item-img" alt="">
                            <div class="text-truncate flex-grow-1">
                                <h6 class="fw-bold mb-1 text-truncate" style="font-size: 13px; color: #0f172a;"><?php echo htmlspecialchars($item['name']); ?></h6>
                                <p class="text-muted mb-0" style="font-size: 11px;"><?php echo $item['price']; ?> ر.س × <?php echo $item['quantity']; ?></p>
                                <p class="mb-0" style="font-size: 12px; color: var(--indrive-green); font-weight: 800;"><?php echo $item_total; ?> ر.س</p>
                            </div>
                        </div>
                        <div>
                            <a href="cart.php?action=remove&id=<?php echo $key; ?>" class="btn btn-outline-danger btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;" title="حذف">
                                <i class="fa-solid fa-trash-can" style="font-size: 11px;"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php 
            $whatsapp_message .= "%0A*المجموع الكلي: " . $total_price . " ر.س*";
            $whatsapp_phone = "966500000000"; // استبدل برقم الواتساب الخاص بك
            $whatsapp_link = "https://wa.me/" . $whatsapp_phone . "?text=" . $whatsapp_message;
            ?>

            <!-- قسم الفاتورة والأزرار -->
            <div class="summary-card mt-3">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <span class="text-muted fw-bold" style="font-size: 12px;">المجموع الكلي:</span>
                    <div class="text-end">
                        <span style="font-size: 16px; font-weight: 800; color: var(--indrive-green) !important;"><?php echo $total_price; ?></span>
                        <span class="text-muted" style="font-size: 10px; font-weight: 700;">ر.س</span>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <a href="<?php echo $whatsapp_link; ?>" target="_blank" class="btn btn-whatsapp py-2.5 d-flex align-items-center justify-content-center gap-2">
                        <i class="fa-brands fa-whatsapp fa-lg"></i> إرسال الطلب عبر الواتساب
                    </a>
                    <a href="cart.php?action=clear" class="btn btn-outline-secondary btn-sm py-1.5 border-0 text-danger fw-bold" style="font-size: 11px;">
                        <i class="fa-solid fa-trash-can me-1"></i> إفراغ السلة بالكامل
                    </a>
                </div>
            </div>

        <?php else: ?>
            <div class="text-center py-5 bg-white rounded-4 border shadow-sm p-4 mt-2">
                <i class="fa-solid fa-basket-shopping fa-3x text-muted mb-3 opacity-50"></i>
                <h6 class="fw-bold mb-1" style="font-size: 14px;">سلة المشتريات فارغة</h6>
                <p class="text-muted small mb-3" style="font-size: 12px;">لم تقم بإضافة أي منتجات للسلة بعد.</p>
                <a href="index.php" class="btn btn-success rounded-pill px-4 btn-sm fw-bold" style="background-color: var(--indrive-green); border: none; font-size: 12px;">تصفح المنتجات الآن</a>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>