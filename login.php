<?php
include 'header.php';

if (isset($_SESSION['khach_hang_id'])) {
    echo "<script>window.location.href='index.php';</script>";
    exit();
}

$thong_bao = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_dangnhap'])) {
    $email = trim($_POST['email']);
    $mat_khau = $_POST['mat_khau'];

    $stmt = $conn->prepare("SELECT * FROM khach_hang WHERE email = ?");
    $stmt->execute([$email]);
    $khach_hang = $stmt->fetch(PDO::FETCH_ASSOC);

    // Kiểm tra tài khoản có tồn tại và mật khẩu có khớp không
    if ($khach_hang && password_verify($mat_khau, $khach_hang['mat_khau'])) {
        // Đăng nhập thành công -> Lưu Session
        $_SESSION['khach_hang_id'] = $khach_hang['id'];
        $_SESSION['khach_hang_ten'] = $khach_hang['ho_ten'];
        $_SESSION['khach_hang_email'] = $khach_hang['email'];
        
        echo "<script>window.location.href='index.php';</script>";
        exit();
    } else {
        $thong_bao = "<div class='alert alert-danger'>Email hoặc mật khẩu không chính xác!</div>";
    }
}
?>

<div class="row justify-content-center mt-5 mb-5">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow border-0">
            <div class="card-body p-5">
                <h2 class="text-center fw-bold mb-4"><i class="fa-solid fa-right-to-bracket text-primary"></i> Đăng Nhập</h2>
                
                <?php echo $thong_bao; ?>
                
                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" class="form-control form-control-lg" name="email" required placeholder="Nhập email...">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Mật khẩu</label>
                        <input type="password" class="form-control form-control-lg" name="mat_khau" required placeholder="Nhập mật khẩu...">
                    </div>
                    
                    <button type="submit" name="btn_dangnhap" class="btn btn-primary w-100 fw-bold btn-lg">Đăng Nhập</button>
                </form>
                <div class="text-center mt-4">
                    Chưa có tài khoản? <a href="register.php" class="text-decoration-none fw-bold">Đăng ký ngay</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>