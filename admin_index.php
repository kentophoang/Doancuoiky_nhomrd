<?php
// Gọi Header và Sidebar (Đã bao gồm khóa bảo mật và menu mới)
include 'admin_header.php';
include 'admin_sidebar.php';

// 1. Thống kê Tổng doanh thu (Chỉ tính các đơn hàng đã Hoàn thành)
$stmt_doanhthu = $conn->query("SELECT SUM(tong_tien) as tong_doanh_thu FROM don_hang WHERE trang_thai = 'Hoàn thành'");
$doanh_thu = $stmt_doanhthu->fetch(PDO::FETCH_ASSOC)['tong_doanh_thu'];
$doanh_thu = $doanh_thu ? $doanh_thu : 0; // Nếu chưa có đơn nào thì gán bằng 0

// 2. Thống kê Tổng số đơn hàng
$so_don_hang = $conn->query("SELECT COUNT(id) FROM don_hang")->fetchColumn();

// 3. Thống kê Tổng số sản phẩm
$so_san_pham = $conn->query("SELECT COUNT(id) FROM san_pham")->fetchColumn();

// 4. Thống kê Tổng số danh mục
$so_danh_muc = $conn->query("SELECT COUNT(id) FROM danh_muc")->fetchColumn();

// Lấy 5 đơn hàng mới nhất để hiển thị nhanh
$stmt_donmoi = $conn->query("SELECT * FROM don_hang ORDER BY ngay_dat DESC LIMIT 5");
$don_hang_moi = $stmt_donmoi->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Tổng Quan Hệ Thống</h2>
    <a href="admin_logout.php" class="btn btn-outline-danger fw-bold">
        <i class="fa-solid fa-power-off me-1"></i> Đăng xuất
    </a>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card bg-white shadow-sm h-100 p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-2">Tổng Doanh Thu</h6>
                    <h4 class="fw-bold text-success mb-0"><?php echo number_format($doanh_thu, 0, ',', '.'); ?> đ</h4>
                </div>
                <div class="icon-box bg-success bg-opacity-10 text-success">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card stat-card bg-white shadow-sm h-100 p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-2">Tổng Đơn Hàng</h6>
                    <h4 class="fw-bold text-primary mb-0"><?php echo $so_don_hang; ?> Đơn</h4>
                </div>
                <div class="icon-box bg-primary bg-opacity-10 text-primary">
                    <i class="fa-solid fa-shopping-cart"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card stat-card bg-white shadow-sm h-100 p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-2">Tổng Sản Phẩm</h6>
                    <h4 class="fw-bold text-warning mb-0"><?php echo $so_san_pham; ?> Sản phẩm</h4>
                </div>
                <div class="icon-box bg-warning bg-opacity-10 text-warning">
                    <i class="fa-solid fa-box-open"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card stat-card bg-white shadow-sm h-100 p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-2">Danh Mục</h6>
                    <h4 class="fw-bold text-info mb-0"><?php echo $so_danh_muc; ?> Danh mục</h4>
                </div>
                <div class="icon-box bg-info bg-opacity-10 text-info">
                    <i class="fa-solid fa-tags"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="fa-solid fa-clock-rotate-left text-primary"></i> Đơn hàng gần đây</h5>
        <a href="admin_orders.php" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center">
                <thead class="table-light">
                    <tr>
                        <th>Mã ĐH</th>
                        <th>Khách hàng</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($don_hang_moi) > 0): ?>
                        <?php foreach ($don_hang_moi as $dh): ?>
                            <tr>
                                <td class="fw-bold">#<?php echo $dh['id']; ?></td>
                                <td class="text-start"><?php echo htmlspecialchars($dh['ten_khach_hang']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($dh['ngay_dat'])); ?></td>
                                <td class="fw-bold text-danger"><?php echo number_format($dh['tong_tien'], 0, ',', '.'); ?> đ</td>
                                <td>
                                    <?php 
                                        $badge_color = 'warning text-dark';
                                        if ($dh['trang_thai'] == 'Hoàn thành') $badge_color = 'success';
                                        if ($dh['trang_thai'] == 'Đã hủy') $badge_color = 'danger';
                                        if ($dh['trang_thai'] == 'Đang giao') $badge_color = 'primary';
                                    ?>
                                    <span class="badge bg-<?php echo $badge_color; ?>">
                                        <?php echo $dh['trang_thai']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="py-4 text-muted">Chưa có đơn hàng nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
// Gọi Footer
include 'admin_footer.php'; 
?>