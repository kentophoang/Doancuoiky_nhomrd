<?php
$host = 'localhost';
$dbname = 'web_dientu';
$username = 'root'; // User mặc định của XAMPP
$password = '';     // Password mặc định của XAMPP là rỗng

try {
    // Tạo kết nối PDO
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Thiết lập chế độ báo lỗi
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}
?>