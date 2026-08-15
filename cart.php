<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'db.php';

// --- XỬ LÝ NHẬN DỮ LIỆU TỪ FORM (Thêm vào giỏ) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $id_sp = (int)$_POST['id_sp'];
    $so_luong = isset($_POST['so_luong']) ? max(1, (int)$_POST['so_luong']) : 1;

    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$id_sp])) {
        $_SESSION['cart'][$id_sp] += $so_luong;
    } else {
        $_SESSION['cart'][$id_sp] = $so_luong;
    }
    echo "<script>window.location.href='cart.php';</script>";
    exit();
}

// --- XỬ LÝ CẬP NHẬT SỐ LƯỢNG ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    if (isset($_POST['quantities']) && is_array($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $id => $qty) {
            $id = (int)$id;
            $qty = (int)$qty;
            if ($qty <= 0) {
                unset($_SESSION['cart'][$id]);
            } else {
                $_SESSION['cart'][$id] = $qty;
            }
        }
    }
    echo "<script>window.location.href='cart.php';</script>";
    exit();
}

// --- XỬ LÝ XÓA SẢN PHẨM KHỎI GIỎ ---
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $id_remove = (int)$_GET['id'];
    unset($_SESSION['cart'][$id_remove]);
    echo "<script>window.location.href='cart.php';</script>";
    exit();
}

include 'header.php';

$placeholder_svg = "data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2280%22%20height%3D%2280%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2080%2080%22%3E%3Crect%20fill%3D%22%23f8fafc%22%20width%3D%2280%22%20height%3D%2280%22%2F%3E%3Ctext%20fill%3D%22%2394a3b8%22%20font-family%3D%22sans-serif%22%20font-size%3D%2210%22%20dy%3D%223.5%22%20font-weight%3D%22bold%22%20x%3D%2250%25%22%20y%3D%2250%25%22%20text-anchor%3D%22middle%22%3EProduct%3C%2Ftext%3E%3C%2Fsvg%3E";
?>

