<?php
include 'admin_header.php';
include 'admin_sidebar.php';
require_once 'db.php';

// 1. Xử lý cập nhật trạng thái giao hàng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $don_hang_id = (int)$_POST['don_hang_id'];
    $trang_thai_moi = $_POST['trang_thai'];
    
    $stmt_update = $conn->prepare("UPDATE don_hang SET trang_thai = ? WHERE id = ?");
    $stmt_update->execute([$trang_thai_moi, $don_hang_id]);
    
    echo "<script>window.location.href='admin_orders.php';</script>";
    exit();
}

// 2. Xử lý cập nhật trạng thái thanh toán
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_payment') {
    $don_hang_id = (int)$_POST['don_hang_id'];
    
    $stmt_pay = $conn->prepare("UPDATE don_hang SET trang_thai_thanh_toan = 'Đã thanh toán' WHERE id = ?");
    $stmt_pay->execute([$don_hang_id]);
    
    echo "<script>window.location.href='admin_orders.php';</script>";
    exit();
}

// Lọc đơn hàng theo trạng thái
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';
$search_order = isset($_GET['search']) ? trim($_GET['search']) : '';

$where_clauses = [];
$params = [];

if ($filter_status !== '') {
    $where_clauses[] = "trang_thai = ?";
    $params[] = $filter_status;
}

if ($search_order !== '') {
    $where_clauses[] = "(ten_khach_hang LIKE ? OR so_dien_thoai LIKE ? OR id = ?)";
    $params[] = "%$search_order%";
    $params[] = "%$search_order%";
    $params[] = (int)$search_order;
}

$sql_orders = "SELECT * FROM don_hang";
if (!empty($where_clauses)) {
    $sql_orders .= " WHERE " . implode(" AND ", $where_clauses);
}
$sql_orders .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql_orders);
$stmt->execute($params);
$don_hangs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h2 class="fw-extrabold text-dark mb-1 font-heading">Quản Lý Đơn Hàng</h2>
        <p class="text-muted small mb-0">Theo dõi, duyệt đơn, cập nhật tiến độ giao hàng và xác nhận thanh toán (<strong><?php echo count($don_hangs); ?></strong> đơn hàng)</p>
    </div>
</div>

<!-- Search & Status Filter Bar -->
<div class="admin-card p-3 mb-4">
    <form method="GET" action="admin_orders.php" class="row g-2 align-items-center">
        <div class="col-md-5 col-lg-4">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Tìm theo tên KH, SĐT, mã đơn..." value="<?php echo htmlspecialchars($search_order); ?>">
            </div>
        </div>
        <div class="col-md-4 col-lg-3">
            <select name="status" class="form-select">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="Chờ xử lý" <?php echo ($filter_status == 'Chờ xử lý') ? 'selected' : ''; ?>>Chờ xử lý</option>
                <option value="Đang giao" <?php echo ($filter_status == 'Đang giao') ? 'selected' : ''; ?>>Đang giao</option>
                <option value="Hoàn thành" <?php echo ($filter_status == 'Hoàn thành') ? 'selected' : ''; ?>>Hoàn thành</option>
                <option value="Đã hủy" <?php echo ($filter_status == 'Đã hủy') ? 'selected' : ''; ?>>Đã hủy</option>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary rounded-pill px-3">
                <i class="fa-solid fa-filter me-1"></i> Lọc
            </button>
            <?php if ($search_order !== '' || $filter_status !== ''): ?>
                <a href="admin_orders.php" class="btn btn-outline-secondary rounded-pill px-3 ms-1">
                    <i class="fa-solid fa-rotate-left me-1"></i> Đặt lại
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Orders Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle mb-0 text-center">
            <thead>
                <tr>
                    <th class="ps-4 text-start" style="width: 10%;">Mã ĐH</th>
                    <th class="text-start" style="width: 25%;">Khách hàng</th>
                    <th style="width: 15%;">Ngày đặt</th>
                    <th style="width: 15%;">Tổng tiền</th>
                    <th style="width: 15%;">Thanh toán</th>
                    <th style="width: 15%;">Trạng thái giao</th>
                    <th class="pe-4 text-end" style="width: 5%;">Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($don_hangs)): ?>
                    <?php foreach ($don_hangs as $dh): ?>
                        <tr>
                            <td class="ps-4 text-start fw-bold text-primary">#MĐH-<?php echo $dh['id']; ?></td>
                            <td class="text-start">
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($dh['ten_khach_hang']); ?></div>
                                <div class="small text-muted"><i class="fa-solid fa-phone me-1"></i><?php echo htmlspecialchars($dh['so_dien_thoai']); ?></div>
                            </td>
                            <td class="small text-muted">
                                <?php echo !empty($dh['ngay_dat']) ? date('d/m/Y H:i', strtotime($dh['ngay_dat'])) : '—'; ?>
                            </td>
                            <td class="fw-extrabold text-danger font-heading">
                                <?php echo number_format($dh['tong_tien'], 0, ',', '.'); ?> ₫
                            </td>
                            
                            <td>
                                <?php if (isset($dh['trang_thai_thanh_toan']) && $dh['trang_thai_thanh_toan'] == 'Đã thanh toán'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success px-3 py-1 rounded-pill small fw-semibold">
                                        <i class="fa-solid fa-circle-check me-1"></i> Đã thanh toán
                                    </span>
                                <?php else: ?>
                                    <div class="d-flex flex-column align-items-center gap-1">
                                        <span class="badge bg-warning-subtle text-warning border border-warning px-2 py-1 rounded-pill small">
                                            Chưa thanh toán
                                        </span>
                                        <form method="POST" action="admin_orders.php">
                                            <input type="hidden" name="action" value="update_payment">
                                            <input type="hidden" name="don_hang_id" value="<?php echo $dh['id']; ?>">
                                            <button type="submit" class="btn btn-xs btn-outline-success rounded-pill px-2 py-0" style="font-size: 0.725rem;" onclick="return confirm('Xác nhận khách hàng này đã thanh toán xong?');">
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
                                    <select name="trang_thai" class="form-select form-select-sm rounded-pill w-auto fw-semibold" onchange="this.form.submit()">
                                        <option value="Chờ xử lý" <?php echo ($dh['trang_thai'] == 'Chờ xử lý') ? 'selected' : ''; ?>>Chờ xử lý</option>
                                        <option value="Đang giao" <?php echo ($dh['trang_thai'] == 'Đang giao') ? 'selected' : ''; ?>>Đang giao</option>
                                        <option value="Hoàn thành" <?php echo ($dh['trang_thai'] == 'Hoàn thành') ? 'selected' : ''; ?>>Hoàn thành</option>
                                        <option value="Đã hủy" <?php echo ($dh['trang_thai'] == 'Đã hủy') ? 'selected' : ''; ?>>Đã hủy</option>
                                    </select>
                                </form>
                            </td>

                            <td class="pe-4 text-end">
                                <a href="admin_order_details.php?id=<?php echo $dh['id']; ?>" class="btn btn-sm btn-light border rounded-circle p-2 text-primary" title="Xem chi tiết đơn hàng">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="py-5 text-muted text-center">
                            <i class="fa-solid fa-box-open fa-3x mb-2 text-light"></i>
                            <p class="mb-0">Không tìm thấy đơn hàng nào phù hợp.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'admin_footer.php'; ?>