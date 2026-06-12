<?php
session_start();
include 'db.php';

// Nếu đã đăng nhập thì đẩy thẳng vào Bảng điều khiển
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_index.php");
    exit();
}

$thong_bao = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        // Lưu phiên đăng nhập
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['ho_ten'];
        header("Location: admin_index.php");
        exit();
    } else {
        $thong_bao = "<div class='alert alert-danger text-center'>Tài khoản hoặc mật khẩu không đúng!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Quản Trị - TechStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #343a40; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-card { width: 100%; max-width: 400px; border-radius: 10px; overflow: hidden; }
        .login-header { background-color: #212529; color: white; padding: 20px; text-align: center; }
    </style>
</head>
<body>

<div class="card login-card shadow-lg border-0">
    <div class="login-header">
        <h3 class="mb-0"><i class="fa-solid fa-user-shield text-warning"></i> ADMIN PANEL</h3>
    </div>
    <div class="card-body p-4">
        <?php echo $thong_bao; ?>
        <form method="POST" action="admin_login.php">
            <div class="mb-3">
                <label class="form-label fw-bold">Tên đăng nhập</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fa-solid fa-user"></i></span>
                    <input type="text" class="form-control" name="username" required placeholder="Nhập admin...">
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label fw-bold">Mật khẩu</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" class="form-control" name="password" required placeholder="Nhập 123456...">
                </div>
            </div>
            <button type="submit" name="btn_login" class="btn btn-primary w-100 fw-bold btn-lg">ĐĂNG NHẬP</button>
        </form>
        <div class="text-center mt-3">
            <a href="index.php" class="text-decoration-none text-muted"><i class="fa-solid fa-arrow-left"></i> Quay lại Cửa hàng</a>
        </div>
    </div>
</div>

</body>
</html>