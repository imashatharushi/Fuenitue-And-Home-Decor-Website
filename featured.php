<?php
require_once 'config/connection.php';

// Get the current category filter
$category_filter = $_GET['category'] ?? 'all';
$sort = $_GET['sort'] ?? 'name_asc';

// Fetch featured products from database
function getFeaturedProducts($category = 'all', $sort = 'name_asc')
{
  global $conn;
  try {
    $sql = "SELECT p.*, c.name as category_name, 
                       (SELECT ROUND(AVG(rating), 1) FROM reviews WHERE product_id = p.product_id) as avg_rating,
                       (SELECT COUNT(*) FROM reviews WHERE product_id = p.product_id) as review_count
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.category_id 
                WHERE 1=1";

    $params = [];

    // Add category filter
    if ($category !== 'all') {
      $sql .= " AND LOWER(c.name) = LOWER(:category)";
      $params[':category'] = $category;
    }

    // Add sorting
    $sql .= match ($sort) {
      'price_asc' => ' ORDER BY p.price ASC',
      'price_desc' => ' ORDER BY p.price DESC',
      'rating_desc' => ' ORDER BY avg_rating DESC NULLS LAST',
      'name_desc' => ' ORDER BY p.name DESC',
      default => ' ORDER BY p.name ASC'
    };

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    error_log("Error in getFeaturedProducts: " . $e->getMessage());
    return [];
  }
}

// Fetch all categories for the filter buttons
function getCategories()
{
  global $conn;
  try {
    $stmt = $conn->query("SELECT name FROM categories ORDER BY name ASC");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
  } catch (PDOException $e) {
    error_log("Error fetching categories: " . $e->getMessage());
    return [];
  }
}

$categories = getCategories();
$products = getFeaturedProducts($category_filter, $sort);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Featured Products - Modern Furniture Store</title>

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
  <link rel="stylesheet" href="assets/css/featured.css" />
</head>

<body>
  <?php include 'includes/nav.php'; ?>

  <!-- Featured Header -->
  <header class="featured-header">
    <div class="container">
      <h1 class="featured-title">Featured Products</h1>
      <p class="featured-description">
        Discover our carefully curated collection of premium furniture pieces
        that blend style, comfort, and functionality.
      </p>
    </div>
  </header>
  <!-- Filter Section -->
  <section class="filter-section">
    <div class="container">
      <form method="GET" class="row g-3 align-items-center justify-content-center">
        <div class="col-md-8 text-center">
          <a href="?category=all" class="filter-btn <?php echo $category_filter === 'all' ? 'active' : ''; ?>">All</a>
          <?php foreach ($categories as $category): ?>
            <a href="?category=<?php echo urlencode($category); ?>"
              class="filter-btn <?php echo strtolower($category_filter) === strtolower($category) ? 'active' : ''; ?>">
              <?php echo htmlspecialchars($category); ?>
            </a>
          <?php endforeach; ?>
        </div>
        <div class="col-md-4">
          <select name="sort" class="form-select" onchange="this.form.submit()">
            <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
            <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
            <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Price (Low to High)</option>
            <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Price (High to Low)</option>
            <option value="rating_desc" <?php echo $sort === 'rating_desc' ? 'selected' : ''; ?>>Highest Rated</option>
          </select>
        </div>
        <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_filter); ?>">
      </form>
    </div>
  </section>
  <!-- Featured Products -->
  <section class="container my-5">
    <div class="row g-4">
      <?php if (empty($products)): ?>
        <div class="col-12 text-center">
          <p>No products available at the moment.</p>
        </div>
      <?php else: ?>
        <?php foreach ($products as $product): ?>
          <div class="col-md-4">
            <div class="featured-product-card">
              <?php if ($product['image_url']): ?>
                <img
                  src="<?php echo htmlspecialchars($product['image_url']); ?>"
                  alt="<?php echo htmlspecialchars($product['name']); ?>"
                  class="featured-product-image" />
              <?php else: ?>
                <div class="featured-product-no-image">
                  <i class="fas fa-image"></i>
                </div>
              <?php endif; ?>
              <h3 class="featured-product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
              <div class="featured-product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
              <div class="featured-product-rating">
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
                <span>(<?php echo number_format($rating, 1); ?>/5)</span>
                <span class="review-count"><?php echo $product['review_count']; ?> reviews</span>
              </div>
              <div class="featured-product-price">$<?php echo number_format($product['price'], 2); ?></div>
              <?php if ($product['stock_quantity'] > 0): ?>
                <a href="product-details.php?id=<?php echo $product['product_id']; ?>" class="btn-view-details mb-2">View Details</a>
                <form action="cart.php" method="POST" class="d-inline">
                  <input type="hidden" name="action" value="add">
                  <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                  <button type="submit" class="btn-add-cart">Add to Cart</button>
                </form>
              <?php else: ?>
                <button class="btn-out-of-stock" disabled>Out of Stock</button>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>

  <?php include 'includes/footer.php'; ?>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Get the current URL parameters
      const urlParams = new URLSearchParams(window.location.search);
      const currentSort = urlParams.get('sort') || 'name_asc';

      // Add click handlers to filter buttons
      document.querySelectorAll('.filter-btn').forEach(button => {
        button.addEventListener('click', function(e) {
          e.preventDefault();

          // Update URL with both category and current sort
          const category = new URL(this.href).searchParams.get('category');
          window.location.href = `?category=${category}&sort=${currentSort}`;
        });
      });
    });
  </script>
</body>

</html>