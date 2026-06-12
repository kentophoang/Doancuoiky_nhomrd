<?php
// Bật hiển thị lỗi để gỡ lỗi (bạn có thể xóa 3 dòng này khi hệ thống đã chạy ổn định)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Khởi động session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Nhúng file kết nối CSDL (Đảm bảo file db.php đang nằm cùng thư mục)
require_once 'db.php';

// --- BẮT ĐẦU ĐOẠN MÃ KHÓA BẢO MẬT ---
// Kiểm tra nếu chưa có session admin_id thì đá văng về trang đăng nhập
// Sử dụng basename($_SERVER['PHP_SELF']) để tránh lặp vô hạn nếu lỡ gọi header ở trang login
if (!isset($_SESSION['admin_id']) && basename($_SERVER['PHP_SELF']) != 'admin_login.php') {
    header("Location: admin_login.php");
    exit();
}
// --- KẾT THÚC ĐOẠN MÃ KHÓA BẢO MẬT ---
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - TechStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; }
        
        /* Cấu trúc Sidebar */
        .sidebar { min-height: 100vh; background-color: #343a40; color: white; padding-top: 20px; display: flex; flex-direction: column; }
        .sidebar a { color: #ced4da; text-decoration: none; padding: 15px 20px; display: block; border-bottom: 1px solid #495057; transition: 0.2s; }
        .sidebar a:hover { background-color: #495057; color: white; }
        .sidebar .active { background-color: #0d6efd; color: white; border-bottom: none; border-left: 4px solid #fff; }
        
        /* Cấu trúc các thành phần chung */
        .stat-card { border-radius: 10px; border: none; transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-5px); }
        .icon-box { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; }
        .img-thumbnail-custom { width: 60px; height: 60px; object-fit: cover; border-radius: 5px; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">