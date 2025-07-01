<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Shopping Cart - Modern Furniture Store</title>

  <!-- Favicon -->
  <link
    rel="apple-touch-icon"
    sizes="180x180"
    href="./assets/img/favicon_io/apple-touch-icon.png" />
  <link
    rel="icon"
    type="image/png"
    sizes="32x32"
    href="./assets/img/favicon_io/favicon-32x32.png" />
  <link
    rel="icon"
    type="image/png"
    sizes="16x16"
    href="./assets/img/favicon_io/favicon-16x16.png" />
  <link rel="manifest" href="/site.webmanifest" />

  <!-- Bootstrap 5 CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet" />

  <!-- Font Awesome -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/colors.css" />
  <link rel="stylesheet" href="assets/css/navigation.css" />
  <link rel="stylesheet" href="assets/css/footer.css" />
  <link rel="stylesheet" href="assets/css/styles.css" />
  <link rel="stylesheet" href="assets/css/cart.css" />
</head>

<body> <?php
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

        // Handle cart actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $action = $_POST['action'] ?? '';
          $product_id = $_POST['product_id'] ?? '';
          $quantity = $_POST['quantity'] ?? 1;

          switch ($action) {
            case 'add':
              try {
                // Check if product already in cart
                $stmt = $conn->prepare("SELECT cart_id, quantity FROM cart_items WHERE user_id = :user_id AND product_id = :product_id");
                $stmt->execute([':user_id' => $user_id, ':product_id' => $product_id]);
                $cart_item = $stmt->fetch();

                if ($cart_item) {
                  // Update quantity
                  $new_quantity = $cart_item['quantity'] + $quantity;
                  $stmt = $conn->prepare("UPDATE cart_items SET quantity = :quantity WHERE cart_id = :cart_id");
                  $stmt->execute([':quantity' => $new_quantity, ':cart_id' => $cart_item['cart_id']]);
                } else {
                  // Insert new item
                  $stmt = $conn->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)");
                  $stmt->execute([':user_id' => $user_id, ':product_id' => $product_id, ':quantity' => $quantity]);
                }
                $message = 'Product added to cart successfully!';
              } catch (PDOException $e) {
                $message = 'Error adding product to cart: ' . $e->getMessage();
              }
              break;

            case 'update':
              try {
                $stmt = $conn->prepare("UPDATE cart_items SET quantity = :quantity WHERE cart_id = :cart_id AND user_id = :user_id");
                $stmt->execute([
                  ':quantity' => $quantity,
                  ':cart_id' => $_POST['cart_id'],
                  ':user_id' => $user_id
                ]);
              } catch (PDOException $e) {
                $message = 'Error updating cart: ' . $e->getMessage();
              }
              break;

            case 'remove':
              try {
                $stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_id = :cart_id AND user_id = :user_id");
                $stmt->execute([':cart_id' => $_POST['cart_id'], ':user_id' => $user_id]);
              } catch (PDOException $e) {
                $message = 'Error removing item: ' . $e->getMessage();
              }
              break;
          }
        }

        // Get cart items
        try {
          $stmt = $conn->prepare("
        SELECT c.cart_id, c.quantity, p.product_id, p.name, p.price, p.image_url, p.stock_quantity
        FROM cart_items c
        JOIN products p ON c.product_id = p.product_id
        WHERE c.user_id = :user_id
    ");
          $stmt->execute([':user_id' => $user_id]);
          $cart_items = $stmt->fetchAll();
        } catch (PDOException $e) {
          $message = 'Error fetching cart items: ' . $e->getMessage();
          $cart_items = [];
        }

        include 'includes/nav.php';
        ?>

  <!-- Cart Section -->
  <section class="cart-page">
    <div class="container">
      <h1 class="cart-title">Shopping Cart</h1>

      <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
      <?php endif; ?>

      <?php if (empty($cart_items)): ?>
        <div class="empty-cart">
          <i class="fas fa-shopping-cart"></i>
          <h2>Your cart is empty</h2>
          <p>Looks like you haven't added anything to your cart yet</p>
          <a href="featured.php" class="continue-shopping">Continue Shopping</a>
        </div>
      <?php else: ?>
        <div class="row">
          <div class="col-lg-8">
            <!-- Cart Items -->
            <?php
            $subtotal = 0;
            foreach ($cart_items as $item):
              $item_total = $item['price'] * $item['quantity'];
              $subtotal += $item_total;
            ?>
              <div class="cart-item">
                <img src="<?php echo htmlspecialchars($item['image_url']); ?>"
                  alt="<?php echo htmlspecialchars($item['name']); ?>"
                  class="cart-item-image" />
                <div class="cart-item-details">
                  <h3 class="cart-item-title"><?php echo htmlspecialchars($item['name']); ?></h3>
                  <p class="cart-item-price">$<?php echo number_format($item['price'], 2); ?></p>
                  <form action="cart.php" method="POST" class="d-inline">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                    <div class="quantity-control">
                      <button type="button" class="quantity-btn" onclick="updateQuantity(this, -1)">-</button>
                      <input type="number" name="quantity" class="quantity-input"
                        value="<?php echo $item['quantity']; ?>"
                        min="1" max="<?php echo $item['stock_quantity']; ?>"
                        onchange="this.form.submit()">
                      <button type="button" class="quantity-btn" onclick="updateQuantity(this, 1)">+</button>
                    </div>
                  </form>
                  <form action="cart.php" method="POST" class="d-inline">
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                    <button type="submit" class="remove-item">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Cart Summary -->
          <div class="col-lg-4">
            <div class="cart-summary">
              <h2 class="summary-title">Order Summary</h2>
              <div class="summary-item">
                <span>Subtotal</span>
                <span>$<?php echo number_format($subtotal, 2); ?></span>
              </div>
              <div class="summary-item">
                <span>Shipping</span>
                <span>$<?php echo number_format(10, 2); ?></span>
              </div>
              <div class="summary-item summary-total">
                <span>Total</span>
                <span>$<?php echo number_format($subtotal + 10, 2); ?></span>
              </div>
              <form action="checkout.php" method="GET">
                <button type="submit" class="btn btn-primary w-100 proceed-to-checkout">
                  Proceed to Checkout
                </button>
              </form>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <?php include 'includes/footer.php'; ?>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    function updateQuantity(button, change) {
      const input = button.parentNode.querySelector('.quantity-input');
      let newValue = parseInt(input.value) + change;
      const min = parseInt(input.min);
      const max = parseInt(input.max);

      // Ensure value is within min and max bounds
      newValue = Math.max(min, Math.min(max, newValue));

      if (input.value !== newValue.toString()) {
        input.value = newValue;
        // Show loading state
        button.disabled = true;
        const originalText = button.textContent;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        // Submit the form
        input.form.submit();
      }
    }

    // Handle manual input changes
    document.querySelectorAll('.quantity-input').forEach(input => {
      input.addEventListener('change', function() {
        const min = parseInt(this.min);
        const max = parseInt(this.max);
        let value = parseInt(this.value);

        // Handle invalid input
        if (isNaN(value)) {
          value = min;
        }

        // Ensure value is within bounds
        value = Math.max(min, Math.min(max, value));

        if (this.value !== value.toString()) {
          this.value = value;
        }

        // Submit form if value changed
        this.form.submit();
      });

      // Prevent non-numeric input
      input.addEventListener('keypress', function(e) {
        if (!/[0-9]/.test(e.key)) {
          e.preventDefault();
        }
      });
    });

    // Handle remove item confirmation
    document.querySelectorAll('.remove-item').forEach(button => {
      button.addEventListener('click', function(e) {
        if (!confirm('Are you sure you want to remove this item from your cart?')) {
          e.preventDefault();
        }
      });
    });

    // Show success message temporarily
    const alertSuccess = document.querySelector('.alert-success');
    if (alertSuccess) {
      setTimeout(() => {
        alertSuccess.style.opacity = '0';
        setTimeout(() => alertSuccess.remove(), 300);
      }, 3000);
    }
  </script>
</body>

</html>