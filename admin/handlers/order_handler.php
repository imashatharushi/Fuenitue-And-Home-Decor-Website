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
$order_id = $_POST['order_id'] ?? '';
$status = $_POST['status'] ?? '';

if (!$order_id || !$status) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Validate status
$validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
if (!in_array($status, $validStatuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    $conn->beginTransaction();    // Update order
    $stmt = $conn->prepare("
        UPDATE orders 
        SET status = :status
        WHERE order_id = :order_id
    ");

    $stmt->execute([
        ':status' => $status,
        ':order_id' => $order_id
    ]);

    // Add to order history if we have order_history table
    $stmt = $conn->prepare("SHOW TABLES LIKE 'order_history'");
    $stmt->execute();
    if ($stmt->rowCount() > 0 && !empty($notes)) {
        $stmt = $conn->prepare('
            INSERT INTO order_history (order_id, status, notes, created_by)
            VALUES (:order_id, :status, :notes, :created_by)
        ');
        $stmt->execute([
            ':order_id' => $order_id,
            ':status' => $status,
            ':notes' => $notes,
            ':created_by' => $_SESSION['admin_id']
        ]);
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Order status updated successfully']);
} catch (Exception $e) {
    $conn->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error updating order: ' . $e->getMessage()]);
}