<div class="container py-4" style="min-height: 65vh;">
    <!-- Breadcrumb & Step Tracker -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="fw-bold text-dark fs-3 mb-1"><i class="fa-solid fa-cart-shopping text-primary me-2"></i> Giỏ Hàng Của Bạn</h1>
            <p class="text-muted small mb-0">Quản lý danh sách các sản phẩm công nghệ bạn đã chọn</p>
        </div>
        
        <!-- Stepper Indicator -->
        <div class="d-flex align-items-center gap-2 small fw-bold">
            <span class="badge bg-primary rounded-pill px-3 py-2">1. Giỏ hàng</span>
            <i class="fa-solid fa-chevron-right text-muted small"></i>
            <span class="badge bg-light text-muted border rounded-pill px-3 py-2">2. Thanh toán</span>
            <i class="fa-solid fa-chevron-right text-muted small"></i>
            <span class="badge bg-light text-muted border rounded-pill px-3 py-2">3. Hoàn tất</span>
        </div>
    </div>

    <?php if (empty($_SESSION['cart']) || count($_SESSION['cart']) === 0): ?>
        <!-- Empty Cart State -->
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center my-4" style="background: white;">
            <div class="mb-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light" style="width: 100px; height: 100px;">
                    <i class="fa-solid fa-cart-arrow-down text-muted" style="font-size: 3rem;"></i>
                </div>
            </div>
            <h3 class="fw-bold text-dark mb-2">Giỏ hàng của bạn đang trống!</h3>
            <p class="text-muted mb-4" style="max-width: 420px; margin: 0 auto;">
                Hãy khám phá các sản phẩm công nghệ, linh kiện máy tính và thiết bị điện tử mới nhất tại TechStore nhé.
            </p>
            <div>
                <a href="index.php" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow-sm">
                    <i class="fa-solid fa-bag-shopping me-2"></i> Khám Phá Sản Phẩm Ngay
                </a>
            </div>
        </div>
    <?php else: ?>
        <form method="POST" action="cart.php">
            <input type="hidden" name="action" value="update">
            <div class="row g-4">
                <!-- Left: Items List Table -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: white;">
                        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark">Danh Sách Sản Phẩm (<?php echo count($_SESSION['cart']); ?>)</span>
                            <a href="index.php" class="text-primary small text-decoration-none fw-semibold">
                                <i class="fa-solid fa-plus me-1"></i> Mua thêm sản phẩm khác
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light small text-muted text-uppercase">
                                    <tr>
                                        <th class="ps-4" style="width: 45%;">Sản phẩm</th>
                                        <th class="text-center" style="width: 18%;">Đơn giá</th>
                                        <th class="text-center" style="width: 17%;">Số lượng</th>
                                        <th class="text-end" style="width: 15%;">Thành tiền</th>
                                        <th class="text-center pe-4" style="width: 5%;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $tong_tien = 0;
                                    foreach ($_SESSION['cart'] as $id => $sl): 
                                        $stmt = $conn->prepare("SELECT * FROM san_pham WHERE id = ?");
                                        $stmt->execute([(int)$id]);
                                        $sp = $stmt->fetch(PDO::FETCH_ASSOC);
                                        
                                        if ($sp):
                                            $thanh_tien = $sp['gia'] * $sl;
                                            $tong_tien += $thanh_tien;
                                            $anh = (!empty($sp['hinh_anh']) && file_exists('images/' . $sp['hinh_anh'])) ? 'images/' . htmlspecialchars($sp['hinh_anh']) : $placeholder_svg;
                                    ?>
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="rounded-3 border d-flex align-items-center justify-content-center p-1 bg-light" style="width: 65px; height: 65px; flex-shrink: 0;">
                                                    <img src="<?php echo $anh; ?>" alt="<?php echo htmlspecialchars($sp['ten_sp']); ?>" class="img-fluid" style="max-height: 55px; object-fit: contain;">
                                                </div>
                                                <div>
                                                    <a href="product_detail.php?id=<?php echo $sp['id']; ?>" class="fw-bold text-dark text-decoration-none text-truncate d-block" style="max-width: 250px;">
                                                        <?php echo htmlspecialchars($sp['ten_sp']); ?>
                                                    </a>
                                                    <span class="badge bg-light text-muted border small mt-1">Chính hãng</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center fw-semibold text-dark">
                                            <?php echo number_format($sp['gia'], 0, ',', '.'); ?> ₫
                                        </td>
                                        <td class="text-center">
                                            <div class="input-group input-group-sm mx-auto" style="width: 100px;">
                                                <button class="btn btn-outline-secondary" type="button" onclick="let inp = document.getElementById('qty_<?php echo $id; ?>'); if(inp.value>1){inp.value--; this.form.submit();}">-</button>
                                                <input type="number" name="quantities[<?php echo $id; ?>]" id="qty_<?php echo $id; ?>" value="<?php echo (int)$sl; ?>" min="1" class="form-control text-center fw-bold px-1" onchange="this.form.submit();">
                                                <button class="btn btn-outline-secondary" type="button" onclick="let inp = document.getElementById('qty_<?php echo $id; ?>'); inp.value++; this.form.submit();">+</button>
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold text-danger">
                                            <?php echo number_format($thanh_tien, 0, ',', '.'); ?> ₫
                                        </td>
                                        <td class="text-center pe-4">
                                            <a href="cart.php?action=remove&id=<?php echo $id; ?>" class="btn btn-sm btn-light text-danger rounded-circle p-2" title="Xóa khỏi giỏ" onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?');">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endif; endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white p-3 px-4 d-flex justify-content-between align-items-center border-top">
                            <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                <i class="fa-solid fa-rotate me-1"></i> Cập nhật giỏ hàng
                            </button>
                            <a href="index.php" class="btn btn-link text-muted small text-decoration-none">
                                <i class="fa-solid fa-arrow-left me-1"></i> Tiếp tục mua hàng
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Right: Order Summary Card -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 100px; background: white;">
                        <h5 class="fw-bold text-dark mb-4 pb-2 border-bottom">Tóm Tắt Đơn Hàng</h5>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tạm tính:</span>
                            <span class="fw-bold text-dark"><?php echo number_format($tong_tien, 0, ',', '.'); ?> ₫</span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Phí vận chuyển:</span>
                            <span class="text-success fw-bold"><i class="fa-solid fa-truck-fast me-1"></i> Miễn phí</span>
                        </div>

                        <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                            <span class="text-muted">Mã giảm giá:</span>
                            <span class="text-muted">—</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-baseline mb-4">
                            <span class="fw-bold text-dark fs-5">Tổng cộng:</span>
                            <span class="fw-extrabold text-danger fs-3 font-heading"><?php echo number_format($tong_tien, 0, ',', '.'); ?> ₫</span>
                        </div>

                        <a href="checkout.php" class="btn btn-primary btn-lg rounded-pill w-100 fw-bold py-3 shadow-sm mb-3">
                            <i class="fa-solid fa-credit-card me-2"></i> Tiến Hành Đặt Hàng
                        </a>

                        <div class="p-3 rounded-3 text-muted small" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fa-solid fa-shield-check text-success"></i>
                                <span class="fw-bold text-dark">Thanh toán an toàn 100%</span>
                            </div>
                            <div>Hỗ trợ kiểm tra hàng trước khi thanh toán (COD) hoặc chuyển khoản tiện lợi.</div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>