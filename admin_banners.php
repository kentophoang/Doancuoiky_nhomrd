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
            move_uploaded_file($_FILES["hinh_anh"]["tmp_name"], $target_dir . $hinh_anh);
            
            $stmt = $conn->prepare("INSERT INTO banners (hinh_anh, duong_link) VALUES (?, ?)");
            $stmt->execute([$hinh_anh, $duong_link]);
            echo "<script>window.location.href='admin_banners.php';</script>";
            exit();
        } else {
            echo "<script>alert('Chỉ chấp nhận ảnh JPG, PNG, WEBP.');</script>";
        }
    }
}

// --- XỬ LÝ XÓA BANNER ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    // Xóa file ảnh vật lý
    $stmt_get = $conn->prepare("SELECT hinh_anh FROM banners WHERE id = ?");
    $stmt_get->execute([$id]);
    $banner = $stmt_get->fetch();
    if ($banner && file_exists("images/banners/" . $banner['hinh_anh'])) {
        unlink("images/banners/" . $banner['hinh_anh']);
    }
    // Xóa trong DB
    $stmt_del = $conn->prepare("DELETE FROM banners WHERE id = ?");
    $stmt_del->execute([$id]);
    echo "<script>window.location.href='admin_banners.php';</script>";
    exit();
}

// --- XỬ LÝ ẨN/HIỆN BANNER ---
if (isset($_GET['action']) && $_GET['action'] == 'toggle' && isset($_GET['id']) && isset($_GET['status'])) {
    $new_status = $_GET['status'] == 1 ? 0 : 1;
    $stmt_update = $conn->prepare("UPDATE banners SET trang_thai = ? WHERE id = ?");
    $stmt_update->execute([$new_status, $_GET['id']]);
    echo "<script>window.location.href='admin_banners.php';</script>";
    exit();
}

// Lấy danh sách banner
$banners = $conn->query("SELECT * FROM banners ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid py-4">
    <h2 class="mb-4">Quản Lý Banner Khuyến Mãi</h2>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-bold">Thêm Banner Mới</div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Hình ảnh (Tỷ lệ chuẩn 16:9 hoặc 21:9)</label>
                            <input type="file" class="form-control" name="hinh_anh" accept="image/*" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Đường link khi click vào (Tùy chọn)</label>
                            <input type="text" class="form-control" name="duong_link" placeholder="VD: product_detail.php?id=1">
                        </div>
                        <button type="submit" name="btn_add" class="btn btn-success w-100">Tải lên Banner</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <table class="table table-hover align-middle text-center mb-0">
                    <thead class="table-dark">
                        <tr><th>Hình ảnh</th><th>Đường Link</th><th>Trạng thái</th><th>Thao tác</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($banners as $b): ?>
                        <tr>
                            <td><img src="images/banners/<?php echo $b['hinh_anh']; ?>" style="height: 60px; border-radius: 5px;"></td>
                            <td><?php echo $b['duong_link'] !== '#' ? htmlspecialchars($b['duong_link']) : 'Không có'; ?></td>
                            <td>
                                <?php if ($b['trang_thai'] == 1): ?>
                                    <span class="badge bg-success">Đang hiện</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Đang ẩn</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="admin_banners.php?action=toggle&id=<?php echo $b['id']; ?>&status=<?php echo $b['trang_thai']; ?>" class="btn btn-sm btn-<?php echo $b['trang_thai'] == 1 ? 'warning' : 'success'; ?> me-1">
                                    <i class="fa-solid <?php echo $b['trang_thai'] == 1 ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                                </a>
                                <a href="admin_banners.php?action=delete&id=<?php echo $b['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Xóa banner này?');"><i class="fa-solid fa-trash"></i></a>
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