<?php
include 'admin_header.php';
include 'admin_sidebar.php';

// 1. Thống kê Tổng doanh thu (Chỉ tính các đơn hàng đã Hoàn thành)
$stmt_doanhthu = $conn->query("SELECT SUM(tong_tien) as tong_doanh_thu FROM don_hang WHERE trang_thai = 'Hoàn thành'");
$doanh_thu = $stmt_doanhthu->fetch(PDO::FETCH_ASSOC)['tong_doanh_thu'] ?? 0;

// 2. Thống kê Tổng số đơn hàng
$so_don_hang = $conn->query("SELECT COUNT(id) FROM don_hang")->fetchColumn();

// 3. Thống kê Tổng số sản phẩm
$so_san_pham = $conn->query("SELECT COUNT(id) FROM san_pham")->fetchColumn();

// 4. Thống kê Tổng số danh mục & khách hàng
$so_danh_muc = $conn->query("SELECT COUNT(id) FROM danh_muc")->fetchColumn();
$so_khach_hang = $conn->query("SELECT COUNT(id) FROM khach_hang")->fetchColumn();

// Lấy 6 đơn hàng mới nhất
$stmt_donmoi = $conn->query("SELECT * FROM don_hang ORDER BY id DESC LIMIT 6");
$don_hang_moi = $stmt_donmoi->fetchAll(PDO::FETCH_ASSOC);

// Lấy sản phẩm sắp hết hàng (tồn kho <= 5)
$stmt_low_stock = $conn->query("SELECT * FROM san_pham WHERE so_luong_ton <= 5 ORDER BY so_luong_ton ASC LIMIT 5");
$low_stock_products = $stmt_low_stock->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h2 class="fw-extrabold text-dark mb-1 font-heading">Tổng Quan Hệ Thống</h2>
        <p class="text-muted small mb-0">Theo dõi hiệu suất kinh doanh, doanh số và tình trạng đơn hàng của TechStore</p>
    </div>
    <div class="d-flex gap-2">
        <a href="admin_product_add.php" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">
            <i class="fa-solid fa-plus me-1"></i> Thêm Sản Phẩm
        </a>
        <a href="admin_orders.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold">
            <i class="fa-solid fa-cart-shopping me-1"></i> Quản Lý Đơn
        </a>
    </div>
</div>

