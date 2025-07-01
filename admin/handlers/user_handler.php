<?php
require_once '../../config/connection.php';
require_once '../includes/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Validate input
$user_id = $_POST['user_id'] ?? '';
$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$is_admin = isset($_POST['is_admin']) ? (bool)$_POST['is_admin'] : false;
$password = $_POST['password'] ?? '';

if (!$user_id || !$username || !$email) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    // Check if username is already taken by another user
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = :username AND user_id != :user_id");
    $stmt->execute([':username' => $username, ':user_id' => $user_id]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('Username is already taken');
    }

    // Check if email is already taken by another user
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = :email AND user_id != :user_id");
    $stmt->execute([':email' => $email, ':user_id' => $user_id]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('Email is already taken');
    }

    $conn->beginTransaction();

    // Build update query based on whether password is being changed
    if (!empty($password)) {
        $query = "UPDATE users SET username = :username, email = :email, is_admin = :is_admin, password_hash = :password_hash WHERE user_id = :user_id";
        $params = [
            ':username' => $username,
            ':email' => $email,
            ':is_admin' => $is_admin,
            ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ':user_id' => $user_id
        ];
    } else {
        $query = "UPDATE users SET username = :username, email = :email, is_admin = :is_admin WHERE user_id = :user_id";
        $params = [
            ':username' => $username,
            ':email' => $email,
            ':is_admin' => $is_admin,
            ':user_id' => $user_id
        ];
    }

    $stmt = $conn->prepare($query);
    $stmt->execute($params);

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'User updated successfully']);
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
