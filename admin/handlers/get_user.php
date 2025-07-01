<?php
require_once '../../config/connection.php';
require_once '../includes/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$user_id = $_GET['user_id'] ?? '';

if (!$user_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT user_id, username, email, is_admin FROM users WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    echo json_encode(['success' => true, 'user' => $user]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error fetching user: ' . $e->getMessage()]);
}
