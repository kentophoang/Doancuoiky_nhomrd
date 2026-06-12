<?php
include 'admin_header.php';
include 'admin_sidebar.php';
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_add'])) {
    $ten_sp = trim($_POST['ten_sp']);
    $gia = $_POST['gia'];
    $so_luong_ton = $_POST['so_luong_ton'];
    $danh_muc_id = empty($_POST['danh_muc_id']) ? NULL : $_POST['danh_muc_id'];
    $mo_ta = trim($_POST['mo_ta']);
    
    // --- LƯU THÔNG SỐ KỸ THUẬT THÀNH JSON ---
    $thong_so = NULL;
    if (isset($_POST['thong_so']) && is_array($_POST['thong_so'])) {
        // Lọc bỏ các ô trống không nhập
        $thong_so_data = array_filter($_POST['thong_so'], function($value) { return $value !== ''; });
        $thong_so = json_encode($thong_so_data, JSON_UNESCAPED_UNICODE);
    }

    $hinh_anh = '';
    if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == 0) {
        $target_dir = "images/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $file_extension = strtolower(pathinfo($_FILES["hinh_anh"]["name"], PATHINFO_EXTENSION));
        $hinh_anh = time() . '_' . rand(1000, 9999) . '.' . $file_extension; 
        if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            move_uploaded_file($_FILES["hinh_anh"]["tmp_name"], $target_dir . $hinh_anh);
        }
    }

    try {
        // Thêm trường thong_so vào câu lệnh INSERT
        $stmt = $conn->prepare("INSERT INTO san_pham (ten_sp, gia, so_luong_ton, danh_muc_id, hinh_anh, mo_ta, thong_so) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$ten_sp, $gia, $so_luong_ton, $danh_muc_id, $hinh_anh, $mo_ta, $thong_so]);
        echo "<script>alert('Thêm sản phẩm thành công!'); window.location.href='admin_products.php';</script>"; exit();
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger m-3'>Lỗi thêm sản phẩm: " . $e->getMessage() . "</div>";
    }
}

$sql_cat = "SELECT d1.*, d2.ten_danh_muc AS ten_danh_muc_cha FROM danh_muc d1 LEFT JOIN danh_muc d2 ON d1.parent_id = d2.id ORDER BY d1.parent_id ASC, d1.id ASC";
$categories = $conn->query($sql_cat)->fetchAll(PDO::FETCH_ASSOC);

// Tạo mảng dữ liệu thuộc tính để JavaScript có thể đọc được
$cat_attributes = [];
foreach ($categories as $cat) {
    $cat_attributes[$cat['id']] = !empty($cat['thuoc_tinh']) ? json_decode($cat['thuoc_tinh'], true) : [];
}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fa-solid fa-plus-circle text-success me-2"></i>Thêm Sản Phẩm Mới</h2>
        <a href="admin_products.php" class="btn btn-secondary">Quay lại</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="POST" action="admin_product_add.php" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên sản phẩm <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="ten_sp" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label fw-bold">Giá bán <span class="text-danger">*</span></label><input type="number" class="form-control" name="gia" required></div>
                            <div class="col-md-6 mb-3"><label class="form-label fw-bold">Tồn kho <span class="text-danger">*</span></label><input type="number" class="form-control" name="so_luong_ton" value="10" required></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Danh mục sản phẩm <span class="text-danger">*</span></label>
                            <select class="form-select" name="danh_muc_id" id="danh_muc_select" required>
                                <option value="">-- Chọn danh mục --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>">
                                        <?php echo $cat['ten_danh_muc_cha'] ? $cat['ten_danh_muc_cha'] . ' ➔ ' . $cat['ten_danh_muc'] : htmlspecialchars($cat['ten_danh_muc']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div id="thong_so_container" class="mb-3 p-3 bg-light border rounded" style="display: none;">
                            </div>

                        <div class="mb-3"><label class="form-label fw-bold">Mô tả chi tiết</label><textarea class="form-control" name="mo_ta" rows="4"></textarea></div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-info mb-3">
                            <div class="card-header bg-info text-white fw-bold">Hình ảnh</div>
                            <div class="card-body text-center">
                                <img id="imgPreview" src="https://via.placeholder.com/300x300?text=Chua+co+anh" class="img-fluid rounded mb-3 border" style="max-height: 250px; object-fit: cover;">
                                <input type="file" class="form-control" name="hinh_anh" id="hinh_anh" accept="image/*">
                            </div>
                        </div>
                    </div>
                </div>
                <hr><button type="submit" name="btn_add" class="btn btn-success fw-bold px-4">Lưu Sản Phẩm</button>
            </form>
        </div>
    </div>
</div>

<script>
// Logic tạo form thông số tự động
const catAttributes = <?php echo json_encode($cat_attributes, JSON_UNESCAPED_UNICODE); ?>;

document.getElementById('danh_muc_select').addEventListener('change', function() {
    const catId = this.value;
    const container = document.getElementById('thong_so_container');
    
    if(catId && catAttributes[catId] && catAttributes[catId].length > 0) {
        let html = '<label class="form-label fw-bold text-info"><i class="fa-solid fa-list-check"></i> Điền thông số kỹ thuật</label><div class="row">';
        catAttributes[catId].forEach(attr => {
            // Tạo input name="thong_so[RAM]", name="thong_so[CPU]"...
            html += `
                <div class="col-md-6 mb-2">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text w-25 bg-white fw-bold">${attr}</span>
                        <input type="text" class="form-control" name="thong_so[${attr}]" placeholder="Nhập ${attr}...">
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
});

document.getElementById('hinh_anh').addEventListener('change', function(e) {
    if (e.target.files[0]) document.getElementById('imgPreview').src = URL.createObjectURL(e.target.files[0]);
});
</script>
<?php include 'admin_footer.php'; ?>