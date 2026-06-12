<?php
include 'admin_header.php';
include 'admin_sidebar.php';
require_once 'db.php';

// --- XỬ LÝ THÊM MỚI ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_category') {
    $ten_danh_muc = trim($_POST['ten_danh_muc']);
    $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : NULL;
    $thuoc_tinh_json = NULL;
    if (!empty($_POST['thuoc_tinh'])) {
        $mang_thuoc_tinh = array_map('trim', explode(',', $_POST['thuoc_tinh']));
        $mang_thuoc_tinh = array_filter($mang_thuoc_tinh); 
        $thuoc_tinh_json = json_encode(array_values($mang_thuoc_tinh), JSON_UNESCAPED_UNICODE);
    }
    if ($ten_danh_muc != '') {
        $stmt_add = $conn->prepare("INSERT INTO danh_muc (ten_danh_muc, parent_id, thuoc_tinh) VALUES (?, ?, ?)");
        $stmt_add->execute([$ten_danh_muc, $parent_id, $thuoc_tinh_json]);
        echo "<script>window.location.href='admin_categories.php';</script>"; exit();
    }
}

// --- XỬ LÝ CẬP NHẬT ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit_category') {
    $id = $_POST['id'];
    $ten_danh_muc = trim($_POST['ten_danh_muc']);
    $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : NULL;
    $thuoc_tinh_json = NULL;
    if (!empty($_POST['thuoc_tinh'])) {
        $mang_thuoc_tinh = array_map('trim', explode(',', $_POST['thuoc_tinh']));
        $mang_thuoc_tinh = array_filter($mang_thuoc_tinh); 
        $thuoc_tinh_json = json_encode(array_values($mang_thuoc_tinh), JSON_UNESCAPED_UNICODE);
    }
    $stmt_update = $conn->prepare("UPDATE danh_muc SET ten_danh_muc=?, parent_id=?, thuoc_tinh=? WHERE id=?");
    $stmt_update->execute([$ten_danh_muc, $parent_id, $thuoc_tinh_json, $id]);
    echo "<script>window.location.href='admin_categories.php';</script>"; exit();
}

// --- XỬ LÝ XÓA ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $stmt_del = $conn->prepare("DELETE FROM danh_muc WHERE id = ?");
    $stmt_del->execute([$_GET['id']]);
    echo "<script>window.location.href='admin_categories.php';</script>"; exit();
}

// --- LẤY DỮ LIỆU ---
$is_edit = false; $edit_data = [];
if (isset($_GET['edit_id'])) {
    $is_edit = true;
    $stmt_edit = $conn->prepare("SELECT * FROM danh_muc WHERE id = ?");
    $stmt_edit->execute([$_GET['edit_id']]);
    $edit_data = $stmt_edit->fetch(PDO::FETCH_ASSOC);
}

$stmt_all = $conn->query("SELECT * FROM danh_muc ORDER BY id ASC");
$tat_ca_danh_muc = $stmt_all->fetchAll(PDO::FETCH_ASSOC);

$sql_list = "SELECT d1.*, d2.ten_danh_muc AS ten_danh_muc_cha 
             FROM danh_muc d1 LEFT JOIN danh_muc d2 ON d1.parent_id = d2.id 
             ORDER BY d1.parent_id ASC, d1.id ASC";
$danh_muc_hien_thi = $conn->query($sql_list)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Quản Lý Danh Mục</h2>
        <?php if ($is_edit): ?><a href="admin_categories.php" class="btn btn-secondary">Thêm mới</a><?php endif; ?>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white fw-bold">
                    <?php echo $is_edit ? 'Cập nhật danh mục' : 'Thêm danh mục mới'; ?>
                </div>
                <div class="card-body">
                    <form method="POST" action="admin_categories.php">
                        <input type="hidden" name="action" value="<?php echo $is_edit ? 'edit_category' : 'add_category'; ?>">
                        <?php if ($is_edit): ?><input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>"><?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label">Tên danh mục</label>
                            <input type="text" class="form-control" name="ten_danh_muc" required value="<?php echo $is_edit ? htmlspecialchars($edit_data['ten_danh_muc']) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Danh mục cha</label>
                            <select class="form-select" name="parent_id">
                                <option value="">-- Gốc --</option>
                                <?php foreach ($tat_ca_danh_muc as $dm): 
                                    if ($is_edit && $dm['id'] == $edit_data['id']) continue;
                                    $selected = ($is_edit && $edit_data['parent_id'] == $dm['id']) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $dm['id']; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($dm['ten_danh_muc']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Thuộc tính (cách nhau bằng dấu phẩy)</label>
                            <textarea class="form-control" name="thuoc_tinh" rows="2"><?php 
                                if ($is_edit && !empty($edit_data['thuoc_tinh'])) {
                                    echo htmlspecialchars(implode(', ', json_decode($edit_data['thuoc_tinh'], true)));
                                }
                            ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-success w-100"><?php echo $is_edit ? 'Lưu thay đổi' : 'Thêm mới'; ?></button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr><th>ID</th><th>Tên danh mục</th><th>Danh mục cha</th><th>Thuộc tính</th><th>Thao tác</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($danh_muc_hien_thi as $row): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td class="fw-bold text-primary"><?php echo htmlspecialchars($row['ten_danh_muc']); ?></td>
                            <td><?php echo $row['ten_danh_muc_cha'] ? '<span class="badge bg-info">'.$row['ten_danh_muc_cha'].'</span>' : 'Gốc'; ?></td>
                            <td>
                                <?php 
                                if (!empty($row['thuoc_tinh'])) {
                                    foreach(json_decode($row['thuoc_tinh'], true) as $tt) echo '<span class="badge bg-secondary me-1">'.htmlspecialchars($tt).'</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <a href="admin_categories.php?edit_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen"></i></a>
                                <a href="admin_categories.php?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Chắc chắn xóa?');"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>