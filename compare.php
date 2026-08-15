<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'db.php';

// Khởi tạo session so sánh nếu chưa có
if (!isset($_SESSION['compare']) || !is_array($_SESSION['compare'])) {
    $_SESSION['compare'] = [];
}

// --- 1. XỬ LÝ THÊM SẢN PHẨM VÀO DANH SÁCH SO SÁNH ---
if (isset($_GET['action']) && $_GET['action'] == 'add' && isset($_GET['id'])) {
    $id_them = (int)$_GET['id'];
    
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
        $_SESSION['compare'] = array_values($_SESSION['compare']);
    }
    echo "<script>window.location.href='compare.php';</script>";
    exit();
}

// --- 3. XỬ LÝ XÓA TẤT CẢ ---
if (isset($_GET['action']) && $_GET['action'] == 'clear') {
    $_SESSION['compare'] = [];
    echo "<script>window.location.href='compare.php';</script>";
    exit();
}

// --- 4. LẤY DỮ LIỆU CÁC SẢN PHẨM ĐANG SO SÁNH ---
$compared_products = [];
$all_attribute_keys = [];

if (!empty($_SESSION['compare'])) {
    $placeholders = str_repeat('?,', count($_SESSION['compare']) - 1) . '?';
    $stmt = $conn->prepare("SELECT s.*, d.ten_danh_muc FROM san_pham s LEFT JOIN danh_muc d ON s.danh_muc_id = d.id WHERE s.id IN ($placeholders)");
    $stmt->execute($_SESSION['compare']);
    $compared_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($compared_products as $p) {
        $specs = [];
        if (!empty($p['thong_so_ky_thuat'])) {
            $specs = json_decode($p['thong_so_ky_thuat'], true);
        } elseif (!empty($p['thong_so'])) {
            $specs = json_decode($p['thong_so'], true);
        }
        if (is_array($specs)) {
            foreach (array_keys($specs) as $key) {
                if (!in_array($key, $all_attribute_keys)) {
                    $all_attribute_keys[] = $key;
                }
            }
        }
    }
}

include 'header.php';
$placeholder_svg = "data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22120%22%20height%3D%22120%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20fill%3D%22%23f8fafc%22%20width%3D%22120%22%20height%3D%22120%22%2F%3E%3Ctext%20fill%3D%22%2394a3b8%22%20font-family%3D%22sans-serif%22%20font-size%3D%2210%22%20dy%3D%223.5%22%20font-weight%3D%22bold%22%20x%3D%2250%25%22%20y%3D%2250%25%22%20text-anchor%3D%22middle%22%3ENo%20Image%3C%2Ftext%3E%3C%2Fsvg%3E";
?>

