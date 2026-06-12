<?php
include 'header.php';
require_once 'db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Sản phẩm không tồn tại!'); window.location.href='index.php';</script>";
    exit();
}
$id_sp = $_GET['id'];

// --- XỬ LÝ LƯU ĐÁNH GIÁ MỚI ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_danh_gia'])) {
    $ten_nguoi = trim($_POST['ten_nguoi_danh_gia']);
    $so_sao = (int)$_POST['so_sao'];
    $noi_dung = trim($_POST['noi_dung']);

    if ($so_sao >= 1 && $so_sao <= 5 && !empty($ten_nguoi) && !empty($noi_dung)) {
        try {
            $stmt_dg = $conn->prepare("INSERT INTO danh_gia (san_pham_id, ten_nguoi_danh_gia, so_sao, noi_dung) VALUES (?, ?, ?, ?)");
            $stmt_dg->execute([$id_sp, $ten_nguoi, $so_sao, $noi_dung]);
            echo "<script>alert('Cảm ơn bạn đã gửi đánh giá!'); window.location.href='product_detail.php?id=$id_sp';</script>";
            exit();
        } catch (PDOException $e) {
            echo "<script>alert('Lỗi hệ thống khi gửi đánh giá.');</script>";
        }
    }
}

// --- TRUY VẤN SẢN PHẨM ---
$sql = "SELECT s.*, d.ten_danh_muc 
        FROM san_pham s 
        LEFT JOIN danh_muc d ON s.danh_muc_id = d.id 
        WHERE s.id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id_sp]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "<div class='container my-5 text-center'><h3>Sản phẩm không tồn tại.</h3><a href='index.php' class='btn btn-primary mt-3'>Về trang chủ</a></div>";
    include 'footer.php'; exit();
}

$thong_so_arr = !empty($product['thong_so']) ? json_decode($product['thong_so'], true) : [];

// --- LẤY DANH SÁCH ĐÁNH GIÁ CỦA SẢN PHẨM NÀY ---
$stmt_get_dg = $conn->prepare("SELECT * FROM danh_gia WHERE san_pham_id = ? ORDER BY ngay_tao DESC");
$stmt_get_dg->execute([$id_sp]);
$danh_gias = $stmt_get_dg->fetchAll(PDO::FETCH_ASSOC);

