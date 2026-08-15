<?php
// Khởi động session nếu chưa có
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
require_once 'db.php';

// Tính tổng số lượng hàng trong giỏ
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) { 
        $cart_count += (int)$qty; 
    }
}

// Tính số lượng sản phẩm đang so sánh
$compare_count = 0;
if (isset($_SESSION['compare']) && is_array($_SESSION['compare'])) {
    $compare_count = count($_SESSION['compare']);
}

// Lấy danh mục hiển thị trên Menu
$stmt_menu = $conn->query("SELECT * FROM danh_muc WHERE parent_id IS NULL ORDER BY id ASC");
$menu_categories = $stmt_menu->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechStore - Thế Giới Công Nghệ & Thiết Bị Điện Tử Chính Hãng</title>
    <meta name="description" content="TechStore cung cấp các sản phẩm công nghệ, điện thoại, laptop, linh kiện máy tính và phụ kiện điện tử chính hãng với giá tốt nhất thị trường.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&family=Outfit:wght@500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom TechStore Master Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Top Announcement Bar -->
    <div class="top-bar">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <span class="top-bar-pulse"></span>
                <span>Ưu đãi đặc biệt: <strong>Freeship</strong> đơn hàng từ 500k toàn quốc</span>
            </div>
            <div class="d-none d-md-flex align-items-center gap-4">
                <span><i class="fa-solid fa-phone-volume me-1 text-primary"></i> Hotline: <strong>1900 8888</strong></span>
                <span><i class="fa-solid fa-shield-halved me-1 text-success"></i> Cam kết 100% chính hãng</span>
                <a href="admin_login.php" class="text-light text-decoration-none opacity-75 hover-opacity-100"><i class="fa-solid fa-user-shield me-1"></i> Kênh Quản Trị</a>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <!-- Brand Logo -->
            <a class="brand-logo" href="index.php">
                <div class="brand-icon-wrapper">
                    <i class="fa-solid fa-bolt-lightning"></i>
                </div>
                <span class="brand-text">Tech<span class="text-primary">Store</span></span>
            </a>

            <!-- Mobile Toggler -->
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <i class="fa-solid fa-bars-staggered fs-4 text-dark"></i>
            </button>

            <!-- Navbar Content -->
            <div class="collapse navbar-collapse" id="navbarMain">
                <!-- Navigation Links -->
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                    <li class="nav-item">
                        <a class="nav-link-custom <?php echo (!isset($_GET['cat_id']) && !isset($_GET['search']) && basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>" href="index.php">
                            <i class="fa-solid fa-house-chimney"></i> Trang chủ
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link-custom dropdown-toggle <?php echo isset($_GET['cat_id']) ? 'active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-layer-group"></i> Danh mục
                        </a>
                        <ul class="dropdown-menu shadow-lg border-0 rounded-3 py-2 animate slideIn">
                            <?php foreach ($menu_categories as $cat): ?>
                                <li>
                                    <a class="dropdown-item py-2 px-3 fw-medium d-flex align-items-center justify-content-between" href="index.php?cat_id=<?php echo $cat['id']; ?>">
                                        <span><i class="fa-solid fa-chevron-right text-primary me-2 small"></i> <?php echo htmlspecialchars($cat['ten_danh_muc']); ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-custom <?php echo (basename($_SERVER['PHP_SELF']) == 'compare.php') ? 'active' : ''; ?>" href="compare.php">
                            <i class="fa-solid fa-code-compare"></i> So sánh
                            <?php if ($compare_count > 0): ?>
                                <span class="badge bg-primary rounded-pill ms-1"><?php echo $compare_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>

                <!-- Smart Search Bar -->
                <div class="search-wrapper mx-lg-3 my-2 my-lg-0 col-12 col-lg-5">
                    <form action="index.php" method="GET" autocomplete="off">
                        <div class="search-input-group">
                            <button type="submit" class="search-icon-btn">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </button>
                            <input type="search" name="search" id="smartSearchInput" placeholder="Tìm kiếm sản phẩm, laptop, phụ kiện..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                                <a href="index.php" class="text-muted text-decoration-none me-1"><i class="fa-solid fa-circle-xmark"></i></a>
                            <?php endif; ?>
                        </div>
                    </form>
                    <div id="searchSuggestions" class="search-suggestions-box d-none"></div>
                </div>

                <!-- Right Action Cluster -->
                <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
                    <!-- Cart Button -->
                    <a href="cart.php" class="header-action-btn btn-cart-custom position-relative">
                        <i class="fa-solid fa-cart-shopping fs-5"></i>
                        <span class="d-none d-xl-inline">Giỏ hàng</span>
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-count-badge"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>

                    <!-- User Account / Login -->
                    <?php if (isset($_SESSION['khach_hang_id'])): 
                        $mang_ten = explode(' ', trim($_SESSION['khach_hang_ten']));
                        $ten_hien_thi = end($mang_ten);
                    ?>
                        <div class="dropdown">
                            <button class="header-action-btn btn-user-custom dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-circle-user fs-5"></i>
                                <span><?php echo htmlspecialchars($ten_hien_thi); ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 py-2 mt-2">
                                <li class="px-3 py-2 border-bottom">
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($_SESSION['khach_hang_ten']); ?></div>
                                    <div class="small text-muted"><?php echo htmlspecialchars($_SESSION['khach_hang_email'] ?? 'Khách hàng thân thiết'); ?></div>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2 px-3 fw-medium" href="order_history.php">
                                        <i class="fa-solid fa-bag-shopping text-primary me-2"></i> Đơn mua của tôi
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2 px-3 fw-medium" href="profile.php">
                                        <i class="fa-solid fa-id-card text-success me-2"></i> Cập nhật hồ sơ
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item py-2 px-3 fw-medium text-danger" href="logout.php">
                                        <i class="fa-solid fa-right-from-bracket me-2"></i> Đăng xuất
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="header-action-btn btn-user-custom">
                            <i class="fa-solid fa-arrow-right-to-bracket"></i>
                            <span>Đăng nhập</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Container Content -->
    <main class="main-content">

    <script>
        // Live Smart Search Auto-suggest handler
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('smartSearchInput');
            const suggestionsBox = document.getElementById('searchSuggestions');
            let searchTimeout = null;

            if (searchInput && suggestionsBox) {
                searchInput.addEventListener('input', function() {
                    const keyword = this.value.trim();
                    clearTimeout(searchTimeout);

                    if (keyword.length < 2) {
                        suggestionsBox.classList.add('d-none');
                        suggestionsBox.innerHTML = '';
                        return;
                    }

                    searchTimeout = setTimeout(() => {
                        fetch('ajax_search.php?keyword=' + encodeURIComponent(keyword))
                            .then(response => response.text())
                            .then(htmlData => {
                                suggestionsBox.innerHTML = htmlData;
                                suggestionsBox.classList.remove('d-none');
                            })
                            .catch(() => {
                                suggestionsBox.classList.add('d-none');
                            });
                    }, 250);
                });

                // Close suggestions on outside click
                document.addEventListener('click', function(e) {
                    if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                        suggestionsBox.classList.add('d-none');
                    }
                });
            }
        });
    </script>