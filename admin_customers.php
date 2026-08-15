<?php
include 'admin_header.php';
include 'admin_sidebar.php';
require_once 'db.php';

$thong_bao = '';

// --- XỬ LÝ THÊM KHÁCH HÀNG ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_customer') {
    $ho_ten = trim($_POST['ho_ten']); 
    $email = trim($_POST['email']);
    $mat_khau_hashed = password_hash($_POST['mat_khau'], PASSWORD_DEFAULT);
    $so_dien_thoai = trim($_POST['so_dien_thoai']); 
    $dia_chi = trim($_POST['dia_chi']);

    try {
        $stmt = $conn->prepare("INSERT INTO khach_hang (ho_ten, email, mat_khau, so_dien_thoai, dia_chi) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$ho_ten, $email, $mat_khau_hashed, $so_dien_thoai, $dia_chi]);
        echo "<script>window.location.href='admin_customers.php';</script>"; 
        exit();
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $thong_bao = "<div class='alert alert-danger mb-3'><i class='fa-solid fa-circle-exclamation me-2'></i>Lỗi: Email này đã được đăng ký!</div>";
        }
    }
}

// --- XỬ LÝ CẬP NHẬT KHÁCH HÀNG ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit_customer') {
    $id = (int)$_POST['id'];
    $ho_ten = trim($_POST['ho_ten']); 
    $email = trim($_POST['email']);
    $so_dien_thoai = trim($_POST['so_dien_thoai']); 
    $dia_chi = trim($_POST['dia_chi']);

    try {
        if (!empty($_POST['mat_khau'])) {
            $mat_khau_hashed = password_hash($_POST['mat_khau'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE khach_hang SET ho_ten=?, email=?, mat_khau=?, so_dien_thoai=?, dia_chi=? WHERE id=?");
            $stmt->execute([$ho_ten, $email, $mat_khau_hashed, $so_dien_thoai, $dia_chi, $id]);
        } else {
            $stmt = $conn->prepare("UPDATE khach_hang SET ho_ten=?, email=?, so_dien_thoai=?, dia_chi=? WHERE id=?");
            $stmt->execute([$ho_ten, $email, $so_dien_thoai, $dia_chi, $id]);
        }
        echo "<script>window.location.href='admin_customers.php';</script>"; 
        exit();
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $thong_bao = "<div class='alert alert-danger mb-3'><i class='fa-solid fa-circle-exclamation me-2'></i>Lỗi: Email bị trùng với tài khoản khác!</div>";
        }
    }
}

// Xóa khách hàng
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $conn->prepare("DELETE FROM khach_hang WHERE id = ?")->execute([(int)$_GET['id']]);
    echo "<script>window.location.href='admin_customers.php';</script>"; 
    exit();
}

$is_edit = false;
$edit_data = [];
if (isset($_GET['edit_id'])) {
    $is_edit = true;
    $stmt = $conn->prepare("SELECT * FROM khach_hang WHERE id = ?");
    $stmt->execute([(int)$_GET['edit_id']]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}

$khach_hang_list = $conn->query("SELECT * FROM khach_hang ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h2 class="fw-extrabold text-dark mb-1 font-heading">Quản Lý Khách Hàng</h2>
        <p class="text-muted small mb-0">Quản lý danh sách tài khoản khách hàng, địa chỉ giao hàng và thông tin liên hệ</p>
    </div>
    <?php if ($is_edit): ?>
        <a href="admin_customers.php" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="fa-solid fa-plus me-1"></i> Thêm tài khoản mới
        </a>
    <?php endif; ?>
</div>

<?php echo $thong_bao; ?>

<div class="row g-4">
    <!-- Left Column: Add / Edit Customer Form -->
    <div class="col-lg-5 col-xl-4">
        <div class="admin-card p-4">
            <h5 class="fw-bold text-dark mb-4 pb-2 border-bottom font-heading">
                <i class="fa-solid <?php echo $is_edit ? 'fa-user-pen text-warning' : 'fa-user-plus text-primary'; ?> me-2"></i>
                <?php echo $is_edit ? 'Cập Nhật Tài Khoản' : 'Thêm Tài Khoản Mới'; ?>
            </h5>

            <form method="POST" action="admin_customers.php">
                <input type="hidden" name="action" value="<?php echo $is_edit ? 'edit_customer' : 'add_customer'; ?>">
                <?php if ($is_edit): ?>
                    <input type="hidden" name="id" value="<?php echo (int)$edit_data['id']; ?>">
                <?php endif; ?>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Họ và tên <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="ho_ten" required placeholder="Nguyễn Văn A" value="<?php echo $is_edit ? htmlspecialchars($edit_data['ho_ten']) : ''; ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Email đăng nhập <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" name="email" required placeholder="name@example.com" value="<?php echo $is_edit ? htmlspecialchars($edit_data['email']) : ''; ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Mật khẩu <?php echo !$is_edit ? '<span class="text-danger">*</span>' : '(Tùy chọn)'; ?></label>
                    <input type="password" class="form-control" name="mat_khau" <?php echo !$is_edit ? 'required' : ''; ?> placeholder="<?php echo $is_edit ? 'Bỏ trống nếu không muốn đổi mật khẩu' : 'Nhập mật khẩu...'; ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Số điện thoại</label>
                    <input type="tel" class="form-control" name="so_dien_thoai" placeholder="09xxxxxxxx" value="<?php echo $is_edit ? htmlspecialchars($edit_data['so_dien_thoai']) : ''; ?>">
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-dark">Địa chỉ nhận hàng</label>
                    <textarea class="form-control" name="dia_chi" rows="2" placeholder="Số nhà, tên đường, quận/huyện..."><?php echo $is_edit ? htmlspecialchars($edit_data['dia_chi']) : ''; ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm">
                    <i class="fa-solid fa-floppy-disk me-2"></i> <?php echo $is_edit ? 'Lưu Thay Đổi' : 'Tạo Tài Khoản'; ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Right Column: Customer List Table -->
    <div class="col-lg-7 col-xl-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6 class="fw-bold text-dark mb-0 font-heading">
                    <i class="fa-solid fa-users text-primary me-2"></i> Danh Sách Khách Hàng (<?php echo count($khach_hang_list); ?>)
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table admin-table align-middle text-center mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4 text-start">Khách hàng</th>
                            <th class="text-start">Email</th>
                            <th>Số điện thoại</th>
                            <th class="pe-4 text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($khach_hang_list)): ?>
                            <?php foreach ($khach_hang_list as $kh): ?>
                                <tr>
                                    <td class="ps-4 text-start">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                <?php echo mb_strtoupper(mb_substr($kh['ho_ten'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                                            </div>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($kh['ho_ten']); ?></div>
                                        </div>
                                    </td>
                                    <td class="text-start small text-muted"><?php echo htmlspecialchars($kh['email']); ?></td>
                                    <td class="small fw-semibold"><?php echo !empty($kh['so_dien_thoai']) ? htmlspecialchars($kh['so_dien_thoai']) : '—'; ?></td>
                                    <td class="pe-4 text-end">
                                        <div class="d-inline-flex gap-1">
                                            <a href="admin_customers.php?edit_id=<?php echo $kh['id']; ?>" class="btn btn-sm btn-light border rounded-circle p-2 text-primary" title="Sửa thông tin">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <a href="admin_customers.php?action=delete&id=<?php echo $kh['id']; ?>" class="btn btn-sm btn-light border rounded-circle p-2 text-danger" onclick="return confirm('Bạn có chắc muốn xóa tài khoản này?');" title="Xóa tài khoản">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="py-4 text-muted">Chưa có khách hàng nào đăng ký.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>