<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'db.php';
require_once 'send_mail.php'; // Gọi file gửi mail

$thong_bao = ''; 
$kieu_thong_bao = 'success';
$don_hang_thanh_cong_id = null;

// Nếu giỏ hàng trống và không phải vừa đặt hàng xong thì quay về trang chủ
if (empty($_SESSION['cart']) && !isset($_POST['btn_dathang'])) {
    echo "<script>window.location.href='index.php';</script>";
    exit();
}

// Lấy thông tin sản phẩm trong giỏ nếu còn giỏ hàng
$san_phams_gio = [];
$tong_tien_tam_tinh = 0;

if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $stmt = $conn->prepare("SELECT id, ten_sp, gia, hinh_anh FROM san_pham WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $san_phams_gio = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($san_phams_gio as $sp) {
        $qty = $_SESSION['cart'][$sp['id']];
        $tong_tien_tam_tinh += $sp['gia'] * $qty;
    }
}

// Xử lý lưu đơn hàng và trừ tồn kho
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_dathang'])) {
    $ten_kh = trim($_POST['ten_kh']); 
    $sdt = trim($_POST['sdt']); 
    $email_kh = trim($_POST['email']); 
    $dia_chi = trim($_POST['dia_chi']); 
    $ghi_chu = isset($_POST['ghi_chu']) ? trim($_POST['ghi_chu']) : '';
    $phuong_thuc = isset($_POST['phuong_thuc_tt']) ? $_POST['phuong_thuc_tt'] : 'COD';
    $tong_tien_don_hang = 0;

    if (!empty($_SESSION['cart'])) {
        $ids = array_keys($_SESSION['cart']);
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = $conn->prepare("SELECT id, ten_sp, gia FROM san_pham WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $san_phams = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $chi_tiet_html = "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>
                            <tr style='background: #f1f5f9;'><th>Sản phẩm</th><th>Số lượng</th><th>Đơn giá</th><th>Thành tiền</th></tr>";
        
        foreach ($san_phams as $sp) {
            $so_luong = $_SESSION['cart'][$sp['id']];
            $thanh_tien_sp = $sp['gia'] * $so_luong;
            $tong_tien_don_hang += $thanh_tien_sp;
            $chi_tiet_html .= "<tr><td>{$sp['ten_sp']}</td><td align='center'>{$so_luong}</td><td align='right'>".number_format($sp['gia'],0,',','.')." đ</td><td align='right'>".number_format($thanh_tien_sp,0,',','.')." đ</td></tr>";
        }
        $chi_tiet_html .= "</table><p style='font-size: 16px; margin-top: 15px;'>Tổng tiền thanh toán: <strong style='color: #dc2626;'>".number_format($tong_tien_don_hang,0,',','.')." đ</strong></p>";

        try {
            $conn->beginTransaction();

            $sql_don_hang = "INSERT INTO don_hang (khach_hang_id, ten_khach_hang, so_dien_thoai, dia_chi, ghi_chu, tong_tien) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_don_hang = $conn->prepare($sql_don_hang);
            $stmt_don_hang->execute([isset($_SESSION['khach_hang_id']) ? $_SESSION['khach_hang_id'] : NULL, $ten_kh, $sdt, $dia_chi, $ghi_chu, $tong_tien_don_hang]);
            $don_hang_id = $conn->lastInsertId();

            $stmt_chi_tiet = $conn->prepare("INSERT INTO chi_tiet_don_hang (don_hang_id, san_pham_id, so_luong, gia) VALUES (?, ?, ?, ?)");
            $stmt_tru_kho = $conn->prepare("UPDATE san_pham SET so_luong_ton = so_luong_ton - ? WHERE id = ?");

            foreach ($_SESSION['cart'] as $sp_id => $so_luong) {
                $gia_sp = 0;
                foreach($san_phams as $s) if($s['id'] == $sp_id) $gia_sp = $s['gia'];
                $stmt_chi_tiet->execute([$don_hang_id, $sp_id, $so_luong, $gia_sp]);
                $stmt_tru_kho->execute([$so_luong, $sp_id]);
            }

            $conn->commit();

            // Gửi Email nếu có
            if (function_exists('gui_email_xac_nhan') && !empty($email_kh)) {
                $noi_dung = "<h2>Đơn hàng #$don_hang_id đã được đặt thành công</h2><p>Xin chào <strong>$ten_kh</strong>, cảm ơn bạn đã đặt hàng tại <strong>TechStore</strong>.</p>" . $chi_tiet_html;
                @gui_email_xac_nhan($email_kh, $ten_kh, $don_hang_id, $noi_dung);
            }

            unset($_SESSION['cart']);
            $don_hang_thanh_cong_id = $don_hang_id;
            $thong_bao = "Đặt hàng thành công!";
        } catch (Exception $e) {
            $conn->rollBack();
            $kieu_thong_bao = 'danger'; 
            $thong_bao = "Lỗi khi xử lý đơn hàng: " . $e->getMessage();
        }
    }
}

