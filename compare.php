<?php
include 'header.php';

// Khởi tạo session so sánh nếu chưa có
if (!isset($_SESSION['compare'])) {
    $_SESSION['compare'] = [];
}

// --- 1. XỬ LÝ THÊM SẢN PHẨM VÀO DANH SÁCH SO SÁNH ---
if (isset($_GET['action']) && $_GET['action'] == 'add' && isset($_GET['id'])) {
    $id_them = (int)$_GET['id'];
    
    // Kiểm tra xem sản phẩm đã có trong danh sách chưa
    if (!in_array($id_them, $_SESSION['compare'])) {
        if (count($_SESSION['compare']) >= 3) {
            echo "<script>alert('Bạn chỉ có thể so sánh tối đa 3 sản phẩm cùng lúc!'); window.history.back();</script>";
            exit();
        }
        $_SESSION['compare'][] = $id_them;
    }
    echo "<script>window.location.href='compare.php';</script>";
    exit();
}

// --- 2. XỬ LÝ XÓA SẢN PHẨM KHỎI DANH SÁCH SO SÁNH ---
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $id_xoa = (int)$_GET['id'];
    if (($key = array_search($id_xoa, $_SESSION['compare'])) !== false) {
        unset($_SESSION['compare'][$key]);
        $_SESSION['compare'] = array_values($_SESSION['compare']); // Reset lại chỉ số mảng
    }
    echo "<script>window.location.href='compare.php';</script>";
    exit();
}

// --- 3. LẤY DỮ LIỆU CÁC SẢN PHẨM ĐANG SO SÁNH ---
$compared_products = [];
$all_attribute_keys = []; // Mảng chứa tất cả các tên thuộc tính độc nhất của các sản phẩm

if (!empty($_SESSION['compare'])) {
    $placeholders = str_repeat('?,', count($_SESSION['compare']) - 1) . '?';
    $stmt = $conn->prepare("SELECT s.*, d.ten_danh_muc FROM san_pham s LEFT JOIN danh_muc d ON s.danh_muc_id = d.id WHERE s.id IN ($placeholders)");
    $stmt->execute($_SESSION['compare']);
    $compared_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Thu thập tất cả các key thông số kỹ thuật từ chuỗi JSON của từng sản phẩm
    foreach ($compared_products as $p) {
        if (!empty($p['thong_so_ky_thuat'])) {
            $specs = json_decode($p['thong_so_ky_thuat'], true);
            if (is_array($specs)) {
                foreach (array_keys($specs) as $key) {
                    if (!in_array($key, $all_attribute_keys)) {
                        $all_attribute_keys[] = $key; // Chỉ lấy các thuộc tính không trùng nhau
                    }
                }
            }
        }
    }
}
?>

<div class="mb-4">
    <h2 class="fw-bold text-dark"><i class="fa-solid fa-code-compare text-primary"></i> So Sánh Sản Phẩm</h2>
    <p class="text-muted">Đặt các sản phẩm công nghệ lên bàn cân để tìm ra lựa chọn tối ưu nhất.</p>
</div>

<?php if (count($compared_products) > 0): ?>
    <div class="card shadow-sm border-0 bg-white p-3 rounded">
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center" style="table-layout: fixed; width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 20%; background-color: #f8f9fa;" class="text-start ps-3">Tiêu chí</th>
                        
                        <?php foreach ($compared_products as $p): ?>
                            <th style="width: <?php echo 80 / count($compared_products); ?>%;">
                                <div class="position-relative p-2">
                                    <a href="compare.php?action=remove&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-danger position-absolute top-0 end-0 py-0 px-1" title="Xóa khỏi so sánh">
                                        <i class="fa-solid fa-xmark"></i>
                                    </a>
                                    
                                    <div style="height: 120px;" class="d-flex align-items-center justify-content-center mb-2">
                                        <?php if (!empty($p['hinh_anh']) && file_exists('images/' . $p['hinh_anh'])): ?>
                                            <img src="images/<?php echo htmlspecialchars($p['hinh_anh']); ?>" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                                        <?php else: ?>
                                            <i class="fa-solid fa-image fa-3x text-muted"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="fw-bold text-primary text-truncate mb-1" title="<?php echo htmlspecialchars($p['ten_sp']); ?>">
                                        <?php echo htmlspecialchars($p['ten_sp']); ?>
                                    </div>
                                    <div class="text-danger fw-bold mb-2"><?php echo number_format($p['gia'], 0, ',', '.'); ?> ₫</div>
                                    
                                    <form method="POST" action="cart.php">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold"><i class="fa-solid fa-cart-plus"></i> Mua ngay</button>
                                    </form>
                                </div>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-start ps-3 fw-bold bg-light">Danh mục tuyển chọn</td>
                        <?php foreach ($compared_products as $p): ?>
                            <td class="text-secondary fw-semibold"><?php echo htmlspecialchars($p['ten_danh_muc'] ?: 'Chưa phân loại'); ?></td>
                        <?php endforeach; ?>
                    </tr>

                    <tr>
                        <td class="text-start ps-3 fw-bold bg-light">Tổng quan sản phẩm</td>
                        <?php foreach ($compared_products as $p): ?>
                            <td class="text-start small text-muted" style="vertical-align: top;">
                                <div style="max-height: 80px; overflow-y: auto;">
                                    <?php echo nl2br(htmlspecialchars($p['mo_ta'] ?: 'Chưa có mô tả cụ thể.')); ?>
                                </div>
                            </td>
                        <?php endforeach; ?>
                    </tr>

                    <?php if (count($all_attribute_keys) > 0): ?>
                        <tr class="table-secondary">
                            <td colspan="<?php echo count($compared_products) + 1; ?>" class="text-start ps-3 fw-bold text-uppercase text-dark" style="font-size: 0.85rem; letter-spacing: 0.5px;">Cấu hình chi tiết</td>
                        </tr>
                        <?php foreach ($all_attribute_keys as $key): ?>
                            <tr>
                                <td class="text-start ps-3 fw-bold bg-light"><?php echo htmlspecialchars($key); ?></td>
                                
                                <?php foreach ($compared_products as $p): ?>
                                    <td>
                                        <?php 
                                        $specs = !empty($p['thong_so_ky_thuat']) ? json_decode($p['thong_so_ky_thuat'], true) : [];
                                        if (is_array($specs) && isset($specs[$key]) && trim($specs[$key]) !== '') {
                                            echo '<span class="fw-semibold text-dark">' . htmlspecialchars($specs[$key]) . '</span>';
                                        } else {
                                            echo '<span class="text-muted small">—</span>'; // Hiện dấu gạch ngang nếu sản phẩm này không có thông số đó
                                        }
                                        ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="card shadow-sm border-0 text-center py-5 bg-white">
        <div class="card-body">
            <i class="fa-solid fa-code-compare fa-4x text-muted mb-3 animate-pulse"></i>
            <h4 class="text-secondary fw-bold">Chưa có sản phẩm nào để so sánh!</h4>
            <p class="text-muted small">Hãy bấm vào nút "So sánh" ở trang chi tiết của các sản phẩm để thêm vào bảng đối chiếu.</p>
            <a href="index.php" class="btn btn-primary px-4 fw-bold mt-2"><i class="fa-solid fa-arrow-left me-1"></i> Quay lại mua sắm</a>
        </div>
    </div>
<?php endif; ?>

<?php include 'footer.php'; ?>