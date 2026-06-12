<?php
include 'admin_header.php';
include 'admin_sidebar.php';

// --- XỬ LÝ THÊM KHÁCH HÀNG ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_customer') {
    $ho_ten = trim($_POST['ho_ten']); $email = trim($_POST['email']);
    $mat_khau_hashed = password_hash($_POST['mat_khau'], PASSWORD_DEFAULT);
    $so_dien_thoai = trim($_POST['so_dien_thoai']); $dia_chi = trim($_POST['dia_chi']);

    try {
        $stmt = $conn->prepare("INSERT INTO khach_hang (ho_ten, email, mat_khau, so_dien_thoai, dia_chi) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$ho_ten, $email, $mat_khau_hashed, $so_dien_thoai, $dia_chi]);
        echo "<script>window.location.href='admin_customers.php';</script>"; exit();
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) echo "<script>alert('Lỗi: Email này đã được đăng ký!');</script>";
    }
}

// --- XỬ LÝ CẬP NHẬT KHÁCH HÀNG ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit_customer') {
    $id = $_POST['id'];
    $ho_ten = trim($_POST['ho_ten']); $email = trim($_POST['email']);
    $so_dien_thoai = trim($_POST['so_dien_thoai']); $dia_chi = trim($_POST['dia_chi']);

    try {
        if (!empty($_POST['mat_khau'])) {
            // Nếu có nhập pass mới thì cập nhật cả pass
            $mat_khau_hashed = password_hash($_POST['mat_khau'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE khach_hang SET ho_ten=?, email=?, mat_khau=?, so_dien_thoai=?, dia_chi=? WHERE id=?");
            $stmt->execute([$ho_ten, $email, $mat_khau_hashed, $so_dien_thoai, $dia_chi, $id]);
        } else {
            // Nếu không nhập thì bỏ qua cột mat_khau
            $stmt = $conn->prepare("UPDATE khach_hang SET ho_ten=?, email=?, so_dien_thoai=?, dia_chi=? WHERE id=?");
            $stmt->execute([$ho_ten, $email, $so_dien_thoai, $dia_chi, $id]);
        }
        echo "<script>window.location.href='admin_customers.php';</script>"; exit();
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) echo "<script>alert('Lỗi: Email bị trùng với người khác!');</script>";
    }
}

// Xóa khách hàng
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $conn->prepare("DELETE FROM khach_hang WHERE id = ?")->execute([$_GET['id']]);
    echo "<script>window.location.href='admin_customers.php';</script>"; exit();
}

$is_edit = false;
$edit_data = [];
if (isset($_GET['edit_id'])) {
    $is_edit = true;
    $stmt = $conn->prepare("SELECT * FROM khach_hang WHERE id = ?");
    $stmt->execute([$_GET['edit_id']]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}

$khach_hang_list = $conn->query("SELECT * FROM khach_hang ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Quản Lý Khách Hàng</h2>
    <?php if ($is_edit): ?><a href="admin_customers.php" class="btn btn-secondary"><i class="fa-solid fa-plus"></i> Thêm mới</a><?php endif; ?>
</div>

<div class="row">
    <div class="col-xl-4 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header <?php echo $is_edit ? 'bg-warning text-dark' : 'bg-info text-dark'; ?> fw-bold">
                <i class="fa-solid <?php echo $is_edit ? 'fa-user-pen' : 'fa-user-plus'; ?>"></i> 
                <?php echo $is_edit ? 'Cập Nhật Tài Khoản' : 'Thêm Tài Khoản Mới'; ?>
            </div>
            <div class="card-body">
                <form method="POST" action="admin_customers.php">
                    <input type="hidden" name="action" value="<?php echo $is_edit ? 'edit_customer' : 'add_customer'; ?>">
                    <?php if ($is_edit): ?><input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>"><?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="ho_ten" required value="<?php echo $is_edit ? htmlspecialchars($edit_data['ho_ten']) : ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" required value="<?php echo $is_edit ? htmlspecialchars($edit_data['email']) : ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mật khẩu <?php echo !$is_edit ? '<span class="text-danger">*</span>' : ''; ?></label>
                        <input type="password" class="form-control" name="mat_khau" <?php echo !$is_edit ? 'required' : ''; ?> placeholder="<?php echo $is_edit ? 'Bỏ trống nếu không muốn đổi pass' : 'Nhập mật khẩu...'; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Số điện thoại</label>
                        <input type="tel" class="form-control" name="so_dien_thoai" value="<?php echo $is_edit ? htmlspecialchars($edit_data['so_dien_thoai']) : ''; ?>">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Địa chỉ</label>
                        <textarea class="form-control" name="dia_chi" rows="2"><?php echo $is_edit ? htmlspecialchars($edit_data['dia_chi']) : ''; ?></textarea>
                    </div>
                    <button type="submit" class="btn <?php echo $is_edit ? 'btn-warning' : 'btn-info'; ?> w-100 fw-bold">
                        <i class="fa-solid fa-save"></i> <?php echo $is_edit ? 'Lưu Thay Đổi' : 'Tạo Tài Khoản'; ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-center">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th><th class="text-start">Họ tên</th><th>Email</th><th>SĐT</th><th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($khach_hang_list as $kh): ?>
                                <tr>
                                    <td><?php echo $kh['id']; ?></td>
                                    <td class="text-start fw-bold"><?php echo htmlspecialchars($kh['ho_ten']); ?></td>
                                    <td><?php echo htmlspecialchars($kh['email']); ?></td>
                                    <td><?php echo htmlspecialchars($kh['so_dien_thoai']); ?></td>
                                    <td>
                                        <a href="admin_customers.php?edit_id=<?php echo $kh['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen"></i></a>
                                        <a href="admin_customers.php?action=delete&id=<?php echo $kh['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa tài khoản này?');"><i class="fa-solid fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'admin_footer.php'; ?>