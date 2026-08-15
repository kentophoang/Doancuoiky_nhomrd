<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'header.php';
require_once 'db.php';

// 1. Kiểm tra ID sản phẩm
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Sản phẩm không tồn tại!'); window.location.href='index.php';</script>";
    exit();
}
$id_sp = (int)$_GET['id'];

// --- XỬ LÝ GỬI ĐÁNH GIÁ ---
$review_msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_danh_gia'])) {
    $ten_nguoi = trim($_POST['ten_nguoi_danh_gia']);
    $so_sao = (int)$_POST['so_sao'];
    $noi_dung = trim($_POST['noi_dung']);

    if (!empty($ten_nguoi) && !empty($noi_dung)) {
        try {
            $stmt_dg = $conn->prepare("INSERT INTO danh_gia (san_pham_id, ten_nguoi_danh_gia, so_sao, noi_dung) VALUES (?, ?, ?, ?)");
            $stmt_dg->execute([$id_sp, $ten_nguoi, $so_sao, $noi_dung]);
            $review_msg = "<div class='alert alert-success alert-dismissible fade show'><i class='fa-solid fa-circle-check me-2'></i>Cảm ơn bạn đã gửi đánh giá sản phẩm!<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
        } catch (PDOException $e) {
            $review_msg = "<div class='alert alert-danger alert-dismissible fade show'><i class='fa-solid fa-circle-exclamation me-2'></i>Lỗi: " . htmlspecialchars($e->getMessage()) . "<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
        }
    }
}

// 2. LẤY DỮ LIỆU SẢN PHẨM
$stmt = $conn->prepare("SELECT s.*, d.ten_danh_muc FROM san_pham s LEFT JOIN danh_muc d ON s.danh_muc_id = d.id WHERE s.id = ?");
$stmt->execute([$id_sp]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "<div class='container py-5 text-center my-5'>
            <i class='fa-solid fa-box-open text-muted fa-4x mb-3'></i>
            <h3 class='fw-bold'>Sản phẩm không tồn tại hoặc đã ngừng kinh doanh.</h3>
            <a href='index.php' class='btn btn-primary rounded-pill px-4 mt-3'>Quay lại trang chủ</a>
          </div>";
    include 'footer.php'; 
    exit();
}

$placeholder_svg = "data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22400%22%20height%3D%22400%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%20400%20400%22%3E%3Crect%20fill%3D%22%23f8fafc%22%20width%3D%22400%22%20height%3D%22400%22%2F%3E%3Ctext%20fill%3D%22%2394a3b8%22%20font-family%3D%22sans-serif%22%20font-size%3D%2220%22%20dy%3D%226%22%20font-weight%3D%22bold%22%20x%3D%2250%25%22%20y%3D%2250%25%22%20text-anchor%3D%22middle%22%3ETechStore%20Product%3C%2Ftext%3E%3C%2Fsvg%3E";
$img_src = (!empty($product['hinh_anh']) && file_exists('images/' . $product['hinh_anh'])) ? 'images/' . htmlspecialchars($product['hinh_anh']) : $placeholder_svg;

// Đọc thông số kỹ thuật
$thong_so_arr = [];
if (!empty($product['thong_so_ky_thuat'])) {
    $thong_so_arr = json_decode($product['thong_so_ky_thuat'], true);
} elseif (!empty($product['thong_so'])) {
    $thong_so_arr = json_decode($product['thong_so'], true);
}
if (!is_array($thong_so_arr)) { $thong_so_arr = []; }

// 3. LẤY ĐÁNH GIÁ VÀ TÍNH SAO TRUNG BÌNH
$stmt_dg = $conn->prepare("SELECT * FROM danh_gia WHERE san_pham_id = ? ORDER BY id DESC");
$stmt_dg->execute([$id_sp]);
$danh_gias = $stmt_dg->fetchAll(PDO::FETCH_ASSOC);

