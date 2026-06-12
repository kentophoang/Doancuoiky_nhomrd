</div>
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container"><p>&copy; 2026 TechStore - Cửa hàng điện tử uy tín</p></div>
    </footer>

    <style>
        .mobile-nav { display: none; position: fixed; bottom: 0; left: 0; right: 0; background: #fff; height: 60px; border-top: 1px solid #ddd; z-index: 1000; box-shadow: 0 -2px 5px rgba(0,0,0,0.05); }
        @media (max-width: 767px) { .mobile-nav { display: flex; justify-content: space-around; align-items: center; } }
        .mobile-nav a { color: #6c757d; font-size: 0.75rem; text-decoration: none; text-align: center; }
        .mobile-nav a i { display: block; font-size: 1.2rem; margin-bottom: 2px; }
        .mobile-nav a.active { color: #0d6efd; }
    </style>
    <div class="mobile-nav">
        <a href="index.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>"><i class="fa-solid fa-house"></i> Trang chủ</a>
        <a href="index.php"><i class="fa-solid fa-list"></i> Danh mục</a>
        <a href="cart.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'cart.php') ? 'active' : ''; ?>"><i class="fa-solid fa-cart-shopping"></i> Giỏ hàng</a>
        <a href="profile.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : ''; ?>"><i class="fa-solid fa-user"></i> Tài khoản</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>