<?php
include 'admin_header.php';
include 'admin_sidebar.php';
require_once 'db.php';

// --- XỬ LÝ XÓA SẢN PHẨM ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_sp = (int)$_GET['id'];
    
    try {
        $conn->beginTransaction();
        
        // Xóa liên kết trong chi tiết đơn hàng
        $stmt_del_child = $conn->prepare("DELETE FROM chi_tiet_don_hang WHERE san_pham_id = ?");
        $stmt_del_child->execute([$id_sp]);
        
        // Xóa liên kết trong đánh giá
        $stmt_del_reviews = $conn->prepare("DELETE FROM danh_gia WHERE san_pham_id = ?");
        $stmt_del_reviews->execute([$id_sp]);

        // Xóa file ảnh vật lý nếu có
        $stmt_get_img = $conn->prepare("SELECT hinh_anh FROM san_pham WHERE id = ?");
        $stmt_get_img->execute([$id_sp]);
        $img_row = $stmt_get_img->fetch(PDO::FETCH_ASSOC);
        if ($img_row && !empty($img_row['hinh_anh'])) {
            $file_path = "images/" . $img_row['hinh_anh'];
            if (file_exists($file_path)) {
                @unlink($file_path);
            }
        }
        
        // Xóa sản phẩm gốc
        $stmt_del_parent = $conn->prepare("DELETE FROM san_pham WHERE id = ?");
        $stmt_del_parent->execute([$id_sp]);
        
        $conn->commit();
        echo "<script>alert('Đã xóa sản phẩm thành công!'); window.location.href='admin_products.php';</script>";
    } catch (Exception $e) {
        $conn->rollBack();
        echo "<script>alert('Lỗi hệ thống khi xóa: " . addslashes($e->getMessage()) . "'); window.location.href='admin_products.php';</script>";
    }
    exit();
}

// Xử lý tìm kiếm và lọc theo danh mục
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$cat_filter = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : 0;

$where_clauses = [];
$params = [];

if ($search !== '') {
    $where_clauses[] = "s.ten_sp LIKE ?";
    $params[] = "%$search%";
}

if ($cat_filter > 0) {
    $where_clauses[] = "s.danh_muc_id = ?";
    $params[] = $cat_filter;
}

$sql_list = "SELECT s.*, d.ten_danh_muc 
             FROM san_pham s 
             LEFT JOIN danh_muc d ON s.danh_muc_id = d.id";

if (!empty($where_clauses)) {
    $sql_list .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql_list .= " ORDER BY s.id DESC";
$stmt_list = $conn->prepare($sql_list);
$stmt_list->execute($params);
$san_pham_hien_thi = $stmt_list->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh mục cho bộ lọc
$categories = $conn->query("SELECT * FROM danh_muc ORDER BY ten_danh_muc ASC")->fetchAll(PDO::FETCH_ASSOC);
$placeholder_svg = "data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2260%22%20height%3D%2260%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20fill%3D%22%23f1f5f9%22%20width%3D%2260%22%20height%3D%2260%22%2F%3E%3Ctext%20fill%3D%22%2394a3b8%22%20font-family%3D%22sans-serif%22%20font-size%3D%2210%22%20dy%3D%223.5%22%20font-weight%3D%22bold%22%20x%3D%2250%25%22%20y%3D%2250%25%22%20text-anchor%3D%22middle%22%3ENo%20Img%3C%2Ftext%3E%3C%2Fsvg%3E";
?>

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h2 class="fw-extrabold text-dark mb-1 font-heading">Quản Lý Sản Phẩm</h2>
        <p class="text-muted small mb-0">Danh sách toàn bộ các sản phẩm và tồn kho trong hệ thống (<strong><?php echo count($san_pham_hien_thi); ?></strong> sản phẩm)</p>
    </div>
    <a href="admin_product_add.php" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
        <i class="fa-solid fa-plus me-1"></i> Thêm Sản Phẩm Mới
    </a>
</div>

<!-- Search & Filter Card -->
<div class="admin-card p-3 mb-4">
    <form method="GET" action="admin_products.php" class="row g-2 align-items-center">
        <div class="col-md-5 col-lg-4">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Tìm kiếm tên sản phẩm..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
        </div>
        <div class="col-md-4 col-lg-3">
            <select name="cat_id" class="form-select">
                <option value="0">-- Tất cả danh mục --</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?php echo $c['id']; ?>" <?php echo ($cat_filter == $c['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($c['ten_danh_muc']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary rounded-pill px-3">
                <i class="fa-solid fa-filter me-1"></i> Lọc
            </button>
            <?php if ($search !== '' || $cat_filter > 0): ?>
                <a href="admin_products.php" class="btn btn-outline-secondary rounded-pill px-3 ms-1">
                    <i class="fa-solid fa-rotate-left me-1"></i> Đặt lại
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Products Table Card -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle mb-0 text-center">
            <thead>
                <tr>
                    <th style="width: 5%;" class="ps-4">ID</th>
                    <th style="width: 10%;">Ảnh</th>
                    <th class="text-start" style="width: 35%;">Tên sản phẩm</th>
                    <th style="width: 18%;">Danh mục</th>
                    <th style="width: 15%;">Giá bán</th>
                    <th style="width: 10%;">Tồn kho</th>
                    <th style="width: 7%;" class="pe-4 text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($san_pham_hien_thi)): ?>
                    <?php foreach ($san_pham_hien_thi as $row): 
                        $p_img = (!empty($row['hinh_anh']) && file_exists('images/' . $row['hinh_anh'])) ? 'images/' . htmlspecialchars($row['hinh_anh']) : $placeholder_svg;
                        $in_stock = ($row['so_luong_ton'] > 0);
                    ?>
                        <tr>
                            <td class="ps-4 text-muted small fw-bold">#<?php echo $row['id']; ?></td>
                            <td>
                                <div class="rounded-3 border p-1 bg-light d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <img src="<?php echo $p_img; ?>" alt="Img" style="max-height: 40px; max-width: 40px; object-fit: contain;">
                                </div>
                            </td>
                            <td class="text-start">
                                <div class="fw-bold text-dark text-truncate" style="max-width: 320px;" title="<?php echo htmlspecialchars($row['ten_sp']); ?>">
                                    <?php echo htmlspecialchars($row['ten_sp']); ?>
                                </div>
                                <a href="product_detail.php?id=<?php echo $row['id']; ?>" target="_blank" class="small text-muted text-decoration-none">
                                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Xem trên web
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-light text-primary border border-primary px-3 py-1 rounded-pill small">
                                    <?php echo htmlspecialchars($row['ten_danh_muc'] ?? 'Chưa phân loại'); ?>
                                </span>
                            </td>
                            <td class="text-danger fw-extrabold font-heading">
                                <?php echo number_format($row['gia'], 0, ',', '.'); ?> ₫
                            </td>
                            <td>
                                <?php if ($in_stock): ?>
                                    <span class="badge bg-success-subtle text-success border border-success px-3 py-1 rounded-pill small fw-bold">
                                        <?php echo (int)$row['so_luong_ton']; ?> cái
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger px-3 py-1 rounded-pill small fw-bold">
                                        Hết hàng
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="admin_product_edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-light border rounded-circle p-2 text-primary" title="Chỉnh sửa sản phẩm">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="admin_products.php?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-light border rounded-circle p-2 text-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');" title="Xóa sản phẩm">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="py-5 text-muted text-center">
                            <i class="fa-solid fa-box-open fa-3x mb-2 text-light"></i>
                            <p class="mb-0">Không tìm thấy sản phẩm nào phù hợp.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'admin_footer.php'; ?>