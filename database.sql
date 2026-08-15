-- =========================================================
-- TechStore E-Commerce Database Schema & Sample Data
-- Database Name: web_dientu
-- =========================================================

CREATE DATABASE IF NOT EXISTS `web_dientu` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `web_dientu`;

-- 1. Bảng Admin Users
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `ho_ten` varchar(100) DEFAULT 'Administrator',
  `email` varchar(100) DEFAULT NULL,
  `ngay_tao` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm tài khoản Admin mặc định: admin / 123456
INSERT INTO `admin_users` (`username`, `password`, `ho_ten`, `email`) 
VALUES ('admin', '$2y$10$wT0fQh1n1r6a1nFkYJc7beoN59gA6uX.v8wF2eE2h.bQ7aZ1pZ6eq', 'Admin TechStore', 'admin@techstore.vn')
ON DUPLICATE KEY UPDATE `username`=`username`;

-- 2. Bảng Danh Mục
CREATE TABLE IF NOT EXISTS `danh_muc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ten_danh_muc` varchar(100) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `thuoc_tinh` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `danh_muc` (`id`, `ten_danh_muc`, `parent_id`, `thuoc_tinh`) VALUES
(1, 'Laptop', NULL, '[\"CPU\",\"RAM\",\"Ổ cứng\",\"Card đồ họa\",\"Màn hình\",\"Trọng lượng\"]'),
(2, 'Điện Thoại', NULL, '[\"Màn hình\",\"Camera sau\",\"Camera trước\",\"Chipset\",\"RAM\",\"Bộ nhớ trong\",\"Pin\"]'),
(3, 'Tai Nghe & Âm Thanh', NULL, '[\"Loại kết nối\",\"Thời lượng pin\",\"Chống ồn\",\"Kháng nước\"]'),
(4, 'Phụ Kiện & Bàn Phím', NULL, '[\"Loại switch\",\"Kết nối\",\"Đèn LED\",\"Chất liệu keycap\"]')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- 3. Bảng Sản Phẩm
CREATE TABLE IF NOT EXISTS `san_pham` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ten_sp` varchar(255) NOT NULL,
  `gia` decimal(12,2) NOT NULL DEFAULT 0.00,
  `so_luong_ton` int(11) NOT NULL DEFAULT 10,
  `danh_muc_id` int(11) DEFAULT NULL,
  `hinh_anh` varchar(255) DEFAULT NULL,
  `mo_ta` text DEFAULT NULL,
  `thong_so_ky_thuat` text DEFAULT NULL,
  `thong_so` text DEFAULT NULL,
  `ngay_tao` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `danh_muc_id` (`danh_muc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `san_pham` (`id`, `ten_sp`, `gia`, `so_luong_ton`, `danh_muc_id`, `hinh_anh`, `mo_ta`, `thong_so_ky_thuat`) VALUES
