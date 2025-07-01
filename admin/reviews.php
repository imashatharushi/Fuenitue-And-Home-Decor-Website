<?php
require_once 'includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Review Management - Modern Furniture Store</title>

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
        <h1 class="h3 m-0">Review Management</h1>
        <div>
          <button class="admin-btn admin-btn-success btn-sm me-2">
            <i class="fas fa-download"></i> Export Reviews
          </button>
          <button class="admin-btn admin-btn-primary btn-sm">
            <i class="fas fa-sync"></i> Refresh
          </button>
        </div>
      </header>

      <!-- Review Filters -->
      <div class="admin-card mb-4">
        <div class="row g-3">
          <div class="col-md-2">
            <select class="admin-form-control">
              <option value="">All Ratings</option>
              <option value="5">5 Stars</option>
              <option value="4">4 Stars</option>
              <option value="3">3 Stars</option>
              <option value="2">2 Stars</option>
              <option value="1">1 Star</option>
            </select>
          </div>
          <div class="col-md-3">
            <select class="admin-form-control">
              <option value="">All Products</option>
              <option value="sofa">Modern Sofa</option>
              <option value="chair">Office Chair</option>
              <option value="dining">Dining Set</option>
            </select>
          </div>
          <div class="col-md-3">
            <input
              type="date"
              class="admin-form-control"
              placeholder="Filter by Date" />
          </div>
          <div class="col-md-4">
            <div class="input-group">
              <input
                type="text"
                class="admin-form-control"
                placeholder="Search Reviews..." />
              <button class="admin-btn admin-btn-primary btn-sm">
                Search
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Reviews Table -->
      <div class="admin-card">
        <div class="table-responsive">
          <table class="admin-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>User</th>
                <th>Product</th>
                <th>Rating</th>
                <th>Review</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>#1001</td>
                <td>
                  Sarah Johnson<br /><small class="text-muted">sarah@example.com</small>
                </td>
                <td>Modern Sofa</td>
                <td>
                  <div class="text-warning">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                  </div>
                </td>
                <td>Excellent quality and comfort!</td>
                <td>June 8, 2025</td>
                <td>
                  <button
                    class="admin-btn admin-btn-warning btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#editReviewModal">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="admin-btn admin-btn-danger btn-sm">
                    <i class="fas fa-trash"></i>
                  </button>
                  <button class="admin-btn admin-btn-success btn-sm">
                    <i class="fas fa-check"></i>
                  </button>
                </td>
              </tr>
              <tr>
                <td>#1002</td>
                <td>
                  Michael Chen<br /><small class="text-muted">michael@example.com</small>
                </td>
                <td>Office Chair</td>
                <td>
                  <div class="text-warning">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="far fa-star"></i>
                  </div>
                </td>
                <td>
                  Great ergonomic design, very comfortable for long hours
                </td>
                <td>June 7, 2025</td>
                <td>
                  <button
                    class="admin-btn admin-btn-warning btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#editReviewModal">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="admin-btn admin-btn-danger btn-sm">
                    <i class="fas fa-trash"></i>
                  </button>
                  <button class="admin-btn admin-btn-success btn-sm">
                    <i class="fas fa-check"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>

  <!-- Edit Review Modal -->
  <div class="modal fade" id="editReviewModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Review</h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form>
            <div class="admin-form-group">
              <label class="admin-form-label">Rating</label>
              <div class="rating-select text-center">
                <input
                  type="radio"
                  name="rating"
                  id="star5"
                  value="5"
                  checked />
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
            </div>
            <div class="admin-form-group">
              <label class="admin-form-label">Review Text</label>
              <textarea class="admin-form-control" rows="4" required>
Excellent quality and comfort!</textarea>
            </div>
            <div class="admin-form-group">
              <label class="admin-form-label">Status</label>
              <select class="admin-form-control">
                <option value="approved">Approved</option>
                <option value="pending">Pending</option>
                <option value="rejected">Rejected</option>
              </select>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button
            type="button"
            class="admin-btn admin-btn-secondary"
            data-bs-dismiss="modal">
            Cancel
          </button>
          <button type="button" class="admin-btn admin-btn-primary">
            Save Changes
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>