<?php
$current_page = basename($_SERVER['PHP_SELF']);
$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Administrator';
?>

<!-- Admin Sidebar Navigation -->
<aside class="admin-sidebar" id="adminSidebar">
    <!-- Brand Header -->
    <a href="admin_index.php" class="admin-sidebar-brand">
        <div class="admin-brand-icon">
            <i class="fa-solid fa-bolt-lightning"></i>
        </div>
        <div>
            <div class="admin-brand-name">Tech<span class="text-primary">Store</span></div>
            <div style="font-size: 0.7rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Admin Portal</div>
        </div>
    </a>

    <!-- Navigation Menu Items -->
    <div class="admin-nav-section-title">Tổng quan</div>
    <a class="admin-nav-link <?php echo ($current_page == 'admin_index.php') ? 'active' : ''; ?>" href="admin_index.php">
        <i class="fa-solid fa-chart-pie"></i>
        <span>Bảng điều khiển</span>
    </a>

    <div class="admin-nav-section-title">Cửa hàng & Sản phẩm</div>
    <a class="admin-nav-link <?php echo ($current_page == 'admin_categories.php') ? 'active' : ''; ?>" href="admin_categories.php">
        <i class="fa-solid fa-shapes"></i>
        <span>Quản lý Danh mục</span>
    </a>
    <a class="admin-nav-link <?php echo in_array($current_page, ['admin_products.php', 'admin_product_edit.php']) ? 'active' : ''; ?>" href="admin_products.php">
        <i class="fa-solid fa-box-archive"></i>
        <span>Danh sách Sản phẩm</span>
    </a>
    <a class="admin-nav-link <?php echo ($current_page == 'admin_product_add.php') ? 'active' : ''; ?>" href="admin_product_add.php">
        <i class="fa-solid fa-circle-plus"></i>
        <span>Thêm Sản phẩm mới</span>
    </a>

    <div class="admin-nav-section-title">Bán hàng</div>
    <a class="admin-nav-link <?php echo in_array($current_page, ['admin_orders.php', 'admin_order_details.php']) ? 'active' : ''; ?>" href="admin_orders.php">
        <i class="fa-solid fa-cart-shopping"></i>
        <span>Quản lý Đơn hàng</span>
    </a>

    <div class="admin-nav-section-title">Marketing & Khách hàng</div>
    <a class="admin-nav-link <?php echo ($current_page == 'admin_banners.php') ? 'active' : ''; ?>" href="admin_banners.php">
        <i class="fa-solid fa-image"></i>
        <span>Quản lý Banners</span>
    </a>
    <a class="admin-nav-link <?php echo ($current_page == 'admin_customers.php') ? 'active' : ''; ?>" href="admin_customers.php">
        <i class="fa-solid fa-users"></i>
        <span>Quản lý Khách hàng</span>
    </a>

    <!-- Sidebar Footer -->
    <div class="admin-sidebar-footer">
        <a href="index.php" target="_blank" class="admin-nav-link text-light mb-1 px-3" style="margin: 0; background: rgba(255,255,255,0.05);">
            <i class="fa-solid fa-arrow-up-right-from-square text-primary"></i>
            <span>Xem Cửa Hàng</span>
        </a>
        <a href="admin_logout.php" class="admin-nav-link text-danger px-3 mt-1" style="margin: 0;" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất khỏi trang quản trị?');">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Đăng xuất</span>
        </a>
    </div>
</aside>

<!-- Main Wrapper -->
<div class="admin-main-wrapper">
    <!-- Top Bar -->
    <header class="admin-topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-light d-lg-none rounded-circle border shadow-xs" type="button" onclick="document.getElementById('adminSidebar').classList.toggle('show');">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="d-none d-sm-block text-muted small">
                <i class="fa-solid fa-shield-halved text-success me-1"></i> Phiên làm việc Quản trị viên
            </div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <a href="index.php" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold d-none d-md-inline-flex align-items-center gap-1">
                <i class="fa-solid fa-store"></i>
                <span>Xem Website</span>
            </a>
            
            <div class="dropdown">
                <button class="btn btn-light rounded-pill border d-flex align-items-center gap-2 px-3 py-1 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 0.75rem;">
                        <?php echo mb_strtoupper(mb_substr($admin_name, 0, 1, 'UTF-8'), 'UTF-8'); ?>
                    </div>
                    <span class="small fw-bold text-dark"><?php echo htmlspecialchars($admin_name); ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 py-2 mt-2">
                    <li><a class="dropdown-item py-2 px-3 small" href="admin_index.php"><i class="fa-solid fa-gauge text-primary me-2"></i> Bảng điều khiển</a></li>
                    <li><a class="dropdown-item py-2 px-3 small" href="index.php" target="_blank"><i class="fa-solid fa-store text-success me-2"></i> Xem Cửa hàng</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item py-2 px-3 small text-danger" href="admin_logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i> Đăng xuất</a></li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="admin-content-body">