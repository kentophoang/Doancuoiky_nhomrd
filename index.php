<?php
include 'header.php'; // Đã có session_start(), $conn, navbar
require_once 'db.php';

// 1. LẤY DANH SÁCH TẤT CẢ DANH MỤC CHO QUICK-NAV
$stmt_cats = $conn->query("SELECT * FROM danh_muc ORDER BY id ASC");
$all_categories = $stmt_cats->fetchAll(PDO::FETCH_ASSOC);

// Map icon phù hợp cho từng danh mục phổ biến
function getCategoryIcon($cat_name) {
    $name = mb_strtolower($cat_name, 'UTF-8');
    if (strpos($name, 'laptop') !== false || strpos($name, 'máy tính') !== false) return 'fa-laptop';
    if (strpos($name, 'điện thoại') !== false || strpos($name, 'smartphone') !== false) return 'fa-mobile-screen-button';
    if (strpos($name, 'tai nghe') !== false || strpos($name, 'âm thanh') !== false) return 'fa-headphones';
    if (strpos($name, 'đồng hồ') !== false || strpos($name, 'smartwatch') !== false) return 'fa-clock';
    if (strpos($name, 'phụ kiện') !== false || strpos($name, 'cáp') !== false || strpos($name, 'sạc') !== false) return 'fa-plug';
    if (strpos($name, 'màn hình') !== false || strpos($name, 'tivi') !== false) return 'fa-tv';
    if (strpos($name, 'bàn phím') !== false || strpos($name, 'chuột') !== false) return 'fa-keyboard';
    return 'fa-microchip';
}

// 2. TRUY VẤN LẤY BANNER HOẠT ĐỘNG
$sql_banner = "SELECT * FROM banners WHERE trang_thai = 1 ORDER BY id DESC";
$banners = $conn->query($sql_banner)->fetchAll(PDO::FETCH_ASSOC);

// 3. XỬ LÝ LỌC & TÌM KIẾM SẢN PHẨM
$is_filtered = false;
$filter_title = "Sản Phẩm Mới Nhất & Nổi Bật";
$filter_desc = "Khám phá các sản phẩm công nghệ thế hệ mới chính hãng 100%";
$current_cat_id = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : 0;
$search_keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_order = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

$where_clauses = [];
$params = [];

if ($current_cat_id > 0) {
    $is_filtered = true;
    $where_clauses[] = "s.danh_muc_id = ?";
    $params[] = $current_cat_id;
    
    // Lấy tên danh mục hiện tại
    $stmt_cur_cat = $conn->prepare("SELECT ten_danh_muc FROM danh_muc WHERE id = ?");
    $stmt_cur_cat->execute([$current_cat_id]);
    $cat_obj = $stmt_cur_cat->fetch(PDO::FETCH_ASSOC);
    if ($cat_obj) {
        $filter_title = "Danh mục: " . htmlspecialchars($cat_obj['ten_danh_muc']);
        $filter_desc = "Tất cả sản phẩm thuộc nhóm " . htmlspecialchars($cat_obj['ten_danh_muc']);
    }
}

if ($search_keyword !== '') {
    $is_filtered = true;
    $where_clauses[] = "s.ten_sp LIKE ?";
    $params[] = "%" . $search_keyword . "%";
    $filter_title = 'Kết quả tìm kiếm cho: "' . htmlspecialchars($search_keyword) . '"';
    $filter_desc = "Các sản phẩm phù hợp với từ khóa bạn tìm";
}

// Xây dựng câu ORDER BY
$order_by = "ORDER BY s.id DESC";
if ($sort_order == 'price_asc') {
    $order_by = "ORDER BY s.gia ASC";
} elseif ($sort_order == 'price_desc') {
    $order_by = "ORDER BY s.gia DESC";
} elseif ($sort_order == 'name_asc') {
    $order_by = "ORDER BY s.ten_sp ASC";
}

$sql_sp = "SELECT s.*, d.ten_danh_muc 
           FROM san_pham s 
           LEFT JOIN danh_muc d ON s.danh_muc_id = d.id";

