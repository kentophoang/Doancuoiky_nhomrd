🛒 TechStore – E-Commerce Website
<p align="center"> <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" /> <img src="https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white" /> <img src="https://img.shields.io/badge/Bootstrap%205-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white" /> <img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" /> </p> <p align="center"> <i>Read this README in:</i> <a href="#english">English</a> | <a href="#tiếng-việt">Tiếng Việt</a> </p>

<a id="english"></a>

🇬🇧 English
📌 About The Project

TechStore is a comprehensive final-year e-commerce project for selling electronic devices.
The system includes a responsive customer-facing storefront and a secure, feature-rich Admin Dashboard for store management.

The project focuses on:

Clean and user-friendly UI/UX
Well-designed relational database architecture
Automated and reliable business workflows
✨ Key Features
👤 Client Side (Customers)
Dynamic Home Page
Promotional banners (carousel) and latest products.
Product Browsing
Multi-level category filtering and fast AJAX search.
Product Details
Dynamic technical specifications stored in JSON format, along with rating & review system.
Shopping Cart & Checkout
Session-based cart management and secure checkout flow.
Automated Email System
Automatic electronic invoice delivery using PHPMailer after successful checkout.
🛠 Admin Dashboard
Interactive Dashboard
Revenue and order statistics visualized using Chart.js.
Category Management
Parent–child hierarchical category structure.
Product Management
Full CRUD operations, image uploads, dynamic JSON specification forms, and safe deletion using cascade constraints.
Order & Banner Management
Order status tracking and flexible homepage banner activation.
🧰 Tech Stack
Backend: PHP (PDO for secure database operations)
Database: MySQL (Relational database with foreign keys & transactions)
Frontend: HTML5, CSS3, JavaScript, Bootstrap 5
Libraries: PHPMailer, Chart.js
⚙️ Getting Started

Follow the steps below to run the project locally.

1️⃣ Clone the Repository
git clone https://github.com/kentophoang/doancuoiky_nhomrd.git
2️⃣ Set Up the Database
Open phpMyAdmin
Create a new database (e.g. web_dientu)
Import the provided .sql file
3️⃣ Configure Database Connection

Edit db.php:

$host = "localhost";
$dbname = "web_dientu";
$user = "root";
$password = "";
4️⃣ Run the Application
Start Apache and MySQL (XAMPP / WAMP)
Open your browser and navigate to:
http://localhost/doancuoiky_nhomrd/index.php

<a id="tiếng-việt"></a>

🇻🇳 Tiếng Việt
📌 Giới thiệu Dự án

TechStore là đồ án cuối kỳ xây dựng website thương mại điện tử chuyên cung cấp các thiết bị điện tử.
Hệ thống bao gồm:

Giao diện mua sắm thân thiện cho khách hàng
Trang quản trị (Admin Dashboard) mạnh mẽ và bảo mật

Dự án tập trung vào trải nghiệm người dùng, thiết kế cơ sở dữ liệu chặt chẽ và tự động hóa quy trình nghiệp vụ.

✨ Các Tính Năng Chính
👤 Phía Khách Hàng (Client Side)
Trang chủ động
Hiển thị banner khuyến mãi (carousel) và sản phẩm mới.
Duyệt sản phẩm
Lọc theo danh mục đa cấp và tìm kiếm nhanh bằng AJAX.
Chi tiết sản phẩm
Thông số kỹ thuật động (JSON) và hệ thống đánh giá/bình luận.
Giỏ hàng & Thanh toán
Quản lý giỏ hàng bằng Session và xử lý đặt hàng an toàn (Transaction).
Email tự động
Gửi hóa đơn điện tử bằng PHPMailer sau khi đặt hàng thành công.
🛠 Phía Quản Trị (Admin Dashboard)
Bảng điều khiển (Dashboard)
Thống kê doanh thu và đơn hàng bằng biểu đồ Chart.js.
Quản lý danh mục
Hỗ trợ cấu trúc danh mục cha – con.
Quản lý sản phẩm
Thêm / Sửa / Xóa sản phẩm, upload hình ảnh, sinh form thông số kỹ thuật JSON, đảm bảo toàn vẹn dữ liệu với Cascade.
Quản lý đơn hàng & banner
Cập nhật trạng thái đơn hàng và bật/tắt banner trang chủ linh hoạt.
🧰 Công Nghệ Sử Dụng
Backend: PHP (PDO + Prepared Statements chống SQL Injection)
Cơ sở dữ liệu: MySQL
Frontend: HTML5, CSS3, JavaScript, Bootstrap 5
Thư viện: PHPMailer, Chart.js
⚙️ Hướng Dẫn Cài Đặt
1️⃣ Clone mã nguồn
git clone https://github.com/kentophoang/doancuoiky_nhomrd.git
2️⃣ Thiết lập cơ sở dữ liệu
Mở phpMyAdmin
Tạo database mới (ví dụ: web_dientu)
Import file .sql
3️⃣ Cấu hình kết nối

Chỉnh sửa file db.php cho phù hợp với môi trường local.

4️⃣ Khởi chạy ứng dụng
Bật Apache và MySQL (XAMPP / WAMP)
Truy cập:
http://localhost/doancuoiky_nhomrd/index.php