<div class="container py-4" style="min-height: 65vh;">
    <!-- Page Title & Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="fw-bold text-dark fs-3 mb-1"><i class="fa-solid fa-code-compare text-primary me-2"></i> So Sánh Sản Phẩm</h1>
            <p class="text-muted small mb-0">Đặt các thiết bị công nghệ lên bàn cân để chọn ra sản phẩm phù hợp nhất với bạn</p>
        </div>
        
        <?php if (!empty($compared_products)): ?>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-semibold">
                    Đang so sánh: <?php echo count($compared_products); ?>/3 sản phẩm
                </span>
                <a href="compare.php?action=clear" class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="return confirm('Bạn có chắc muốn xóa toàn bộ danh sách so sánh?');">
                    <i class="fa-solid fa-trash-can me-1"></i> Xóa tất cả
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($compared_products)): ?>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5" style="background: white;">
            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center mb-0" style="table-layout: fixed; width: 100%; min-width: 700px;">
                    <!-- Product Header Cards -->
                    <thead>
                        <tr class="bg-light">
                            <th style="width: 22%;" class="text-start ps-4 py-3 align-middle bg-light text-muted small text-uppercase">
                                Tiêu chí đối chiếu
                            </th>
                            
                            <?php foreach ($compared_products as $p): 
                                $p_img = (!empty($p['hinh_anh']) && file_exists('images/' . $p['hinh_anh'])) ? 'images/' . htmlspecialchars($p['hinh_anh']) : $placeholder_svg;
                                $p_in_stock = ($p['so_luong_ton'] > 0);
                            ?>
                                <th style="width: <?php echo 78 / count($compared_products); ?>%;" class="p-3 bg-white position-relative">
                                    <a href="compare.php?action=remove&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-light text-danger position-absolute top-0 end-0 m-2 rounded-circle shadow-xs" title="Xóa khỏi bảng so sánh">
                                        <i class="fa-solid fa-xmark"></i>
                                    </a>

                                    <div class="d-flex align-items-center justify-content-center p-2 mb-2 bg-light rounded-3" style="height: 140px;">
                                        <img src="<?php echo $p_img; ?>" alt="<?php echo htmlspecialchars($p['ten_sp']); ?>" style="max-height: 120px; max-width: 100%; object-fit: contain;">
                                    </div>

                                    <div class="fw-bold text-dark text-truncate mb-1 px-2" title="<?php echo htmlspecialchars($p['ten_sp']); ?>">
                                        <a href="product_detail.php?id=<?php echo $p['id']; ?>" class="text-decoration-none text-dark hover-primary">
                                            <?php echo htmlspecialchars($p['ten_sp']); ?>
                                        </a>
                                    </div>

                                    <div class="text-danger fw-extrabold fs-5 mb-2 font-heading">
                                        <?php echo number_format($p['gia'], 0, ',', '.'); ?> ₫
                                    </div>

                                    <?php if ($p_in_stock): ?>
                                        <form method="POST" action="cart.php">
                                            <input type="hidden" name="action" value="add">
                                            <input type="hidden" name="id_sp" value="<?php echo (int)$p['id']; ?>">
                                            <input type="hidden" name="so_luong" value="1">
                                            <button type="submit" class="btn btn-sm btn-primary rounded-pill w-100 fw-bold py-2 shadow-xs">
                                                <i class="fa-solid fa-cart-plus me-1"></i> Thêm giỏ
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary rounded-pill w-100 fw-bold py-2" disabled>Hết hàng</button>
                                    <?php endif; ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>

                    <tbody>
                        <!-- Category Row -->
                        <tr>
                            <td class="text-start ps-4 fw-bold bg-light small text-muted">Danh mục</td>
                            <?php foreach ($compared_products as $p): ?>
                                <td class="fw-semibold text-secondary small">
                                    <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($p['ten_danh_muc'] ?: 'Thiết bị công nghệ'); ?></span>
                                </td>
                            <?php endforeach; ?>
                        </tr>

                        <!-- Stock Status Row -->
                        <tr>
                            <td class="text-start ps-4 fw-bold bg-light small text-muted">Tình trạng kho</td>
                            <?php foreach ($compared_products as $p): ?>
                                <td>
                                    <?php if ($p['so_luong_ton'] > 0): ?>
                                        <span class="badge bg-success-subtle text-success fw-semibold"><i class="fa-solid fa-circle-check me-1"></i> Còn hàng (<?php echo (int)$p['so_luong_ton']; ?>)</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger fw-semibold"><i class="fa-solid fa-circle-xmark me-1"></i> Tạm hết hàng</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>

                        <!-- Description Row -->
                        <tr>
                            <td class="text-start ps-4 fw-bold bg-light small text-muted">Mô tả tổng quan</td>
                            <?php foreach ($compared_products as $p): ?>
                                <td class="text-start small text-muted p-3" style="vertical-align: top;">
                                    <div style="max-height: 100px; overflow-y: auto; line-height: 1.6;">
                                        <?php echo nl2br(htmlspecialchars($p['mo_ta'] ?: 'Chưa có mô tả chi tiết.')); ?>
                                    </div>
                                </td>
                            <?php endforeach; ?>
                        </tr>

                        <!-- Dynamic Specs Header & Rows -->
                        <?php if (!empty($all_attribute_keys)): ?>
                            <tr style="background: #eff6ff;">
                                <td colspan="<?php echo count($compared_products) + 1; ?>" class="text-start ps-4 fw-bold text-primary text-uppercase py-2" style="font-size: 0.8rem; letter-spacing: 0.5px;">
                                    <i class="fa-solid fa-sliders me-1"></i> Thông Số Kỹ Thuật Chi Tiết
                                </td>
                            </tr>

                            <?php foreach ($all_attribute_keys as $key): ?>
                                <tr>
                                    <td class="text-start ps-4 fw-bold bg-light small text-dark"><?php echo htmlspecialchars($key); ?></td>
                                    <?php foreach ($compared_products as $p): ?>
                                        <td class="small p-3">
                                            <?php 
                                            $specs = [];
                                            if (!empty($p['thong_so_ky_thuat'])) {
                                                $specs = json_decode($p['thong_so_ky_thuat'], true);
                                            } elseif (!empty($p['thong_so'])) {
                                                $specs = json_decode($p['thong_so'], true);
                                            }
                                            if (is_array($specs) && isset($specs[$key]) && trim($specs[$key]) !== '') {
                                                echo '<span class="fw-semibold text-dark">' . htmlspecialchars($specs[$key]) . '</span>';
                                            } else {
                                                echo '<span class="text-muted opacity-50">—</span>';
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
        <!-- Empty Compare State -->
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center my-4" style="background: white;">
            <div class="mb-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light" style="width: 100px; height: 100px;">
                    <i class="fa-solid fa-code-compare text-muted" style="font-size: 3rem;"></i>
                </div>
            </div>
            <h3 class="fw-bold text-dark mb-2">Chưa có sản phẩm nào trong bảng so sánh!</h3>
            <p class="text-muted mb-4" style="max-width: 440px; margin: 0 auto;">
                Hãy chọn nút <i class="fa-solid fa-code-compare text-primary"></i> tại các thẻ sản phẩm hoặc trang chi tiết để thêm tối đa 3 sản phẩm và so sánh thông số trực tiếp.
            </p>
            <div>
                <a href="index.php" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow-sm">
                    <i class="fa-solid fa-arrow-left me-2"></i> Khám Phá Sản Phẩm Ngay
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>