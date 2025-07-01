<?php
require_once 'includes/auth_check.php';
require_once '../config/connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';    // Delete user
    if ($action === 'deleteUser') {
        $userId = $_POST['user_id'] ?? '';

        if (empty($userId)) {
            echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
            exit;
        }

        try {
            $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND user_id != ?");
            $stmt->execute([$userId, $_SESSION['user_id']]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'success', 'message' => 'User deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Unable to delete user']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error deleting user: ' . $e->getMessage()]);
        }
        exit;
    }

    // Add new user
    if ($action === 'addUser') {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $isAdmin = isset($_POST['is_admin']) ? 1 : 0;

        if (empty($username) || empty($email) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
            exit;
        }

        try {
            $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, is_admin) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT), $isAdmin]);

            echo json_encode(['status' => 'success', 'message' => 'User added successfully']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error adding user: ' . $e->getMessage()]);
        }
        exit;
    }

    // Edit user
    if ($action === 'editUser') {
        $userId = $_POST['user_id'] ?? '';
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $isAdmin = isset($_POST['is_admin']) ? 1 : 0;

        if (empty($userId) || empty($username) || empty($email)) {
            echo json_encode(['status' => 'error', 'message' => 'Required fields are missing']);
            exit;
        }

        try {
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, is_admin = ? WHERE user_id = ?");
            $stmt->execute([$username, $email, $isAdmin, $userId]);

            echo json_encode(['status' => 'success', 'message' => 'User updated successfully']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error updating user: ' . $e->getMessage()]);
        }
        exit;
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
