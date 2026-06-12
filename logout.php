<?php
session_start();
// Chỉ xóa các session liên quan đến khách hàng đăng nhập, không xóa giỏ hàng
unset($_SESSION['khach_hang_id']);
unset($_SESSION['khach_hang_ten']);
unset($_SESSION['khach_hang_email']);

// Chuyển hướng về trang chủ
header("Location: index.php");
exit();
?>