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

$user_id = $_SESSION['user_id'];
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();

        // Get cart total
        $stmt = $conn->prepare("
            SELECT SUM(c.quantity * p.price) as total
            FROM cart_items c
            JOIN products p ON c.product_id = p.product_id
            WHERE c.user_id = :user_id
        ");
        $stmt->execute([':user_id' => $user_id]);
        $total = $stmt->fetch()['total'] ?? 0;

        // Add shipping cost
        $shipping_cost = 10;
        $total += $shipping_cost;        // Get address ID
        if ($_POST['address_option'] === 'new') {
            // Handle new address
            $stmt = $conn->prepare("
                INSERT INTO shipping_addresses 
                (user_id, full_name, address_line1, address_line2, city, state, postal_code, country, phone)
                VALUES (:user_id, :full_name, :address_line1, :address_line2, :city, :state, :postal_code, :country, :phone)
            ");
            $stmt->execute([
                ':user_id' => $user_id,
                ':full_name' => $_POST['full_name'],
                ':address_line1' => $_POST['address_line1'],
                ':address_line2' => $_POST['address_line2'] ?? '',
                ':city' => $_POST['city'],
                ':state' => $_POST['state'],
                ':postal_code' => $_POST['postal_code'],
                ':country' => $_POST['country'],
                ':phone' => $_POST['phone']
            ]);
            $address_id = $conn->lastInsertId();
        } elseif (isset($_POST['address_id'])) {
            // Use existing address
            $address_id = $_POST['address_id'];
        } else {
            throw new Exception('Please select a shipping address or add a new one.');
        }

        // Get payment method ID
        if ($_POST['payment_option'] === 'new') {
            // In a production environment, use proper encryption for card details
            $encrypted_card_number = password_hash($_POST['card_number'], PASSWORD_DEFAULT);

            $stmt = $conn->prepare("
                INSERT INTO payment_methods 
                (user_id, card_holder_name, card_number_encrypted, expiry_month, expiry_year)
                VALUES (:user_id, :card_holder_name, :card_number, :expiry_month, :expiry_year)
            ");
            $stmt->execute([
                ':user_id' => $user_id,
                ':card_holder_name' => $_POST['card_holder_name'],
                ':card_number' => $encrypted_card_number,
                ':expiry_month' => $_POST['expiry_month'],
                ':expiry_year' => $_POST['expiry_year']
            ]);
            $payment_id = $conn->lastInsertId();
        } elseif (isset($_POST['payment_method_id'])) {
            // Use existing payment method
            $payment_id = $_POST['payment_method_id'];
        } else {
            throw new Exception('Please select a payment method or add a new one.');
        }

        // Create order
        $stmt = $conn->prepare("
            INSERT INTO orders 
            (user_id, total_amount, payment_method_id, shipping_address_id, status)
            VALUES (:user_id, :total, :payment_id, :address_id, 'pending')
        ");
        $stmt->execute([
            ':user_id' => $user_id,
            ':total' => $total,
            ':payment_id' => $payment_id,
            ':address_id' => $address_id
        ]);
        $order_id = $conn->lastInsertId();

        // Move items from cart to order_items
        $stmt = $conn->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price)
            SELECT :order_id, c.product_id, c.quantity, p.price
            FROM cart_items c
            JOIN products p ON c.product_id = p.product_id
            WHERE c.user_id = :user_id
        ");
        $stmt->execute([':order_id' => $order_id, ':user_id' => $user_id]);

        // Update product stock
        $stmt = $conn->prepare("
            UPDATE products p
            JOIN cart_items c ON p.product_id = c.product_id
            SET p.stock_quantity = p.stock_quantity - c.quantity
            WHERE c.user_id = :user_id
        ");
        $stmt->execute([':user_id' => $user_id]);

        // Clear cart
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);

        $conn->commit();
        header('Location: order-confirmation.php?order_id=' . $order_id);
        exit();
    } catch (PDOException $e) {
        $conn->rollBack();
        $message = 'Error processing order: ' . $e->getMessage();
    }
}

// Get saved addresses
$stmt = $conn->prepare("SELECT * FROM shipping_addresses WHERE user_id = :user_id");
$stmt->execute([':user_id' => $user_id]);
$addresses = $stmt->fetchAll();

// Get saved payment methods
$stmt = $conn->prepare("SELECT * FROM payment_methods WHERE user_id = :user_id");
$stmt->execute([':user_id' => $user_id]);
$payment_methods = $stmt->fetchAll();

// Get cart items
$stmt = $conn->prepare("
    SELECT c.quantity, p.name, p.price, p.image_url
    FROM cart_items c
    JOIN products p ON c.product_id = p.product_id
    WHERE c.user_id = :user_id
");
$stmt->execute([':user_id' => $user_id]);
$cart_items = $stmt->fetchAll();

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = 10;
$total = $subtotal + $shipping;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Modern Furniture Store</title>

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
            <h1 class="mb-4">Checkout</h1>

            <?php if ($message): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <form method="POST" id="checkout-form" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Shipping Address Section -->
                        <div class="checkout-section mb-4">
                            <h2 class="section-title">Shipping Address</h2>

                            <div class="address-options mb-4">
                                <?php if (!empty($addresses)): ?>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="address_option" id="existing_address" value="existing" checked>
                                        <label class="form-check-label" for="existing_address">
                                            Use a saved address
                                        </label>
                                    </div>

                                    <div class="saved-addresses mb-4">
                                        <?php foreach ($addresses as $address): ?>
                                            <div class="saved-address mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="address_id"
                                                        value="<?php echo $address['address_id']; ?>"
                                                        id="address_<?php echo $address['address_id']; ?>"
                                                        <?php echo $address === reset($addresses) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="address_<?php echo $address['address_id']; ?>">
                                                        <div class="address-details">
                                                            <strong><?php echo htmlspecialchars($address['full_name']); ?></strong><br>
                                                            <?php echo htmlspecialchars($address['address_line1']); ?><br>
                                                            <?php if ($address['address_line2']): ?>
                                                                <?php echo htmlspecialchars($address['address_line2']); ?><br>
                                                            <?php endif; ?>
                                                            <?php echo htmlspecialchars($address['city']) . ', ' . htmlspecialchars($address['state']) . ' ' . htmlspecialchars($address['postal_code']); ?><br>
                                                            <?php echo htmlspecialchars($address['country']); ?><br>
                                                            <?php echo htmlspecialchars($address['phone']); ?>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="address_option" id="new_address" value="new" <?php echo empty($addresses) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="new_address">
                                        Add a new address
                                    </label>
                                </div>
                            </div>

                            <div id="new-address-form" class="<?php echo !empty($addresses) ? 'd-none' : ''; ?>">
                                <div class="row">
                                    <div class="col-12 form-group">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="full_name" class="form-control" required>
                                        <div class="invalid-feedback">Please enter your full name.</div>
                                    </div>
                                    <div class="col-12 form-group">
                                        <label class="form-label">Address Line 1</label>
                                        <input type="text" name="address_line1" class="form-control" required>
                                        <div class="invalid-feedback">Please enter your address.</div>
                                    </div>
                                    <div class="col-12 form-group">
                                        <label class="form-label">Address Line 2 (Optional)</label>
                                        <input type="text" name="address_line2" class="form-control">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="form-label">City</label>
                                        <input type="text" name="city" class="form-control" required>
                                        <div class="invalid-feedback">Please enter your city.</div>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="form-label">State</label>
                                        <input type="text" name="state" class="form-control" required>
                                        <div class="invalid-feedback">Please enter your state.</div>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="form-label">Postal Code</label>
                                        <input type="text" name="postal_code" class="form-control" required>
                                        <div class="invalid-feedback">Please enter your postal code.</div>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="form-label">Country</label>
                                        <input type="text" name="country" class="form-control" value="India" required>
                                        <div class="invalid-feedback">Please enter your country.</div>
                                    </div>
                                    <div class="col-12 form-group">
                                        <label class="form-label">Phone Number</label>
                                        <input type="tel" name="phone" class="form-control" required>
                                        <div class="invalid-feedback">Please enter a valid phone number.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method Section -->
                        <div class="checkout-section">
                            <h2 class="section-title">Payment Method</h2>

                            <div class="payment-options mb-4">
                                <?php if (!empty($payment_methods)): ?>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="payment_option" id="existing_payment" value="existing" checked>
                                        <label class="form-check-label" for="existing_payment">
                                            Use a saved payment method
                                        </label>
                                    </div>

                                    <div class="saved-cards mb-4">
                                        <?php foreach ($payment_methods as $payment): ?>
                                            <div class="saved-card mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="payment_method_id"
                                                        value="<?php echo $payment['payment_id']; ?>"
                                                        id="payment_<?php echo $payment['payment_id']; ?>"
                                                        <?php echo $payment === reset($payment_methods) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="payment_<?php echo $payment['payment_id']; ?>">
                                                        <div class="card-info">
                                                            <strong><?php echo htmlspecialchars($payment['card_holder_name']); ?></strong><br>
                                                            <span class="card-number">**** **** **** <?php echo substr($payment['card_number_encrypted'], -4); ?></span><br>
                                                            Expires: <?php echo sprintf('%02d/%d', $payment['expiry_month'], $payment['expiry_year']); ?>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_option" id="new_payment" value="new" <?php echo empty($payment_methods) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="new_payment">
                                        Add a new payment method
                                    </label>
                                </div>
                            </div>

                            <div id="new-card-form" class="<?php echo !empty($payment_methods) ? 'd-none' : ''; ?>">
                                <div class="row">
                                    <div class="col-12 form-group">
                                        <label class="form-label">Card Holder Name</label>
                                        <input type="text" name="card_holder_name" class="form-control" required>
                                        <div class="invalid-feedback">Please enter the cardholder's name.</div>
                                    </div>
                                    <div class="col-12 form-group">
                                        <label class="form-label">Card Number</label>
                                        <input type="text" name="card_number" class="form-control" pattern="[0-9]{16}" maxlength="16" required>
                                        <div class="invalid-feedback">Please enter a valid 16-digit card number.</div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label class="form-label">Expiry Month</label>
                                        <select name="expiry_month" class="form-control" required>
                                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                                <option value="<?php echo $i; ?>"><?php echo sprintf('%02d', $i); ?></option>
                                            <?php endfor; ?>
                                        </select>
                                        <div class="invalid-feedback">Please select the expiry month.</div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label class="form-label">Expiry Year</label>
                                        <select name="expiry_year" class="form-control" required>
                                            <?php
                                            $current_year = date('Y');
                                            for ($i = $current_year; $i <= $current_year + 10; $i++):
                                            ?>
                                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                        <div class="invalid-feedback">Please select the expiry year.</div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label class="form-label">CVV</label>
                                        <input type="password" name="cvv" class="form-control" pattern="[0-9]{3,4}" maxlength="4" required>
                                        <div class="invalid-feedback">Please enter a valid CVV (3-4 digits).</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="col-lg-4">
                        <div class="checkout-section order-summary">
                            <h2 class="section-title">Order Summary</h2>

                            <?php foreach ($cart_items as $item): ?>
                                <div class="summary-item">
                                    <span>
                                        <?php echo htmlspecialchars($item['name']); ?>
                                        × <?php echo $item['quantity']; ?>
                                    </span>
                                    <span>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                </div>
                            <?php endforeach; ?>

                            <div class="summary-item">
                                <span>Subtotal</span>
                                <span>$<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="summary-item">
                                <span>Shipping</span>
                                <span>$<?php echo number_format($shipping, 2); ?></span>
                            </div>
                            <div class="summary-item summary-total">
                                <span>Total</span>
                                <span>$<?php echo number_format($total, 2); ?></span>
                            </div>

                            <button type="submit" class="place-order-btn">Place Order</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function() {
            'use strict';

            // Get form elements            const form = document.getElementById('checkout-form');
            const addressOptions = document.querySelectorAll('input[name="address_option"]');
            const paymentOptions = document.querySelectorAll('input[name="payment_option"]');
            const newAddressForm = document.getElementById('new-address-form');
            const newCardForm = document.getElementById('new-card-form');

            // Form validation
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                // Get selected options
                const selectedAddressOption = document.querySelector('input[name="address_option"]:checked');
                const selectedPaymentOption = document.querySelector('input[name="payment_option"]:checked');

                // Custom validation for address selection
                if (selectedAddressOption.value === 'existing' && !document.querySelector('input[name="address_id"]:checked')) {
                    event.preventDefault();
                    alert('Please select a shipping address or add a new one.');
                    return;
                }

                // Custom validation for payment selection
                if (selectedPaymentOption.value === 'existing' && !document.querySelector('input[name="payment_method_id"]:checked')) {
                    event.preventDefault();
                    alert('Please select a payment method or add a new one.');
                    return;
                }

                // If new address is selected, validate address form
                if (selectedAddressOption.value === 'new') {
                    const addressInputs = newAddressForm.querySelectorAll('input[required]');
                    for (const input of addressInputs) {
                        if (!input.value) {
                            event.preventDefault();
                            input.focus();
                            return;
                        }
                    }
                }

                // If new payment is selected, validate payment form
                if (selectedPaymentOption.value === 'new') {
                    const paymentInputs = newCardForm.querySelectorAll('input[required], select[required]');
                    for (const input of paymentInputs) {
                        if (!input.value) {
                            event.preventDefault();
                            input.focus();
                            return;
                        }
                    }
                }

                form.classList.add('was-validated');
            }, false);

            // Address option toggle
            document.querySelectorAll('input[name="address_option"]').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    newAddressForm.classList.toggle('d-none', this.value === 'existing');
                    if (this.value === 'new') {
                        existingAddresses.forEach(addr => addr.checked = false);
                    }
                });
            });

            // Payment option toggle
            document.querySelectorAll('input[name="payment_option"]').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    newCardForm.classList.toggle('d-none', this.value === 'existing');
                    if (this.value === 'new') {
                        existingPayments.forEach(payment => payment.checked = false);
                    }
                });
            });

            // Card number formatting
            const cardInput = document.querySelector('input[name="card_number"]');
            if (cardInput) {
                cardInput.addEventListener('input', function(e) {
                    let value = this.value.replace(/\D/g, '');
                    if (value.length > 16) {
                        value = value.substr(0, 16);
                    }
                    this.value = value;
                });
            }

            // CVV formatting
            const cvvInput = document.querySelector('input[name="cvv"]');
            if (cvvInput) {
                cvvInput.addEventListener('input', function(e) {
                    let value = this.value.replace(/\D/g, '');
                    if (value.length > 4) {
                        value = value.substr(0, 4);
                    }
                    this.value = value;
                });
            }
        })();
    </script>
</body>

</html>