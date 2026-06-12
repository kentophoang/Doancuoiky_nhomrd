<?php
// Gọi Header và Sidebar
include 'admin_header.php';
include 'admin_sidebar.php';

// 1. Xử lý cập nhật trạng thái giao hàng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $don_hang_id = $_POST['don_hang_id'];
    $trang_thai_moi = $_POST['trang_thai'];
    
    $stmt_update = $conn->prepare("UPDATE don_hang SET trang_thai = ? WHERE id = ?");
    $stmt_update->execute([$trang_thai_moi, $don_hang_id]);
    
    echo "<script>window.location.href='admin_orders.php';</script>";
    exit();
}

// 2. Xử lý cập nhật trạng thái thanh toán (TÍNH NĂNG MỚI)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_payment') {
    $don_hang_id = $_POST['don_hang_id'];
    
    $stmt_pay = $conn->prepare("UPDATE don_hang SET trang_thai_thanh_toan = 'Đã thanh toán' WHERE id = ?");
    $stmt_pay->execute([$don_hang_id]);
    
    echo "<script>window.location.href='admin_orders.php';</script>";
    exit();
}

// Lấy danh sách đơn hàng
$stmt = $conn->query("SELECT * FROM don_hang ORDER BY ngay_dat DESC");
$don_hangs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 class="mb-4">Danh Sách Đơn Hàng</h2>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Mã ĐH</th>
                        <th>Khách hàng</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Thanh toán</th> <th>Trạng thái giao</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($don_hangs) > 0): ?>
                        <?php foreach ($don_hangs as $dh): ?>
                            <tr>
                                <td class="fw-bold">#<?php echo $dh['id']; ?></td>
                                <td class="text-start">
                                    <div class="fw-bold"><?php echo htmlspecialchars($dh['ten_khach_hang']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($dh['so_dien_thoai']); ?></small>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($dh['ngay_dat'])); ?></td>
                                <td class="fw-bold text-danger"><?php echo number_format($dh['tong_tien'], 0, ',', '.'); ?> đ</td>
                                
                                <td>
                                    <?php if (isset($dh['trang_thai_thanh_toan']) && $dh['trang_thai_thanh_toan'] == 'Đã thanh toán'): ?>
                                        <span class="badge bg-success py-2"><i class="fa-solid fa-check-circle"></i> Đã thanh toán</span>
                                    <?php else: ?>
                                        <div class="d-flex flex-column align-items-center">
                                            <span class="badge bg-secondary mb-1">Chưa thanh toán</span>
                                            <form method="POST" action="admin_orders.php">
                                                <input type="hidden" name="action" value="update_payment">
                                                <input type="hidden" name="don_hang_id" value="<?php echo $dh['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-success py-0 px-2" style="font-size: 0.8rem;" onclick="return confirm('Xác nhận khách hàng này đã thanh toán?');">
                                                    <i class="fa-solid fa-check"></i> Xác nhận
                                                </button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <form method="POST" action="admin_orders.php" class="d-flex justify-content-center">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="don_hang_id" value="<?php echo $dh['id']; ?>">
                                        <select name="trang_thai" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                                            <option value="Chờ xử lý" <?php echo ($dh['trang_thai'] == 'Chờ xử lý') ? 'selected' : ''; ?>>Chờ xử lý</option>
                                            <option value="Đang giao" <?php echo ($dh['trang_thai'] == 'Đang giao') ? 'selected' : ''; ?>>Đang giao</option>
                                            <option value="Hoàn thành" <?php echo ($dh['trang_thai'] == 'Hoàn thành') ? 'selected' : ''; ?>>Hoàn thành</option>
                                            <option value="Đã hủy" <?php echo ($dh['trang_thai'] == 'Đã hủy') ? 'selected' : ''; ?>>Đã hủy</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <a href="admin_order_details.php?id=<?php echo $dh['id']; ?>" class="btn btn-sm btn-info text-white">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="py-4 text-muted">Chưa có đơn hàng nào trong hệ thống.</td>
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