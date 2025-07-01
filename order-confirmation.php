<?php
require_once 'config/connection.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    header('Location: index.php');
    exit();
}

$order_id = $_GET['order_id'];
$user_id = $_SESSION['user_id'];

try {
    // Get order details
    $stmt = $conn->prepare("
        SELECT o.*, sa.*, pm.*,
               sa.full_name as shipping_name,
               pm.card_holder_name as payment_name
        FROM orders o
        JOIN shipping_addresses sa ON o.shipping_address_id = sa.address_id
        JOIN payment_methods pm ON o.payment_method_id = pm.payment_id
        WHERE o.order_id = :order_id AND o.user_id = :user_id
    ");
    $stmt->execute([':order_id' => $order_id, ':user_id' => $user_id]);
    $order = $stmt->fetch();

    if (!$order) {
        header('Location: index.php');
        exit();
    }

    // Get order items
    $stmt = $conn->prepare("
        SELECT oi.*, p.name, p.image_url
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id = :order_id
    ");
    $stmt->execute([':order_id' => $order_id]);
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    die('Error fetching order details: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Modern Furniture Store</title>

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="./assets/img/favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="./assets/img/favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./assets/img/favicon_io/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/colors.css">
    <link rel="stylesheet" href="assets/css/navigation.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/checkout.css">
</head>

<body>
    <?php include 'includes/nav.php'; ?>

    <div class="checkout-page">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="checkout-section text-center mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                        <h1 class="mt-3">Order Confirmed!</h1>
                        <p class="text-muted">Order #<?php echo $order_id; ?></p>
                        <p>Thank you for your purchase. We'll send you an email with your order details.</p>
                    </div>

                    <div class="checkout-section">
                        <h2 class="section-title">Order Details</h2>

                        <div class="row">
                            <div class="col-md-6">
                                <h3 class="h5 mb-3">Shipping Address</h3>
                                <p>
                                    <?php echo htmlspecialchars($order['shipping_name']); ?><br>
                                    <?php echo htmlspecialchars($order['address_line1']); ?><br>
                                    <?php if ($order['address_line2']): ?>
                                        <?php echo htmlspecialchars($order['address_line2']); ?><br>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($order['city']); ?>,
                                    <?php echo htmlspecialchars($order['state']); ?>
                                    <?php echo htmlspecialchars($order['postal_code']); ?><br>
                                    <?php echo htmlspecialchars($order['country']); ?><br>
                                    Phone: <?php echo htmlspecialchars($order['phone']); ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h3 class="h5 mb-3">Payment Method</h3>
                                <p>
                                    <?php echo htmlspecialchars($order['payment_name']); ?><br>
                                    Card ending in ****
                                </p>
                            </div>
                        </div>

                        <h3 class="h5 mt-4 mb-3">Order Items</h3>
                        <?php foreach ($items as $item): ?>
                            <div class="summary-item">
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>"
                                        alt="<?php echo htmlspecialchars($item['name']); ?>"
                                        class="me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                    <div>
                                        <h4 class="h6 mb-0"><?php echo htmlspecialchars($item['name']); ?></h4>
                                        <small class="text-muted">Quantity: <?php echo $item['quantity']; ?></small>
                                    </div>
                                </div>
                                <span>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>

                        <div class="summary-item">
                            <span>Subtotal</span>
                            <span>$<?php echo number_format($order['total_amount'] - 10, 2); ?></span>
                        </div>
                        <div class="summary-item">
                            <span>Shipping</span>
                            <span>$10.00</span>
                        </div>
                        <div class="summary-item summary-total">
                            <span>Total</span>
                            <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <a href="index.php" class="btn btn-primary">Continue Shopping</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>