if (!empty($where_clauses)) {
    $sql_sp .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql_sp .= " " . $order_by;

if (!$is_filtered) {
    $sql_sp .= " LIMIT 16";
}

$stmt_sp = $conn->prepare($sql_sp);
$stmt_sp->execute($params);
$san_phams = $stmt_sp->fetchAll(PDO::FETCH_ASSOC);

$placeholder_svg = "data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22300%22%20height%3D%22300%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%20300%20300%22%3E%3Crect%20fill%3D%22%23f8fafc%22%20width%3D%22300%22%20height%3D%22300%22%2F%3E%3Ctext%20fill%3D%22%2394a3b8%22%20font-family%3D%22sans-serif%22%20font-size%3D%2218%22%20dy%3D%226%22%20font-weight%3D%22bold%22%20x%3D%2250%25%22%20y%3D%2250%25%22%20text-anchor%3D%22middle%22%3ETechStore%20Product%3C%2Ftext%3E%3C%2Fsvg%3E";
?>

<div class="container py-3">

    <?php if (!$is_filtered): ?>
        <!-- ==========================================
             HERO BANNER & CAROUSEL SECTION
             ========================================== -->
        <?php if (!empty($banners)): ?>
            <div class="hero-banner-wrapper">
                <div id="homeBannerCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4500">
                    <div class="carousel-indicators">
                        <?php foreach ($banners as $index => $b): ?>
                            <button type="button" data-bs-target="#homeBannerCarousel" data-bs-slide-to="<?php echo $index; ?>" class="<?php echo $index == 0 ? 'active' : ''; ?>" aria-label="Slide <?php echo $index + 1; ?>"></button>
                        <?php endforeach; ?>
                    </div>

                    <div class="carousel-inner">
                        <?php foreach ($banners as $index => $b): ?>
                            <div class="carousel-item <?php echo $index == 0 ? 'active' : ''; ?>">
                                <a href="<?php echo !empty($b['duong_link']) ? htmlspecialchars($b['duong_link']) : '#'; ?>">
                                    <?php 
                                        $banner_img = 'images/banners/' . $b['hinh_anh'];
                                        if (!file_exists($banner_img)) { $banner_img = $placeholder_svg; }
                                    ?>
                                    <img src="<?php echo htmlspecialchars($banner_img); ?>" class="d-block w-100 hero-carousel-img" alt="Banner Khuyến Mãi">
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button class="carousel-control-prev" type="button" data-bs-target="#homeBannerCarousel" data-bs-slide="prev">
                        <i class="fa-solid fa-chevron-left"></i>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#homeBannerCarousel" data-bs-slide="next">
                        <i class="fa-solid fa-chevron-right"></i>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
        <?php else: ?>
            <!-- Fallback Hero Banner if no banner images exist -->
            <div class="hero-fallback-banner my-4">
                <div class="row align-items-center position-relative" style="z-index: 2;">
                    <div class="col-lg-7">
                        <span class="hero-tag"><i class="fa-solid fa-bolt-lightning text-warning"></i> Công Nghệ Đỉnh Cao 2026</span>
                        <h1 class="display-4 fw-extrabold text-white mb-3" style="letter-spacing: -1px;">Khám Phá Thiết Bị Thông Minh Cùng TechStore</h1>
                        <p class="lead text-light mb-4" style="max-width: 540px; opacity: 0.9;">
                            Cung cấp điện thoại, laptop, linh kiện chính hãng cùng các ưu đãi giảm giá độc quyền và chính sách bảo hành 1 đổi 1 tiện lợi.
                        </p>
                        <div class="d-flex flex-wrap gap-3">
                            <a href="#product-grid-section" class="btn btn-primary btn-lg rounded-pill px-4 py-2 fw-bold shadow-lg">
                                <i class="fa-solid fa-bag-shopping me-2"></i> Mua Ngay Hôm Nay
                            </a>
                            <a href="compare.php" class="btn btn-outline-light btn-lg rounded-pill px-4 py-2 fw-bold">
                                <i class="fa-solid fa-code-compare me-2"></i> So Sánh Sản Phẩm
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-5 d-none d-lg-block text-center">
                        <div class="p-4" style="background: rgba(255,255,255,0.06); border-radius: 24px; backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.12);">
                            <i class="fa-solid fa-microchip display-1 text-primary mb-3"></i>
                            <h4 class="text-white fw-bold">100% Chính Hãng</h4>
                            <p class="text-light small mb-0">Bảo hành 24 tháng toàn quốc | Đổi mới trong 30 ngày</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ==========================================
             VALUE PROPOSITION / TRUST BADGES BAR
             ========================================== -->
        <div class="row g-3 mb-5">
            <div class="col-6 col-lg-3">
                <div class="feature-box">
                    <div class="feature-icon-wrapper feature-icon-blue">
                        <i class="fa-solid fa-truck-fast"></i>
                    </div>
                    <div>
                        <div class="feature-title">Giao Hàng Siêu Tốc</div>
                        <div class="feature-desc">Nhận hàng trong 2h nội thành</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="feature-box">
                    <div class="feature-icon-wrapper feature-icon-green">
                        <i class="fa-solid fa-shield-check"></i>
                    </div>
                    <div>
                        <div class="feature-title">Bảo Hành Chính Hãng</div>
                        <div class="feature-desc">1 đổi 1 trong 30 ngày đầu</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="feature-box">
                    <div class="feature-icon-wrapper feature-icon-purple">
                        <i class="fa-solid fa-credit-card"></i>
                    </div>
                    <div>
                        <div class="feature-title">Trả Góp 0% Lãi Suất</div>
                        <div class="feature-desc">Thủ tục online chỉ 5 phút</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="feature-box">
                    <div class="feature-icon-wrapper feature-icon-orange">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    <div>
                        <div class="feature-title">Hỗ Trợ Tận Tâm 24/7</div>
                        <div class="feature-desc">Tư vấn kỹ thuật chuyên sâu</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==========================================
             QUICK CATEGORIES NAVIGATION
             ========================================== -->
        <?php if (!empty($all_categories)): ?>
            <div class="mb-5">
                <div class="section-header-modern">
                    <h2 class="section-title">
                        <i class="fa-solid fa-shapes text-primary"></i> Danh Mục Nổi Bật
                    </h2>
                    <span class="section-badge">Khám phá</span>
                </div>
                <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-3">
                    <?php foreach (array_slice($all_categories, 0, 6) as $cat_item): ?>
                        <div class="col">
                            <a href="index.php?cat_id=<?php echo $cat_item['id']; ?>" class="category-card">
                                <div class="category-icon">
                                    <i class="fa-solid <?php echo getCategoryIcon($cat_item['ten_danh_muc']); ?>"></i>
                                </div>
                                <div class="category-name"><?php echo htmlspecialchars($cat_item['ten_danh_muc']); ?></div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- ==========================================
             FILTER / SEARCH BREADCRUMB HEADER
             ========================================== -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><i class="fa-solid fa-house me-1"></i> Trang chủ</a></li>
                <li class="breadcrumb-item active" aria-current="page">Lọc sản phẩm</li>
            </ol>
        </nav>
    <?php endif; ?>

    <!-- ==========================================
         PRODUCT GRID SECTION
         ========================================== -->
    <div id="product-grid-section" class="mb-5">
        <div class="section-header-modern flex-wrap gap-2">
            <div>
                <h2 class="section-title">
                    <i class="fa-solid fa-microchip text-primary"></i> <?php echo $filter_title; ?>
                </h2>
                <p class="text-muted small mb-0 mt-1"><?php echo $filter_desc; ?> (<strong><?php echo count($san_phams); ?></strong> sản phẩm)</p>
            </div>

            <!-- Sorting & Filter Controls -->
            <div class="d-flex align-items-center gap-2">
                <label for="sortSelect" class="small text-muted fw-bold d-none d-sm-inline">Sắp xếp:</label>
                <select id="sortSelect" class="form-select form-select-sm border-subtle shadow-none rounded-pill px-3" style="width: auto;" onchange="location = this.value;">
                    <?php 
                        $base_url = "index.php?";
                        if ($current_cat_id > 0) $base_url .= "cat_id=" . $current_cat_id . "&";
                        if ($search_keyword !== '') $base_url .= "search=" . urlencode($search_keyword) . "&";
                    ?>
                    <option value="<?php echo $base_url; ?>sort=newest" <?php echo $sort_order == 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                    <option value="<?php echo $base_url; ?>sort=price_asc" <?php echo $sort_order == 'price_asc' ? 'selected' : ''; ?>>Giá tăng dần</option>
                    <option value="<?php echo $base_url; ?>sort=price_desc" <?php echo $sort_order == 'price_desc' ? 'selected' : ''; ?>>Giá giảm dần</option>
                    <option value="<?php echo $base_url; ?>sort=name_asc" <?php echo $sort_order == 'name_asc' ? 'selected' : ''; ?>>Tên A - Z</option>
                </select>

                <?php if ($is_filtered): ?>
                    <a href="index.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="fa-solid fa-rotate-left me-1"></i> Xóa lọc
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($san_phams)): ?>
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3 g-lg-4">
                <?php foreach ($san_phams as $sp): ?>
                    <?php 
                        $img_src = (!empty($sp['hinh_anh']) && file_exists('images/' . $sp['hinh_anh'])) ? 'images/' . htmlspecialchars($sp['hinh_anh']) : $placeholder_svg;
                        $in_stock = ($sp['so_luong_ton'] > 0);
                    ?>
                    <div class="col">
                        <div class="product-card">
                            <!-- Floating Badges -->
                            <div class="product-badge-group">
                                <span class="product-badge badge-new">Mới</span>
                                <?php if ($sp['gia'] > 20000000): ?>
                                    <span class="product-badge badge-hot">Hot</span>
                                <?php endif; ?>
                            </div>

                            <!-- Quick Action Overlay -->
                            <div class="product-action-overlay">
                                <a href="compare.php?action=add&id=<?php echo $sp['id']; ?>" class="product-action-btn" title="So sánh sản phẩm">
                                    <i class="fa-solid fa-code-compare"></i>
                                </a>
                                <a href="product_detail.php?id=<?php echo $sp['id']; ?>" class="product-action-btn" title="Xem chi tiết">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </div>

                            <!-- Thumbnail Container -->
                            <a href="product_detail.php?id=<?php echo $sp['id']; ?>" class="product-thumb-wrapper">
                                <img src="<?php echo $img_src; ?>" alt="<?php echo htmlspecialchars($sp['ten_sp']); ?>" class="product-thumb-img" loading="lazy">
                            </a>

                            <!-- Card Body -->
                            <div class="product-card-body">
                                <div class="product-category-tag"><?php echo htmlspecialchars($sp['ten_danh_muc'] ?? 'Thiết bị công nghệ'); ?></div>
                                
                                <h3 class="product-title">
                                    <a href="product_detail.php?id=<?php echo $sp['id']; ?>">
                                        <?php echo htmlspecialchars($sp['ten_sp']); ?>
                                    </a>
                                </h3>

                                <div class="product-rating">
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star-half-stroke"></i>
                                    <span class="product-rating-count">(4.9)</span>
                                </div>

                                <div class="product-price-box">
                                    <div class="product-current-price">
                                        <?php echo number_format($sp['gia'], 0, ',', '.'); ?> ₫
                                    </div>
                                    <div class="product-stock-tag">
                                        <?php if ($in_stock): ?>
                                            <i class="fa-solid fa-circle-check"></i> Còn hàng
                                        <?php else: ?>
                                            <span class="text-danger"><i class="fa-solid fa-circle-xmark"></i> Hết hàng</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if ($in_stock): ?>
                                    <form action="cart.php" method="POST" class="mt-2">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="id_sp" value="<?php echo (int)$sp['id']; ?>">
                                        <input type="hidden" name="so_luong" value="1">
                                        <button type="submit" class="btn-add-cart-card">
                                            <i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-secondary w-100 rounded-3 mt-2 fw-semibold py-2" disabled style="font-size: 0.875rem;">
                                        Tạm hết hàng
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Clean Empty State -->
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center my-4" style="background: white;">
                <div class="mb-3">
                    <i class="fa-solid fa-box-open text-muted" style="font-size: 4rem;"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">Không tìm thấy sản phẩm nào</h4>
                <p class="text-muted mb-4">Rất tiếc, hiện tại không có sản phẩm nào phù hợp với yêu cầu tìm kiếm hoặc danh mục của bạn.</p>
                <div>
                    <a href="index.php" class="btn btn-primary rounded-pill px-4 py-2 fw-bold">
                        <i class="fa-solid fa-arrow-left me-2"></i> Quay lại trang chủ
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!$is_filtered): ?>
        <!-- ==========================================
             PROMOTIONAL PROMO BANNERS GRID
             ========================================== -->
        <div class="row g-4 mb-5">
            <div class="col-12 col-md-6">
                <div class="promo-banner-card promo-banner-1">
                    <div class="promo-subtitle">Phụ Kiện Công Nghệ</div>
                    <h3 class="promo-title">Nâng Cấp Không Gian Làm Việc Cực Chất</h3>
                    <a href="index.php" class="btn-promo">
                        Khám Phá Ngay <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="promo-banner-card promo-banner-2">
                    <div class="promo-subtitle">Ưu Đãi Đặc Biệt</div>
                    <h3 class="promo-title">Laptop & Linh Kiện Chính Hãng Giảm Đến 30%</h3>
                    <a href="index.php" class="btn-promo">
                        Xem Ưu Đãi <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- ==========================================
             CUSTOMER TESTIMONIALS / TRUST SECTION
             ========================================== -->
        <div class="mb-5">
            <div class="section-header-modern">
                <h2 class="section-title">
                    <i class="fa-solid fa-comments text-primary"></i> Khách Hàng Nói Về TechStore
                </h2>
                <span class="section-badge">Đánh giá</span>
            </div>
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4 rounded-4" style="background: white;">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-3" style="width: 44px; height: 44px;">
                                NV
                            </div>
                            <div>
                                <div class="fw-bold text-dark">Nguyễn Văn An</div>
                                <div class="small text-muted"><i class="fa-solid fa-circle-check text-success me-1"></i> Đã mua Laptop Asus ROG</div>
                            </div>
                        </div>
                        <div class="text-warning mb-2 small">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                        </div>
                        <p class="text-muted small mb-0">"Giao hàng cực nhanh trong 2 giờ, sản phẩm nguyên seal 100%, nhân viên hỗ trợ cài đặt nhiệt tình chu đáo!"</p>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4 rounded-4" style="background: white;">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-accent text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-3" style="width: 44px; height: 44px; background: #8b5cf6;">
                                TH
                            </div>
                            <div>
                                <div class="fw-bold text-dark">Trần Thu Hà</div>
                                <div class="small text-muted"><i class="fa-solid fa-circle-check text-success me-1"></i> Đã mua iPhone 15 Pro Max</div>
                            </div>
                        </div>
                        <div class="text-warning mb-2 small">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                        </div>
                        <p class="text-muted small mb-0">"Giá tốt hơn nhiều so với các siêu thị lớn, chính sách bảo hành rõ ràng, trả góp qua thẻ tín dụng rất tiện lợi."</p>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4 rounded-4" style="background: white;">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-3" style="width: 44px; height: 44px;">
                                LQ
                            </div>
                            <div>
                                <div class="fw-bold text-dark">Lê Quốc Huy</div>
                                <div class="small text-muted"><i class="fa-solid fa-circle-check text-success me-1"></i> Đã mua Bàn phím cơ & Chuột</div>
                            </div>
                        </div>
                        <div class="text-warning mb-2 small">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                        </div>
                        <p class="text-muted small mb-0">"Đóng gói 3 lớp chống sốc rất kỹ càng. Hàng chính hãng dùng rất mượt, chắc chắn sẽ tiếp tục ủng hộ shop."</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==========================================
             NEWSLETTER SUBSCRIPTION
             ========================================== -->
        <div class="newsletter-card">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold mb-2">Ưu đãi thành viên</span>
                    <h3 class="display-6 fw-bold text-white mb-2">Đăng Ký Nhận Voucher 200.000₫</h3>
                    <p class="text-light mb-0" style="opacity: 0.9;">Nhận thông báo về các đợt flash sale công nghệ và mã giảm giá sớm nhất.</p>
                </div>
                <div class="col-lg-6">
                    <div class="newsletter-input-group">
                        <input type="email" placeholder="Nhập địa chỉ email của bạn..." aria-label="Email">
                        <button type="button">Đăng ký</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php include 'footer.php'; ?>