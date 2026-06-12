<?php
// Khởi động session nếu chưa có
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'db.php';

// Tính tổng số lượng hàng trong giỏ
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) { $cart_count += $qty; }
}

// Lấy danh mục hiển thị trên Menu
$stmt_menu = $conn->query("SELECT * FROM danh_muc WHERE parent_id IS NULL");
$menu_categories = $stmt_menu->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechStore - Cửa Hàng Điện Tử</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; display: flex; flex-direction: column; min-height: 100vh; }
        .main-content { flex: 1; padding-top: 30px; padding-bottom: 30px; }
        @media (max-width: 767px) { body { padding-bottom: 120px; } }
        
        .navbar-brand { font-weight: bold; font-size: 1.5rem; }
        .search-box-container { position: relative; }
        .search-suggestions-box { position: absolute; top: 100%; left: 0; right: 0; background: white; border-radius: 8px; max-height: 320px; overflow-y: auto; z-index: 9999; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .search-result-item { padding: 10px; cursor: pointer; }
        .search-result-item:hover { background-color: #f1f3f5; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand text-primary fw-bold" href="index.php"><i class="fa-solid fa-microchip"></i> TechStore</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="fa-solid fa-house"></i> Trang chủ</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"><i class="fa-solid fa-list"></i> Danh mục</a>
                        <ul class="dropdown-menu">
                            <?php foreach ($menu_categories as $cat): ?>
                                <li><a class="dropdown-item" href="index.php?cat_id=<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['ten_danh_muc']); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                </ul>

                <div class="search-box-container me-3 col-10 col-lg-4 px-0">
                    <form class="d-flex" action="index.php" method="GET" autocomplete="off">
                        <input class="form-control me-2" type="search" name="search" id="smartSearchInput" placeholder="Tìm sản phẩm..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    </form>
                    <div id="searchSuggestions" class="search-suggestions-box d-none"></div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <a href="cart.php" class="btn btn-outline-light position-relative">
                        <i class="fa-solid fa-cart-shopping"></i> Giỏ hàng
                        <?php if($cart_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>

                    <?php if(isset($_SESSION['khach_hang_id'])): 
                        $mang_ten = explode(' ', $_SESSION['khach_hang_ten']);
                        $ten_hien_thi = $mang_ten[count($mang_ten) - 1];
                    ?>
                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle fw-bold" type="button" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($ten_hien_thi); ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li><a class="dropdown-item" href="order_history.php"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Đơn mua của tôi</a></li>
                                <li><a class="dropdown-item" href="profile.php"><i class="fa-solid fa-user-pen text-success me-2"></i> Cập nhật hồ sơ</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i> Đăng xuất</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary fw-bold"><i class="fa-solid fa-right-to-bracket"></i> Đăng nhập</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="main-content container">

    <script>
        // Xử lý tìm kiếm thông minh
        document.getElementById('smartSearchInput').addEventListener('input', function() {
            let keyword = this.value.trim();
            let suggestionsBox = document.getElementById('searchSuggestions');
            if (keyword.length < 2) { suggestionsBox.classList.add('d-none'); return; }
            fetch('ajax_search.php?keyword=' + encodeURIComponent(keyword))
                .then(response => response.text())
                .then(htmlData => { 
                    suggestionsBox.innerHTML = htmlData; 
                    suggestionsBox.classList.remove('d-none'); 
                });
        });
        // Ẩn bảng gợi ý khi click ra ngoài
        document.addEventListener('click', function(e) {
            if (!document.querySelector('.search-box-container').contains(e.target)) {
                document.getElementById('searchSuggestions').classList.add('d-none');
            }
        });
    </script>