<?php
require_once 'db.php';

$mat_khau_moi = '123456';
$hash_chuan = password_hash($mat_khau_moi, PASSWORD_DEFAULT);

try {
    // Tạo bảng nếu chưa có
    $conn->exec("CREATE TABLE IF NOT EXISTS admin_users (
        id int(11) NOT NULL AUTO_INCREMENT,
        username varchar(50) NOT NULL UNIQUE,
        password varchar(255) NOT NULL,
        ho_ten varchar(100) DEFAULT 'Administrator',
        email varchar(100) DEFAULT NULL,
        ngay_tao datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Kiểm tra tài khoản admin
    $stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();

    if ($admin) {
        $stmt_update = $conn->prepare("UPDATE admin_users SET password = ? WHERE username = 'admin'");
        $stmt_update->execute([$hash_chuan]);
    } else {
        $stmt_insert = $conn->prepare("INSERT INTO admin_users (username, password, ho_ten, email) VALUES ('admin', ?, 'Admin TechStore', 'admin@techstore.vn')");
        $stmt_insert->execute([$hash_chuan]);
    }

    echo "<div style='font-family: Arial, sans-serif; max-width: 500px; margin: 50px auto; padding: 25px; border: 1px solid #10b981; border-radius: 12px; background: #ecfdf5; text-align: center;'>";
    echo "<h2 style='color: #059669; margin-top: 0;'>🎉 Khôi phục tài khoản Admin thành công!</h2>";
    echo "<p style='font-size: 16px; color: #374151;'>Tài khoản: <strong style='color: #1d4ed8;'>admin</strong></p>";
    echo "<p style='font-size: 16px; color: #374151;'>Mật khẩu mới: <strong style='color: #dc2626;'>123456</strong></p>";
    echo "<a href='admin_login.php' style='display: inline-block; margin-top: 15px; padding: 10px 24px; background: #2563eb; color: white; text-decoration: none; border-radius: 9999px; font-weight: bold;'>Đến Trang Đăng Nhập &rarr;</a>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div style='color: red; padding: 20px;'>Lỗi kết nối CSDL: " . $e->getMessage() . "</div>";
}
?>
