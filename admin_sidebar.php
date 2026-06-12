<div class="bg-dark text-white p-3 vh-100" style="width: 260px; position: fixed; top: 0; left: 0; overflow-y: auto;">
    <div class="text-center mb-4 border-bottom pb-3 mt-2">
        <h4 class="fw-bold text-uppercase text-warning"><i class="fa-solid fa-laptop-code me-2"></i>TechStore</h4>
        <small class="text-light">Administrator</small>
    </div>
    
    <ul class="nav flex-column gap-2">
        <li class="nav-item">
            <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'admin_index.php' ? 'bg-primary rounded fw-bold' : ''; ?>" href="admin_index.php">
                <i class="fa-solid fa-gauge w-20px text-center me-2"></i> Bảng điều khiển
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'admin_categories.php' ? 'bg-primary rounded fw-bold' : ''; ?>" href="admin_categories.php">
                <i class="fa-solid fa-list w-20px text-center me-2"></i> Quản lý Danh mục
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link text-white <?php echo in_array(basename($_SERVER['PHP_SELF']), ['admin_products.php', 'admin_product_add.php', 'admin_product_edit.php']) ? 'bg-primary rounded fw-bold' : ''; ?>" href="admin_products.php">
                <i class="fa-solid fa-box w-20px text-center me-2"></i> Quản lý Sản phẩm
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'admin_orders.php' ? 'bg-primary rounded fw-bold' : ''; ?>" href="admin_orders.php">
                <i class="fa-solid fa-cart-shopping w-20px text-center me-2"></i> Quản lý Đơn hàng
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'admin_banners.php' ? 'bg-primary rounded fw-bold' : ''; ?>" href="admin_banners.php">
                <i class="fa-solid fa-images w-20px text-center me-2"></i> Quản lý Banner
            </a>
        </li>

        </ul>

    <div class="mt-5 border-top pt-3">
        <a class="nav-link text-danger fw-bold" href="logout.php" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất?');">
            <i class="fa-solid fa-right-from-bracket w-20px text-center me-2"></i> Đăng xuất
        </a>
    </div>
</div>

<style>
    .w-20px { width: 20px; } /* Cố định độ rộng icon để text thẳng hàng */
    body { padding-left: 260px; background-color: #f8f9fa; }
</style>