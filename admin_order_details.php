<?php
include 'admin_header.php';
include 'admin_sidebar.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>window.location.href='admin_orders.php';</script>";
    exit();
}

$don_hang_id = (int)$_GET['id'];

// 1. Lấy thông tin chung của đơn hàng
$stmt_dh = $conn->prepare("SELECT * FROM don_hang WHERE id = ?");
$stmt_dh->execute([$don_hang_id]);
$don_hang = $stmt_dh->fetch(PDO::FETCH_ASSOC);

if (!$don_hang) {
    echo "<div class='alert alert-danger m-4'><i class='fa-solid fa-circle-exclamation me-2'></i>Không tìm thấy đơn hàng #$don_hang_id!</div>";
    include 'admin_footer.php';
    exit();
}

// 2. Lấy danh sách sản phẩm trong đơn hàng
$sql_chitiet = "SELECT c.so_luong, c.gia, s.ten_sp, s.hinh_anh 
                FROM chi_tiet_don_hang c 
                LEFT JOIN san_pham s ON c.san_pham_id = s.id 
                WHERE c.don_hang_id = ?";
$stmt_chitiet = $conn->prepare($sql_chitiet);
$stmt_chitiet->execute([$don_hang_id]);
$chi_tiet_list = $stmt_chitiet->fetchAll(PDO::FETCH_ASSOC);

$trang_thai_tt = $don_hang['trang_thai_thanh_toan'] ?? 'Chưa thanh toán';
$placeholder_svg = "data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2250%22%20height%3D%2250%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20fill%3D%22%23f1f5f9%22%20width%3D%2250%22%20height%3D%2250%22%2F%3E%3C%2Fsvg%3E";
?>

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h2 class="fw-extrabold text-dark mb-1 font-heading">Chi Tiết Đơn Hàng <span class="text-primary">#MĐH-<?php echo $don_hang['id']; ?></span></h2>
        <p class="text-muted small mb-0">Ngày tạo: <strong><?php echo date('d/m/Y H:i', strtotime($don_hang['ngay_dat'])); ?></strong></p>
    </div>
    <a href="admin_orders.php" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold">
        <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách
    </a>
</div>

<div class="row g-4">
    <!-- Left: Customer & Shipping Information -->
    <div class="col-lg-4">
        <div class="admin-card p-4 h-100">
            <h5 class="fw-bold text-dark mb-4 pb-2 border-bottom font-heading d-flex align-items-center gap-2">
                <i class="fa-solid fa-address-card text-primary"></i> Thông Tin Nhận Hàng
            </h5>

            <div class="d-flex flex-column gap-3">
                <div>
                    <div class="text-muted small">Người nhận hàng:</div>
                    <div class="fw-bold text-dark fs-6"><?php echo htmlspecialchars($don_hang['ten_khach_hang']); ?></div>
                </div>

                <div>
                    <div class="text-muted small">Số điện thoại:</div>
                    <div class="fw-bold text-dark"><i class="fa-solid fa-phone text-success me-1"></i> <?php echo htmlspecialchars($don_hang['so_dien_thoai']); ?></div>
                </div>

                <div>
                    <div class="text-muted small">Địa chỉ giao hàng:</div>
                    <div class="text-dark"><i class="fa-solid fa-location-dot text-danger me-1"></i> <?php echo htmlspecialchars($don_hang['dia_chi']); ?></div>
                </div>

                <?php if (!empty($don_hang['ghi_chu'])): ?>
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="text-muted small fw-bold mb-1"><i class="fa-solid fa-pen text-secondary me-1"></i> Ghi chú của khách:</div>
                        <div class="small text-dark"><?php echo nl2br(htmlspecialchars($don_hang['ghi_chu'])); ?></div>
                    </div>
                <?php endif; ?>

                <hr class="my-1">

                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Trạng thái vận chuyển:</span>
                    <?php 
                        $stt = $don_hang['trang_thai'] ?? 'Chờ xử lý';
                        $badge_c = 'warning text-dark';
                        if ($stt == 'Hoàn thành') $badge_c = 'success text-white';
                        if ($stt == 'Đã hủy') $badge_c = 'danger text-white';
                        if ($stt == 'Đang giao') $badge_c = 'primary text-white';
                    ?>
                    <span class="badge bg-<?php echo $badge_c; ?> px-3 py-1 rounded-pill small fw-semibold">
                        <?php echo htmlspecialchars($stt); ?>
                    </span>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Tình trạng thanh toán:</span>
                    <?php if ($trang_thai_tt == 'Đã thanh toán'): ?>
                        <span class="badge bg-success-subtle text-success border border-success px-3 py-1 rounded-pill small fw-semibold">
                            <i class="fa-solid fa-circle-check me-1"></i> Đã thanh toán
                        </span>
                    <?php else: ?>
                        <span class="badge bg-warning-subtle text-warning border border-warning px-3 py-1 rounded-pill small">
                            Chưa thanh toán
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Ordered Items List -->
    <div class="col-lg-8">
        <div class="admin-card h-100 d-flex flex-column">
            <div class="admin-card-header">
                <h6 class="fw-bold text-dark mb-0 font-heading">
                    <i class="fa-solid fa-box-open text-primary me-2"></i> Danh Sách Sản Phẩm Trong Đơn
                </h6>
                <span class="badge bg-light text-muted border"><?php echo count($chi_tiet_list); ?> loại sản phẩm</span>
            </div>

            <div class="table-responsive flex-grow-1">
                <table class="table admin-table align-middle mb-0 text-center">
                    <thead>
                        <tr>
                            <th class="ps-4 text-start">Sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th class="pe-4 text-end">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $tong_cong = 0;
                        foreach ($chi_tiet_list as $item): 
                            $thanh_tien = $item['gia'] * $item['so_luong'];
                            $tong_cong += $thanh_tien;
                            $item_img = (!empty($item['hinh_anh']) && file_exists('images/' . $item['hinh_anh'])) ? 'images/' . htmlspecialchars($item['hinh_anh']) : $placeholder_svg;
                        ?>
                            <tr>
                                <td class="ps-4 text-start">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-3 border p-1 bg-light d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; flex-shrink: 0;">
                                            <img src="<?php echo $item_img; ?>" alt="Img" style="max-height: 40px; max-width: 40px; object-fit: contain;">
                                        </div>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($item['ten_sp'] ?? 'Sản phẩm'); ?></div>
                                    </div>
                                </td>
                                <td><?php echo number_format($item['gia'], 0, ',', '.'); ?> ₫</td>
                                <td><span class="badge bg-light text-dark border px-2 py-1">x<?php echo (int)$item['so_luong']; ?></span></td>
                                <td class="pe-4 text-end fw-extrabold text-danger font-heading">
                                    <?php echo number_format($thanh_tien, 0, ',', '.'); ?> ₫
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="p-4 bg-light border-top d-flex justify-content-between align-items-baseline">
                <span class="fw-bold text-dark fs-5">Tổng Tiền Thanh Toán:</span>
                <span class="fw-extrabold text-danger fs-3 font-heading"><?php echo number_format($tong_cong, 0, ',', '.'); ?> ₫</span>
            </div>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>