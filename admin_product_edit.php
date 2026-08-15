<?php
include 'admin_header.php';
include 'admin_sidebar.php';
require_once 'db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) { 
    echo "<script>window.location.href='admin_products.php';</script>"; 
    exit(); 
}
$id_sp = (int)$_GET['id'];
$thong_bao = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_edit'])) {
    $ten_sp = trim($_POST['ten_sp']);
    $gia = (float)$_POST['gia'];
    $so_luong_ton = (int)$_POST['so_luong_ton'];
    $danh_muc_id = empty($_POST['danh_muc_id']) ? NULL : (int)$_POST['danh_muc_id'];
    $mo_ta = trim($_POST['mo_ta']);
    $hinh_anh = $_POST['hinh_anh_cu']; 

    // Cập nhật thông số kỹ thuật JSON
    $thong_so_json = NULL;
    if (isset($_POST['thong_so']) && is_array($_POST['thong_so'])) {
        $thong_so_data = array_filter($_POST['thong_so'], function($value) { return trim($value) !== ''; });
        if (!empty($thong_so_data)) {
            $thong_so_json = json_encode($thong_so_data, JSON_UNESCAPED_UNICODE);
        }
    }

    if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == 0) {
        $target_dir = "images/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $file_extension = strtolower(pathinfo($_FILES["hinh_anh"]["name"], PATHINFO_EXTENSION));
        $hinh_anh_moi = time() . '_' . rand(1000, 9999) . '.' . $file_extension; 
        if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            if (move_uploaded_file($_FILES["hinh_anh"]["tmp_name"], $target_dir . $hinh_anh_moi)) {
                if (!empty($hinh_anh) && file_exists($target_dir . $hinh_anh)) {
                    @unlink($target_dir . $hinh_anh);
                }
                $hinh_anh = $hinh_anh_moi;
            }
        }
    }

    try {
        $stmt_update = $conn->prepare("UPDATE san_pham SET ten_sp=?, gia=?, so_luong_ton=?, danh_muc_id=?, hinh_anh=?, mo_ta=?, thong_so_ky_thuat=? WHERE id=?");
        $stmt_update->execute([$ten_sp, $gia, $so_luong_ton, $danh_muc_id, $hinh_anh, $mo_ta, $thong_so_json, $id_sp]);
        echo "<script>alert('Cập nhật sản phẩm thành công!'); window.location.href='admin_products.php';</script>"; 
        exit();
    } catch (PDOException $e) {
        try {
            $stmt_update2 = $conn->prepare("UPDATE san_pham SET ten_sp=?, gia=?, so_luong_ton=?, danh_muc_id=?, hinh_anh=?, mo_ta=?, thong_so=? WHERE id=?");
            $stmt_update2->execute([$ten_sp, $gia, $so_luong_ton, $danh_muc_id, $hinh_anh, $mo_ta, $thong_so_json, $id_sp]);
            echo "<script>alert('Cập nhật sản phẩm thành công!'); window.location.href='admin_products.php';</script>"; 
            exit();
        } catch (PDOException $e2) {
            $thong_bao = "<div class='alert alert-danger mb-4'><i class='fa-solid fa-circle-exclamation me-2'></i>Lỗi cập nhật: " . htmlspecialchars($e2->getMessage()) . "</div>";
        }
    }
}

$stmt_get = $conn->prepare("SELECT * FROM san_pham WHERE id = ?");
$stmt_get->execute([$id_sp]);
$product = $stmt_get->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "<script>alert('Sản phẩm không tồn tại!'); window.location.href='admin_products.php';</script>";
    exit();
}

$sql_cat = "SELECT d1.*, d2.ten_danh_muc AS ten_danh_muc_cha FROM danh_muc d1 LEFT JOIN danh_muc d2 ON d1.parent_id = d2.id ORDER BY d1.parent_id ASC, d1.id ASC";
$categories = $conn->query($sql_cat)->fetchAll(PDO::FETCH_ASSOC);

$cat_attributes = [];
foreach ($categories as $cat) {
    $cat_attributes[$cat['id']] = !empty($cat['thuoc_tinh']) ? json_decode($cat['thuoc_tinh'], true) : [];
}

// Lấy thông số hiện tại
$current_thong_so = '{}';
if (!empty($product['thong_so_ky_thuat'])) {
    $current_thong_so = $product['thong_so_ky_thuat'];
} elseif (!empty($product['thong_so'])) {
    $current_thong_so = $product['thong_so'];
}
$img_src = (!empty($product['hinh_anh']) && file_exists('images/' . $product['hinh_anh'])) ? 'images/' . htmlspecialchars($product['hinh_anh']) : 'data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22200%22%20height%3D%22200%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20fill%3D%22%23f1f5f9%22%20width%3D%22200%22%20height%3D%22200%22%2F%3E%3Ctext%20fill%3D%22%2394a3b8%22%20font-family%3D%22sans-serif%22%20font-size%3D%2214%22%20dy%3D%224.5%22%20font-weight%3D%22bold%22%20x%3D%2250%25%22%20y%3D%2250%25%22%20text-anchor%3D%22middle%22%3ENo%20Img%3C%2Ftext%3E%3C%2Fsvg%3E';
?>

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h2 class="fw-extrabold text-dark mb-1 font-heading">Chỉnh Sửa Sản Phẩm #<?php echo $id_sp; ?></h2>
        <p class="text-muted small mb-0">Cập nhật thông tin, giá bán, cấu hình kỹ thuật hoặc thay ảnh đại diện</p>
    </div>
    <a href="admin_products.php" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold">
        <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
    </a>
