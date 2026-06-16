<?php
session_start();
include 'header.php';
require_once 'db.php';

// 1. Kiểm tra ID sản phẩm
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Sản phẩm không tồn tại!'); window.location.href='index.php';</script>";
    exit();
}
$id_sp = $_GET['id'];

// --- XỬ LÝ GỬI ĐÁNH GIÁ ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_danh_gia'])) {
    $ten_nguoi = trim($_POST['ten_nguoi_danh_gia']);
    $so_sao = (int)$_POST['so_sao'];
    $noi_dung = trim($_POST['noi_dung']);

    if (!empty($ten_nguoi) && !empty($noi_dung)) {
        try {
            $stmt_dg = $conn->prepare("INSERT INTO danh_gia (san_pham_id, ten_nguoi_danh_gia, so_sao, noi_dung) VALUES (?, ?, ?, ?)");
            $stmt_dg->execute([$id_sp, $ten_nguoi, $so_sao, $noi_dung]);
            echo "<script>alert('Cảm ơn bạn đã đánh giá!'); window.location.href='product_detail.php?id=$id_sp';</script>";
            exit();
        } catch (PDOException $e) {
            echo "<script>alert('Lỗi hệ thống: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
}

// 2. LẤY DỮ LIỆU SẢN PHẨM
$stmt = $conn->prepare("SELECT s.*, d.ten_danh_muc FROM san_pham s LEFT JOIN danh_muc d ON s.danh_muc_id = d.id WHERE s.id = ?");
$stmt->execute([$id_sp]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "<div class='container py-5 text-center'><h3>Sản phẩm không tồn tại.</h3></div>";
    include 'footer.php'; exit();
}

$thong_so_arr = !empty($product['thong_so']) ? json_decode($product['thong_so'], true) : [];

// 3. LẤY ĐÁNH GIÁ VÀ TÍNH SAO TRUNG BÌNH
$stmt_dg = $conn->prepare("SELECT * FROM danh_gia WHERE san_pham_id = ? ORDER BY ngay_tao DESC");
$stmt_dg->execute([$id_sp]);
$danh_gias = $stmt_dg->fetchAll(PDO::FETCH_ASSOC);

$so_luong_dg = count($danh_gias);
$tong_sao = 0;
foreach ($danh_gias as $dg) $tong_sao += $dg['so_sao'];
$sao_tb = ($so_luong_dg > 0) ? round($tong_sao / $so_luong_dg, 1) : 0;
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-5">
            <img src="images/<?php echo htmlspecialchars($product['hinh_anh']); ?>" class="img-fluid rounded shadow-sm w-100">
        </div>
        <div class="col-md-7">
            <h1 class="fw-bold"><?php echo htmlspecialchars($product['ten_sp']); ?></h1>
            <h3 class="text-danger my-3"><?php echo number_format($product['gia'], 0, ',', '.'); ?> ₫</h3>
            
            <form action="cart.php" method="POST" class="mt-4">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="id_sp" value="<?php echo $product['id']; ?>">
                <div class="d-flex align-items-center mb-4">
                    <input type="number" name="so_luong" value="1" min="1" class="form-control me-3" style="width: 80px;">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fa-solid fa-cart-plus me-2"></i>Thêm vào giỏ hàng</button>
                </div>
            </form>
        </div>
    </div>

    <ul class="nav nav-tabs mt-5">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#desc">Mô tả</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#specs">Thông số kỹ thuật</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#reviews">Đánh giá (<?php echo $so_luong_dg; ?>)</button></li>
    </ul>

    <div class="tab-content p-4 border border-top-0">
        <div class="tab-pane fade show active" id="desc">
            <?php echo nl2br(htmlspecialchars($product['mo_ta'])); ?>
        </div>
        <div class="tab-pane fade" id="specs">
            <table class="table table-striped">
                <?php foreach ($thong_so_arr as $k => $v): ?>
                <tr><th class="w-25"><?php echo htmlspecialchars($k); ?></th><td><?php echo htmlspecialchars($v); ?></td></tr>
                <?php endforeach; ?>
            </table>
        </div>
        <div class="tab-pane fade" id="reviews">
            <div class="row">
                <div class="col-md-4">
                    <div class="card p-3 mb-3 text-center bg-light">
                        <h3 class="fw-bold"><?php echo $sao_tb; ?>/5</h3>
                        <div class="text-warning"><?php for($i=1; $i<=5; $i++) echo ($i<=$sao_tb) ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>'; ?></div>
                    </div>
                    <form method="POST" class="card p-3">
                        <input type="text" name="ten_nguoi_danh_gia" class="form-control mb-2" placeholder="Tên bạn" required>
                        <select name="so_sao" class="form-select mb-2 text-warning"><option value="5">5 Sao</option><option value="4">4 Sao</option><option value="3">3 Sao</option></select>
                        <textarea name="noi_dung" class="form-control mb-2" placeholder="Nhận xét..." required></textarea>
                        <button type="submit" name="btn_danh_gia" class="btn btn-primary w-100">Gửi đánh giá</button>
                    </form>
                </div>
                <div class="col-md-8">
                    <?php foreach ($danh_gias as $dg): ?>
                    <div class="border-bottom mb-3 pb-2">
                        <div class="fw-bold"><?php echo htmlspecialchars($dg['ten_nguoi_danh_gia']); ?></div>
                        <div class="text-warning"><?php for($i=1; $i<=$dg['so_sao']; $i++) echo '<i class="fa-solid fa-star"></i>'; ?></div>
                        <p><?php echo htmlspecialchars($dg['noi_dung']); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>