<!-- Sidebar -->
<nav class="admin-sidebar">
    <a class="navbar-brand" href="index.php">
        <i class="fas fa-chair"></i> Admin
    </a>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>" href="index.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'products.php') ? 'active' : ''; ?>" href="products.php">
                <i class="fas fa-box"></i> Products
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'categories.php') ? 'active' : ''; ?>" href="categories.php">
                <i class="fas fa-tags"></i> Categories
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'orders.php') ? 'active' : ''; ?>" href="orders.php">
                <i class="fas fa-shopping-cart"></i> Orders
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'users.php') ? 'active' : ''; ?>" href="users.php">
                <i class="fas fa-users"></i> Users
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'reviews.php') ? 'active' : ''; ?>" href="reviews.php">
                <i class="fas fa-star"></i> Reviews
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../index.php">
                <i class="fas fa-home"></i> Main Site
            </a>
        </li>
        <li class="nav-item mt-2">
            <form action="../includes/auth_handler.php" method="POST">
                <input type="hidden" name="action" value="logout">
                <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </li>
    </ul>
</nav>