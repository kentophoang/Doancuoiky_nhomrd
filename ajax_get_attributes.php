<?php
// File này chỉ làm nhiệm vụ ngầm trả về dữ liệu JSON cho Javascript xử lý
require_once 'db.php';

if (isset($_GET['cat_id'])) {
    $cat_id = $_GET['cat_id'];
    $stmt = $conn->prepare("SELECT thuoc_tinh FROM danh_muc WHERE id = ?");
    $stmt->execute([$cat_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Trả về JSON, nếu rỗng thì trả về mảng rỗng []
    if ($result && !empty($result['thuoc_tinh'])) {
        echo $result['thuoc_tinh'];
    } else {
        echo json_encode([]);
    }
}
?>