include 'header.php';
?>

<div class="container py-4" style="min-height: 65vh;">
    <!-- Breadcrumb & Step Tracker -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="fw-bold text-dark fs-3 mb-1"><i class="fa-solid fa-credit-card text-primary me-2"></i> Xác Nhận & Thanh Toán</h1>
            <p class="text-muted small mb-0">Hoàn tất thông tin giao hàng để chúng tôi chuẩn bị đơn cho bạn</p>
        </div>
        
        <div class="d-flex align-items-center gap-2 small fw-bold">
            <span class="badge bg-light text-muted border rounded-pill px-3 py-2">1. Giỏ hàng</span>
            <i class="fa-solid fa-chevron-right text-muted small"></i>
            <span class="badge bg-primary rounded-pill px-3 py-2">2. Thanh toán</span>
            <i class="fa-solid fa-chevron-right text-muted small"></i>
            <span class="badge bg-light text-muted border rounded-pill px-3 py-2">3. Hoàn tất</span>
        </div>
    </div>

    <?php if ($don_hang_thanh_cong_id): ?>
        <!-- Order Success Screen -->
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center my-4" style="background: white;">
            <div class="mb-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success-subtle" style="width: 100px; height: 100px;">
                    <i class="fa-solid fa-circle-check text-success" style="font-size: 3.5rem;"></i>
                </div>
            </div>
            <h2 class="fw-extrabold text-dark mb-2">Đặt Hàng Thành Công!</h2>
            <p class="text-muted mb-3 fs-5">Mã đơn hàng của bạn là: <strong class="text-primary">#MĐH-<?php echo $don_hang_thanh_cong_id; ?></strong></p>
            <p class="text-muted mb-4" style="max-width: 500px; margin: 0 auto;">
                Cảm ơn bạn đã tin tưởng TechStore. Chúng tôi đã nhận được thông tin đơn hàng và sẽ liên hệ xác nhận sớm nhất qua số điện thoại hoặc email của bạn.
            </p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="order_history.php" class="btn btn-primary btn-lg rounded-pill px-4 fw-bold shadow-sm">
                    <i class="fa-solid fa-clock-rotate-left me-2"></i> Xem Đơn Hàng Của Tôi
                </a>
                <a href="index.php" class="btn btn-outline-secondary btn-lg rounded-pill px-4 fw-bold">
                    <i class="fa-solid fa-house me-2"></i> Quay Lại Trang Chủ
                </a>
            </div>
        </div>
    <?php else: ?>
        <?php if ($thong_bao != '' && $kieu_thong_bao == 'danger'): ?>
            <div class="alert alert-danger rounded-3 mb-4"><i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo $thong_bao; ?></div>
        <?php endif; ?>

        <form method="POST" action="checkout.php">
            <div class="row g-4">
                <!-- Left Column: Shipping & Customer Information Form -->
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" style="background: white;">
                        <h5 class="fw-bold text-dark mb-4 pb-2 border-bottom d-flex align-items-center gap-2">
                            <i class="fa-solid fa-truck-ramp-box text-primary"></i> 1. Thông Tin Nhận Hàng
                        </h5>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Họ và tên người nhận <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-user text-muted"></i></span>
                                    <input type="text" name="ten_kh" class="form-control border-start-0" required placeholder="Nguyễn Văn A" value="<?php echo isset($_SESSION['khach_hang_ten']) ? htmlspecialchars($_SESSION['khach_hang_ten']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Số điện thoại liên hệ <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-phone text-muted"></i></span>
                                    <input type="tel" name="sdt" class="form-control border-start-0" required placeholder="09xxxxxxxx">
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Email nhận hóa đơn & thông báo</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                                    <input type="email" name="email" class="form-control border-start-0" placeholder="email@example.com" value="<?php echo isset($_SESSION['khach_hang_email']) ? htmlspecialchars($_SESSION['khach_hang_email']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Địa chỉ nhận hàng chi tiết <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-location-dot text-muted"></i></span>
                                    <textarea name="dia_chi" class="form-control border-start-0" rows="2" required placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố..."></textarea>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Ghi chú đơn hàng (Tùy chọn)</label>
                                <textarea name="ghi_chu" class="form-control" rows="2" placeholder="Ví dụ: Giao hàng vào giờ hành chính, gọi trước khi đến..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Methods Card -->
                    <div class="card border-0 shadow-sm rounded-4 p-4" style="background: white;">
                        <h5 class="fw-bold text-dark mb-4 pb-2 border-bottom d-flex align-items-center gap-2">
                            <i class="fa-solid fa-wallet text-primary"></i> 2. Phương Thức Thanh Toán
                        </h5>

                        <div class="d-flex flex-column gap-3">
                            <label class="p-3 border rounded-3 d-flex align-items-center gap-3 cursor-pointer hover-bg-light" style="cursor: pointer;">
                                <input type="radio" name="phuong_thuc_tt" value="COD" checked class="form-check-input mt-0">
                                <i class="fa-solid fa-money-bill-wave text-success fs-4"></i>
                                <div>
                                    <div class="fw-bold text-dark">Thanh toán khi nhận hàng (COD)</div>
                                    <div class="text-muted small">Kiểm tra sản phẩm trước khi thanh toán tiền mặt cho shipper</div>
                                </div>
                            </label>

                            <label class="p-3 border rounded-3 d-flex align-items-center gap-3 cursor-pointer hover-bg-light" style="cursor: pointer;">
                                <input type="radio" name="phuong_thuc_tt" value="VNPAY" class="form-check-input mt-0">
                                <i class="fa-solid fa-qrcode text-primary fs-4"></i>
                                <div>
                                    <div class="fw-bold text-dark">Chuyển khoản qua QR Code / VNPAY / MoMo</div>
                                    <div class="text-muted small">Quét mã tiện lợi, miễn phí giao dịch</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Order Summary Review -->
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 100px; background: white;">
                        <h5 class="fw-bold text-dark mb-4 pb-2 border-bottom d-flex justify-content-between align-items-center">
                            <span>Đơn Hàng Của Bạn</span>
                            <span class="badge bg-primary rounded-pill"><?php echo count($san_phams_gio); ?> món</span>
                        </h5>

                        <!-- List of items in cart -->
                        <div class="d-flex flex-column gap-3 mb-4 max-height-300 overflow-auto pe-1" style="max-height: 280px; overflow-y: auto;">
                            <?php foreach ($san_phams_gio as $sp_item): 
                                $sl_item = $_SESSION['cart'][$sp_item['id']];
                                $item_img = (!empty($sp_item['hinh_anh']) && file_exists('images/' . $sp_item['hinh_anh'])) ? 'images/' . htmlspecialchars($sp_item['hinh_anh']) : 'data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2240%22%20height%3D%2240%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20fill%3D%22%23f1f5f9%22%20width%3D%2240%22%20height%3D%2240%22%2F%3E%3C%2Fsvg%3E';
                            ?>
                                <div class="d-flex align-items-center justify-content-between gap-2 border-bottom pb-2">
                                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                                        <img src="<?php echo $item_img; ?>" style="width: 45px; height: 45px; object-fit: contain;" class="rounded border p-1 bg-light">
                                        <div class="text-truncate" style="max-width: 170px;">
                                            <div class="small fw-bold text-dark text-truncate"><?php echo htmlspecialchars($sp_item['ten_sp']); ?></div>
                                            <div class="text-muted" style="font-size: 0.75rem;">SL: <strong>x<?php echo $sl_item; ?></strong></div>
                                        </div>
                                    </div>
                                    <div class="text-end fw-bold text-danger small">
                                        <?php echo number_format($sp_item['gia'] * $sl_item, 0, ',', '.'); ?> ₫
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Price calculation -->
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted">Tạm tính:</span>
                            <span class="fw-bold text-dark"><?php echo number_format($tong_tien_tam_tinh, 0, ',', '.'); ?> ₫</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 pb-3 border-bottom small">
                            <span class="text-muted">Phí giao hàng:</span>
                            <span class="text-success fw-bold"><i class="fa-solid fa-truck-fast me-1"></i> Miễn phí (Freeship)</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-baseline mb-4">
                            <span class="fw-bold text-dark fs-5">Tổng thanh toán:</span>
                            <span class="fw-extrabold text-danger fs-3 font-heading"><?php echo number_format($tong_tien_tam_tinh, 0, ',', '.'); ?> ₫</span>
                        </div>

                        <button type="submit" name="btn_dathang" class="btn btn-primary btn-lg rounded-pill w-100 fw-bold py-3 shadow-sm mb-3">
                            <i class="fa-solid fa-shield-check me-2"></i> Xác Nhận Đặt Hàng
                        </button>

                        <div class="text-center text-muted" style="font-size: 0.775rem;">
                            Bằng việc đặt hàng, bạn đồng ý với <a href="#" class="text-decoration-none">Điều khoản dịch vụ</a> của TechStore.
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>