<!-- 4 KPI Stat Cards -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="admin-stat-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted small fw-bold text-uppercase">Tổng Doanh Thu</span>
                <div class="stat-icon-wrapper stat-icon-green">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
            </div>
            <h3 class="fw-extrabold text-dark mb-1 font-heading"><?php echo number_format($doanh_thu, 0, ',', '.'); ?> ₫</h3>
            <div class="small text-success fw-semibold">
                <i class="fa-solid fa-circle-check me-1"></i> Đơn hàng đã hoàn tất
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="admin-stat-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted small fw-bold text-uppercase">Tổng Đơn Hàng</span>
                <div class="stat-icon-wrapper stat-icon-blue">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
            </div>
            <h3 class="fw-extrabold text-dark mb-1 font-heading"><?php echo $so_don_hang; ?> <span class="fs-6 text-muted fw-normal">đơn</span></h3>
            <div class="small text-primary fw-semibold">
                <a href="admin_orders.php" class="text-decoration-none">Xem tất cả đơn hàng &rarr;</a>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="admin-stat-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted small fw-bold text-uppercase">Kho Sản Phẩm</span>
                <div class="stat-icon-wrapper stat-icon-yellow">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>
            <h3 class="fw-extrabold text-dark mb-1 font-heading"><?php echo $so_san_pham; ?> <span class="fs-6 text-muted fw-normal">sản phẩm</span></h3>
            <div class="small text-muted">
                Phân bổ trong <strong><?php echo $so_danh_muc; ?></strong> danh mục
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="admin-stat-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted small fw-bold text-uppercase">Khách Hàng</span>
                <div class="stat-icon-wrapper stat-icon-purple">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
            <h3 class="fw-extrabold text-dark mb-1 font-heading"><?php echo $so_khach_hang; ?> <span class="fs-6 text-muted fw-normal">tài khoản</span></h3>
            <div class="small text-purple fw-semibold">
                <a href="admin_customers.php" class="text-decoration-none">Quản lý khách hàng &rarr;</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left: Recent Orders Table -->
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <div>
                    <h5 class="fw-bold text-dark mb-0 font-heading"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Đơn Hàng Gần Đây</h5>
                </div>
                <a href="admin_orders.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    Xem tất cả đơn
                </a>
            </div>
            <div class="table-responsive">
                <table class="table admin-table align-middle mb-0 text-center">
                    <thead>
                        <tr>
                            <th class="ps-4 text-start">Mã ĐH</th>
                            <th class="text-start">Khách hàng</th>
                            <th>Ngày đặt</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th class="pe-4 text-end">Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($don_hang_moi)): ?>
                            <?php foreach ($don_hang_moi as $dh): ?>
                                <tr>
                                    <td class="ps-4 text-start fw-bold text-primary">#MĐH-<?php echo $dh['id']; ?></td>
                                    <td class="text-start">
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($dh['ten_khach_hang']); ?></div>
                                        <div class="small text-muted"><?php echo htmlspecialchars($dh['so_dien_thoai']); ?></div>
                                    </td>
                                    <td class="small text-muted"><?php echo !empty($dh['ngay_dat']) ? date('d/m H:i', strtotime($dh['ngay_dat'])) : '—'; ?></td>
                                    <td class="fw-bold text-danger"><?php echo number_format($dh['tong_tien'], 0, ',', '.'); ?> ₫</td>
                                    <td>
                                        <?php 
                                            $stt = $dh['trang_thai'] ?? 'Chờ xử lý';
                                            $badge_c = 'warning text-dark';
                                            if ($stt == 'Hoàn thành') $badge_c = 'success text-white';
                                            if ($stt == 'Đã hủy') $badge_c = 'danger text-white';
                                            if ($stt == 'Đang giao') $badge_c = 'primary text-white';
                                        ?>
                                        <span class="badge bg-<?php echo $badge_c; ?> px-2 py-1 rounded-pill small">
                                            <?php echo htmlspecialchars($stt); ?>
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="admin_order_details.php?id=<?php echo $dh['id']; ?>" class="btn btn-sm btn-light border rounded-circle p-2" title="Xem chi tiết">
                                            <i class="fa-solid fa-arrow-right"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="py-4 text-muted">Chưa có đơn hàng nào trong hệ thống.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right: Quick Shortcuts & Low Stock Alert -->
    <div class="col-lg-4">
        <!-- Low Stock Alert -->
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h6 class="fw-bold text-dark mb-0 font-heading">
                    <i class="fa-solid fa-triangle-exclamation text-warning me-2"></i> Cảnh Báo Tồn Kho
                </h6>
                <span class="badge bg-danger-subtle text-danger rounded-pill"><?php echo count($low_stock_products); ?> SP</span>
            </div>
            <div class="p-3">
                <?php if (!empty($low_stock_products)): ?>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($low_stock_products as $lsp): ?>
                            <div class="d-flex align-items-center justify-content-between p-2 rounded-2 bg-light">
                                <div class="text-truncate pe-2">
                                    <div class="small fw-bold text-dark text-truncate"><?php echo htmlspecialchars($lsp['ten_sp']); ?></div>
                                    <div class="text-danger small fw-semibold">
                                        <?php echo ($lsp['so_luong_ton'] <= 0) ? 'Hết hàng (0)' : 'Còn lại: ' . (int)$lsp['so_luong_ton']; ?>
                                    </div>
                                </div>
                                <a href="admin_product_edit.php?id=<?php echo $lsp['id']; ?>" class="btn btn-xs btn-outline-primary btn-sm rounded px-2 py-1 small">
                                    Nhập hàng
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted small py-3">
                        <i class="fa-solid fa-circle-check text-success fs-4 mb-2 d-block"></i>
                        Mọi sản phẩm trong kho đều có số lượng an toàn.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick System Links -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h6 class="fw-bold text-dark mb-0 font-heading">
                    <i class="fa-solid fa-bolt text-primary me-2"></i> Thao Tác Nhanh
                </h6>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <a href="admin_product_add.php" class="btn btn-light text-start border p-2 d-flex align-items-center gap-3">
                    <i class="fa-solid fa-circle-plus text-success fs-5"></i>
                    <div>
                        <div class="fw-bold small text-dark">Thêm sản phẩm mới</div>
                        <div class="text-muted" style="font-size: 0.75rem;">Đăng bán thiết bị lên website</div>
                    </div>
                </a>
                <a href="admin_banners.php" class="btn btn-light text-start border p-2 d-flex align-items-center gap-3">
                    <i class="fa-solid fa-images text-warning fs-5"></i>
                    <div>
                        <div class="fw-bold small text-dark">Cập nhật Banner</div>
                        <div class="text-muted" style="font-size: 0.75rem;">Thay đổi khuyến mãi trang chủ</div>
                    </div>
                </a>
                <a href="admin_categories.php" class="btn btn-light text-start border p-2 d-flex align-items-center gap-3">
                    <i class="fa-solid fa-shapes text-info fs-5"></i>
                    <div>
                        <div class="fw-bold small text-dark">Quản lý danh mục</div>
                        <div class="text-muted" style="font-size: 0.75rem;">Tạo nhóm và thông số kỹ thuật</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>