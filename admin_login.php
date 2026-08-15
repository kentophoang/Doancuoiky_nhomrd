<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'db.php';

// Nếu đã đăng nhập thì chuyển hướng vào Bảng điều khiển
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
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['ho_ten'];
        header("Location: admin_index.php");
        exit();
    } else {
        $thong_bao = "<div class='alert alert-danger alert-dismissible fade show rounded-3'><i class='fa-solid fa-circle-exclamation me-2'></i>Tên đăng nhập hoặc mật khẩu quản trị không đúng!<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập Quản Trị - TechStore Admin</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&family=Outfit:wght@500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background: #0f172a;
            color: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: -20%;
            left: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.2) 0%, rgba(0,0,0,0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        body::after {
            content: '';
            position: absolute;
            bottom: -20%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(124, 58, 237, 0.18) 0%, rgba(0,0,0,0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: rgba(30, 41, 59, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 2;
            overflow: hidden;
        }

        .brand-logo-icon {
            width: 52px;
            height: 52px;
            background: #2563eb;
            color: white;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
            margin-bottom: 16px;
        }

        .form-control {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #ffffff !important;
            padding: 12px 16px;
            border-radius: 12px;
        }

        .form-control:focus {
            background: rgba(15, 23, 42, 0.9);
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.25);
            color: #ffffff;
        }

        .input-group-text {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #94a3b8;
            border-radius: 12px;
        }

        .btn-admin-login {
            background: #2563eb;
            color: white;
            font-weight: 700;
            padding: 12px;
            border-radius: 9999px;
            border: none;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4);
            transition: all 0.2s ease;
        }

        .btn-admin-login:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.5);
            color: white;
        }
    </style>
</head>
<body>

<div class="login-card p-4 p-sm-5">
    <div class="text-center mb-4">
        <div class="brand-logo-icon">
            <i class="fa-solid fa-bolt-lightning"></i>
        </div>
        <h3 class="fw-extrabold text-white mb-1" style="font-family: 'Outfit', sans-serif;">TechStore Admin</h3>
        <p class="text-muted small mb-0">Cổng truy cập hệ thống quản trị nội bộ</p>
    </div>

    <?php echo $thong_bao; ?>

    <form method="POST" action="admin_login.php">
        <div class="mb-3">
            <label class="form-label small fw-bold text-light">Tên đăng nhập</label>
            <div class="input-group">
                <span class="input-group-text border-end-0"><i class="fa-solid fa-user"></i></span>
                <input type="text" class="form-control border-start-0" name="username" required placeholder="admin..." value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label small fw-bold text-light">Mật khẩu bảo mật</label>
            <div class="input-group">
                <span class="input-group-text border-end-0"><i class="fa-solid fa-lock"></i></span>
                <input type="password" class="form-control border-start-0 border-end-0" name="password" id="adminPass" required placeholder="••••••••">
                <button class="btn btn-outline-secondary input-group-text border-start-0" type="button" onclick="let p=document.getElementById('adminPass'); p.type = p.type === 'password' ? 'text' : 'password';">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
        </div>

        <button type="submit" name="btn_login" class="btn btn-admin-login w-100 mb-3">
            <i class="fa-solid fa-right-to-bracket me-2"></i> Đăng Nhập Hệ Thống
        </button>
    </form>

    <div class="text-center pt-3 border-top border-secondary border-opacity-25">
        <a href="index.php" class="text-muted small text-decoration-none">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại Cửa Hàng TechStore
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>