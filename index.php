<?php
include 'header.php'; // Đã bao gồm session_start() và kết nối Navbar
require_once 'db.php';

// 1. TRUY VẤN LẤY DANH SÁCH BANNER (Chỉ lấy những banner có trang_thai = 1)
$sql_banner = "SELECT * FROM banners WHERE trang_thai = 1 ORDER BY id DESC";
$banners = $conn->query($sql_banner)->fetchAll(PDO::FETCH_ASSOC);

// 2. TRUY VẤN LẤY DANH SÁCH SẢN PHẨM MỚI NHẤT
// Sắp xếp theo ID giảm dần để sản phẩm mới nhất hiện lên đầu
$sql_sp = "SELECT s.*, d.ten_danh_muc 
           FROM san_pham s 
           LEFT JOIN danh_muc d ON s.danh_muc_id = d.id 
           ORDER BY s.id DESC LIMIT 12"; 
$stmt_sp = $conn->query($sql_sp);
$san_phams = $stmt_sp->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if (count($banners) > 0): ?>
<div class="container-fluid p-0 mb-5">
    <div id="homeBannerCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <?php foreach ($banners as $index => $b): ?>
                <button type="button" data-bs-target="#homeBannerCarousel" data-bs-slide-to="<?php echo $index; ?>" class="<?php echo $index == 0 ? 'active' : ''; ?>"></button>
            <?php endforeach; ?>
        </div>

        <div class="carousel-inner">
            <?php foreach ($banners as $index => $b): ?>
                <div class="carousel-item <?php echo $index == 0 ? 'active' : ''; ?>">
                    <a href="<?php echo htmlspecialchars($b['duong_link']); ?>">
                        <img src="images/banners/<?php echo htmlspecialchars($b['hinh_anh']); ?>" class="d-block w-100" alt="Banner Khuyến Mãi" style="height: 400px; object-fit: cover;">
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#homeBannerCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#homeBannerCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</div>
<?php else: ?>
<div class="container-fluid bg-primary text-white py-5 mb-5 text-center">
    <h1 class="display-4 fw-bold">Chào mừng đến với TechStore</h1>
    <p class="lead">Khám phá những sản phẩm công nghệ đỉnh cao với giá tốt nhất thị trường.</p>
</div>
<?php endif; ?>

<div class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold border-start border-primary border-4 ps-3">Sản Phẩm Mới Nhất</h2>
    </div>

    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php foreach ($san_phams as $sp): ?>
            <div class="col">
                <div class="card h-100 shadow-sm border-0 product-card transition-hover">
                    
                    <a href="product_detail.php?id=<?php echo $sp['id']; ?>" class="text-center overflow-hidden" style="height: 220px; display: block;">
                        <?php $img_src = (!empty($sp['hinh_anh'])) ? "images/".$sp['hinh_anh'] : "https://via.placeholder.com/300x300?text=Chua+co+anh"; ?>
                        <img src="<?php echo $img_src; ?>" alt="<?php echo htmlspecialchars($sp['ten_sp']); ?>" 
                             class="card-img-top w-100 h-100" 
                             style="object-fit: cover; object-position: center; transition: transform 0.3s ease;">
                    </a>

                    <div class="card-body d-flex flex-column">
                        <div class="mb-2">
                            <span class="badge bg-light text-secondary border"><?php echo htmlspecialchars($sp['ten_danh_muc'] ?? 'Chưa phân loại'); ?></span>
                        </div>
                        
                        <h5 class="card-title text-truncate-2" style="font-size: 1.1rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            <a href="product_detail.php?id=<?php echo $sp['id']; ?>" class="text-decoration-none text-dark fw-bold hover-primary">
                                <?php echo htmlspecialchars($sp['ten_sp']); ?>
                            </a>
                        </h5>
                        
                        <div class="mt-auto pt-3">
                            <h5 class="text-danger fw-bold mb-3"><?php echo number_format($sp['gia'], 0, ',', '.'); ?> ₫</h5>
                            
                            <?php if ($sp['so_luong_ton'] > 0): ?>
                                <form action="cart.php" method="POST">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="id_sp" value="<?php echo $sp['id']; ?>">
                                    <input type="hidden" name="so_luong" value="1">
                                    <button type="submit" class="btn btn-outline-primary w-100 fw-bold">
                                        <i class="fa-solid fa-cart-plus"></i> Thêm giỏ hàng
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100 fw-bold" disabled>Hết hàng</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
    /* Hiệu ứng zoom nhẹ ảnh khi trỏ chuột vào */
    .product-card:hover img {
        transform: scale(1.05);
    }
    /* Đổi màu tên sản phẩm khi hover */
    .hover-primary:hover {
        color: #0d6efd !important;
    }
</style>

<?php include 'footer.php'; ?>