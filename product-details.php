<?php
require_once 'config/connection.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch product details
try {
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name,
               (SELECT ROUND(AVG(rating), 1) FROM reviews WHERE product_id = p.product_id) as avg_rating,
               (SELECT COUNT(*) FROM reviews WHERE product_id = p.product_id) as review_count
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        WHERE p.product_id = ?
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        header('Location: index.php');
        exit;
    }

    // Fetch product reviews
    $stmt = $conn->prepare("
        SELECT r.*, u.username 
        FROM reviews r 
        LEFT JOIN users u ON r.user_id = u.user_id 
        WHERE r.product_id = ? 
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$product_id]);
    $reviews = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = "Error fetching product details: " . $e->getMessage();
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Modern Furniture Store</title>

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="assets/img/favicon_io/apple-touch-icon.png" />
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon_io/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon_io/favicon-16x16.png" />
    <link rel="manifest" href="assets/img/favicon_io/site.webmanifest" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/colors.css">
    <link rel="stylesheet" href="assets/css/navigation.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/product-details.css">
</head>

<body>
    <?php include 'includes/nav.php'; ?> <main class="container my-5">
        <div class="row">
            <!-- Product Images -->
            <div class="col-md-6">
                <div class="product-image-container mb-4">
                    <?php if ($product['image_url']): ?>
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                            class="img-fluid product-main-image">
                    <?php else: ?>
                        <div class="product-no-image">
                            <i class="fas fa-image"></i>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Product Details -->
            <div class="col-md-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="featured.php?category=<?php echo $product['category_id']; ?>">
                                <?php echo htmlspecialchars($product['category_name']); ?>
                            </a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </li>
                    </ol>
                </nav>

                <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>

                <div class="product-rating mb-3">
                    <?php
                    $rating = $product['avg_rating'] ?? 0;
                    $fullStars = floor($rating);
                    $hasHalfStar = ($rating - $fullStars) >= 0.5;

                    // Output full stars
                    for ($i = 0; $i < $fullStars; $i++) {
                        echo '<i class="fas fa-star"></i>';
                    }
                    // Output half star if needed
                    if ($hasHalfStar) {
                        echo '<i class="fas fa-star-half-alt"></i>';
                    }
                    // Output empty stars
                    for ($i = 0; $i < (5 - $fullStars - ($hasHalfStar ? 1 : 0)); $i++) {
                        echo '<i class="far fa-star"></i>';
                    }
                    ?>
                    <span class="rating-text">
                        <?php echo number_format($rating, 1); ?>
                        (<?php echo $product['review_count']; ?> reviews)
                    </span>
                </div>

                <div class="product-price mb-4">$<?php echo number_format($product['price'], 2); ?></div>

                <div class="product-description mb-4">
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </div>

                <div class="product-meta mb-4">
                    <div class="meta-item">
                        <span class="meta-label">Category:</span>
                        <span class="meta-value"><?php echo htmlspecialchars($product['category_name']); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Availability:</span>
                        <span class="meta-value <?php echo $product['stock_quantity'] > 0 ? 'text-success' : 'text-danger'; ?>">
                            <?php echo $product['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                        </span>
                    </div>
                </div> <?php if ($product['stock_quantity'] > 0): ?>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form action="cart.php" method="POST" class="d-flex gap-3 mb-4">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                            <div class="quantity-input">
                                <button type="button" class="quantity-btn" onclick="updateQuantity(-1)">-</button>
                                <input type="number" name="quantity" value="1" min="1"
                                    max="<?php echo $product['stock_quantity']; ?>"
                                    class="quantity-value">
                                <button type="button" class="quantity-btn" onclick="updateQuantity(1)">+</button>
                            </div>
                            <button type="submit" class="btn-add-cart flex-grow-1">Add to Cart</button>
                        </form>
                    <?php else: ?>
                        <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                            class="btn btn-add-cart w-100">Login to Add to Cart</a>
                    <?php endif; ?>
                <?php else: ?>
                    <button class="btn-out-of-stock w-100" disabled>Out of Stock</button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Product Reviews -->
        <div class="row mt-5">
            <div class="col-12">
                <h2>Customer Reviews</h2>
                <?php if (empty($reviews)): ?>
                    <p>No reviews yet. Be the first to review this product!</p>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-card mb-3">
                            <div class="review-header">
                                <div class="review-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?php echo $i <= $review['rating'] ? '' : '-o'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <div class="review-meta">
                                    by <?php echo htmlspecialchars($review['username']); ?>
                                    on <?php echo date('F j, Y', strtotime($review['created_at'])); ?>
                                </div>
                            </div>
                            <div class="review-body">
                                <?php echo nl2br(htmlspecialchars($review['review_text'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="add-review-section mt-4">
                        <h3>Write a Review</h3>
                        <form action="review.php" method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                            <div class="mb-3">
                                <label for="rating" class="form-label">Rating</label>
                                <select class="form-select" id="rating" name="rating" required>
                                    <option value="">Select rating</option>
                                    <option value="5">5 stars - Excellent</option>
                                    <option value="4">4 stars - Very Good</option>
                                    <option value="3">3 stars - Good</option>
                                    <option value="2">2 stars - Fair</option>
                                    <option value="1">1 star - Poor</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="review" class="form-label">Your Review</label>
                                <textarea class="form-control" id="review" name="review_text" rows="4" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Review</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mt-4">
                        Please <a href="login.php">log in</a> to write a review.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateMainImage(src) {
            document.querySelector('.main-product-image').src = src;
        }

        function updateQuantity(change) {
            const input = document.querySelector('.quantity-value');
            let newValue = parseInt(input.value) + change;

            // Ensure value is within min and max bounds
            newValue = Math.max(parseInt(input.min), Math.min(parseInt(input.max), newValue));

            input.value = newValue;
        }

        // Prevent manual input of invalid values
        document.querySelector('.quantity-value').addEventListener('change', function() {
            const min = parseInt(this.min);
            const max = parseInt(this.max);
            let value = parseInt(this.value);

            if (isNaN(value)) value = 1;
            this.value = Math.max(min, Math.min(max, value));
        });
    </script>
</body>

</html>