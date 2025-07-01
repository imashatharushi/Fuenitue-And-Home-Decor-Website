<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>About Us - Modern Furniture Store</title>

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
  <link rel="stylesheet" href="assets/css/about.css" />
</head>

<body>
  <?php include 'includes/nav.php'; ?>

  <!-- About Hero Section -->
  <section class="about-hero">
    <div class="container">
      <h1>About Us</h1>
      <p>
        We believe in the power of teamwork and collaboration. Our diverse
        experts work tirelessly to deliver innovative solutions tailored to
        our clients' needs.
      </p>
    </div>
  </section>

  <!-- Main Content -->
  <section class="container my-5">
    <div class="row align-items-center">
      <div class="col-md-6">
        <img
          src="assets/images/about-furniture.jpg"
          alt="Modern Furniture Setup"
          class="about-image" />
      </div>
      <div class="col-md-6">
        <p>
          At our furniture store, we believe that furniture is more than just
          decor. It's a reflection of your lifestyle and personality. Founded
          with a passion for craftsmanship and timeless design, we are
          dedicated to offering high-quality, stylish, and functional pieces
          for every room in your home.
        </p>
        <p>
          Whether you're furnishing a cozy apartment or designing your dream
          home, our curated collection ensures comfort, elegance, and
          durability. With a commitment to customer satisfaction and
          sustainable practices, we're here to help you create spaces you'll
          love for years to come.
        </p>
      </div>
    </div>

    <!-- Statistics -->
    <div class="stats-container">
      <div class="stat-box">
        <div class="stat-number">370+</div>
        <div class="stat-label">Qualified Experts</div>
      </div>
      <div class="stat-box">
        <div class="stat-number">18k+</div>
        <div class="stat-label">Satisfied Clients</div>
      </div>
    </div>

    <div class="text-center">
      <a href="#" class="explore-btn">Explore →</a>
    </div>
  </section>

  <?php include 'includes/footer.php'; ?>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>