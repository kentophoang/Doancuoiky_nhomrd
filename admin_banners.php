<?php
include 'admin_header.php';
include 'admin_sidebar.php';
require_once 'db.php';

// --- XỬ LÝ THÊM BANNER ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_add'])) {
    $duong_link = trim($_POST['duong_link']) ?: '#';

    if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == 0) {
        $target_dir = "images/banners/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_extension = strtolower(pathinfo($_FILES["hinh_anh"]["name"], PATHINFO_EXTENSION));
        $hinh_anh = 'banner_' . time() . '.' . $file_extension; 
        
        if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'webp'])) {
            if (move_uploaded_file($_FILES["hinh_anh"]["tmp_name"], $target_dir . $hinh_anh)) {
                $stmt = $conn->prepare("INSERT INTO banners (hinh_anh, duong_link, trang_thai) VALUES (?, ?, 1)");
                $stmt->execute([$hinh_anh, $duong_link]);
                echo "<script>window.location.href='admin_banners.php';</script>";
                exit();
            }
        } else {
            echo "<script>alert('Chỉ chấp nhận ảnh JPG, PNG, WEBP.');</script>";
        }
    }
}

// --- XỬ LÝ XÓA BANNER ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt_get = $conn->prepare("SELECT hinh_anh FROM banners WHERE id = ?");
    $stmt_get->execute([$id]);
    $banner = $stmt_get->fetch();
    if ($banner && file_exists("images/banners/" . $banner['hinh_anh'])) {
        @unlink("images/banners/" . $banner['hinh_anh']);
    }
    $stmt_del = $conn->prepare("DELETE FROM banners WHERE id = ?");
    $stmt_del->execute([$id]);
    echo "<script>window.location.href='admin_banners.php';</script>";
    exit();
}

// --- XỬ LÝ ẨN/HIỆN BANNER ---
if (isset($_GET['action']) && $_GET['action'] == 'toggle' && isset($_GET['id']) && isset($_GET['status'])) {
    $new_status = $_GET['status'] == 1 ? 0 : 1;
    $stmt_update = $conn->prepare("UPDATE banners SET trang_thai = ? WHERE id = ?");
    $stmt_update->execute([$new_status, (int)$_GET['id']]);
    echo "<script>window.location.href='admin_banners.php';</script>";
    exit();
}

$banners = $conn->query("SELECT * FROM banners ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h2 class="fw-extrabold text-dark mb-1 font-heading">Quản Lý Banner Khuyến Mãi</h2>
        <p class="text-muted small mb-0">Tải lên các banner quảng cáo xuất hiện trên thanh Carousel ở Trang chủ</p>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Add Banner Form -->
    <div class="col-lg-5 col-xl-4">
        <div class="admin-card p-4">
            <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom font-heading">
                <i class="fa-solid fa-cloud-arrow-up text-primary me-2"></i> Thêm Banner Mới
            </h5>

            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Tải ảnh Banner <span class="text-danger">*</span></label>
                    <div class="p-2 bg-light rounded-3 d-flex align-items-center justify-content-center mb-2 border" style="height: 130px;">
                        <img id="bannerPreview" src="data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22200%22%20height%3D%2280%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20fill%3D%22%23f1f5f9%22%20width%3D%22200%22%20height%3D%2280%22%2F%3E%3Ctext%20fill%3D%22%2394a3b8%22%20font-family%3D%22sans-serif%22%20font-size%3D%2212%22%20dy%3D%224.5%22%20font-weight%3D%22bold%22%20x%3D%2250%25%22%20y%3D%2250%25%22%20text-anchor%3D%22middle%22%3ECh%E1%BB%8Dn%20Banner%3C%2Ftext%3E%3C%2Fsvg%3E" class="img-fluid rounded" style="max-height: 110px; object-fit: contain;">
                    </div>
                    <input type="file" class="form-control" name="hinh_anh" id="bannerFile" accept="image/*" required>
                    <small class="text-muted mt-1 d-block" style="font-size: 0.75rem;">Kích thước đề xuất: 1200 x 400px (Tỷ lệ 3:1 hoặc 16:9)</small>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-dark">Liên kết khi click vào (Tùy chọn)</label>
                    <input type="text" class="form-control" name="duong_link" placeholder="Ví dụ: index.php?cat_id=1">
                </div>

                <button type="submit" name="btn_add" class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm">
                    <i class="fa-solid fa-upload me-2"></i> Tải Lên Banner
                </button>
            </form>
        </div>
    </div>

    <!-- Right Column: Banners List Table -->
    <div class="col-lg-7 col-xl-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6 class="fw-bold text-dark mb-0 font-heading">
                    <i class="fa-solid fa-images text-primary me-2"></i> Danh Sách Banner Hiện Có (<?php echo count($banners); ?>)
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table admin-table align-middle text-center mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4 text-start">Hình ảnh</th>
                            <th class="text-start">Đường dẫn liên kết</th>
                            <th>Trạng thái</th>
                            <th class="pe-4 text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($banners)): ?>
                            <?php foreach ($banners as $b): 
                                $b_src = file_exists('images/banners/' . $b['hinh_anh']) ? 'images/banners/' . htmlspecialchars($b['hinh_anh']) : 'data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22120%22%20height%3D%2240%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20fill%3D%22%23f1f5f9%22%20width%3D%22120%22%20height%3D%2240%22%2F%3E%3C%2Fsvg%3E';
                            ?>
                                <tr>
                                    <td class="ps-4 text-start">
                                        <img src="<?php echo $b_src; ?>" style="height: 55px; width: 140px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    </td>
                                    <td class="text-start">
                                        <span class="small text-muted text-truncate d-inline-block" style="max-width: 200px;">
                                            <?php echo ($b['duong_link'] !== '#' && !empty($b['duong_link'])) ? htmlspecialchars($b['duong_link']) : '— (Không có)'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($b['trang_thai'] == 1): ?>
                                            <span class="badge bg-success-subtle text-success border border-success px-3 py-1 rounded-pill small fw-semibold">
                                                <i class="fa-solid fa-eye me-1"></i> Đang hiển thị
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary px-3 py-1 rounded-pill small">
                                                <i class="fa-solid fa-eye-slash me-1"></i> Đang ẩn
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="d-inline-flex gap-1">
                                            <a href="admin_banners.php?action=toggle&id=<?php echo $b['id']; ?>&status=<?php echo $b['trang_thai']; ?>" class="btn btn-sm btn-light border rounded-circle p-2 <?php echo $b['trang_thai'] == 1 ? 'text-warning' : 'text-success'; ?>" title="<?php echo $b['trang_thai'] == 1 ? 'Ẩn banner' : 'Hiện banner'; ?>">
                                                <i class="fa-solid <?php echo $b['trang_thai'] == 1 ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                                            </a>
                                            <a href="admin_banners.php?action=delete&id=<?php echo $b['id']; ?>" class="btn btn-sm btn-light border rounded-circle p-2 text-danger" onclick="return confirm('Bạn có chắc muốn xóa banner này?');" title="Xóa banner">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="py-4 text-muted">Chưa có banner nào.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('bannerFile').addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        document.getElementById('bannerPreview').src = URL.createObjectURL(e.target.files[0]);
    }
});
</script>

<?php include 'admin_footer.php'; ?>