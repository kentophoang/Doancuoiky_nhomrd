<?php
require_once 'db.php';

if (isset($_GET['keyword']) && trim($_GET['keyword']) != '') {
    $keyword = '%' . trim($_GET['keyword']) . '%';
    
    // Tìm kiếm tối đa 5 sản phẩm khớp với từ khóa để tối ưu tốc độ load
    $stmt = $conn->prepare("SELECT id, ten_sp, gia, hinh_anh FROM san_pham WHERE ten_sp LIKE ? LIMIT 5");
    $stmt->execute([$keyword]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($products) > 0) {
        foreach ($products as $row) {
            $link = "product_detail.php?id=" . $row['id'];
            $anh = (!empty($row['hinh_anh']) && file_exists('images/' . $row['hinh_anh'])) ? 'images/' . $row['hinh_anh'] : 'images/default-placeholder.png';
            $gia_formatted = number_format($row['gia'], 0, ',', '.') . ' ₫';
            
            // Xuất ra cấu trúc HTML hiển thị từng hàng sản phẩm gợi ý
            echo "
            <a href='{$link}' class='d-flex align-items-center p-2 text-decoration-none border-bottom search-result-item text-dark'>
                <img src='{$anh}' style='width: 45px; height: 45px; object-fit: contain;' class='me-2 rounded'>
                <div class='flex-grow-1 text-truncate'>
                    <div class='fw-bold small text-truncate'>{$row['ten_sp']}</div>
                    <div class='text-danger fw-bold small'>{$gia_formatted}</div>
                </div>
            </a>
            ";
        }
    } else {
        echo "<div class='p-3 text-center text-muted small'><i class='fa-solid fa-face-frown'></i> Không tìm thấy sản phẩm phù hợp</div>";
    }
}
?>