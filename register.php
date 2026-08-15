<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'db.php';

// Nếu đã đăng nhập thì không cho vào trang đăng ký
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

    if (strlen($mat_khau) < 6) {
        $thong_bao = "<div class='alert alert-warning alert-dismissible fade show rounded-3'><i class='fa-solid fa-triangle-exclamation me-2'></i>Mật khẩu phải có độ dài tối thiểu từ 6 ký tự trở lên!<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    } elseif ($mat_khau !== $xac_nhan_mat_khau) {
        $thong_bao = "<div class='alert alert-danger alert-dismissible fade show rounded-3'><i class='fa-solid fa-circle-exclamation me-2'></i>Mật khẩu xác nhận không khớp!<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    } else {
        $mat_khau_hashed = password_hash($mat_khau, PASSWORD_DEFAULT);
        try {
            $stmt = $conn->prepare("INSERT INTO khach_hang (ho_ten, email, mat_khau, so_dien_thoai) VALUES (?, ?, ?, ?)");
            $stmt->execute([$ho_ten, $email, $mat_khau_hashed, $so_dien_thoai]);
            
            $thong_bao = "<div class='alert alert-success rounded-3'>
                            <i class='fa-solid fa-circle-check me-2'></i>Đăng ký thành công! 
                            <a href='login.php' class='alert-link fw-bold ms-2'>Bấm vào đây để đăng nhập</a>.
                          </div>";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $thong_bao = "<div class='alert alert-danger alert-dismissible fade show rounded-3'><i class='fa-solid fa-circle-exclamation me-2'></i>Email này đã tồn tại trong hệ thống. Vui lòng sử dụng email khác!<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
            } else {
                $thong_bao = "<div class='alert alert-danger alert-dismissible fade show rounded-3'><i class='fa-solid fa-circle-exclamation me-2'></i>Lỗi hệ thống: " . htmlspecialchars($e->getMessage()) . "<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
            }
        }
    }
}

include 'header.php';
?>

<div class="container py-5 my-3" style="min-height: 65vh;">
    <div class="row justify-content-center">
        <div class="col-11 col-sm-9 col-md-7 col-lg-5 col-xl-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden" style="background: white;">
                <!-- Header with Brand -->
                <div class="p-4 text-center pb-2">
                    <div class="d-inline-flex align-items-center justify-content-center brand-icon-wrapper mx-auto mb-3" style="width: 48px; height: 48px; font-size: 1.4rem;">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <h3 class="fw-extrabold text-dark mb-1 font-heading">Tạo Tài Khoản Mới</h3>
                    <p class="text-muted small">Đăng ký để nhận voucher giảm giá và tích lũy điểm thưởng</p>
                </div>

                <div class="card-body p-4 pt-2">
                    <?php echo $thong_bao; ?>
                    
                    <form method="POST" action="register.php">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Họ và tên <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-user text-muted"></i></span>
                                <input type="text" class="form-control border-start-0" name="ho_ten" required placeholder="Nguyễn Văn A" value="<?php echo isset($_POST['ho_ten']) ? htmlspecialchars($_POST['ho_ten']) : ''; ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Địa chỉ Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                                <input type="email" class="form-control border-start-0" name="email" required placeholder="name@example.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Số điện thoại</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-phone text-muted"></i></span>
                                <input type="tel" class="form-control border-start-0" name="so_dien_thoai" placeholder="09xxxxxxxx" value="<?php echo isset($_POST['so_dien_thoai']) ? htmlspecialchars($_POST['so_dien_thoai']) : ''; ?>">
                            </div>
                        </div>

                        <div class="row g-2 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Mật khẩu <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                                    <input type="password" class="form-control border-start-0" name="mat_khau" required placeholder="Tối thiểu 6 ký tự">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-shield-check text-muted"></i></span>
                                    <input type="password" class="form-control border-start-0" name="xac_nhan_mat_khau" required placeholder="Nhập lại mật khẩu">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" name="btn_dangky" class="btn btn-primary w-100 fw-bold py-3 rounded-pill shadow-sm mb-3">
                            <i class="fa-solid fa-circle-check me-2"></i> Đăng Ký Tài Khoản
                        </button>
                    </form>

                    <div class="text-center pt-3 border-top">
                        <span class="text-muted small">Bạn đã có tài khoản?</span>
                        <a href="login.php" class="text-primary fw-bold small text-decoration-none ms-1">Đăng nhập ngay</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>