# 🛒 TechStore - E-Commerce Website
<p align="center">
  <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/Bootstrap_5-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white" />
  <img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" />
</p>

*Read this in other languages: [English](#english) | [Tiếng Việt](#tiếng-việt)*

---

<a id="english"></a>
## 🇬🇧 English Version

### 📝 About The Project
TechStore is a comprehensive final-year e-commerce project designed for selling electronic devices. It features a fully responsive customer-facing storefront and a secure, powerful Admin Dashboard for store management. The project emphasizes UI/UX, robust database relationships, and automated business workflows.

### 🚀 Key Features

**Client-Side (Customers)**
* **Dynamic Home Page:** Features promotional carousels and highlights the newest products.
* **Product Browsing:** Filter products by multi-level categories and search via AJAX.
* **Product Details:** Dynamic technical specifications (stored as JSON) and a user rating/review system.
* **Shopping Cart & Checkout:** Session-based cart management and secure checkout process.
* **Automated Emails:** Integrates **PHPMailer** to automatically send electronic receipts upon successful checkout.

**Server-Side (Admin Dashboard)**
* **Interactive Dashboard:** Revenue and order statistics visualized using **Chart.js**.
* **Category Management:** Support for parent-child category hierarchies.
* **Product Management:** Full CRUD operations with image upload handling and dynamic JSON specification forms. Safe deletion via Cascade constraints.
* **Order & Banner Management:** Order status tracking and dynamic homepage banner toggling.

### 🛠 Built With
* **Backend:** PHP (PDO for secure database connection)
* **Database:** MySQL (Relational DB with Foreign Keys & Transactions)
* **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5
* **Third-party Libraries:** PHPMailer, Chart.js

### ⚙️ Getting Started
To get a local copy up and running, follow these simple steps:

1. **Clone the repo**
   ```sh
   git clone [https://github.com/kentophoang/doancuoiky_nhomrd.git](https://github.com/kentophoang/doancuoiky_nhomrd.git)
Setup Database

Open phpMyAdmin and create a new database (e.g., web_dientu).

Import the provided .sql database file.

Configure Database Connection

Open db.php and update the connection credentials (Host, DB Name, User, Password).

Run the Application

Start your Apache and MySQL servers (via XAMPP/WAMP).

Navigate to http://localhost/doancuoiky_nhomrd/index.php.

🇻🇳 Tiếng Việt
📝 Giới thiệu Dự án
TechStore là đồ án cuối kỳ xây dựng một hệ thống website thương mại điện tử chuyên cung cấp các thiết bị điện tử. Dự án bao gồm một giao diện mua sắm thân thiện cho khách hàng (Client-side) và một hệ thống Quản trị (Admin Dashboard) mạnh mẽ, bảo mật. Dự án tập trung vào trải nghiệm người dùng, thiết kế cơ sở dữ liệu chặt chẽ và tự động hóa quy trình nghiệp vụ.

🚀 Các tính năng chính
Phía Khách hàng (Client-side)

Trang chủ động: Hiển thị banner khuyến mãi (Carousel) và danh sách sản phẩm mới nhất.

Duyệt sản phẩm: Lọc sản phẩm theo danh mục đa cấp và tìm kiếm nhanh qua AJAX.

Chi tiết sản phẩm: Hiển thị thông số kỹ thuật động (dữ liệu JSON) và hệ thống đánh giá/bình luận (Rating & Review) của người dùng.

Giỏ hàng & Thanh toán: Quản lý giỏ hàng bằng Session và quy trình đặt hàng an toàn (Sử dụng Transaction).

Email Tự động: Tích hợp thư viện PHPMailer để tự động gửi hóa đơn điện tử ngay khi khách hàng đặt đơn thành công.

Phía Quản trị (Admin Dashboard)

Bảng điều khiển (Dashboard): Trực quan hóa dữ liệu thống kê doanh thu và đơn hàng bằng biểu đồ Chart.js.

Quản lý Danh mục: Hỗ trợ tạo cấu trúc cây danh mục (Cha - Con).

Quản lý Sản phẩm: Thêm/Sửa/Xóa sản phẩm kèm upload hình ảnh và tự động sinh form thông số kỹ thuật (JSON). Đảm bảo tính toàn vẹn dữ liệu qua cơ chế xóa Cascade.

Quản lý Đơn hàng & Banner: Theo dõi, cập nhật trạng thái giao hàng và bật/tắt banner quảng cáo ngoài trang chủ linh hoạt.

🛠 Công nghệ sử dụng
Backend: PHP (Sử dụng PDO kết hợp Prepared Statements chống SQL Injection)

Cơ sở dữ liệu: MySQL

Frontend: HTML5, CSS3, JavaScript, Bootstrap 5

Thư viện hỗ trợ: PHPMailer, Chart.js

⚙️ Hướng dẫn cài đặt
Để chạy dự án trên môi trường local (máy cá nhân), thực hiện các bước sau:

Clone mã nguồn

Bash
git clone [https://github.com/kentophoang/doancuoiky_nhomrd.git](https://github.com/kentophoang/doancuoiky_nhomrd.git)
Thiết lập Cơ sở dữ liệu

Mở phpMyAdmin, tạo một cơ sở dữ liệu mới (ví dụ: web_dientu).

Import file .sql (nếu có) vào cơ sở dữ liệu vừa tạo.

Cấu hình Kết nối

Mở file db.php và thay đổi thông tin cấu hình (Host, Tên DB, User, Mật khẩu) cho khớp với máy của bạn.

Khởi chạy ứng dụng

Bật Apache và MySQL trên XAMPP/WAMP.

Truy cập đường dẫn: http://localhost/doancuoiky_nhomrd/index.php.
