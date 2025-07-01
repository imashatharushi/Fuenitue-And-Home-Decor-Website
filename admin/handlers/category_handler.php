<?php
require_once __DIR__ . '/../../config/connection.php';

// Get all categories with product counts
function getAllCategories()
{
    global $conn;
    try {
        // First check if the categories table exists and has data
        $checkSql = "SELECT COUNT(*) FROM categories";
        $stmt = $conn->prepare($checkSql);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count === 0) {
            return ['status' => 'error', 'message' => 'No categories found in the database'];
        }
        $sql = "SELECT 
                    c.category_id,
                    c.name,
                    c.description,
                    COUNT(p.product_id) as product_count
                FROM categories c
                LEFT JOIN products p ON c.category_id = p.category_id
                GROUP BY c.category_id, c.name, c.description
                ORDER BY c.name ASC";

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($results)) {
            return ['status' => 'error', 'message' => 'No categories found after query'];
        }

        return ['status' => 'success', 'data' => $results];
    } catch (PDOException $e) {
        error_log('Error in getAllCategories: ' . $e->getMessage());
        return ['status' => 'error', 'message' => 'Error fetching categories: ' . $e->getMessage()];
    }
}

// Add new category
function addCategory($name, $description)
{
    global $conn;
    try {
        $sql = "INSERT INTO categories (name, description) VALUES (:name, :description)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':name' => $name,
            ':description' => $description
        ]);
        return ['status' => 'success', 'message' => 'Category added successfully'];
    } catch (PDOException $e) {
        return ['status' => 'error', 'message' => 'Error adding category: ' . $e->getMessage()];
    }
}

// Update existing category
function updateCategory($category_id, $name, $description)
{
    global $conn;
    try {
        $sql = "UPDATE categories SET name = :name, description = :description WHERE category_id = :category_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':category_id' => $category_id,
            ':name' => $name,
            ':description' => $description
        ]);
        return ['status' => 'success', 'message' => 'Category updated successfully'];
    } catch (PDOException $e) {
        return ['status' => 'error', 'message' => 'Error updating category: ' . $e->getMessage()];
    }
}

// Delete category
function deleteCategory($category_id)
{
    global $conn;
    try {
        // First check if there are any products in this category
        $sql = "SELECT COUNT(*) FROM products WHERE category_id = :category_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':category_id' => $category_id]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            return ['status' => 'error', 'message' => 'Cannot delete category: It contains products'];
        }

        $sql = "DELETE FROM categories WHERE category_id = :category_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':category_id' => $category_id]);
        return ['status' => 'success', 'message' => 'Category deleted successfully'];
    } catch (PDOException $e) {
        return ['status' => 'error', 'message' => 'Error deleting category: ' . $e->getMessage()];
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';

            if (empty($name)) {
                $_SESSION['error'] = 'Category name is required';
            } else {
                $result = addCategory($name, $description);
                if ($result['status'] === 'success') {
                    $_SESSION['success'] = $result['message'];
                } else {
                    $_SESSION['error'] = $result['message'];
                }
            }
            break;
        case 'update':
            $category_id = $_POST['category_id'] ?? '';
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';

            if (empty($category_id) || empty($name)) {
                $_SESSION['error'] = 'Category ID and name are required';
            } else {
                $result = updateCategory($category_id, $name, $description);
                if ($result['status'] === 'success') {
                    $_SESSION['success'] = $result['message'];
                } else {
                    $_SESSION['error'] = $result['message'];
                }
            }
            break;

        case 'delete':
            $category_id = $_POST['category_id'] ?? '';

            if (empty($category_id)) {
                $_SESSION['error'] = 'Category ID is required';
            } else {
                $result = deleteCategory($category_id);
                if ($result['status'] === 'success') {
                    $_SESSION['success'] = $result['message'];
                } else {
                    $_SESSION['error'] = $result['message'];
                }
            }
            break;
    }

    // Redirect back to categories page
    header('Location: ../categories.php');
    exit;
}
