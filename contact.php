<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Contact Us - Modern Furniture Store</title>

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
  <link rel="stylesheet" href="assets/css/contact.css" />
</head>

<body>
  <?php include 'includes/nav.php'; ?>

  <!-- Contact Section -->
  <section class="contact-container">
    <div class="container">
      <div class="contact-info">
        <div class="row g-4">
          <div class="col-md-4">
            <div class="contact-card">
              <div class="contact-icon">
                <i class="fas fa-map-marker-alt"></i>
              </div>
              <h3>Our Location</h3>
              <p>Kegalle, Sri Lanka</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="contact-card">
              <div class="contact-icon">
                <i class="fas fa-phone"></i>
              </div>
              <h3>Phone Number</h3>
              <p>+ 94717323579</p>
              <p>+ 94728410781</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="contact-card">
              <div class="contact-icon">
                <i class="fas fa-envelope"></i>
              </div>
              <h3>Email Address</h3>
              <p>furniture@gmail.com</p>
            </div>
          </div>
        </div>
      </div>

      <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="contact-form">
            <h2>Send us a Message</h2>
            <form>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">First Name</label>
                    <input
                      type="text"
                      class="form-control"
                      placeholder="Enter your first name"
                      required />
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">Last Name</label>
                    <input
                      type="text"
                      class="form-control"
                      placeholder="Enter your last name"
                      required />
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Email Address</label>
                <input
                  type="email"
                  class="form-control"
                  placeholder="Enter your email"
                  required />
              </div>
              <div class="form-group">
                <label class="form-label">Subject</label>
                <input
                  type="text"
                  class="form-control"
                  placeholder="Enter subject"
                  required />
              </div>
              <div class="form-group">
                <label class="form-label">Message</label>
                <textarea
                  class="form-control"
                  placeholder="Write your message here..."
                  required></textarea>
              </div>
              <div class="text-center">
                <button type="submit" class="contact-btn">
                  Send Message
                </button>
              </div>
            </form>
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