<?php
include 'header.php';
require_once 'send_mail.php'; // Gọi file gửi mail

// Nếu giỏ hàng trống thì quay về trang chủ
if (empty($_SESSION['cart'])) {
    echo "<script>window.location.href='index.php';</script>";
    exit();
}

$thong_bao = ''; 
$kieu_thong_bao = 'success';

// Xử lý lưu đơn hàng và trừ tồn kho
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_dathang'])) {
    $ten_kh = $_POST['ten_kh']; 
    $sdt = $_POST['sdt']; 
    $email_kh = $_POST['email']; // Giả sử bạn thêm input email vào form
    $dia_chi = $_POST['dia_chi']; 
    $ghi_chu = $_POST['ghi_chu'] ?? '';
    $tong_tien_don_hang = 0;

    $ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $stmt = $conn->prepare("SELECT id, ten_sp, gia FROM san_pham WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $san_phams = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $chi_tiet_html = "<table border='1' cellpadding='5' style='border-collapse: collapse;'><tr><th>Sản phẩm</th><th>SL</th><th>Giá</th></tr>";
    foreach ($san_phams as $sp) {
        $so_luong = $_SESSION['cart'][$sp['id']];
        $tong_tien_don_hang += $sp['gia'] * $so_luong;
        $chi_tiet_html .= "<tr><td>{$sp['ten_sp']}</td><td>{$so_luong}</td><td>".number_format($sp['gia'],0,',','.')." đ</td></tr>";
    }
    $chi_tiet_html .= "</table><p>Tổng cộng: <b>".number_format($tong_tien_don_hang,0,',','.')." đ</b></p>";

    try {
        $conn->beginTransaction();

        $sql_don_hang = "INSERT INTO don_hang (khach_hang_id, ten_khach_hang, so_dien_thoai, dia_chi, ghi_chu, tong_tien) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_don_hang = $conn->prepare($sql_don_hang);
        $stmt_don_hang->execute([isset($_SESSION['khach_hang_id']) ? $_SESSION['khach_hang_id'] : NULL, $ten_kh, $sdt, $dia_chi, $ghi_chu, $tong_tien_don_hang]);
        $don_hang_id = $conn->lastInsertId();

        $stmt_chi_tiet = $conn->prepare("INSERT INTO chi_tiet_don_hang (don_hang_id, san_pham_id, so_luong, gia) VALUES (?, ?, ?, ?)");
        $stmt_tru_kho = $conn->prepare("UPDATE san_pham SET so_luong_ton = so_luong_ton - ? WHERE id = ?");

        foreach ($_SESSION['cart'] as $sp_id => $so_luong) {
            $gia_sp = 0;
            foreach($san_phams as $s) if($s['id'] == $sp_id) $gia_sp = $s['gia'];
            $stmt_chi_tiet->execute([$don_hang_id, $sp_id, $so_luong, $gia_sp]);
            $stmt_tru_kho->execute([$so_luong, $sp_id]);
        }

        $conn->commit();

        // Gửi Email
        $noi_dung = "<h1>Đơn hàng #$don_hang_id đã được xác nhận</h1><p>Chào $ten_kh, cảm ơn bạn đã mua sắm tại TechStore.</p>" . $chi_tiet_html;
        gui_email_xac_nhan($email_kh, $ten_kh, $don_hang_id, $noi_dung);

        unset($_SESSION['cart']);
        $thong_bao = "Đặt hàng thành công! Mã đơn hàng: <strong>#MĐH-" . $don_hang_id . "</strong>. Vui lòng kiểm tra Email.";
    } catch (Exception $e) {
        $conn->rollBack();
        $kieu_thong_bao = 'danger'; $thong_bao = "Lỗi đặt hàng: " . $e->getMessage();
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 p-4">
            <h2 class="fw-bold mb-4">Xác Nhận Thanh Toán</h2>
            <?php if ($thong_bao != ''): ?>
                <div class="alert alert-<?php echo $kieu_thong_bao; ?>"><?php echo $thong_bao; ?></div>
                <a href="index.php" class="btn btn-primary">Về trang chủ</a>
            <?php else: ?>
                <form method="POST" action="checkout.php">
                    <div class="mb-3"><label>Họ tên</label><input type="text" name="ten_kh" class="form-control" required></div>
                    <div class="mb-3"><label>Email nhận hóa đơn</label><input type="email" name="email" class="form-control" required></div>
                    <div class="mb-3"><label>Số điện thoại</label><input type="text" name="sdt" class="form-control" required></div>
                    <div class="mb-3"><label>Địa chỉ</label><textarea name="dia_chi" class="form-control" required></textarea></div>
                    <button type="submit" name="btn_dathang" class="btn btn-success w-100">Xác nhận đặt hàng</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>