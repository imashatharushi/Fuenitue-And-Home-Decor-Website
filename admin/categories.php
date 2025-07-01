<?php
require_once 'includes/auth_check.php';
require_once 'handlers/category_handler.php';

// Fetch all categories
$categoriesResult = getAllCategories();
if ($categoriesResult['status'] === 'error') {
  $_SESSION['error'] = $categoriesResult['message'];
}
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
  <title>Category Management - Modern Furniture Store</title>

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
    <main class="admin-main"> <!-- Header -->
      <header class="admin-header">
        <h1 class="h3 m-0">Category Management</h1>
        <button
          class="admin-btn admin-btn-primary btn-sm"
          data-bs-toggle="modal"
          data-bs-target="#addCategoryModal">
          <i class="fas fa-plus"></i> Add New Category
        </button>
      </header>
      <!-- Categories Table -->
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
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Products Count</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($categories)): ?>
                <tr>
                  <td colspan="6" class="text-center">No categories found</td>
                </tr>
              <?php else: ?>
                <?php foreach ($categories as $category): ?>
                  <tr>
                    <td>#<?php echo str_pad($category['category_id'], 3, '0', STR_PAD_LEFT); ?></td>
                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                    <td><?php echo htmlspecialchars($category['description']); ?></td>
                    <td><span class="badge bg-primary"><?php echo $category['product_count']; ?> Products</span></td>
                    <td>
                      <button
                        class="admin-btn admin-btn-warning btn-sm edit-category"
                        data-bs-toggle="modal"
                        data-bs-target="#editCategoryModal" data-category='<?php echo htmlspecialchars(json_encode([
                                                                              'id' => $category['category_id'],
                                                                              'name' => $category['name'],
                                                                              'description' => $category['description']
                                                                            ])); ?>'>
                        <i class="fas fa-edit"></i>
                      </button>
                      <form action="handlers/category_handler.php" method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
                        <button type="submit" class="admin-btn admin-btn-danger btn-sm"
                          onclick="return confirm('Are you sure you want to delete category: <?php echo htmlspecialchars($category['name']); ?>?');">
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

  <!-- Add Category Modal -->
  <div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New Category</h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form action="handlers/category_handler.php" method="POST">
            <input type="hidden" name="action" value="add">
            <div class="admin-form-group">
              <label class="admin-form-label">Category Name</label>
              <input type="text" name="name" class="admin-form-control" required />
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
              <button type="button" class="admin-btn admin-btn-secondary btn-sm" data-bs-dismiss="modal">
                Cancel
              </button>
              <button type="submit" class="admin-btn admin-btn-primary btn-sm">
                Add Category
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Category Modal -->
  <div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Category</h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form action="handlers/category_handler.php" method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="category_id" id="editCategoryId">
            <div class="admin-form-group">
              <label class="admin-form-label">Category Name</label>
              <input
                type="text"
                name="name"
                id="editCategoryName"
                class="admin-form-control"
                required />
            </div>
            <div class="admin-form-group">
              <label class="admin-form-label">Description</label>
              <textarea
                name="description"
                id="editCategoryDescription"
                class="admin-form-control"
                rows="3"
                required></textarea>
            </div>
            <div class="modal-footer">
              <button type="button" class="admin-btn admin-btn-secondary btn-sm" data-bs-dismiss="modal">
                Cancel
              </button>
              <button type="submit" class="admin-btn admin-btn-primary btn-sm">
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
      // Edit Category - Populate Modal
      document.querySelectorAll('.edit-category').forEach(button => {
        button.addEventListener('click', function() {
          const categoryData = JSON.parse(this.dataset.category);
          document.getElementById('editCategoryId').value = categoryData.id;
          document.getElementById('editCategoryName').value = categoryData.name;
          document.getElementById('editCategoryDescription').value = categoryData.description;
        });
      });
    });
  </script>
</body>

</html>