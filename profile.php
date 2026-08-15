<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'db.php';

if (!isset($_SESSION['khach_hang_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

$kh_id = (int)$_SESSION['khach_hang_id'];
$thong_bao = '';

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ho_ten = trim($_POST['ho_ten']);
    $so_dien_thoai = trim($_POST['so_dien_thoai']);
    $dia_chi = trim($_POST['dia_chi']);
    
    if (!empty($_POST['mat_khau_moi'])) {
        if (strlen($_POST['mat_khau_moi']) < 6) {
            $thong_bao = "<div class='alert alert-warning alert-dismissible fade show rounded-3'><i class='fa-solid fa-triangle-exclamation me-2'></i>Mật khẩu mới phải có tối thiểu 6 ký tự!<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
        } else {
            $mat_khau_moi = password_hash($_POST['mat_khau_moi'], PASSWORD_DEFAULT);
            $stmt_up = $conn->prepare("UPDATE khach_hang SET ho_ten=?, so_dien_thoai=?, dia_chi=?, mat_khau=? WHERE id=?");
            $stmt_up->execute([$ho_ten, $so_dien_thoai, $dia_chi, $mat_khau_moi, $kh_id]);
            $_SESSION['khach_hang_ten'] = $ho_ten;
            $thong_bao = "<div class='alert alert-success alert-dismissible fade show rounded-3'><i class='fa-solid fa-circle-check me-2'></i>Cập nhật thông tin và mật khẩu thành công!<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
        }
    } else {
        $stmt_up = $conn->prepare("UPDATE khach_hang SET ho_ten=?, so_dien_thoai=?, dia_chi=? WHERE id=?");
        $stmt_up->execute([$ho_ten, $so_dien_thoai, $dia_chi, $kh_id]);
        $_SESSION['khach_hang_ten'] = $ho_ten;
        $thong_bao = "<div class='alert alert-success alert-dismissible fade show rounded-3'><i class='fa-solid fa-circle-check me-2'></i>Cập nhật hồ sơ thành công!<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    }
}

// Lấy thông tin hiện tại
$stmt = $conn->prepare("SELECT * FROM khach_hang WHERE id = ?");
$stmt->execute([$kh_id]);
$khach_hang = $stmt->fetch(PDO::FETCH_ASSOC);

include 'header.php';
?>

<div class="container py-4" style="min-height: 65vh;">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php"><i class="fa-solid fa-house me-1"></i> Trang chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page">Hồ sơ cá nhân</li>
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
                    <a href="profile.php" class="list-group-item list-group-item-action border-0 rounded-3 py-3 fw-bold active d-flex align-items-center gap-3">
                        <i class="fa-solid fa-user-pen"></i> Hồ sơ của tôi
                    </a>
                    <a href="order_history.php" class="list-group-item list-group-item-action border-0 rounded-3 py-3 fw-semibold text-muted d-flex align-items-center gap-3">
                        <i class="fa-solid fa-clock-rotate-left text-primary"></i> Đơn mua của tôi
                    </a>
                    <a href="logout.php" class="list-group-item list-group-item-action border-0 rounded-3 py-3 fw-semibold text-danger d-flex align-items-center gap-3">
                        <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Column: Profile Edit Form -->
        <div class="col-lg-8 col-xl-9">
            <div class="card border-0 shadow-sm rounded-4 p-4" style="background: white;">
                <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
                    <div>
                        <h4 class="fw-bold text-dark mb-1"><i class="fa-solid fa-id-card text-primary me-2"></i> Thông Tin Tài Khoản</h4>
                        <p class="text-muted small mb-0">Quản lý và cập nhật thông tin cá nhân của bạn</p>
                    </div>
                </div>

                <?php echo $thong_bao; ?>

                <form method="POST" action="profile.php">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Họ và tên <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-user text-muted"></i></span>
                                <input type="text" class="form-control border-start-0" name="ho_ten" required value="<?php echo htmlspecialchars($khach_hang['ho_ten']); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Địa chỉ Email (Cố định)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                                <input type="email" class="form-control border-start-0 bg-light" disabled value="<?php echo htmlspecialchars($khach_hang['email']); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Số điện thoại liên hệ</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-phone text-muted"></i></span>
                            <input type="tel" class="form-control border-start-0" name="so_dien_thoai" placeholder="09xxxxxxxx" value="<?php echo htmlspecialchars($khach_hang['so_dien_thoai']); ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-dark">Địa chỉ nhận hàng mặc định</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-location-dot text-muted"></i></span>
                            <textarea class="form-control border-start-0" name="dia_chi" rows="2" placeholder="Nhập địa chỉ giao hàng..."><?php echo htmlspecialchars($khach_hang['dia_chi']); ?></textarea>
                        </div>
                    </div>

                    <!-- Password Update Section -->
                    <div class="p-3 rounded-3 mb-4" style="background: #f8fafc; border: 1px dashed var(--border-subtle);">
                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-lock text-primary me-2"></i> Đổi Mật Khẩu (Tùy chọn)</h6>
                        <p class="text-muted small mb-3">Nếu không muốn thay đổi mật khẩu, vui lòng để trống ô bên dưới.</p>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-key text-muted"></i></span>
                            <input type="password" class="form-control border-start-0" name="mat_khau_moi" placeholder="Nhập mật khẩu mới (tối thiểu 6 ký tự)...">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold rounded-pill shadow-sm">
                            <i class="fa-solid fa-floppy-disk me-2"></i> Lưu Thay Đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>