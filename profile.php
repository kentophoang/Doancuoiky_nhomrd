<?php
include 'header.php';

if (!isset($_SESSION['khach_hang_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

$kh_id = $_SESSION['khach_hang_id'];
$thong_bao = '';

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ho_ten = trim($_POST['ho_ten']);
    $so_dien_thoai = trim($_POST['so_dien_thoai']);
    $dia_chi = trim($_POST['dia_chi']);
    
    if (!empty($_POST['mat_khau_moi'])) {
        $mat_khau_moi = password_hash($_POST['mat_khau_moi'], PASSWORD_DEFAULT);
        $stmt_up = $conn->prepare("UPDATE khach_hang SET ho_ten=?, so_dien_thoai=?, dia_chi=?, mat_khau=? WHERE id=?");
        $stmt_up->execute([$ho_ten, $so_dien_thoai, $dia_chi, $mat_khau_moi, $kh_id]);
    } else {
        $stmt_up = $conn->prepare("UPDATE khach_hang SET ho_ten=?, so_dien_thoai=?, dia_chi=? WHERE id=?");
        $stmt_up->execute([$ho_ten, $so_dien_thoai, $dia_chi, $kh_id]);
    }
    $_SESSION['khach_hang_ten'] = $ho_ten; // Cập nhật lại tên trên Header
    $thong_bao = "<div class='alert alert-success'>Cập nhật hồ sơ thành công!</div>";
}

// Lấy thông tin hiện tại
$stmt = $conn->prepare("SELECT * FROM khach_hang WHERE id = ?");
$stmt->execute([$kh_id]);
$khach_hang = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="list-group shadow-sm">
            <a href="profile.php" class="list-group-item list-group-item-action active"><i class="fa-solid fa-user-pen me-2"></i> Hồ sơ của tôi</a>
            <a href="order_history.php" class="list-group-item list-group-item-action"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Lịch sử đơn hàng</a>
            <a href="logout.php" class="list-group-item list-group-item-action text-danger"><i class="fa-solid fa-right-from-bracket me-2"></i> Đăng xuất</a>
        </div>
    </div>

    <div class="col-md-9">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold fs-5 py-3">Hồ Sơ Của Tôi</div>
            <div class="card-body p-4">
                <?php echo $thong_bao; ?>
                <form method="POST" action="profile.php">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Họ và tên</label>
                            <input type="text" class="form-control" name="ho_ten" required value="<?php echo htmlspecialchars($khach_hang['ho_ten']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email (Không thể thay đổi)</label>
                            <input type="email" class="form-control bg-light" disabled value="<?php echo htmlspecialchars($khach_hang['email']); ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Số điện thoại</label>
                        <input type="tel" class="form-control" name="so_dien_thoai" value="<?php echo htmlspecialchars($khach_hang['so_dien_thoai']); ?>">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Địa chỉ giao hàng mặc định</label>
                        <textarea class="form-control" name="dia_chi" rows="2"><?php echo htmlspecialchars($khach_hang['dia_chi']); ?></textarea>
                    </div>
                    <hr>
                    <div class="mb-4">
                        <label class="form-label fw-bold text-danger">Đổi mật khẩu mới (Tùy chọn)</label>
                        <input type="password" class="form-control" name="mat_khau_moi" placeholder="Bỏ trống nếu không muốn đổi mật khẩu...">
                    </div>
                    <button type="submit" class="btn btn-success fw-bold px-4">Lưu Thay Đổi</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>