<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'db.php';

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

    if ($khach_hang && password_verify($mat_khau, $khach_hang['mat_khau'])) {
        $_SESSION['khach_hang_id'] = $khach_hang['id'];
        $_SESSION['khach_hang_ten'] = $khach_hang['ho_ten'];
        $_SESSION['khach_hang_email'] = $khach_hang['email'];
        
        echo "<script>window.location.href='index.php';</script>";
        exit();
    } else {
        $thong_bao = "<div class='alert alert-danger alert-dismissible fade show rounded-3'><i class='fa-solid fa-circle-exclamation me-2'></i>Email hoặc mật khẩu không chính xác!<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    }
}

include 'header.php';
?>

<div class="container py-5 my-3" style="min-height: 65vh;">
    <div class="row justify-content-center">
        <div class="col-11 col-sm-9 col-md-7 col-lg-5 col-xl-4">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden" style="background: white;">
                <!-- Card Header with Brand -->
                <div class="p-4 text-center pb-2">
                    <div class="d-inline-flex align-items-center justify-content-center brand-icon-wrapper mx-auto mb-3" style="width: 48px; height: 48px; font-size: 1.4rem;">
                        <i class="fa-solid fa-bolt-lightning"></i>
                    </div>
                    <h3 class="fw-extrabold text-dark mb-1 font-heading">Chào Mừng Trở Lại!</h3>
                    <p class="text-muted small">Đăng nhập tài khoản để tiếp tục mua sắm</p>
                </div>

                <div class="card-body p-4 pt-2">
                    <?php echo $thong_bao; ?>
                    
                    <form method="POST" action="login.php">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Địa chỉ Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                                <input type="email" class="form-control border-start-0" name="email" required placeholder="name@example.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label small fw-bold text-dark mb-0">Mật khẩu</label>
                                <a href="#" class="small text-muted text-decoration-none" onclick="alert('Vui lòng liên hệ hotline 1900 8888 để được hỗ trợ lấy lại mật khẩu nhanh nhất.');">Quên mật khẩu?</a>
                            </div>
                            <div class="input-group mt-1">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                                <input type="password" class="form-control border-start-0" name="mat_khau" id="loginPass" required placeholder="Nhập mật khẩu...">
                                <button class="btn btn-outline-secondary border-start-0" type="button" onclick="let p=document.getElementById('loginPass'); p.type = p.type === 'password' ? 'text' : 'password';">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <button type="submit" name="btn_dangnhap" class="btn btn-primary w-100 fw-bold py-3 rounded-pill shadow-sm mb-3">
                            <i class="fa-solid fa-arrow-right-to-bracket me-2"></i> Đăng Nhập
                        </button>
                    </form>

                    <div class="text-center pt-3 border-top">
                        <span class="text-muted small">Bạn chưa có tài khoản?</span>
                        <a href="register.php" class="text-primary fw-bold small text-decoration-none ms-1">Đăng ký tài khoản mới</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>