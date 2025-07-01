<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Modern Furniture Store</title>

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
</head>

<body>
  <?php include 'includes/nav.php'; ?>

  <!-- Hero Section -->
  <section class="hero-section">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-6">
          <h1 class="hero-title">Modern Furniture for Stylish Comfort</h1>
          <p class="hero-description">
            Each piece of furniture in their home suited the style of the
            house with wood composition or fabricated wood materials.
          </p>
          <a href="#" class="btn-shop-now">Shop now</a>
          <a href="#" class="btn ms-3">Show reel</a>
        </div>
        <div class="col-md-6">
          <div class="position-relative">
            <img
              src="assets/images/modern-chair.jpg"
              alt="Modern Chair"
              class="img-fluid" />
            <div
              class="position-absolute top-0 end-0 bg-dark text-white p-3 rounded">
              <h5 class="mb-0">Modern Swivel Chair</h5>
              <p class="mb-0">Rs3000</p>
              <button class="btn btn-sm text-white">Add to Cart</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Categories Section -->
  <section class="categories-section py-5 bg-light">
    <div class="container">
      <h2 class="text-center mb-5">Shop by Category</h2>
      <div class="row g-4">
        <div class="col-md-3 col-sm-6">
          <div class="category-card">
            <img
              src="assets/images/products/category-chairs.jpg"
              alt="Chairs"
              class="category-image" />
            <div class="category-content">
              <h3>Chairs</h3>
              <p>Modern & Comfortable</p>
              <a href="featured.html" class="category-link">View Collection →</a>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6">
          <div class="category-card">
            <img
              src="assets/images/category-sofas.jpg"
              alt="Sofas"
              class="category-image" />
            <div class="category-content">
              <h3>Sofas</h3>
              <p>Luxurious & Cozy</p>
              <a href="featured.html" class="category-link">View Collection →</a>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6">
          <div class="category-card">
            <img
              src="assets/images/category-tables.jpg"
              alt="Tables"
              class="category-image" />
            <div class="category-content">
              <h3>Tables</h3>
              <p>Elegant & Functional</p>
              <a href="featured.html" class="category-link">View Collection →</a>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6">
          <div class="category-card">
            <img
              src="assets/images/category-decor.jpg"
              alt="Home Decor"
              class="category-image" />
            <div class="category-content">
              <h3>Home Decor</h3>
              <p>Stylish & Trendy</p>
              <a href="featured.html" class="category-link">View Collection →</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>


  <!-- Features -->
  <section class="features-section">
    <div class="container">
      <div class="row">
        <div class="col-md-4">
          <div class="feature-item">
            <i class="fas fa-tag feature-icon"></i>
            <h4>Affordable Prices</h4>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-item">
            <i class="fas fa-truck feature-icon"></i>
            <h4>Free Shipping</h4>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-item">
            <i class="fas fa-shield-alt feature-icon"></i>
            <h4>5 Years Warranty</h4>
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php include 'includes/footer.php'; ?>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>