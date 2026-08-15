<?php
include 'admin_header.php';
include 'admin_sidebar.php';
require_once 'db.php';

// --- XỬ LÝ THÊM MỚI ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_category') {
    $ten_danh_muc = trim($_POST['ten_danh_muc']);
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : NULL;
    $thuoc_tinh_json = NULL;
    if (!empty($_POST['thuoc_tinh'])) {
        $mang_thuoc_tinh = array_map('trim', explode(',', $_POST['thuoc_tinh']));
        $mang_thuoc_tinh = array_filter($mang_thuoc_tinh); 
        $thuoc_tinh_json = json_encode(array_values($mang_thuoc_tinh), JSON_UNESCAPED_UNICODE);
    }
    if ($ten_danh_muc != '') {
        $stmt_add = $conn->prepare("INSERT INTO danh_muc (ten_danh_muc, parent_id, thuoc_tinh) VALUES (?, ?, ?)");
        $stmt_add->execute([$ten_danh_muc, $parent_id, $thuoc_tinh_json]);
        echo "<script>window.location.href='admin_categories.php';</script>"; 
        exit();
    }
}

// --- XỬ LÝ CẬP NHẬT ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit_category') {
    $id = (int)$_POST['id'];
    $ten_danh_muc = trim($_POST['ten_danh_muc']);
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : NULL;
    $thuoc_tinh_json = NULL;
    if (!empty($_POST['thuoc_tinh'])) {
        $mang_thuoc_tinh = array_map('trim', explode(',', $_POST['thuoc_tinh']));
        $mang_thuoc_tinh = array_filter($mang_thuoc_tinh); 
        $thuoc_tinh_json = json_encode(array_values($mang_thuoc_tinh), JSON_UNESCAPED_UNICODE);
    }
    $stmt_update = $conn->prepare("UPDATE danh_muc SET ten_danh_muc=?, parent_id=?, thuoc_tinh=? WHERE id=?");
    $stmt_update->execute([$ten_danh_muc, $parent_id, $thuoc_tinh_json, $id]);
    echo "<script>window.location.href='admin_categories.php';</script>"; 
    exit();
}

// --- XỬ LÝ XÓA ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_del = (int)$_GET['id'];
    $stmt_del = $conn->prepare("DELETE FROM danh_muc WHERE id = ?");
    $stmt_del->execute([$id_del]);
    echo "<script>window.location.href='admin_categories.php';</script>"; 
    exit();
}

// --- LẤY DỮ LIỆU ---
$is_edit = false; 
$edit_data = [];
if (isset($_GET['edit_id'])) {
    $is_edit = true;
    $stmt_edit = $conn->prepare("SELECT * FROM danh_muc WHERE id = ?");
    $stmt_edit->execute([(int)$_GET['edit_id']]);
    $edit_data = $stmt_edit->fetch(PDO::FETCH_ASSOC);
}

$stmt_all = $conn->query("SELECT * FROM danh_muc ORDER BY id ASC");
$tat_ca_danh_muc = $stmt_all->fetchAll(PDO::FETCH_ASSOC);

$sql_list = "SELECT d1.*, d2.ten_danh_muc AS ten_danh_muc_cha 
             FROM danh_muc d1 LEFT JOIN danh_muc d2 ON d1.parent_id = d2.id 
             ORDER BY d1.parent_id ASC, d1.id ASC";
$categories_list = $conn->query($sql_list)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h2 class="fw-extrabold text-dark mb-1 font-heading">Quản Lý Danh Mục</h2>
        <p class="text-muted small mb-0">Tạo cây phân cấp danh mục và cấu hình thuộc tính kỹ thuật động cho từng nhóm sản phẩm</p>
    </div>
    <?php if ($is_edit): ?>
        <a href="admin_categories.php" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="fa-solid fa-plus me-1"></i> Tạo danh mục mới
        </a>
    <?php endif; ?>
</div>

