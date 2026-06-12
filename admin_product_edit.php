<?php
include 'admin_header.php';
include 'admin_sidebar.php';
require_once 'db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) { echo "<script>window.location.href='admin_products.php';</script>"; exit(); }
$id_sp = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_edit'])) {
    $ten_sp = trim($_POST['ten_sp']);
    $gia = $_POST['gia'];
    $so_luong_ton = $_POST['so_luong_ton'];
    $danh_muc_id = empty($_POST['danh_muc_id']) ? NULL : $_POST['danh_muc_id'];
    $mo_ta = trim($_POST['mo_ta']);
    $hinh_anh = $_POST['hinh_anh_cu']; 

    // --- CẬP NHẬT THÔNG SỐ KỸ THUẬT ---
    $thong_so = NULL;
    if (isset($_POST['thong_so']) && is_array($_POST['thong_so'])) {
        $thong_so_data = array_filter($_POST['thong_so'], function($value) { return $value !== ''; });
        $thong_so = json_encode($thong_so_data, JSON_UNESCAPED_UNICODE);
    }

    if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == 0) {
        $target_dir = "images/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $file_extension = strtolower(pathinfo($_FILES["hinh_anh"]["name"], PATHINFO_EXTENSION));
        $hinh_anh_moi = time() . '_' . rand(1000, 9999) . '.' . $file_extension; 
        if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            if (move_uploaded_file($_FILES["hinh_anh"]["tmp_name"], $target_dir . $hinh_anh_moi)) {
                if (!empty($hinh_anh) && file_exists($target_dir . $hinh_anh)) unlink($target_dir . $hinh_anh);
                $hinh_anh = $hinh_anh_moi;
            }
        }
    }

    try {
        $stmt_update = $conn->prepare("UPDATE san_pham SET ten_sp=?, gia=?, so_luong_ton=?, danh_muc_id=?, hinh_anh=?, mo_ta=?, thong_so=? WHERE id=?");
        $stmt_update->execute([$ten_sp, $gia, $so_luong_ton, $danh_muc_id, $hinh_anh, $mo_ta, $thong_so, $id_sp]);
        echo "<script>alert('Cập nhật thành công!'); window.location.href='admin_products.php';</script>"; exit();
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger m-3'>Lỗi cập nhật: " . $e->getMessage() . "</div>";
    }
}

$stmt_get = $conn->prepare("SELECT * FROM san_pham WHERE id = ?");
$stmt_get->execute([$id_sp]);
$product = $stmt_get->fetch(PDO::FETCH_ASSOC);

$sql_cat = "SELECT d1.*, d2.ten_danh_muc AS ten_danh_muc_cha FROM danh_muc d1 LEFT JOIN danh_muc d2 ON d1.parent_id = d2.id ORDER BY d1.parent_id ASC, d1.id ASC";
$categories = $conn->query($sql_cat)->fetchAll(PDO::FETCH_ASSOC);

$cat_attributes = [];
foreach ($categories as $cat) {
    $cat_attributes[$cat['id']] = !empty($cat['thuoc_tinh']) ? json_decode($cat['thuoc_tinh'], true) : [];
}

// Lấy thông số hiện tại của sản phẩm
$current_thong_so = !empty($product['thong_so']) ? $product['thong_so'] : '{}';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Sửa Sản Phẩm</h2>
        <a href="admin_products.php" class="btn btn-secondary">Quay lại</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="POST" action="admin_product_edit.php?id=<?php echo $id_sp; ?>" enctype="multipart/form-data">
                <input type="hidden" name="hinh_anh_cu" value="<?php echo htmlspecialchars($product['hinh_anh']); ?>">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3"><label class="form-label fw-bold">Tên sản phẩm</label><input type="text" class="form-control" name="ten_sp" required value="<?php echo htmlspecialchars($product['ten_sp']); ?>"></div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label fw-bold">Giá bán</label><input type="number" class="form-control" name="gia" required value="<?php echo $product['gia']; ?>"></div>
                            <div class="col-md-6 mb-3"><label class="form-label fw-bold">Tồn kho</label><input type="number" class="form-control" name="so_luong_ton" required value="<?php echo $product['so_luong_ton']; ?>"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Danh mục</label>
                            <select class="form-select" name="danh_muc_id" id="danh_muc_select" required>
                                <option value="">-- Chọn danh mục --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id'] == $product['danh_muc_id']) ? 'selected' : ''; ?>>
                                        <?php echo $cat['ten_danh_muc_cha'] ? $cat['ten_danh_muc_cha'] . ' ➔ ' . $cat['ten_danh_muc'] : htmlspecialchars($cat['ten_danh_muc']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div id="thong_so_container" class="mb-3 p-3 bg-light border rounded" style="display: none;"></div>

                        <div class="mb-3"><label class="form-label fw-bold">Mô tả chi tiết</label><textarea class="form-control" name="mo_ta" rows="4"><?php echo htmlspecialchars($product['mo_ta']); ?></textarea></div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-primary mb-3">
                            <div class="card-header bg-primary text-white fw-bold">Hình ảnh</div>
                            <div class="card-body text-center">
                                <?php $img_src = (!empty($product['hinh_anh'])) ? "images/".$product['hinh_anh'] : "https://via.placeholder.com/300x300?text=Chua+co+anh"; ?>
                                <img id="imgPreview" src="<?php echo $img_src; ?>" class="img-fluid rounded mb-3 border" style="max-height: 250px; object-fit: cover;">
                                <input type="file" class="form-control" name="hinh_anh" id="hinh_anh" accept="image/*">
                            </div>
                        </div>
                    </div>
                </div>
                <hr><button type="submit" name="btn_edit" class="btn btn-primary fw-bold px-4">Cập Nhật</button>
            </form>
        </div>
    </div>
</div>

<script>
const catAttributes = <?php echo json_encode($cat_attributes, JSON_UNESCAPED_UNICODE); ?>;
const existingThongSo = <?php echo $current_thong_so; ?>; // Lấy dữ liệu cũ từ PHP

const danhMucSelect = document.getElementById('danh_muc_select');
const container = document.getElementById('thong_so_container');

function renderThongSo() {
    const catId = danhMucSelect.value;
    if(catId && catAttributes[catId] && catAttributes[catId].length > 0) {
        let html = '<label class="form-label fw-bold text-info"><i class="fa-solid fa-list-check"></i> Thông số kỹ thuật</label><div class="row">';
        catAttributes[catId].forEach(attr => {
            // Nếu có dữ liệu cũ thì điền vào, nếu không thì để trống
            let val = existingThongSo[attr] ? existingThongSo[attr] : '';
            html += `
                <div class="col-md-6 mb-2">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text w-25 bg-white fw-bold">${attr}</span>
                        <input type="text" class="form-control" name="thong_so[${attr}]" value="${val}" placeholder="Nhập ${attr}...">
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

// Chạy hàm ngay khi load trang để hiển thị thông số cũ
window.onload = renderThongSo;

// Chạy hàm khi người dùng đổi danh mục
danhMucSelect.addEventListener('change', renderThongSo);

document.getElementById('hinh_anh').addEventListener('change', function(e) {
    if (e.target.files[0]) document.getElementById('imgPreview').src = URL.createObjectURL(e.target.files[0]);
});
</script>
<?php include 'admin_footer.php'; ?>