// Tính sao trung bình
$tong_sao = 0;
$so_luong_dg = count($danh_gias);
if ($so_luong_dg > 0) {
    foreach ($danh_gias as $dg) $tong_sao += $dg['so_sao'];
    $sao_tb = round($tong_sao / $so_luong_dg, 1);
} else {
    $sao_tb = 0;
}
?>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="#" class="text-decoration-none"><?php echo htmlspecialchars($product['ten_danh_muc']); ?></a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['ten_sp']); ?></li>
        </ol>
    </nav>

    <div class="row mb-5">
        <div class="col-md-5 mb-4">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <?php $img_src = (!empty($product['hinh_anh'])) ? "images/".$product['hinh_anh'] : "https://via.placeholder.com/600x600?text=Chua+co+anh"; ?>
                <img src="<?php echo $img_src; ?>" class="img-fluid w-100" style="object-fit: cover;">
            </div>
        </div>

        <div class="col-md-7">
            <h1 class="fw-bold mb-2"><?php echo htmlspecialchars($product['ten_sp']); ?></h1>
            
            <div class="mb-3 text-warning">
                <?php
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $sao_tb) echo '<i class="fa-solid fa-star"></i>';
                    elseif ($i - 0.5 <= $sao_tb) echo '<i class="fa-solid fa-star-half-stroke"></i>';
                    else echo '<i class="fa-regular fa-star"></i>';
                }
                ?>
                <span class="text-muted ms-2 fs-6">(<?php echo $so_luong_dg; ?> đánh giá)</span>
            </div>

            <p class="text-muted mb-2">Danh mục: <strong><?php echo htmlspecialchars($product['ten_danh_muc']); ?></strong></p>
            <h2 class="text-danger fw-bold mb-4"><?php echo number_format($product['gia'], 0, ',', '.'); ?> ₫</h2>

            <div class="mb-4">
                <?php if ($product['so_luong_ton'] > 0): ?>
                    <span class="badge bg-success fs-6"><i class="fa-solid fa-check-circle"></i> Còn hàng (<?php echo $product['so_luong_ton']; ?>)</span>
                <?php else: ?>
                    <span class="badge bg-danger fs-6"><i class="fa-solid fa-times-circle"></i> Hết hàng</span>
                <?php endif; ?>
            </div>

            <?php if ($product['so_luong_ton'] > 0): ?>
                <form action="cart.php" method="POST" class="d-flex align-items-center gap-3 mb-4">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="id_sp" value="<?php echo $product['id']; ?>">
                    <div class="input-group" style="width: 130px;">
                        <span class="input-group-text">SL</span>
                        <input type="number" class="form-control text-center" name="so_luong" value="1" min="1" max="<?php echo $product['so_luong_ton']; ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg fw-bold flex-grow-1"><i class="fa-solid fa-cart-plus me-2"></i> Thêm Vào Giỏ Hàng</button>
                </form>
            <?php else: ?>
                <button class="btn btn-secondary btn-lg fw-bold w-100 mb-4" disabled>Sản phẩm tạm hết hàng</button>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <ul class="nav nav-tabs mb-4" id="productTabs">
                <li class="nav-item"><button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#desc">Mô Tả Sản Phẩm</button></li>
                <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#specs">Thông Số Kỹ Thuật</button></li>
                <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#reviews">Đánh Giá (<?php echo $so_luong_dg; ?>)</button></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="desc">
                    <div class="lh-lg" style="white-space: pre-line;"><?php echo !empty($product['mo_ta']) ? htmlspecialchars($product['mo_ta']) : "Chưa có thông tin mô tả."; ?></div>
                </div>

                <div class="tab-pane fade" id="specs">
                    <?php if (!empty($thong_so_arr) && is_array($thong_so_arr)): ?>
                        <div class="table-responsive col-md-8 mx-auto">
                            <table class="table table-striped table-bordered align-middle">
                                <tbody>
                                    <?php foreach ($thong_so_arr as $key => $value): ?>
                                        <tr><th class="w-25 ps-4 py-3"><?php echo htmlspecialchars($key); ?></th><td class="py-3"><?php echo htmlspecialchars($value); ?></td></tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">Chưa có thông số kỹ thuật.</p>
                    <?php endif; ?>
                </div>

                <div class="tab-pane fade" id="reviews">
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="card shadow-sm border-0 bg-light">
                                <div class="card-body">
                                    <h5 class="fw-bold mb-3">Viết đánh giá của bạn</h5>
                                    <form method="POST" action="product_detail.php?id=<?php echo $id_sp; ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Tên của bạn</label>
                                            <input type="text" class="form-control" name="ten_nguoi_danh_gia" required placeholder="Nhập tên...">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Đánh giá sao</label>
                                            <select class="form-select text-warning fw-bold" name="so_sao">
                                                <option value="5">⭐⭐⭐⭐⭐ (5 Sao - Tuyệt vời)</option>
                                                <option value="4">⭐⭐⭐⭐ (4 Sao - Tốt)</option>
                                                <option value="3">⭐⭐⭐ (3 Sao - Bình thường)</option>
                                                <option value="2">⭐⭐ (2 Sao - Kém)</option>
                                                <option value="1">⭐ (1 Sao - Quá tệ)</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Nội dung đánh giá</label>
                                            <textarea class="form-control" name="noi_dung" rows="3" required placeholder="Sản phẩm này thế nào?"></textarea>
                                        </div>
                                        <button type="submit" name="btn_danh_gia" class="btn btn-primary w-100 fw-bold">Gửi đánh giá</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <?php if ($so_luong_dg > 0): ?>
                                <?php foreach ($danh_gias as $dg): ?>
                                    <div class="card border-0 border-bottom mb-3 pb-3">
                                        <div class="card-body p-0">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="fw-bold mb-0"><i class="fa-solid fa-user-circle text-secondary me-2"></i><?php echo htmlspecialchars($dg['ten_nguoi_danh_gia']); ?></h6>
                                                <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($dg['ngay_tao'])); ?></small>
                                            </div>
                                            <div class="text-warning mb-2" style="font-size: 0.9rem;">
                                                <?php
                                                for ($i = 1; $i <= 5; $i++) {
                                                    echo ($i <= $dg['so_sao']) ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
                                                }
                                                ?>
                                            </div>
                                            <p class="mb-0 text-dark"><?php echo nl2br(htmlspecialchars($dg['noi_dung'])); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="fa-regular fa-comments fs-1 mb-3"></i>
                                    <h5>Chưa có đánh giá nào</h5>
                                    <p>Hãy là người đầu tiên đánh giá sản phẩm này!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>