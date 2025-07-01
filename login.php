<?php
require_once 'includes/auth_handler.php';

// Store redirect URL if provided
if (isset($_GET['redirect'])) {
  $_SESSION['redirect_after_login'] = $_GET['redirect'];
}

// Redirect if already logged in
if (isLoggedIn()) {
  if (isset($_SESSION['redirect_after_login'])) {
    $redirect = $_SESSION['redirect_after_login'];
    unset($_SESSION['redirect_after_login']);
    header('Location: ' . $redirect);
  } else {
    header('Location: index.php');
  }
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - Modern Furniture Store</title>

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
  <link rel="stylesheet" href="assets/css/auth.css" />
</head>

<body>
  <?php include 'includes/nav.php'; ?>

  <!-- Login Form -->
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <h2>Welcome Back</h2>
        <p>Sign in to continue shopping</p>
      </div>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
          <?php
          echo $_SESSION['error'];
          unset($_SESSION['error']);
          ?>
        </div>
      <?php endif; ?>

      <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
          <?php
          echo $_SESSION['success'];
          unset($_SESSION['success']);
          ?>
        </div>
      <?php endif; ?> <form method="POST" action="includes/auth_handler.php">
        <input type="hidden" name="action" value="login">
        <?php if (isset($_GET['redirect'])): ?>
          <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_GET['redirect']); ?>"><?php endif; ?>

        <div class="form-group mb-3">
          <label class="form-label">Email</label>
          <input
            type="email"
            name="email"
            class="form-control"
            placeholder="Enter your email"
            required>
        </div>

        <div class="form-group mb-4">
          <label class="form-label">Password</label>
          <input
            type="password"
            name="password"
            class="form-control"
            placeholder="Enter your password"
            required>
        </div>

        <button type="submit" class="auth-btn">Sign In</button>

        <div class="auth-links">
          <p>Don't have an account? <a href="register.php">Register</a></p>
        </div>
      </form>
    </div>
  </div>

  <?php include 'includes/footer.php'; ?>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>