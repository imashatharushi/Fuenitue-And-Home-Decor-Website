<?php
require_once __DIR__ . '/../../config/connection.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle image upload
function uploadImage($image)
{
    $target_dir = "../../assets/img/products/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_extension = strtolower(pathinfo($image["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;

    // Check if image file is a actual image
    $check = getimagesize($image["tmp_name"]);
    if ($check === false) {
        return ['status' => 'error', 'message' => 'File is not an image.'];
    }

    // Check file size (5MB max)
    if ($image["size"] > 5000000) {
        return ['status' => 'error', 'message' => 'File is too large. Maximum size is 5MB.'];
    }

    // Allow certain file formats
    $allowed_types = ["jpg", "jpeg", "png", "gif"];
    if (!in_array($file_extension, $allowed_types)) {
        return ['status' => 'error', 'message' => 'Only JPG, JPEG, PNG & GIF files are allowed.'];
    }

    if (move_uploaded_file($image["tmp_name"], $target_file)) {
        return ['status' => 'success', 'filename' => "assets/img/products/" . $new_filename];
    } else {
        return ['status' => 'error', 'message' => 'Error uploading file.'];
    }
}

// Get all products with category information
function getAllProducts()
{
    global $conn;
    try {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.category_id 
                ORDER BY p.name ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return ['status' => 'success', 'data' => $products];
    } catch (PDOException $e) {
        return ['status' => 'error', 'message' => 'Failed to fetch products: ' . $e->getMessage()];
    }
}

// Get a single product by ID
function getProductById($product_id)
{
    global $conn;
    try {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.category_id 
                WHERE p.product_id = :product_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        return ['status' => 'success', 'data' => $product];
    } catch (PDOException $e) {
        return ['status' => 'error', 'message' => 'Failed to fetch product: ' . $e->getMessage()];
    }
}

// Add a new product
function addProduct($name, $description, $price, $category_id, $stock_quantity, $image)
{
    global $conn;
    try {
        // Handle image upload first
        if ($image['error'] === 0) {
            $upload_result = uploadImage($image);
            if ($upload_result['status'] === 'error') {
                return $upload_result;
            }
            $image_url = $upload_result['filename'];
        } else {
            $image_url = null;
        }

        $sql = "INSERT INTO products (name, description, price, category_id, stock_quantity, image_url) 
                VALUES (:name, :description, :price, :category_id, :stock_quantity, :image_url)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':stock_quantity', $stock_quantity);
        $stmt->bindParam(':image_url', $image_url);

        $stmt->execute();
        return ['status' => 'success', 'message' => 'Product added successfully'];
    } catch (PDOException $e) {
        return ['status' => 'error', 'message' => 'Failed to add product: ' . $e->getMessage()];
    }
}

// Update an existing product
function updateProduct($product_id, $name, $description, $price, $category_id, $stock_quantity, $image = null)
{
    global $conn;
    try {
        // Start with existing product data
        $current_product = getProductById($product_id);
        if ($current_product['status'] === 'error') {
            return $current_product;
        }

        $image_url = $current_product['data']['image_url'];

        // Handle new image upload if provided
        if ($image && $image['error'] === 0) {
            $upload_result = uploadImage($image);
            if ($upload_result['status'] === 'error') {
                return $upload_result;
            }
            $image_url = $upload_result['filename'];

            // Delete old image if exists
            if ($current_product['data']['image_url']) {
                $old_image = '../../' . $current_product['data']['image_url'];
                if (file_exists($old_image)) {
                    unlink($old_image);
                }
            }
        }

        $sql = "UPDATE products 
                SET name = :name, 
                    description = :description, 
                    price = :price, 
                    category_id = :category_id, 
                    stock_quantity = :stock_quantity, 
                    image_url = :image_url 
                WHERE product_id = :product_id";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':stock_quantity', $stock_quantity);
        $stmt->bindParam(':image_url', $image_url);

        $stmt->execute();
        return ['status' => 'success', 'message' => 'Product updated successfully'];
    } catch (PDOException $e) {
        return ['status' => 'error', 'message' => 'Failed to update product: ' . $e->getMessage()];
    }
}

// Delete a product
function deleteProduct($product_id)
{
    global $conn;
    try {
        // Get product image before deleting
        $product = getProductById($product_id);
        if ($product['status'] === 'success' && $product['data']['image_url']) {
            $image_path = '../../' . $product['data']['image_url'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }

        $sql = "DELETE FROM products WHERE product_id = :product_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();

        return ['status' => 'success', 'message' => 'Product deleted successfully'];
    } catch (PDOException $e) {
        return ['status' => 'error', 'message' => 'Failed to delete product: ' . $e->getMessage()];
    }
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $result = addProduct(
                $_POST['name'],
                $_POST['description'],
                $_POST['price'],
                $_POST['category_id'],
                $_POST['stock_quantity'],
                $_FILES['image'] ?? null
            );
            break;

        case 'update':
            $result = updateProduct(
                $_POST['product_id'],
                $_POST['name'],
                $_POST['description'],
                $_POST['price'],
                $_POST['category_id'],
                $_POST['stock_quantity'],
                $_FILES['image'] ?? null
            );
            break;

        case 'delete':
            $result = deleteProduct($_POST['product_id']);
            break;

        default:
            $result = ['status' => 'error', 'message' => 'Invalid action'];
    }

    if ($result['status'] === 'success') {
        $_SESSION['success'] = $result['message'];
    } else {
        $_SESSION['error'] = $result['message'];
    }

    // Redirect back to products page
    header('Location: ../products.php');
    exit();
}
