<?php
include 'header.php';

// Nếu đã đăng nhập thì không cho vào trang đăng ký, đẩy về trang chủ
if (isset($_SESSION['khach_hang_id'])) {
    echo "<script>window.location.href='index.php';</script>";
    exit();
}

$thong_bao = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_dangky'])) {
    $ho_ten = trim($_POST['ho_ten']);
    $email = trim($_POST['email']);
    $mat_khau = $_POST['mat_khau'];
    $xac_nhan_mat_khau = $_POST['xac_nhan_mat_khau'];
    $so_dien_thoai = trim($_POST['so_dien_thoai']);

    if ($mat_khau !== $xac_nhan_mat_khau) {
        $thong_bao = "<div class='alert alert-danger'>Mật khẩu xác nhận không khớp!</div>";
    } else {
        $mat_khau_hashed = password_hash($mat_khau, PASSWORD_DEFAULT);
        try {
            $stmt = $conn->prepare("INSERT INTO khach_hang (ho_ten, email, mat_khau, so_dien_thoai) VALUES (?, ?, ?, ?)");
            $stmt->execute([$ho_ten, $email, $mat_khau_hashed, $so_dien_thoai]);
            
            $thong_bao = "<div class='alert alert-success'>Đăng ký thành công! <a href='login.php' class='alert-link'>Bấm vào đây để đăng nhập</a>.</div>";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $thong_bao = "<div class='alert alert-danger'>Email này đã được sử dụng. Vui lòng chọn email khác!</div>";
            } else {
                $thong_bao = "<div class='alert alert-danger'>Lỗi hệ thống: " . $e->getMessage() . "</div>";
            }
        }
    }
}
?>

<div class="row justify-content-center mt-5 mb-5">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow border-0">
            <div class="card-body p-5">
                <h2 class="text-center fw-bold mb-4"><i class="fa-solid fa-user-plus text-primary"></i> Đăng Ký</h2>
                
                <?php echo $thong_bao; ?>
                
                <form method="POST" action="register.php">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="ho_ten" required placeholder="Nhập họ và tên...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" required placeholder="email@example.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Số điện thoại</label>
                        <input type="tel" class="form-control" name="so_dien_thoai" placeholder="09xxxxxxx">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mật khẩu <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="mat_khau" required placeholder="Tạo mật khẩu...">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="xac_nhan_mat_khau" required placeholder="Nhập lại mật khẩu...">
                    </div>
                    
                    <button type="submit" name="btn_dangky" class="btn btn-primary w-100 fw-bold btn-lg">Đăng Ký Tài Khoản</button>
                </form>
                <div class="text-center mt-4">
                    Đã có tài khoản? <a href="login.php" class="text-decoration-none fw-bold">Đăng nhập ngay</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>