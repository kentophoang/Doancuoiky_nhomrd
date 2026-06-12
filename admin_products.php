<?php
include 'admin_header.php';
include 'admin_sidebar.php';
require_once 'db.php';

// --- XỬ LÝ XÓA SẢN PHẨM (CÁCH 2: XÓA CASCADE) ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_sp = $_GET['id'];
    
    try {
        // Bắt đầu một giao dịch (Transaction)
        $conn->beginTransaction();
        
        // 1. Xóa tất cả dữ liệu liên quan trong bảng chi_tiet_don_hang trước
        $stmt_del_child = $conn->prepare("DELETE FROM chi_tiet_don_hang WHERE san_pham_id = ?");
        $stmt_del_child->execute([$id_sp]);
        
        // (Tùy chọn) Xóa file ảnh vật lý trong thư mục hình ảnh nếu có
        $stmt_get_img = $conn->prepare("SELECT hinh_anh FROM san_pham WHERE id = ?");
        $stmt_get_img->execute([$id_sp]);
        $img_row = $stmt_get_img->fetch(PDO::FETCH_ASSOC);
        if ($img_row && !empty($img_row['hinh_anh'])) {
            $file_path = "images/" . $img_row['hinh_anh'];
            if (file_exists($file_path)) {
                unlink($file_path); // Xóa file ảnh khỏi ổ cứng
            }
        }
        
        // 2. Sau khi đã dọn dẹp xong dữ liệu con, tiến hành xóa sản phẩm gốc
        $stmt_del_parent = $conn->prepare("DELETE FROM san_pham WHERE id = ?");
        $stmt_del_parent->execute([$id_sp]);
        
        // Xác nhận giao dịch thành công
        $conn->commit();
        echo "<script>alert('Đã xóa sản phẩm thành công!'); window.location.href='admin_products.php';</script>";
    } catch (Exception $e) {
        // Nếu có bất kỳ lỗi nào, hoàn tác mọi thao tác thay đổi ở trên
        $conn->rollBack();
        echo "<script>alert('Lỗi hệ thống khi xóa: " . $e->getMessage() . "'); window.location.href='admin_products.php';</script>";
    }
    exit();
}

// --- LẤY DANH SÁCH SẢN PHẨM HIỂN THỊ TRONG BẢNG ---
$sql_list = "SELECT s.*, d.ten_danh_muc 
             FROM san_pham s 
             LEFT JOIN danh_muc d ON s.danh_muc_id = d.id 
             ORDER BY s.id DESC";
$san_pham_hien_thi = $conn->query($sql_list)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Quản Lý Sản Phẩm</h2>
        <a href="admin_product_add.php" class="btn btn-success fw-bold">
            <i class="fa-solid fa-plus"></i> Thêm Sản Phẩm Mới
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Hình ảnh</th>
                            <th class="text-start">Tên sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Giá bán</th>
                            <th>Tồn kho</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($san_pham_hien_thi) > 0): ?>
                            <?php foreach ($san_pham_hien_thi as $row): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td>
                                        <?php if (!empty($row['hinh_anh'])): ?>
                                            <img src="images/<?php echo htmlspecialchars($row['hinh_anh']); ?>" alt="Img" style="width: 50px; height: 50px; object-fit: cover;" class="rounded border">
                                        <?php else: ?>
                                            <span class="text-muted small">No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-start fw-bold text-primary">
                                        <?php echo htmlspecialchars($row['ten_sp']); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-dark">
                                            <?php echo htmlspecialchars($row['ten_danh_muc'] ?? 'Không có'); ?>
                                        </span>
                                    </td>
                                    <td class="text-danger fw-bold">
                                        <?php echo number_format($row['gia'], 0, ',', '.'); ?> đ
                                    </td>
                                    <td>
                                        <?php if ($row['so_luong_ton'] > 0): ?>
                                            <span class="badge bg-success"><?php echo $row['so_luong_ton']; ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Hết hàng</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="admin_product_edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary me-1" title="Sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <a href="admin_products.php?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn sản phẩm này cùng toàn bộ lịch sử đơn hàng của nó?');" title="Xóa">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Chưa có sản phẩm nào trong kho.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>