<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'db.php';

if (!isset($_SESSION['khach_hang_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

$kh_id = (int)$_SESSION['khach_hang_id'];

// Lấy thông tin khách hàng
$stmt_kh = $conn->prepare("SELECT * FROM khach_hang WHERE id = ?");
$stmt_kh->execute([$kh_id]);
$khach_hang = $stmt_kh->fetch(PDO::FETCH_ASSOC);

// Lấy danh sách đơn hàng của khách
$stmt = $conn->prepare("SELECT * FROM don_hang WHERE khach_hang_id = ? ORDER BY id DESC");
$stmt->execute([$kh_id]);
$don_hang_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<div class="container py-4" style="min-height: 65vh;">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php"><i class="fa-solid fa-house me-1"></i> Trang chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page">Lịch sử đơn hàng</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Left Column: User Sidebar Navigation -->
        <div class="col-lg-4 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center mb-4" style="background: white;">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white mx-auto mb-3 shadow-sm font-heading" style="width: 70px; height: 70px; font-size: 1.8rem; font-weight: 800;">
                    <?php echo mb_strtoupper(mb_substr($khach_hang['ho_ten'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                </div>
                <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($khach_hang['ho_ten']); ?></h5>
                <p class="text-muted small mb-2"><?php echo htmlspecialchars($khach_hang['email']); ?></p>
                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1 fw-semibold align-self-center small">
                    <i class="fa-solid fa-shield-check me-1"></i> Thành viên TechStore
                </span>
            </div>

            <!-- Navigation Links -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: white;">
                <div class="list-group list-group-flush p-2">
                    <a href="profile.php" class="list-group-item list-group-item-action border-0 rounded-3 py-3 fw-semibold text-muted d-flex align-items-center gap-3">
                        <i class="fa-solid fa-user-pen"></i> Hồ sơ của tôi
                    </a>
                    <a href="order_history.php" class="list-group-item list-group-item-action border-0 rounded-3 py-3 fw-bold active d-flex align-items-center gap-3">
                        <i class="fa-solid fa-clock-rotate-left"></i> Đơn mua của tôi
                    </a>
                    <a href="logout.php" class="list-group-item list-group-item-action border-0 rounded-3 py-3 fw-semibold text-danger d-flex align-items-center gap-3">
                        <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Column: Order History List -->
        <div class="col-lg-8 col-xl-9">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: white;">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold text-dark mb-0 fs-5"><i class="fa-solid fa-bag-shopping text-primary me-2"></i> Đơn Hàng Của Tôi</h4>
                    </div>
                    <span class="badge bg-light text-muted border px-3 py-2 rounded-pill">
                        Tổng cộng: <strong><?php echo count($don_hang_list); ?></strong> đơn hàng
                    </span>
                </div>

                <div class="card-body p-0">
                    <?php if (!empty($don_hang_list)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 text-center">
                                <thead class="table-light small text-muted text-uppercase">
                                    <tr>
                                        <th class="ps-4 text-start">Mã ĐH</th>
                                        <th>Ngày đặt</th>
                                        <th>Tổng tiền</th>
                                        <th>Thanh toán</th>
                                        <th>Trạng thái giao</th>
                                        <th class="pe-4 text-end">Chi tiết</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($don_hang_list as $dh): ?>
                                        <?php
                                            // Lấy danh sách sản phẩm trong đơn này
                                            $stmt_items = $conn->prepare("SELECT ct.*, s.ten_sp, s.hinh_anh FROM chi_tiet_don_hang ct LEFT JOIN san_pham s ON ct.san_pham_id = s.id WHERE ct.don_hang_id = ?");
                                            $stmt_items->execute([$dh['id']]);
                                            $order_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
                                        ?>
                                        <tr>
                                            <td class="ps-4 text-start py-3">
                                                <span class="fw-bold text-primary">#MĐH-<?php echo $dh['id']; ?></span>
                                                <div class="small text-muted" style="font-size: 0.75rem;">
                                                    <?php echo count($order_items); ?> sản phẩm
                                                </div>
                                            </td>
                                            <td class="small text-muted">
                                                <?php echo !empty($dh['ngay_dat']) ? date('d/m/Y H:i', strtotime($dh['ngay_dat'])) : '—'; ?>
                                            </td>
                                            <td class="fw-bold text-danger">
                                                <?php echo number_format($dh['tong_tien'], 0, ',', '.'); ?> ₫
                                            </td>
                                            <td>
                                                <?php if (isset($dh['trang_thai_thanh_toan']) && $dh['trang_thai_thanh_toan'] == 'Đã thanh toán'): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1 rounded-pill small">
                                                        <i class="fa-solid fa-check me-1"></i> Đã thanh toán
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning-subtle text-warning border border-warning px-2 py-1 rounded-pill small">
                                                        <i class="fa-solid fa-clock me-1"></i> Chờ thanh toán
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    $status = $dh['trang_thai'] ?? 'Đang xử lý';
                                                    $badge_cls = 'bg-primary-subtle text-primary border-primary';
                                                    if ($status == 'Hoàn thành' || $status == 'Đã giao') $badge_cls = 'bg-success-subtle text-success border-success';
                                                    if ($status == 'Đã hủy') $badge_cls = 'bg-danger-subtle text-danger border-danger';
                                                    if ($status == 'Đang giao') $badge_cls = 'bg-info-subtle text-info border-info';
                                                ?>
                                                <span class="badge <?php echo $badge_cls; ?> border px-3 py-1 rounded-pill small fw-semibold">
                                                    <?php echo htmlspecialchars($status); ?>
                                                </span>
                                            </td>
                                            <td class="pe-4 text-end">
                                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3" type="button" data-bs-toggle="collapse" data-bs-target="#order_details_<?php echo $dh['id']; ?>">
                                                    <i class="fa-solid fa-eye me-1"></i> Xem
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Collapsible Item Details Row -->
                                        <tr class="collapse" id="order_details_<?php echo $dh['id']; ?>">
                                            <td colspan="6" class="p-3 bg-light text-start border-bottom">
                                                <div class="card border-0 rounded-3 p-3 shadow-xs bg-white">
                                                    <div class="fw-bold text-dark small mb-2"><i class="fa-solid fa-box text-primary me-2"></i> Chi tiết đơn hàng #MĐH-<?php echo $dh['id']; ?>:</div>
                                                    <div class="row g-2">
                                                        <?php foreach ($order_items as $item): 
                                                            $item_img = (!empty($item['hinh_anh']) && file_exists('images/' . $item['hinh_anh'])) ? 'images/' . htmlspecialchars($item['hinh_anh']) : 'data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2240%22%20height%3D%2240%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20fill%3D%22%23f1f5f9%22%20width%3D%2240%22%20height%3D%2240%22%2F%3E%3C%2Fsvg%3E';
                                                        ?>
                                                            <div class="col-md-6">
                                                                <div class="d-flex align-items-center gap-2 p-2 border rounded-2 bg-light">
                                                                    <img src="<?php echo $item_img; ?>" style="width: 40px; height: 40px; object-fit: contain;" class="rounded border bg-white p-1">
                                                                    <div class="flex-grow-1 overflow-hidden">
                                                                        <div class="small fw-bold text-dark text-truncate"><?php echo htmlspecialchars($item['ten_sp'] ?? 'Sản phẩm'); ?></div>
                                                                        <div class="text-muted small" style="font-size: 0.75rem;">
                                                                            SL: x<?php echo (int)$item['so_luong']; ?> • Đơn giá: <?php echo number_format($item['gia'], 0, ',', '.'); ?> ₫
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <div class="small text-muted mt-3 pt-2 border-top">
                                                        <i class="fa-solid fa-location-dot text-primary me-1"></i> <strong>Địa chỉ giao hàng:</strong> <?php echo htmlspecialchars($dh['dia_chi'] ?? '—'); ?> 
                                                        <?php if (!empty($dh['ghi_chu'])): ?>
                                                            | <i class="fa-solid fa-pen text-secondary me-1"></i> <strong>Ghi chú:</strong> <?php echo htmlspecialchars($dh['ghi_chu']); ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <!-- Empty Orders State -->
                        <div class="p-5 text-center my-4">
                            <i class="fa-solid fa-box-open text-muted fa-4x mb-3"></i>
                            <h4 class="fw-bold text-dark mb-2">Bạn chưa có đơn hàng nào</h4>
                            <p class="text-muted mb-4">Các sản phẩm bạn đặt mua sẽ xuất hiện tại đây để bạn tiện theo dõi trạng thái vận chuyển.</p>
                            <a href="index.php" class="btn btn-primary rounded-pill px-4 py-2 fw-bold">
                                <i class="fa-solid fa-bag-shopping me-2"></i> Khám Phá Mua Sắm Ngay
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>