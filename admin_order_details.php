<?php
include 'admin_header.php';
include 'admin_sidebar.php';

// Kiểm tra xem có truyền ID đơn hàng lên URL không
if (!isset($_GET['id'])) {
    echo "<script>window.location.href='admin_orders.php';</script>";
    exit();
}

$don_hang_id = $_GET['id'];

// 1. Lấy thông tin chung của đơn hàng
$stmt_dh = $conn->prepare("SELECT * FROM don_hang WHERE id = ?");
$stmt_dh->execute([$don_hang_id]);
$don_hang = $stmt_dh->fetch(PDO::FETCH_ASSOC);

if (!$don_hang) {
    die("<div class='alert alert-danger m-4'>Không tìm thấy đơn hàng!</div>");
}

// 2. Lấy danh sách sản phẩm trong đơn hàng
$sql_chitiet = "SELECT c.so_luong, c.gia, s.ten_sp 
                FROM chi_tiet_don_hang c 
                JOIN san_pham s ON c.san_pham_id = s.id 
                WHERE c.don_hang_id = ?";
$stmt_chitiet = $conn->prepare($sql_chitiet);
$stmt_chitiet->execute([$don_hang_id]);
$chi_tiet_list = $stmt_chitiet->fetchAll(PDO::FETCH_ASSOC);

// Xử lý mặc định nếu dữ liệu cũ chưa có cột trạng thái thanh toán
$trang_thai_tt = isset($don_hang['trang_thai_thanh_toan']) ? $don_hang['trang_thai_thanh_toan'] : 'Chưa thanh toán';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Chi Tiết Đơn Hàng <span class="text-primary">#<?php echo $don_hang['id']; ?></span></h2>
    <a href="admin_orders.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Quay lại</a>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-dark text-white fw-bold">
                <i class="fa-solid fa-address-card"></i> Thông tin giao hàng
            </div>
            <div class="card-body">
                <p><strong>Người nhận:</strong> <?php echo htmlspecialchars($don_hang['ten_khach_hang']); ?></p>
                <p><strong>Điện thoại:</strong> <?php echo htmlspecialchars($don_hang['so_dien_thoai']); ?></p>
                <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($don_hang['dia_chi']); ?></p>
                <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($don_hang['ngay_dat'])); ?></p>
                
                <hr>
                
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>Giao hàng:</strong> 
                    <span class="badge bg-<?php echo ($don_hang['trang_thai'] == 'Hoàn thành') ? 'success' : (($don_hang['trang_thai'] == 'Đã hủy') ? 'danger' : 'warning text-dark'); ?>">
                        <?php echo $don_hang['trang_thai']; ?>
                    </span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <strong>Thanh toán:</strong>
                    <?php if ($trang_thai_tt == 'Đã thanh toán'): ?>
                        <span class="badge bg-success"><i class="fa-solid fa-check-circle"></i> Đã thanh toán</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Chưa thanh toán</span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($don_hang['ghi_chu'])): ?>
                    <hr>
                    <p class="mb-0 text-danger"><strong>Ghi chú:</strong> <?php echo nl2br(htmlspecialchars($don_hang['ghi_chu'])); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-8 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-dark text-white fw-bold">
                <i class="fa-solid fa-box-open"></i> Danh sách sản phẩm
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-center">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start ps-3">Tên sản phẩm</th>
                                <th>Đơn giá</th>
                                <th>Số lượng</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $tong_cong = 0;
                            foreach ($chi_tiet_list as $item): 
                                $thanh_tien = $item['gia'] * $item['so_luong'];
                                $tong_cong += $thanh_tien;
                            ?>
                                <tr>
                                    <td class="text-start ps-3 fw-semibold"><?php echo htmlspecialchars($item['ten_sp']); ?></td>
                                    <td><?php echo number_format($item['gia'], 0, ',', '.'); ?> đ</td>
                                    <td><?php echo $item['so_luong']; ?></td>
                                    <td class="fw-bold text-danger"><?php echo number_format($thanh_tien, 0, ',', '.'); ?> đ</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white text-end py-3">
                <h4 class="mb-0">Tổng cộng: <span class="text-danger fw-bold"><?php echo number_format($tong_cong, 0, ',', '.'); ?> VNĐ</span></h4>
            </div>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>