<div class="row g-4">
    <!-- Left Column: Add / Edit Form -->
    <div class="col-lg-5 col-xl-4">
        <div class="admin-card p-4">
            <div class="pb-3 mb-3 border-bottom">
                <h5 class="fw-bold text-dark mb-0 font-heading">
                    <i class="fa-solid <?php echo $is_edit ? 'fa-pen-to-square text-warning' : 'fa-circle-plus text-primary'; ?> me-2"></i>
                    <?php echo $is_edit ? 'Cập Nhật Danh Mục' : 'Thêm Danh Mục Mới'; ?>
                </h5>
            </div>

            <form method="POST" action="admin_categories.php">
                <input type="hidden" name="action" value="<?php echo $is_edit ? 'edit_category' : 'add_category'; ?>">
                <?php if ($is_edit): ?>
                    <input type="hidden" name="id" value="<?php echo (int)$edit_data['id']; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Tên danh mục <span class="text-danger">*</span></label>
                    <input type="text" name="ten_danh_muc" class="form-control" required placeholder="Ví dụ: Laptop, Tai nghe, Chuột..." value="<?php echo $is_edit ? htmlspecialchars($edit_data['ten_danh_muc']) : ''; ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Danh mục cha (Tùy chọn)</label>
                    <select name="parent_id" class="form-select">
                        <option value="">-- Là danh mục gốc (Cấp 1) --</option>
                        <?php foreach ($tat_ca_danh_muc as $cat): ?>
                            <?php if (!$is_edit || $cat['id'] != $edit_data['id']): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($is_edit && $edit_data['parent_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['ten_danh_muc']); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-dark">Thuộc tính kỹ thuật (Cách nhau dấu phẩy)</label>
                    <?php 
                        $thuoc_tinh_str = '';
                        if ($is_edit && !empty($edit_data['thuoc_tinh'])) {
                            $arr = json_decode($edit_data['thuoc_tinh'], true);
                            if (is_array($arr)) $thuoc_tinh_str = implode(', ', $arr);
                        }
                    ?>
                    <textarea name="thuoc_tinh" class="form-control" rows="3" placeholder="Ví dụ: CPU, RAM, Ổ cứng, Card đồ họa, Màn hình..."><?php echo htmlspecialchars($thuoc_tinh_str); ?></textarea>
                    <small class="text-muted mt-1 d-block" style="font-size: 0.775rem;">Khi thêm sản phẩm thuộc danh mục này, hệ thống sẽ tự động tạo ô nhập các thông số trên.</small>
                </div>

                <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm">
                    <i class="fa-solid fa-floppy-disk me-2"></i> <?php echo $is_edit ? 'Lưu Thay Đổi' : 'Thêm Danh Mục'; ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Right Column: Categories List Table -->
    <div class="col-lg-7 col-xl-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6 class="fw-bold text-dark mb-0 font-heading">
                    <i class="fa-solid fa-shapes text-primary me-2"></i> Danh Sách Danh Mục Hiện Tại (<?php echo count($categories_list); ?>)
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table admin-table align-middle mb-0 text-center">
                    <thead>
                        <tr>
                            <th class="ps-4 text-start">Tên danh mục</th>
                            <th>Cấp bậc</th>
                            <th class="text-start">Thuộc tính kỹ thuật</th>
                            <th class="pe-4 text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($categories_list)): ?>
                            <?php foreach ($categories_list as $cat): 
                                $attrs = !empty($cat['thuoc_tinh']) ? json_decode($cat['thuoc_tinh'], true) : [];
                            ?>
                                <tr>
                                    <td class="ps-4 text-start">
                                        <div class="fw-bold text-dark">
                                            <?php if ($cat['parent_id']): ?>
                                                <span class="text-muted me-1">↳</span>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($cat['ten_danh_muc']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($cat['parent_id']): ?>
                                            <span class="badge bg-light text-muted border small">
                                                Thuộc: <?php echo htmlspecialchars($cat['ten_danh_muc_cha']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-primary-subtle text-primary border border-primary small fw-bold">
                                                Danh mục gốc
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-start">
                                        <?php if (!empty($attrs) && is_array($attrs)): ?>
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php foreach (array_slice($attrs, 0, 4) as $att): ?>
                                                    <span class="badge bg-light text-dark border small" style="font-size: 0.725rem;"><?php echo htmlspecialchars($att); ?></span>
                                                <?php endforeach; ?>
                                                <?php if (count($attrs) > 4): ?>
                                                    <span class="badge bg-light text-muted border small">+<?php echo count($attrs) - 4; ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="d-inline-flex gap-1">
                                            <a href="admin_categories.php?edit_id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-light border rounded-circle p-2 text-primary" title="Sửa danh mục">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <a href="admin_categories.php?action=delete&id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-light border rounded-circle p-2 text-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này?');" title="Xóa danh mục">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="py-4 text-muted">Chưa có danh mục nào.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>