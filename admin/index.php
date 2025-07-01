<?php
require_once 'includes/auth_check.php';
require_once '../config/connection.php';

// Fetch dashboard statistics
try {
  // Total Users
  $stmt = $conn->query("SELECT COUNT(*) as total_users FROM users WHERE is_admin = 0");
  $userStats = $stmt->fetch();
  $totalUsers = $userStats['total_users'];

  // Total Products
  $stmt = $conn->query("SELECT COUNT(*) as total_products FROM products");
  $productStats = $stmt->fetch();
  $totalProducts = $productStats['total_products'];

  // Total Orders
  $stmt = $conn->query("SELECT COUNT(*) as total_orders, SUM(total_amount) as total_revenue FROM orders");
  $orderStats = $stmt->fetch();
  $totalOrders = $orderStats['total_orders'] ?? 0;
  $totalRevenue = $orderStats['total_revenue'] ?? 0;

  // Recent Orders
  $stmt = $conn->query("
        SELECT o.*, u.username 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.user_id 
        ORDER BY o.created_at DESC 
        LIMIT 5
    ");
  $recentOrders = $stmt->fetchAll();  // Low Stock Products (less than 5 items)
  $stmt = $conn->query("
        SELECT product_id, name as product_name, stock_quantity 
        FROM products 
        WHERE stock_quantity < 5 
        ORDER BY stock_quantity ASC 
        LIMIT 5
    ");
  $lowStockProducts = $stmt->fetchAll();

  // Recent Reviews
  $stmt = $conn->query("
        SELECT r.*, u.username, p.name as product_name 
        FROM reviews r 
        LEFT JOIN users u ON r.user_id = u.user_id 
        LEFT JOIN products p ON r.product_id = p.product_id 
        ORDER BY r.created_at DESC 
        LIMIT 5
    ");
  $recentReviews = $stmt->fetchAll();
} catch (PDOException $e) {
  $_SESSION['error'] = "Error fetching dashboard data: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard - Modern Furniture Store</title>

  <!-- Favicon -->
  <link
    rel="apple-touch-icon"
    sizes="180x180"
    href="../assets/img/favicon_io/apple-touch-icon.png" />
  <link
    rel="icon"
    type="image/png"
    sizes="32x32"
    href="../assets/img/favicon_io/favicon-32x32.png" />
  <link
    rel="icon"
    type="image/png"
    sizes="16x16"
    href="../assets/img/favicon_io/favicon-16x16.png" />
  <link rel="manifest" href="../assets/img/favicon_io/site.webmanifest" />

  <!-- Bootstrap 5 CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet" />
  <!-- Font Awesome -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <!-- Custom CSS -->
  <link rel="stylesheet" href="../assets/css/colors.css" />
  <link rel="stylesheet" href="../assets/css/admin/admin.css" />
</head>

<body>
  <div class="admin-wrapper">
    <?php include '../includes/admin-sidebar.php'; ?> <!-- Main Content -->
    <main class="admin-main">
      <!-- Header -->
      <header class="admin-header">
        <h1 class="h3 m-0">Dashboard</h1>
        <div class="admin-header-right">
          <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
          <span class="text-muted">Last updated: <?php echo date('F j, Y, g:i a'); ?></span>
        </div>
      </header>
      <!-- Stats Row -->
      <div class="row g-4 mt-4">
        <div class="col-md-3">
          <div class="stats-card primary">
            <i class="fas fa-shopping-cart"></i>
            <h3><?php echo number_format($totalOrders); ?></h3>
            <p>Total Orders</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stats-card success">
            <i class="fas fa-box"></i>
            <h3><?php echo number_format($totalProducts); ?></h3>
            <p>Products</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stats-card warning">
            <i class="fas fa-users"></i>
            <h3><?php echo number_format($totalUsers); ?></h3>
            <p>Customers</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stats-card info">
            <i class="fas fa-dollar-sign"></i>
            <h3>$<?php echo number_format($totalRevenue, 2); ?></h3>
            <p>Total Revenue</p>
          </div>
        </div>
      </div>

      <!-- Recent Orders, Low Stock Products, and Recent Reviews -->
      <div class="row g-4 mt-4">
        <div class="col-md-8">
          <div class="admin-card">
            <div class="admin-card-header">
              <h2 class="admin-card-title">Recent Orders</h2>
              <a href="orders.php" class="admin-btn admin-btn-primary">View All</a>
            </div>
            <div class="table-responsive">
              <table class="admin-table">
                <thead>
                  <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($recentOrders)): ?>
                    <tr>
                      <td colspan="5" class="text-center">No recent orders</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($recentOrders as $order): ?>
                      <tr>
                        <td>#<?php echo str_pad($order['order_id'], 4, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo htmlspecialchars($order['username']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                          <?php
                          $statusClass = match ($order['status']) {
                            'completed' => 'success',
                            'pending' => 'warning',
                            'cancelled' => 'danger',
                            default => 'secondary'
                          };
                          ?>
                          <span class="badge bg-<?php echo $statusClass; ?>">
                            <?php echo ucfirst($order['status']); ?>
                          </span>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="admin-card">
            <div class="admin-card-header">
              <h2 class="admin-card-title">Low Stock Alert</h2>
              <a href="products.php" class="admin-btn admin-btn-primary">View All</a>
            </div>
            <div class="table-responsive">
              <table class="admin-table">
                <thead>
                  <tr>
                    <th>Product</th>
                    <th>Stock</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($lowStockProducts)): ?>
                    <tr>
                      <td colspan="2" class="text-center">No low stock items</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($lowStockProducts as $product): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                        <td>
                          <span class="badge bg-<?php echo $product['stock_quantity'] === 0 ? 'danger' : 'warning'; ?>">
                            <?php echo $product['stock_quantity']; ?> left
                          </span>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-md-12 mt-4">
          <div class="admin-card">
            <div class="admin-card-header">
              <h2 class="admin-card-title">Recent Reviews</h2>
              <a href="reviews.php" class="admin-btn admin-btn-primary">View All</a>
            </div>
            <div class="table-responsive">
              <table class="admin-table">
                <thead>
                  <tr>
                    <th>Review ID</th>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Rating</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($recentReviews)): ?>
                    <tr>
                      <td colspan="5" class="text-center">No recent reviews</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($recentReviews as $review): ?>
                      <tr>
                        <td>#<?php echo str_pad($review['review_id'], 4, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo htmlspecialchars($review['username']); ?></td>
                        <td><?php echo htmlspecialchars($review['product_name']); ?></td>
                        <td>
                          <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                          <?php endfor; ?>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($review['created_at'])); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
    </main>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>