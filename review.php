<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reviews - Modern Furniture Store</title>

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
  <link rel="stylesheet" href="assets/css/review.css" />
</head>

<body>
  <?php include 'includes/nav.php'; ?>

  <!-- Review Section -->
  <section class="review-container">
    <div class="container">
      <div class="review-header">
        <h2>Customer Reviews</h2>
        <p>
          Read what our customers have to say about their experience with our
          products and services.
        </p>
      </div>

      <!-- Write Review Section -->
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="write-review-card">
            <h3>Write a Review</h3>
            <form>
              <div class="rating-select text-center">
                <input type="radio" name="rating" id="star5" value="5" />
                <label for="star5"><i class="fas fa-star"></i></label>
                <input type="radio" name="rating" id="star4" value="4" />
                <label for="star4"><i class="fas fa-star"></i></label>
                <input type="radio" name="rating" id="star3" value="3" />
                <label for="star3"><i class="fas fa-star"></i></label>
                <input type="radio" name="rating" id="star2" value="2" />
                <label for="star2"><i class="fas fa-star"></i></label>
                <input type="radio" name="rating" id="star1" value="1" />
                <label for="star1"><i class="fas fa-star"></i></label>
              </div>
              <div class="form-group">
                <label class="form-label">Your Review</label>
                <textarea
                  class="form-control"
                  rows="4"
                  placeholder="Share your experience with us..."></textarea>
              </div>
              <div class="text-center">
                <button type="submit" class="review-btn">
                  Submit Review
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Reviews List -->
      <div class="row">
        <!-- Review 1 -->
        <div class="col-lg-6">
          <div class="review-card">
            <div class="review-user">
              <div class="review-user-info">
                <h4>Sarah Johnson</h4>
                <p>Verified Buyer</p>
              </div>
            </div>
            <div class="review-rating">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
            </div>
            <div class="review-text">
              <p>
                The modern sofa I purchased exceeded my expectations. The
                quality is outstanding, and it perfectly fits my living room.
                The delivery was quick and the assembly was straightforward.
                Highly recommend!
              </p>
            </div>
            <div class="review-date">2 weeks ago</div>
          </div>
        </div>

        <!-- Review 2 -->
        <div class="col-lg-6">
          <div class="review-card">
            <div class="review-user">
              <div class="review-user-info">
                <h4>Michael Chen</h4>
                <p>Verified Buyer</p>
              </div>
            </div>
            <div class="review-rating">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
            </div>
            <p class="review-text">
              "Excellent quality furniture! The modern chair I purchased is
              not only stylish but also very comfortable. The delivery was
              prompt and the customer service was exceptional."
            </p>
            <p class="review-date">June 8, 2025</p>
          </div>
        </div>

        <!-- Review 2 -->
        <div class="col-lg-6">
          <div class="review-card">
            <div class="review-user">
              <img
                src="assets/images/user2.jpg"
                alt="User"
                class="review-avatar" />
              <div class="review-user-info">
                <h4>Sarah Johnson</h4>
                <p>Verified Buyer</p>
              </div>
            </div>
            <div class="review-rating">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star-half-alt"></i>
            </div>
            <p class="review-text">
              "The sofa set I ordered is beautiful and exactly as pictured.
              Assembly was easy and the quality is outstanding. Would
              definitely recommend!"
            </p>
            <p class="review-date">June 5, 2025</p>
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