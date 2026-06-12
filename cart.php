<?php
session_start();
include 'db.php';

if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }

// 1. XỬ LÝ: Thêm vào giỏ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $id = $_POST['id'];
    if (isset($_SESSION['cart'][$id])) { $_SESSION['cart'][$id]++; } else { $_SESSION['cart'][$id] = 1; }
    header("Location: cart.php");
    exit();
}

// 2. XỬ LÝ: Cập nhật số lượng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    if (isset($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $id => $qty) {
            if ($qty > 0) { $_SESSION['cart'][$id] = $qty; } else { unset($_SESSION['cart'][$id]); }
        }
    }
    header("Location: cart.php");
    exit();
}

// 3. XỬ LÝ: Xóa 1 sản phẩm
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $id_to_remove = $_GET['id'];
    if (isset($_SESSION['cart'][$id_to_remove])) { unset($_SESSION['cart'][$id_to_remove]); }
    header("Location: cart.php");
    exit();
}

include 'header.php';
?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-4">
        <h2 class="fw-bold mb-4"><i class="fa-solid fa-cart-shopping text-primary"></i> Giỏ Hàng Của Bạn</h2>
        
        <?php if (empty($_SESSION['cart'])): ?>
            <div class="alert alert-info text-center py-4" role="alert">
                <i class="fa-solid fa-box-open fa-3x mb-3 text-muted"></i>
                <p class="mb-0 fs-5">Giỏ hàng của bạn đang trống.</p>
            </div>
            <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Quay lại cửa hàng</a>
        <?php else: ?>
            <form method="POST" action="cart.php">
                <input type="hidden" name="action" value="update">
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Tên sản phẩm</th>
                                <th>Đơn giá</th>
                                <th style="width: 150px;">Số lượng</th>
                                <th>Thành tiền</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $tong_tien_don_hang = 0;
                            $ids = array_keys($_SESSION['cart']);
                            $placeholders = str_repeat('?,', count($ids) - 1) . '?';
                            $stmt = $conn->prepare("SELECT * FROM san_pham WHERE id IN ($placeholders)");
                            $stmt->execute($ids);
                            
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $id_sp = $row['id'];
                                $so_luong = $_SESSION['cart'][$id_sp];
                                $thanh_tien = $row['gia'] * $so_luong;
                                $tong_tien_don_hang += $thanh_tien;
                                
                                echo "<tr>";
                                echo "<td class='text-start fw-semibold'>" . htmlspecialchars($row['ten_sp']) . "</td>";
                                echo "<td class='text-danger'>" . number_format($row['gia'], 0, ',', '.') . " đ</td>";
                                echo "<td><input type='number' class='form-control text-center mx-auto' style='max-width: 80px;' name='quantities[" . $id_sp . "]' value='" . $so_luong . "' min='1'></td>";
                                echo "<td class='fw-bold text-danger'>" . number_format($thanh_tien, 0, ',', '.') . " đ</td>";
                                echo "<td><a href='cart.php?action=remove&id=" . $id_sp . "' class='btn btn-sm btn-outline-danger'><i class='fa-solid fa-trash'></i> Xóa</a></td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mt-3 border-top pt-3">
                    <button type="submit" class="btn btn-warning text-dark fw-bold">
                        <i class="fa-solid fa-rotate"></i> Cập nhật giỏ hàng
                    </button>
                    <h4 class="mb-0">Tổng cộng: <span class="text-danger fw-bold"><?php echo number_format($tong_tien_don_hang, 0, ',', '.'); ?> VNĐ</span></h4>
                </div>
            </form>
            
            <div class="d-flex justify-content-between mt-4">
                <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Tiếp tục mua sắm</a>
                <a href="checkout.php" class="btn btn-success fw-bold px-4">Tiến hành thanh toán <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>