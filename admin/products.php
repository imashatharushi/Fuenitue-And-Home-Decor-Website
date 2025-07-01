<?php
require_once 'includes/auth_check.php';
require_once 'handlers/product_handler.php';
require_once 'handlers/category_handler.php';

// Fetch all products
$productsResult = getAllProducts();
$products = $productsResult['status'] === 'success' ? $productsResult['data'] : [];

// Fetch all categories for the dropdown
$categoriesResult = getAllCategories();
$categories = $categoriesResult['status'] === 'success' ? $categoriesResult['data'] : [];

// Flash messages
if (isset($_SESSION['success'])) {
  $success_message = $_SESSION['success'];
  unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
  $error_message = $_SESSION['error'];
  unset($_SESSION['error']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Product Management - Modern Furniture Store</title>

  <!-- Favicon -->
  <link
    rel="apple-touch-icon"
    sizes="180x180"
    href="../assets/img/favicon_io/apple-touch-icon.png" />
  <link
    rel="icon"
    type="image/png"
    sizes="32x32"
    href="../assets/img/favicon_io/favicon-32x32.png" />
  <link
    rel="icon"
    type="image/png"
    sizes="16x16"
    href="../assets/img/favicon_io/favicon-16x16.png" />
  <link rel="manifest" href="../assets/img/favicon_io/site.webmanifest" />

  <!-- Bootstrap 5 CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet" />
  <!-- Font Awesome -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <!-- Custom CSS -->
  <link rel="stylesheet" href="../assets/css/colors.css" />
  <link rel="stylesheet" href="../assets/css/admin/admin.css" />
</head>

<body>
  <div class="admin-wrapper">
    <?php include '../includes/admin-sidebar.php'; ?>

    <!-- Main Content -->
    <main class="admin-main">
      <!-- Header -->
      <header class="admin-header">
        <h1 class="h3 m-0">Product Management</h1>
        <button
          class="admin-btn admin-btn-primary btn-sm"
          data-bs-toggle="modal"
          data-bs-target="#addProductModal">
          <i class="fas fa-plus"></i> Add New Product
        </button>
      </header> <!-- Products Table -->
      <div class="admin-card mt-4">
        <?php if (isset($success_message)): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <div class="table-responsive">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($products)): ?>
                <tr>
                  <td colspan="6" class="text-center">No products found</td>
                </tr>
              <?php else: ?>
                <?php foreach ($products as $product): ?>
                  <tr>
                    <td>
                      <?php if ($product['image_url']): ?>
                        <img
                          src="../<?php echo htmlspecialchars($product['image_url']); ?>"
                          alt="<?php echo htmlspecialchars($product['name']); ?>"
                          width="50"
                          height="50"
                          class="rounded" />
                      <?php else: ?>
                        <span class="text-muted">No image</span>
                      <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                    <td>$<?php echo number_format($product['price'], 2); ?></td>
                    <td><?php echo $product['stock_quantity']; ?></td>
                    <td>
                      <button
                        class="admin-btn admin-btn-warning btn-sm edit-product"
                        data-bs-toggle="modal"
                        data-bs-target="#editProductModal"
                        data-product='<?php echo htmlspecialchars(json_encode([
                                        'id' => $product['product_id'],
                                        'name' => $product['name'],
                                        'description' => $product['description'],
                                        'price' => $product['price'],
                                        'category_id' => $product['category_id'],
                                        'stock_quantity' => $product['stock_quantity']
                                      ])); ?>'>
                        <i class="fas fa-edit"></i>
                      </button>
                      <form action="handlers/product_handler.php" method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                        <button type="submit" class="admin-btn admin-btn-danger btn-sm"
                          onclick="return confirm('Are you sure you want to delete this product?');">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>

  <!-- Add Product Modal -->
  <div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New Product</h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form action="handlers/product_handler.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <div class="admin-form-group">
              <label class="admin-form-label">Product Name</label>
              <input type="text" name="name" class="admin-form-control" required />
            </div>
            <div class="admin-form-group">
              <label class="admin-form-label">Category</label>
              <select name="category_id" class="admin-form-control" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $category): ?>
                  <option value="<?php echo $category['category_id']; ?>">
                    <?php echo htmlspecialchars($category['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="admin-form-group">
              <label class="admin-form-label">Price ($)</label>
              <input type="number" name="price" class="admin-form-control" step="0.01" min="0" required />
            </div>
            <div class="admin-form-group">
              <label class="admin-form-label">Stock</label>
              <input type="number" name="stock_quantity" class="admin-form-control" min="0" required />
            </div>
            <div class="admin-form-group">
              <label class="admin-form-label">Product Image</label>
              <input
                type="file"
                name="image"
                class="admin-form-control"
                accept="image/*"
                required />
            </div>
            <div class="admin-form-group">
              <label class="admin-form-label">Description</label>
              <textarea
                name="description"
                class="admin-form-control"
                rows="3"
                required></textarea>
            </div>
            <div class="modal-footer">
              <button
                type="button"
                class="admin-btn admin-btn-secondary"
                data-bs-dismiss="modal">
                Cancel
              </button>
              <button type="submit" class="admin-btn admin-btn-primary">
                Add Product
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Product Modal -->
  <div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Product</h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form action="handlers/product_handler.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="product_id" id="editProductId">
            <div class="admin-form-group">
              <label class="admin-form-label">Product Name</label>
              <input type="text" name="name" id="editProductName" class="admin-form-control" required />
            </div>
            <div class="admin-form-group">
              <label class="admin-form-label">Category</label>
              <select name="category_id" id="editProductCategory" class="admin-form-control" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $category): ?>
                  <option value="<?php echo $category['category_id']; ?>">
                    <?php echo htmlspecialchars($category['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="admin-form-group">
              <label class="admin-form-label">Price ($)</label>
              <input type="number" name="price" id="editProductPrice" class="admin-form-control" step="0.01" min="0" required />
            </div>
            <div class="admin-form-group">
              <label class="admin-form-label">Stock</label>
              <input type="number" name="stock_quantity" id="editProductStock" class="admin-form-control" min="0" required />
            </div>
            <div class="admin-form-group">
              <label class="admin-form-label">Product Image</label>
              <input
                type="file"
                name="image"
                class="admin-form-control"
                accept="image/*" />
              <small class="text-muted">Leave empty to keep current image</small>
            </div>
            <div class="admin-form-group">
              <label class="admin-form-label">Description</label>
              <textarea
                name="description"
                id="editProductDescription"
                class="admin-form-control"
                rows="3"
                required></textarea>
            </div>
            <div class="modal-footer">
              <button
                type="button"
                class="admin-btn admin-btn-secondary"
                data-bs-dismiss="modal">
                Cancel
              </button>
              <button type="submit" class="admin-btn admin-btn-primary">
                Save Changes
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Edit Product - Populate Modal
      document.querySelectorAll('.edit-product').forEach(button => {
        button.addEventListener('click', function() {
          const productData = JSON.parse(this.dataset.product);
          document.getElementById('editProductId').value = productData.id;
          document.getElementById('editProductName').value = productData.name;
          document.getElementById('editProductCategory').value = productData.category_id;
          document.getElementById('editProductPrice').value = productData.price;
          document.getElementById('editProductStock').value = productData.stock_quantity;
          document.getElementById('editProductDescription').value = productData.description;
        });
      });
    });
  </script>
</body>

</html>