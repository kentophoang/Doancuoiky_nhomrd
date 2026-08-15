<?php
require_once 'db.php';

if (isset($_GET['keyword']) && trim($_GET['keyword']) != '') {
    $keyword = '%' . trim($_GET['keyword']) . '%';
    
    // Tìm kiếm tối đa 6 sản phẩm khớp với từ khóa
    $stmt = $conn->prepare("SELECT id, ten_sp, gia, hinh_anh FROM san_pham WHERE ten_sp LIKE ? ORDER BY id DESC LIMIT 6");
    $stmt->execute([$keyword]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($products) > 0) {
        $placeholder = "data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2260%22%20height%3D%2260%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2060%2060%22%3E%3Crect%20fill%3D%22%23f1f5f9%22%20width%3D%2260%22%20height%3D%2260%22%2F%3E%3Ctext%20fill%3D%22%2394a3b8%22%20font-family%3D%22sans-serif%22%20font-size%3D%2210%22%20dy%3D%223.5%22%20font-weight%3D%22bold%22%20x%3D%2250%25%22%20y%3D%2250%25%22%20text-anchor%3D%22middle%22%3ENo%20Image%3C%2Ftext%3E%3C%2Fsvg%3E";

        foreach ($products as $row) {
            $link = "product_detail.php?id=" . (int)$row['id'];
            $anh = (!empty($row['hinh_anh']) && file_exists('images/' . $row['hinh_anh'])) ? 'images/' . htmlspecialchars($row['hinh_anh']) : $placeholder;
            $gia_formatted = number_format($row['gia'], 0, ',', '.') . ' ₫';
            $ten_sp = htmlspecialchars($row['ten_sp']);
            
            echo "
            <a href='{$link}' class='search-result-item'>
                <div style='width: 48px; height: 48px; flex-shrink: 0; background: #f8fafc; border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden; margin-right: 12px; border: 1px solid #e2e8f0;'>
                    <img src='{$anh}' alt='{$ten_sp}' style='max-width: 100%; max-height: 100%; object-fit: contain;'>
                </div>
                <div class='flex-grow-1 overflow-hidden'>
                    <div class='fw-bold text-dark text-truncate' style='font-size: 0.875rem;'>{$ten_sp}</div>
                    <div class='text-danger fw-bold' style='font-size: 0.825rem;'>{$gia_formatted}</div>
                </div>
                <i class='fa-solid fa-chevron-right text-muted small ms-2'></i>
            </a>
            ";
        }
    } else {
        echo "<div class='p-3 text-center text-muted small'><i class='fa-solid fa-magnifying-glass me-1'></i> Không tìm thấy sản phẩm nào phù hợp</div>";
    }
}
?>