<?php
require_once 'includes/auth_check.php';
require_once '../config/connection.php';

// Initialize variables
$message = '';
$orders = [];
$filtered_status = $_GET['status'] ?? '';
$search_term = $_GET['search'] ?? '';
$date_filter = $_GET['date'] ?? '';

try {
  // Prepare base query
  $query = "
        SELECT 
            o.*,
            u.username,
            u.email,
            sa.full_name,
            sa.address_line1,
            sa.city,
            sa.state,
            sa.postal_code,
            GROUP_CONCAT(
                CONCAT(p.name, ' (', oi.quantity, ')')
                SEPARATOR ', '
            ) as products
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.user_id
        LEFT JOIN shipping_addresses sa ON o.shipping_address_id = sa.address_id
        LEFT JOIN order_items oi ON o.order_id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.product_id
        WHERE 1=1
    ";

  $params = [];

  // Add filters
  if ($filtered_status) {
    $query .= " AND o.status = :status";
    $params[':status'] = $filtered_status;
  }

  if ($search_term) {
    $query .= " AND (
            u.username LIKE :search 
            OR u.email LIKE :search
            OR sa.full_name LIKE :search
            OR o.order_id LIKE :search
        )";
    $params[':search'] = "%$search_term%";
  }

  if ($date_filter) {
    $query .= " AND DATE(o.created_at) = :date";
    $params[':date'] = $date_filter;
  }

  // Group and order
  $query .= " GROUP BY o.order_id ORDER BY o.created_at DESC";

  // Prepare and execute query
  $stmt = $conn->prepare($query);
  $stmt->execute($params);
  $orders = $stmt->fetchAll();
} catch (PDOException $e) {
  $message = "Error fetching orders: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Order Management - Modern Furniture Store</title>
  <link rel="apple-touch-icon" sizes="180x180" href="../assets/img/favicon_io/apple-touch-icon.png" />
  <link rel="icon" type="image/png" sizes="32x32" href="../assets/img/favicon_io/favicon-32x32.png" />
  <link rel="icon" type="image/png" sizes="16x16" href="../assets/img/favicon_io/favicon-16x16.png" />
  <link rel="manifest" href="../assets/img/favicon_io/site.webmanifest" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="../assets/css/colors.css" />
  <link rel="stylesheet" href="../assets/css/admin/admin.css" />
</head>

<body>
  <div class="admin-wrapper">
    <?php include '../includes/admin-sidebar.php'; ?>

    <main class="admin-main">
      <header class="admin-header">
        <h1 class="h3 m-0">Order Management</h1>
      </header>

      <?php if ($message): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($message ?? ''); ?></div>
      <?php endif; ?>

      <!-- Filter Section -->
      <div class="admin-card mb-4">
        <form method="GET" class="row g-3">
          <div class="col-md-3">
            <select name="status" class="form-control" onchange="this.form.submit()">
              <option value="">Filter by Status</option>
              <option value="pending" <?php echo $filtered_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
              <option value="processing" <?php echo $filtered_status === 'processing' ? 'selected' : ''; ?>>Processing</option>
              <option value="shipped" <?php echo $filtered_status === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
              <option value="delivered" <?php echo $filtered_status === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
              <option value="cancelled" <?php echo $filtered_status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
          </div>
          <div class="col-md-3">
            <input type="date" name="date" class="form-control" value="<?php echo $date_filter; ?>" onchange="this.form.submit()">
          </div>
          <div class="col-md-4">
            <div class="input-group">
              <input type="text" name="search" class="form-control" placeholder="Search orders..." value="<?php echo htmlspecialchars($search_term); ?>">
              <button class="btn btn-primary" type="submit">Search</button>
            </div>
          </div>
          <?php if ($filtered_status || $search_term || $date_filter): ?>
            <div class="col-md-2">
              <a href="orders.php" class="btn btn-secondary w-100">Clear Filters</a>
            </div>
          <?php endif; ?>
        </form>
      </div>

      <!-- Orders Table -->
      <div class="admin-card">
        <div class="table-responsive">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Products</th>
                <th>Total</th>
                <th>Date</th>
                <th>Status</th>
                <th>Shipping Address</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($orders)): ?>
                <tr>
                  <td colspan="8" class="text-center">No orders found</td>
                </tr>
              <?php else: ?>
                <?php foreach ($orders as $order): ?>
                  <tr>
                    <td>#<?php echo str_pad($order['order_id'], 4, '0', STR_PAD_LEFT); ?></td>
                    <td>
                      <div><?php echo htmlspecialchars($order['username'] ?? ''); ?></div>
                      <small class="text-muted"><?php echo htmlspecialchars($order['email'] ?? ''); ?></small>
                    </td>
                    <td>
                      <small><?php echo htmlspecialchars($order['products'] ?? ''); ?></small>
                    </td>
                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                    <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                    <td>
                      <?php
                      $statusClass = match ($order['status']) {
                        'completed', 'delivered' => 'success',
                        'pending' => 'warning',
                        'processing' => 'info',
                        'shipped' => 'primary',
                        'cancelled' => 'danger',
                        default => 'secondary'
                      };
                      ?>
                      <span class="badge bg-<?php echo $statusClass; ?>">
                        <?php echo ucfirst($order['status']); ?>
                      </span>
                    </td>
                    <td> <small>
                        <?php echo htmlspecialchars($order['full_name'] ?? ''); ?><br>
                        <?php echo htmlspecialchars($order['address_line1'] ?? ''); ?><br>
                        <?php echo htmlspecialchars($order['city'] ?? '') . ', ' .
                          htmlspecialchars($order['state'] ?? '') . ' ' .
                          htmlspecialchars($order['postal_code'] ?? ''); ?>
                      </small>
                    </td>
                    <td>
                      <button class="admin-btn admin-btn-warning btn-sm update-order-btn"
                        data-bs-toggle="modal"
                        data-bs-target="#updateOrderModal"
                        data-order-id="<?php echo $order['order_id']; ?>"
                        data-current-status="<?php echo $order['status']; ?>">
                        <i class="fas fa-edit"></i>
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>

  <!-- Update Order Modal -->
  <div class="modal fade" id="updateOrderModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Update Order Status</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="updateOrderForm">
            <input type="hidden" id="orderId" name="order_id">
            <div class="admin-form-group">
              <label class="admin-form-label">Order Status</label> <select class="admin-form-control" name="status" id="orderStatus">
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="admin-btn admin-btn-secondary" data-bs-dismiss="modal">
            Cancel
          </button>
          <button type="button" class="admin-btn admin-btn-primary" id="updateOrderBtn">
            Update Status
          </button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const updateModal = document.getElementById('updateOrderModal');
      const updateOrderForm = document.getElementById('updateOrderForm');
      const orderIdInput = document.getElementById('orderId');
      const orderStatusSelect = document.getElementById('orderStatus');
      const updateOrderBtn = document.getElementById('updateOrderBtn');

      // Update button click handlers
      document.querySelectorAll('.update-order-btn').forEach(button => {
        button.addEventListener('click', function() {
          const orderId = this.dataset.orderId;
          const currentStatus = this.dataset.currentStatus;
          orderIdInput.value = orderId;
          orderStatusSelect.value = currentStatus;
        });
      });

      // Update order status
      updateOrderBtn.addEventListener('click', function() {
        const formData = new FormData(updateOrderForm);

        fetch('handlers/order_handler.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              window.location.reload();
            } else {
              alert(data.message || 'Error updating order status');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('Error updating order status');
          });
      });
    });
  </script>
</body>

</html>