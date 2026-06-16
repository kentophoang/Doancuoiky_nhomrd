<?php
session_start();
include 'header.php';
require_once 'db.php';

// --- XỬ LÝ NHẬN DỮ LIỆU TỪ FORM (Thêm vào giỏ) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $id_sp = (int)$_POST['id_sp'];
    $so_luong = isset($_POST['so_luong']) ? (int)$_POST['so_luong'] : 1;

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$id_sp])) {
        $_SESSION['cart'][$id_sp] += $so_luong;
    } else {
        $_SESSION['cart'][$id_sp] = $so_luong;
    }
    // Dùng JS thay cho header() để tránh lỗi headers already sent
    echo "<script>window.location.href='cart.php';</script>";
    exit();
}

// --- XỬ LÝ XÓA SẢN PHẨM KHỎI GIỎ ---
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $id_remove = (int)$_GET['id'];
    unset($_SESSION['cart'][$id_remove]);
    echo "<script>window.location.href='cart.php';</script>";
    exit();
}
?>

<div class="container py-5" style="min-height: 60vh;">
    <h2 class="mb-4 fw-bold border-start border-primary border-4 ps-3">Giỏ hàng của bạn</h2>

    <?php if (empty($_SESSION['cart'])): ?>
        <div class="alert alert-info">Giỏ hàng đang trống. <a href="index.php" class="alert-link">Quay lại mua sắm</a></div>
    <?php else: ?>
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Ảnh</th>
                            <th class="text-start">Tên sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $tong_tien = 0;
                        foreach ($_SESSION['cart'] as $id => $sl): 
                            $stmt = $conn->prepare("SELECT * FROM san_pham WHERE id = ?");
                            $stmt->execute([$id]);
                            $sp = $stmt->fetch(PDO::FETCH_ASSOC);
                            
                            if ($sp):
                                $thanh_tien = $sp['gia'] * $sl;
                                $tong_tien += $thanh_tien;
                        ?>
                        <tr>
                            <td>
                                <img src="images/<?php echo htmlspecialchars($sp['hinh_anh']); ?>" style="width: 60px; height: 60px; object-fit: cover;" class="rounded border">
                            </td>
                            <td class="text-start fw-bold"><?php echo htmlspecialchars($sp['ten_sp']); ?></td>
                            <td class="text-danger fw-bold"><?php echo number_format($sp['gia'], 0, ',', '.'); ?> ₫</td>
                            <td><span class="badge bg-secondary fs-6"><?php echo $sl; ?></span></td>
                            <td class="text-danger fw-bold"><?php echo number_format($thanh_tien, 0, ',', '.'); ?> ₫</td>
                            <td>
                                <a href="cart.php?action=remove&id=<?php echo $id; ?>" class="btn btn-sm btn-outline-danger">Xóa</a>
                            </td>
                        </tr>
                        <?php endif; endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer p-3 d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Tổng cộng: <span class="text-danger fw-bold"><?php echo number_format($tong_tien, 0, ',', '.'); ?> ₫</span></h4>
                <a href="checkout.php" class="btn btn-success btn-lg">Tiến hành đặt hàng</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>