<?php
require 'db.php';

// Tạo mã hóa chuẩn xác cho mật khẩu 123456
$mat_khau_moi = '123456';
$hash_chuan = password_hash($mat_khau_moi, PASSWORD_DEFAULT);

// Cập nhật vào database
$stmt = $conn->prepare("UPDATE admin_users SET password = ? WHERE username = 'admin'");
$stmt->execute([$hash_chuan]);

echo "<h3>Đã đổi mật khẩu admin thành công!</h3>";
echo "<p>Mật khẩu mới của bạn là: <strong>123456</strong></p>";
echo "<a href='admin_login.php'>Bấm vào đây để đi tới trang đăng nhập</a>";
?>