<?php
include 'header.php';

if (!isset($_SESSION['khach_hang_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

$kh_id = $_SESSION['khach_hang_id'];

// Lấy danh sách đơn hàng của khách
$stmt = $conn->prepare("SELECT * FROM don_hang WHERE khach_hang_id = ? ORDER BY ngay_dat DESC");
$stmt->execute([$kh_id]);
$don_hang_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="list-group shadow-sm">
            <a href="profile.php" class="list-group-item list-group-item-action"><i class="fa-solid fa-user-pen text-success me-2"></i> Hồ sơ của tôi</a>
            <a href="order_history.php" class="list-group-item list-group-item-action active"><i class="fa-solid fa-clock-rotate-left me-2"></i> Lịch sử đơn hàng</a>
            <a href="logout.php" class="list-group-item list-group-item-action text-danger"><i class="fa-solid fa-right-from-bracket me-2"></i> Đăng xuất</a>
        </div>
    </div>

    <div class="col-md-9">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold fs-5 py-3">Đơn Mua Của Tôi</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Mã ĐH</th>
                                <th>Ngày đặt</th>
                                <th>Tổng tiền</th>
                                <th>Thanh toán</th>
                                <th>Trạng thái giao</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($don_hang_list) > 0): ?>
                                <?php foreach ($don_hang_list as $dh): ?>
                                    <tr>
                                        <td class="fw-bold text-primary">#<?php echo $dh['id']; ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($dh['ngay_dat'])); ?></td>
                                        <td class="fw-bold text-danger"><?php echo number_format($dh['tong_tien'], 0, ',', '.'); ?> đ</td>
                                        <td>
                                            <?php if (isset($dh['trang_thai_thanh_toan']) && $dh['trang_thai_thanh_toan'] == 'Đã thanh toán'): ?>
                                                <span class="badge bg-success"><i class="fa-solid fa-check"></i> Đã thanh toán</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Chưa thanh toán</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                                $badge_color = 'warning text-dark';
                                                if ($dh['trang_thai'] == 'Hoàn thành') $badge_color = 'success';
                                                if ($dh['trang_thai'] == 'Đã hủy') $badge_color = 'danger';
                                                if ($dh['trang_thai'] == 'Đang giao') $badge_color = 'primary';
                                            ?>
                                            <span class="badge bg-<?php echo $badge_color; ?>"><?php echo $dh['trang_thai']; ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="py-5 text-muted">
                                        <i class="fa-solid fa-box-open fa-3x mb-3"></i><br>
                                        Bạn chưa có đơn hàng nào.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>