<?php
require_once 'includes/auth_handler.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Get user's orders
require_once 'config/connection.php';
$userId = $_SESSION['user_id'];

try {
    // Fetch orders with their items and products
    // Get orders
    $stmt = $conn->prepare("
        SELECT 
            o.order_id,
            o.total_amount,
            o.status,
            o.created_at,
            GROUP_CONCAT(p.name) as products
        FROM orders o
        LEFT JOIN order_items oi ON o.order_id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.product_id
        WHERE o.user_id = ?
        GROUP BY o.order_id
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll();

    // Get saved addresses
    $stmt = $conn->prepare("SELECT * FROM shipping_addresses WHERE user_id = ?");
    $stmt->execute([$userId]);
    $addresses = $stmt->fetchAll();

    // Get saved payment methods
    $stmt = $conn->prepare("SELECT * FROM payment_methods WHERE user_id = ?");
    $stmt->execute([$userId]);
    $payment_methods = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching orders: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Modern Furniture Store</title>

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="assets/img/favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon_io/favicon-16x16.png">
    <link rel="manifest" href="assets/img/favicon_io/site.webmanifest">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/colors.css">
    <link rel="stylesheet" href="assets/css/navigation.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/styles.css">

    <style>
        .profile-section {
            padding: 50px 0;
            background-color: var(--light-bg);
        }

        .profile-card {
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .profile-header {
            margin-bottom: 30px;
            border-bottom: 2px solid var(--light-bg);
            padding-bottom: 20px;
        }

        .order-card {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.2s;
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .order-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .status-pending {
            background-color: #ffeeba;
            color: #856404;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>
    <?php include 'includes/nav.php'; ?>

    <div class="profile-section">
        <div class="container">
            <div class="profile-card">
                <div class="profile-header">
                    <h2><i class="fas fa-user-circle me-2"></i>My Profile</h2>
                    <p class="text-muted">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <!-- Account Details Card -->
                        <div class="profile-card mb-4">
                            <h4>Account Details</h4>
                            <hr>
                            <p><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></p>
                            <button class="btn btn-outline-dark mt-3" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                                <i class="fas fa-edit me-2"></i>Edit Profile
                            </button>
                        </div>

                        <!-- Saved Addresses Card -->
                        <div class="profile-card mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="mb-0">Saved Addresses</h4>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                                    <i class="fas fa-plus me-2"></i>Add New
                                </button>
                            </div>
                            <hr>
                            <?php if (empty($addresses)): ?>
                                <p class="text-muted">No saved addresses yet.</p>
                            <?php else: ?>
                                <?php foreach ($addresses as $address): ?>
                                    <div class="saved-address mb-3 p-3 border rounded">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong><?php echo htmlspecialchars($address['full_name']); ?></strong>
                                                <p class="mb-1 text-muted">
                                                    <?php echo htmlspecialchars($address['address_line1']); ?><br>
                                                    <?php if ($address['address_line2']): ?>
                                                        <?php echo htmlspecialchars($address['address_line2']); ?><br>
                                                    <?php endif; ?>
                                                    <?php echo htmlspecialchars($address['city']) . ', ' .
                                                        htmlspecialchars($address['state']) . ' ' .
                                                        htmlspecialchars($address['postal_code']); ?><br>
                                                    <?php echo htmlspecialchars($address['country']); ?><br>
                                                    Phone: <?php echo htmlspecialchars($address['phone']); ?>
                                                </p>
                                            </div>
                                            <div>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteAddress(<?php echo $address['address_id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Saved Payment Methods Card -->
                        <div class="profile-card">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="mb-0">Payment Methods</h4>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                                    <i class="fas fa-plus me-2"></i>Add New
                                </button>
                            </div>
                            <hr>
                            <?php if (empty($payment_methods)): ?>
                                <p class="text-muted">No saved payment methods yet.</p>
                            <?php else: ?>
                                <?php foreach ($payment_methods as $payment): ?>
                                    <div class="saved-payment mb-3 p-3 border rounded">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong><?php echo htmlspecialchars($payment['card_holder_name']); ?></strong>
                                                <p class="mb-1">
                                                    <span class="text-muted">
                                                        **** **** **** <?php echo substr($payment['card_number_encrypted'], -4); ?>
                                                    </span><br>
                                                    <small class="text-muted">
                                                        Expires: <?php echo sprintf('%02d/%d', $payment['expiry_month'], $payment['expiry_year']); ?>
                                                    </small>
                                                </p>
                                            </div>
                                            <div>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deletePayment(<?php echo $payment['payment_id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Order History Section -->
                    <div class="col-md-8">
                        <h4>Order History</h4>
                        <hr>
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php else: ?>
                            <?php if (empty($orders)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-shopping-bag fa-3x mb-3 text-muted"></i>
                                    <p class="text-muted">You haven't placed any orders yet.</p>
                                    <a href="featured.php" class="btn btn-dark">Start Shopping</a>
                                </div>
                            <?php else: ?>
                                <?php foreach ($orders as $order): ?>
                                    <div class="order-card">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="mb-0">Order #<?php echo $order['order_id']; ?></h5>
                                            <span class="order-status status-<?php echo strtolower($order['status']); ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </div>
                                        <p class="text-muted mb-2">
                                            <i class="far fa-calendar-alt me-2"></i>
                                            <?php echo date('F j, Y', strtotime($order['created_at'])); ?>
                                        </p>
                                        <p class="mb-2"><strong>Products:</strong> <?php echo $order['products']; ?></p>
                                        <p class="mb-0"><strong>Total:</strong> Rs<?php echo number_format($order['total_amount'], 2); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="includes/update_profile.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username"
                                value="<?php echo htmlspecialchars($_SESSION['username']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email"
                                value="<?php echo htmlspecialchars($_SESSION['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password"
                                placeholder="Leave blank to keep current password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="current_password"
                                placeholder="Enter current password to confirm changes" required>
                        </div>
                        <button type="submit" class="btn btn-dark w-100">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Address Modal -->
    <div class="modal fade" id="addAddressModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="includes/update_profile.php" method="POST">
                        <input type="hidden" name="action" value="add_address">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address Line 1</label>
                            <input type="text" class="form-control" name="address_line1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address Line 2 (Optional)</label>
                            <input type="text" class="form-control" name="address_line2">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control" name="city" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">State</label>
                                <input type="text" class="form-control" name="state" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Postal Code</label>
                                <input type="text" class="form-control" name="postal_code" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Country</label>
                                <input type="text" class="form-control" name="country" value="India" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" name="phone" required>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Address</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Payment Method Modal -->
    <div class="modal fade" id="addPaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Payment Method</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="includes/update_profile.php" method="POST">
                        <input type="hidden" name="action" value="add_payment">
                        <div class="mb-3">
                            <label class="form-label">Card Holder Name</label>
                            <input type="text" class="form-control" name="card_holder_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Card Number</label>
                            <input type="text" class="form-control" name="card_number"
                                pattern="[0-9]{16}" maxlength="16" required>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Expiry Month</label>
                                <select name="expiry_month" class="form-control" required>
                                    <?php for ($i = 1; $i <= 12; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo sprintf('%02d', $i); ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Expiry Year</label>
                                <select name="expiry_year" class="form-control" required>
                                    <?php
                                    $current_year = date('Y');
                                    for ($i = $current_year; $i <= $current_year + 10; $i++):
                                    ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">CVV</label>
                                <input type="password" class="form-control" name="cvv"
                                    pattern="[0-9]{3,4}" maxlength="4" required>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Payment Method</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Card number formatting
        document.querySelector('input[name="card_number"]')?.addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').substr(0, 16);
        });

        // CVV formatting
        document.querySelector('input[name="cvv"]')?.addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').substr(0, 4);
        });

        // Delete address
        function deleteAddress(addressId) {
            if (confirm('Are you sure you want to delete this address?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'includes/update_profile.php';

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete_address';

                const addressInput = document.createElement('input');
                addressInput.type = 'hidden';
                addressInput.name = 'address_id';
                addressInput.value = addressId;

                form.appendChild(actionInput);
                form.appendChild(addressInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Delete payment method
        function deletePayment(paymentId) {
            if (confirm('Are you sure you want to delete this payment method?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'includes/update_profile.php';

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete_payment';

                const paymentInput = document.createElement('input');
                paymentInput.type = 'hidden';
                paymentInput.name = 'payment_id';
                paymentInput.value = paymentId;

                form.appendChild(actionInput);
                form.appendChild(paymentInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>

</html>