</div>

<?php echo $thong_bao; ?>

<div class="admin-card p-4">
    <form method="POST" action="admin_product_edit.php?id=<?php echo $id_sp; ?>" enctype="multipart/form-data">
        <input type="hidden" name="hinh_anh_cu" value="<?php echo htmlspecialchars($product['hinh_anh']); ?>">
        
        <div class="row g-4">
            <!-- Left: Inputs -->
            <div class="col-lg-8">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Tên sản phẩm <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg" name="ten_sp" required value="<?php echo htmlspecialchars($product['ten_sp']); ?>">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">Giá bán (VNĐ) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="gia" required value="<?php echo (float)$product['gia']; ?>" min="0">
                            <span class="input-group-text bg-light fw-bold">₫</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">Số lượng tồn kho <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="so_luong_ton" required value="<?php echo (int)$product['so_luong_ton']; ?>" min="0">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Danh mục sản phẩm <span class="text-danger">*</span></label>
                    <select class="form-select" name="danh_muc_id" id="danh_muc_select" required>
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id'] == $product['danh_muc_id']) ? 'selected' : ''; ?>>
                                <?php echo $cat['ten_danh_muc_cha'] ? $cat['ten_danh_muc_cha'] . ' ➔ ' . $cat['ten_danh_muc'] : htmlspecialchars($cat['ten_danh_muc']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Dynamic Specs Container -->
                <div id="thong_so_container" class="mb-4 p-3 rounded-3" style="display: none; background: #eff6ff; border: 1px dashed #93c5fd;">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Mô tả sản phẩm chi tiết</label>
                    <textarea class="form-control" name="mo_ta" rows="6"><?php echo htmlspecialchars($product['mo_ta']); ?></textarea>
                </div>
            </div>

            <!-- Right: Image Preview & Update -->
            <div class="col-lg-4">
                <div class="card border border-subtle rounded-3 p-3 text-center mb-3">
                    <label class="form-label small fw-bold text-dark text-start w-100 mb-2">Hình ảnh sản phẩm</label>
                    <div class="p-3 bg-light rounded-3 d-flex align-items-center justify-content-center mb-3" style="height: 240px;">
                        <img id="imgPreview" src="<?php echo $img_src; ?>" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;">
                    </div>
                    <input type="file" class="form-control" name="hinh_anh" id="hinh_anh" accept="image/*">
                    <small class="text-muted mt-2 d-block">Chọn ảnh mới nếu bạn muốn thay đổi</small>
                </div>

                <button type="submit" name="btn_edit" class="btn btn-primary btn-lg rounded-pill w-100 fw-bold shadow-sm py-3">
                    <i class="fa-solid fa-floppy-disk me-2"></i> Lưu Cập Nhật Sản Phẩm
                </button>
            </div>
        </div>
    </form>
</div>

<script>
const catAttributes = <?php echo json_encode($cat_attributes, JSON_UNESCAPED_UNICODE); ?>;
const existingThongSo = <?php echo $current_thong_so; ?>;

const danhMucSelect = document.getElementById('danh_muc_select');
const container = document.getElementById('thong_so_container');

function renderThongSo() {
    const catId = danhMucSelect.value;
    if (catId && catAttributes[catId] && catAttributes[catId].length > 0) {
        let html = '<label class="form-label fw-bold text-primary small mb-3"><i class="fa-solid fa-sliders me-1"></i> Thông số kỹ thuật của sản phẩm:</label><div class="row g-2">';
        catAttributes[catId].forEach(attr => {
            let val = existingThongSo[attr] ? existingThongSo[attr] : '';
            html += `
                <div class="col-md-6 mb-2">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white fw-semibold" style="width: 35%;">${attr}</span>
                        <input type="text" class="form-control" name="thong_so[${attr}]" value="${val.replace(/"/g, '&quot;')}" placeholder="Nhập ${attr}...">
                    </div>
                </div>`;
        });
        html += '</div>';
        container.innerHTML = html;
        container.style.display = 'block';
    } else {
        container.innerHTML = '';
        container.style.display = 'none';
    }
}

// Initial render
document.addEventListener('DOMContentLoaded', renderThongSo);
danhMucSelect.addEventListener('change', renderThongSo);

document.getElementById('hinh_anh').addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        document.getElementById('imgPreview').src = URL.createObjectURL(e.target.files[0]);
    }
});
</script>

<?php include 'admin_footer.php'; ?>