$so_luong_dg = count($danh_gias);
$tong_sao = 0;
foreach ($danh_gias as $dg) { $tong_sao += (int)$dg['so_sao']; }
$sao_tb = ($so_luong_dg > 0) ? round($tong_sao / $so_luong_dg, 1) : 5.0;

// 4. LẤY SẢN PHẨM LIÊN QUAN CÙNG DANH MỤC
$stmt_rel = $conn->prepare("SELECT * FROM san_pham WHERE danh_muc_id = ? AND id != ? ORDER BY id DESC LIMIT 4");
$stmt_rel->execute([$product['danh_muc_id'], $id_sp]);
$related_products = $stmt_rel->fetchAll(PDO::FETCH_ASSOC);

$in_stock = ($product['so_luong_ton'] > 0);
?>

<div class="container py-4">
    <!-- Breadcrumb Navigation -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php"><i class="fa-solid fa-house me-1"></i> Trang chủ</a></li>
            <?php if (!empty($product['ten_danh_muc'])): ?>
                <li class="breadcrumb-item"><a href="index.php?cat_id=<?php echo (int)$product['danh_muc_id']; ?>"><?php echo htmlspecialchars($product['ten_danh_muc']); ?></a></li>
            <?php endif; ?>
            <li class="breadcrumb-item active text-truncate" aria-current="page" style="max-width: 300px;"><?php echo htmlspecialchars($product['ten_sp']); ?></li>
        </ol>
    </nav>

    <?php if (!empty($review_msg)) echo $review_msg; ?>

    <!-- Product Showcase Card -->
    <div class="card border-0 shadow-sm rounded-4 p-3 p-lg-4 mb-5" style="background: white;">
        <div class="row g-4">
            <!-- Left: Product Image Gallery -->
            <div class="col-lg-5">
                <div class="position-relative rounded-4 overflow-hidden border border-subtle d-flex align-items-center justify-content-center p-4" style="background: #f8fafc; min-height: 380px;">
                    <span class="badge bg-success position-absolute top-0 start-0 m-3 px-3 py-2 rounded-pill"><i class="fa-solid fa-shield-check me-1"></i> Chính Hãng 100%</span>
                    <img src="<?php echo $img_src; ?>" alt="<?php echo htmlspecialchars($product['ten_sp']); ?>" class="img-fluid" style="max-height: 320px; object-fit: contain; transition: transform 0.3s ease;" id="mainProductImg">
                </div>
                
                <div class="d-flex gap-2 justify-content-center mt-3">
                    <div class="p-1 border border-primary rounded-3" style="width: 64px; height: 64px; cursor: pointer; background: #f8fafc;">
                        <img src="<?php echo $img_src; ?>" class="w-100 h-100 object-fit-contain" alt="Thumbnail">
                    </div>
                </div>
            </div>

            <!-- Right: Product Info & Actions -->
            <div class="col-lg-7">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-light text-primary border border-primary px-3 py-1 rounded-pill fw-semibold">
                        <?php echo htmlspecialchars($product['ten_danh_muc'] ?? 'Công nghệ'); ?>
                    </span>
                    <?php if ($in_stock): ?>
                        <span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill fw-semibold">
                            <i class="fa-solid fa-circle-check me-1"></i> Còn hàng (<?php echo (int)$product['so_luong_ton']; ?>)
                        </span>
                    <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger px-3 py-1 rounded-pill fw-semibold">
                            <i class="fa-solid fa-circle-xmark me-1"></i> Tạm hết hàng
                        </span>
                    <?php endif; ?>
                </div>

                <h1 class="fw-bold text-dark mb-3" style="font-size: 1.75rem; line-height: 1.3;">
                    <?php echo htmlspecialchars($product['ten_sp']); ?>
                </h1>

                <!-- Rating Snippet -->
                <div class="d-flex align-items-center gap-2 mb-3 pb-3 border-bottom">
                    <div class="text-warning">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php if ($i <= floor($sao_tb)): ?>
                                <i class="fa-solid fa-star"></i>
                            <?php elseif ($i - 0.5 <= $sao_tb): ?>
                                <i class="fa-solid fa-star-half-stroke"></i>
                            <?php else: ?>
                                <i class="fa-regular fa-star text-muted"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <span class="fw-bold text-dark"><?php echo $sao_tb; ?></span>
                    <span class="text-muted small">| <?php echo $so_luong_dg; ?> đánh giá</span>
                    <span class="text-muted small">| <i class="fa-solid fa-truck-fast text-primary"></i> Freeship toàn quốc</span>
                </div>

                <!-- Price Box -->
                <div class="p-3 rounded-3 mb-4 d-flex align-items-baseline gap-3" style="background: #f8fafc; border: 1px solid var(--border-subtle);">
                    <div class="text-danger fw-extrabold display-6 mb-0 font-heading">
                        <?php echo number_format($product['gia'], 0, ',', '.'); ?> ₫
                    </div>
                    <span class="badge bg-danger-subtle text-danger fw-bold px-2 py-1 rounded">Tiết kiệm 15%</span>
                </div>

                <!-- Special Offers Card -->
                <div class="card border-0 rounded-3 p-3 mb-4" style="background: #eff6ff; border: 1px dashed #93c5fd !important;">
                    <div class="fw-bold text-primary mb-2 small"><i class="fa-solid fa-gift me-2"></i> ƯU ĐÃI ĐẶC QUYỀN TẠI TECHSTORE</div>
                    <ul class="list-unstyled small text-muted mb-0 d-flex flex-column gap-1">
                        <li><i class="fa-solid fa-check text-success me-2"></i> Tặng kèm gói bảo hành vàng 24 tháng chính hãng.</li>
                        <li><i class="fa-solid fa-check text-success me-2"></i> Giảm thêm 5% khi thanh toán qua thẻ ngân hàng hoặc VNPAY.</li>
                        <li><i class="fa-solid fa-check text-success me-2"></i> Miễn phí giao hàng hỏa tốc trong 2 giờ nội thành.</li>
                    </ul>
                </div>

                <!-- Add to Cart Form -->
                <?php if ($in_stock): ?>
                    <form action="cart.php" method="POST">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="id_sp" value="<?php echo (int)$product['id']; ?>">
                        
                        <div class="row g-2 align-items-center mb-3">
                            <div class="col-auto">
                                <label class="fw-bold small text-muted">Số lượng:</label>
                            </div>
                            <div class="col-auto">
                                <div class="input-group" style="width: 120px;">
                                    <button class="btn btn-outline-secondary btn-sm" type="button" onclick="let q=document.getElementById('productQty'); if(q.value>1) q.value--;">-</button>
                                    <input type="number" name="so_luong" id="productQty" value="1" min="1" max="<?php echo (int)$product['so_luong_ton']; ?>" class="form-control form-control-sm text-center fw-bold">
                                    <button class="btn btn-outline-secondary btn-sm" type="button" onclick="let q=document.getElementById('productQty'); q.value++;">+</button>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill px-4 fw-bold flex-grow-1 shadow-sm">
                                <i class="fa-solid fa-cart-plus me-2"></i> Thêm Vào Giỏ Hàng
                            </button>
                            <a href="compare.php?action=add&id=<?php echo (int)$product['id']; ?>" class="btn btn-outline-secondary btn-lg rounded-pill px-3 fw-bold">
                                <i class="fa-solid fa-code-compare me-1"></i> So sánh
                            </a>
                        </div>
                    </form>
                <?php else: ?>
                    <button class="btn btn-secondary btn-lg rounded-pill w-100 fw-bold" disabled>
                        <i class="fa-solid fa-circle-xmark me-2"></i> Sản phẩm tạm hết hàng
                    </button>
                <?php endif; ?>

                <!-- Commitments Grid -->
                <div class="row g-2 mt-4 pt-3 border-top text-muted small">
                    <div class="col-6 col-sm-4 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-rotate-left text-primary fs-5"></i>
                        <span>1 đổi 1 trong 30 ngày</span>
                    </div>
                    <div class="col-6 col-sm-4 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-success fs-5"></i>
                        <span>Bảo hành 24 tháng</span>
                    </div>
                    <div class="col-6 col-sm-4 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-truck text-warning fs-5"></i>
                        <span>Giao hàng an toàn</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Details & Reviews Tabs -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5" style="background: white;">
        <div class="card-header bg-white border-bottom p-0">
            <ul class="nav nav-tabs border-0 px-3 pt-2" id="productTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active fw-bold py-3 px-4" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc" type="button">
                        <i class="fa-solid fa-align-left me-2"></i> Mô Tả Chi Tiết
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold py-3 px-4" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs" type="button">
                        <i class="fa-solid fa-sliders me-2"></i> Thông Số Kỹ Thuật
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold py-3 px-4" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button">
                        <i class="fa-solid fa-star me-2"></i> Đánh Giá (<?php echo $so_luong_dg; ?>)
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="productTabContent">
                <!-- Description Pane -->
                <div class="tab-pane fade show active" id="desc" role="tabpanel">
                    <div class="py-2" style="line-height: 1.8; color: #334155;">
                        <?php if (!empty($product['mo_ta'])): ?>
                            <?php echo nl2br(htmlspecialchars($product['mo_ta'])); ?>
                        <?php else: ?>
                            <p class="text-muted">Đang cập nhật thông tin mô tả chi tiết cho sản phẩm này.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Specifications Pane -->
                <div class="tab-pane fade" id="specs" role="tabpanel">
                    <?php if (!empty($thong_so_arr)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered align-middle">
                                <tbody>
                                    <?php foreach ($thong_so_arr as $k => $v): ?>
                                        <tr>
                                            <th class="w-35 bg-light text-dark fw-bold ps-3"><?php echo htmlspecialchars($k); ?></th>
                                            <td class="ps-3 text-secondary"><?php echo htmlspecialchars($v); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted py-3">Chưa có thông số kỹ thuật chi tiết.</p>
                    <?php endif; ?>
                </div>

                <!-- Reviews Pane -->
                <div class="tab-pane fade" id="reviews" role="tabpanel">
                    <div class="row g-4">
                        <!-- Left: Write Review Form -->
                        <div class="col-lg-5">
                            <div class="card border-0 rounded-4 p-4 mb-4 text-center" style="background: #f8fafc;">
                                <div class="display-5 fw-extrabold text-dark font-heading"><?php echo $sao_tb; ?><span class="fs-4 text-muted">/5</span></div>
                                <div class="text-warning mb-2 fs-5">
                                    <?php for ($i = 1; $i <= 5; $i++) echo ($i <= $sao_tb) ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>'; ?>
                                </div>
                                <div class="small text-muted">Dựa trên <?php echo $so_luong_dg; ?> nhận xét từ khách hàng</div>
                            </div>

                            <form method="POST" action="product_detail.php?id=<?php echo $id_sp; ?>" class="card border-0 rounded-4 p-4 shadow-sm" style="background: white; border: 1px solid var(--border-subtle) !important;">
                                <h5 class="fw-bold mb-3">Gửi Đánh Giá Của Bạn</h5>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Họ và tên của bạn</label>
                                    <input type="text" name="ten_nguoi_danh_gia" class="form-control rounded-3" placeholder="Nhập tên..." required value="<?php echo isset($_SESSION['khach_hang_ten']) ? htmlspecialchars($_SESSION['khach_hang_ten']) : ''; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Số sao đánh giá</label>
                                    <select name="so_sao" class="form-select rounded-3 text-warning fw-bold">
                                        <option value="5" selected>⭐⭐⭐⭐⭐ (5 Sao - Tuyệt vời)</option>
                                        <option value="4">⭐⭐⭐⭐ (4 Sao - Tốt)</option>
                                        <option value="3">⭐⭐⭐ (3 Sao - Bình thường)</option>
                                        <option value="2">⭐⭐ (2 Sao - Kém)</option>
                                        <option value="1">⭐ (1 Sao - Rất tệ)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Nhận xét chi tiết</label>
                                    <textarea name="noi_dung" rows="3" class="form-control rounded-3" placeholder="Chia sẻ trải nghiệm sử dụng của bạn..." required></textarea>
                                </div>
                                <button type="submit" name="btn_danh_gia" class="btn btn-primary w-100 rounded-pill fw-bold py-2">
                                    <i class="fa-solid fa-paper-plane me-2"></i> Gửi Đánh Giá
                                </button>
                            </form>
                        </div>

                        <!-- Right: List of Reviews -->
                        <div class="col-lg-7">
                            <h5 class="fw-bold mb-3">Tất Cả Nhận Xét (<?php echo $so_luong_dg; ?>)</h5>
                            <?php if (!empty($danh_gias)): ?>
                                <div class="d-flex flex-column gap-3">
                                    <?php foreach ($danh_gias as $dg): ?>
                                        <div class="p-3 rounded-3 border-bottom" style="background: #f8fafc;">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                                        <?php echo mb_strtoupper(mb_substr($dg['ten_nguoi_danh_gia'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                                                    </div>
                                                    <span><?php echo htmlspecialchars($dg['ten_nguoi_danh_gia']); ?></span>
                                                    <span class="badge bg-success-subtle text-success small py-0 px-1"><i class="fa-solid fa-circle-check"></i> Đã mua hàng</span>
                                                </div>
                                                <div class="text-warning small">
                                                    <?php for ($i = 1; $i <= (int)$dg['so_sao']; $i++) echo '<i class="fa-solid fa-star"></i>'; ?>
                                                </div>
                                            </div>
                                            <p class="text-muted small mb-0 ps-4 ms-2"><?php echo nl2br(htmlspecialchars($dg['noi_dung'])); ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="fa-regular fa-comment-dots fa-3x mb-2 text-light"></i>
                                    <p>Chưa có đánh giá nào. Hãy là người đầu tiên đánh giá sản phẩm này!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products Section -->
    <?php if (!empty($related_products)): ?>
        <div class="mb-5">
            <div class="section-header-modern">
                <h3 class="section-title"><i class="fa-solid fa-sparkles text-primary"></i> Sản Phẩm Cùng Danh Mục</h3>
                <a href="index.php?cat_id=<?php echo (int)$product['danh_muc_id']; ?>" class="section-badge text-decoration-none">Xem tất cả</a>
            </div>
            <div class="row row-cols-2 row-cols-md-4 g-3 g-lg-4">
                <?php foreach ($related_products as $rel): ?>
                    <?php 
                        $rel_img = (!empty($rel['hinh_anh']) && file_exists('images/' . $rel['hinh_anh'])) ? 'images/' . htmlspecialchars($rel['hinh_anh']) : $placeholder_svg;
                    ?>
                    <div class="col">
                        <div class="product-card">
                            <a href="product_detail.php?id=<?php echo $rel['id']; ?>" class="product-thumb-wrapper">
                                <img src="<?php echo $rel_img; ?>" alt="<?php echo htmlspecialchars($rel['ten_sp']); ?>" class="product-thumb-img" loading="lazy">
                            </a>
                            <div class="product-card-body">
                                <h3 class="product-title">
                                    <a href="product_detail.php?id=<?php echo $rel['id']; ?>"><?php echo htmlspecialchars($rel['ten_sp']); ?></a>
                                </h3>
                                <div class="product-price-box mt-auto">
                                    <div class="product-current-price"><?php echo number_format($rel['gia'], 0, ',', '.'); ?> ₫</div>
                                </div>
                                <a href="product_detail.php?id=<?php echo $rel['id']; ?>" class="btn btn-outline-primary btn-sm rounded-pill w-100 fw-bold mt-2">
                                    Xem chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>