(1, 'Laptop ASUS ROG Strix G16 (2026)', 32990000.00, 15, 1, '1781232449_7416.webp', 'Laptop gaming đỉnh cao với bộ vi xử lý thế hệ mới, màn hình 240Hz sắc nét và hệ thống tản nhiệt buồng hơi tiên tiến.', '{\"CPU\":\"Intel Core i9 14900HX\",\"RAM\":\"32GB DDR5\",\"Ổ cứng\":\"1TB NVMe PCIe 4.0\",\"Card đồ họa\":\"NVIDIA RTX 4070 8GB\",\"Màn hình\":\"16 inch QHD+ 240Hz\",\"Trọng lượng\":\"2.5 kg\"}'),
(2, 'iPhone 15 Pro Max 256GB Titan Tự Nhiên', 28990000.00, 20, 2, '1781232598_4498.webp', 'Khung viền Titan chuẩn hàng không vũ trụ siêu bền nhẹ, chip Apple A17 Pro mạnh mẽ cùng cụm 3 camera đỉnh cao.', '{\"Màn hình\":\"6.7 inch Super Retina XDR OLED 120Hz\",\"Camera sau\":\"48MP + 12MP + 12MP (Zoom 5x)\",\"Camera trước\":\"12MP TrueDepth\",\"Chipset\":\"Apple A17 Pro\",\"RAM\":\"8GB\",\"Bộ nhớ trong\":\"256GB\",\"Pin\":\"4422 mAh (Hỗ trợ sạc nhanh)\"}'),
(3, 'Tai Nghe Sony WH-1000XM5 Chống Ồn', 6990000.00, 12, 3, '1781232726_3616.webp', 'Tai nghe chụp tai chống ồn hàng đầu thế giới với vi xử lý V1, chất âm Hi-Res Audio đỉnh cao và pin tới 30 giờ.', '{\"Loại kết nối\":\"Bluetooth 5.2 / Jack 3.5mm\",\"Thời lượng pin\":\"30 giờ (Bật chống ồn)\",\"Chống ồn\":\"Active Noise Cancelling (ANC)\",\"Kháng nước\":\"IPX4\"}'),
(4, 'Bàn Phím Cơ Không Dây Keychron Q1 Pro', 4290000.00, 18, 4, '1781232855_6234.webp', 'Bàn phím cơ full nhôm CNC cao cấp, kết nối Bluetooth 5.1 và có dây Type-C, tương thích hoàn hảo cả Mac & Windows.', '{\"Loại switch\":\"Keychron K Pro Red (Hotswap)\",\"Kết nối\":\"Bluetooth 5.1 & Type-C\",\"Đèn LED\":\"RGB 16.8 triệu màu\",\"Chất liệu keycap\":\"PBT Double-shot OSA Profile\"}')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- 4. Bảng Banners Khuyến Mãi
CREATE TABLE IF NOT EXISTS `banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hinh_anh` varchar(255) NOT NULL,
  `duong_link` varchar(255) DEFAULT '#',
  `trang_thai` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `banners` (`id`, `hinh_anh`, `duong_link`, `trang_thai`) VALUES
(1, 'banner_1781233928.png', 'index.php?cat_id=1', 1),
(2, 'banner_1781234085.png', 'index.php?cat_id=2', 1)
ON DUPLICATE KEY UPDATE `id`=`id`;

-- 5. Bảng Khách Hàng
CREATE TABLE IF NOT EXISTS `khach_hang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ho_ten` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `mat_khau` varchar(255) NOT NULL,
  `so_dien_thoai` varchar(20) DEFAULT NULL,
  `dia_chi` text DEFAULT NULL,
  `ngay_tao` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Bảng Đơn Hàng
CREATE TABLE IF NOT EXISTS `don_hang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `khach_hang_id` int(11) DEFAULT NULL,
  `ten_khach_hang` varchar(100) NOT NULL,
  `so_dien_thoai` varchar(20) NOT NULL,
  `dia_chi` text NOT NULL,
  `ghi_chu` text DEFAULT NULL,
  `tong_tien` decimal(12,2) NOT NULL DEFAULT 0.00,
  `trang_thai` varchar(50) DEFAULT 'Chờ xử lý',
  `trang_thai_thanh_toan` varchar(50) DEFAULT 'Chưa thanh toán',
  `ngay_dat` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `khach_hang_id` (`khach_hang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Bảng Chi Tiết Đơn Hàng
CREATE TABLE IF NOT EXISTS `chi_tiet_don_hang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `don_hang_id` int(11) NOT NULL,
  `san_pham_id` int(11) NOT NULL,
  `so_luong` int(11) NOT NULL DEFAULT 1,
  `gia` decimal(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `don_hang_id` (`don_hang_id`),
  KEY `san_pham_id` (`san_pham_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Bảng Đánh Giá & Bình Luận
CREATE TABLE IF NOT EXISTS `danh_gia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `san_pham_id` int(11) NOT NULL,
  `ten_nguoi_danh_gia` varchar(100) NOT NULL,
  `so_sao` tinyint(4) NOT NULL DEFAULT 5,
  `noi_dung` text NOT NULL,
  `ngay_tao` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `san_pham_id` (`san_pham_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
wweb_dientumysqleb_dientuadmin_usersadmin_usersbannersdanh_gia