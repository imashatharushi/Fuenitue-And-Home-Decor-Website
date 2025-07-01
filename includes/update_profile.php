<?php
session_start();
require_once dirname(__DIR__) . '/config/connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $action = $_POST['action'] ?? 'update_profile';

    try {
        switch ($action) {
            case 'update_profile':
                handleUpdateProfile($conn, $userId);
                $_SESSION['success'] = 'Profile updated successfully';
                break;

            case 'add_address':
                handleAddAddress($conn, $userId);
                $_SESSION['success'] = 'Address added successfully';
                break;

            case 'delete_address':
                handleDeleteAddress($conn, $userId);
                $_SESSION['success'] = 'Address deleted successfully';
                break;

            case 'add_payment':
                handleAddPayment($conn, $userId);
                $_SESSION['success'] = 'Payment method added successfully';
                break;

            case 'delete_payment':
                handleDeletePayment($conn, $userId);
                $_SESSION['success'] = 'Payment method deleted successfully';
                break;

            default:
                throw new Exception('Invalid action');
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }

    header('Location: ../profile.php');
    exit();
}

function handleUpdateProfile($conn, $userId)
{
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $currentPassword = $_POST['current_password'] ?? '';

    // First verify the current password
    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
        throw new Exception('Current password is incorrect');
    }

    // Check if username or email already exists for other users
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
    $stmt->execute([$username, $email, $userId]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('Username or email already exists');
    }

    // Update user information
    if ($newPassword) {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password_hash = ? WHERE user_id = ?");
        $stmt->execute([$username, $email, $passwordHash, $userId]);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE user_id = ?");
        $stmt->execute([$username, $email, $userId]);
    }

    // Update session variables
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
}

function handleAddAddress($conn, $userId)
{
    // Validate required fields
    $requiredFields = ['full_name', 'address_line1', 'city', 'state', 'postal_code', 'country', 'phone'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }

    $stmt = $conn->prepare("
        INSERT INTO shipping_addresses 
        (user_id, full_name, address_line1, address_line2, city, state, postal_code, country, phone)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $userId,
        $_POST['full_name'],
        $_POST['address_line1'],
        $_POST['address_line2'] ?? '',
        $_POST['city'],
        $_POST['state'],
        $_POST['postal_code'],
        $_POST['country'],
        $_POST['phone']
    ]);
}

function handleDeleteAddress($conn, $userId)
{
    $addressId = $_POST['address_id'] ?? 0;
    if (!$addressId) {
        throw new Exception('Invalid address ID');
    }

    // Verify the address belongs to the user
    $stmt = $conn->prepare("DELETE FROM shipping_addresses WHERE address_id = ? AND user_id = ?");
    $stmt->execute([$addressId, $userId]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Address not found or access denied');
    }
}

function handleAddPayment($conn, $userId)
{
    // Validate required fields
    $requiredFields = ['card_holder_name', 'card_number', 'expiry_month', 'expiry_year', 'cvv'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }

    // Validate card number format
    if (!preg_match('/^[0-9]{16}$/', $_POST['card_number'])) {
        throw new Exception('Invalid card number format');
    }

    // Validate CVV format
    if (!preg_match('/^[0-9]{3,4}$/', $_POST['cvv'])) {
        throw new Exception('Invalid CVV format');
    }

    // Encrypt card number (in production, use proper encryption)
    $encrypted_card_number = password_hash($_POST['card_number'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO payment_methods 
        (user_id, card_holder_name, card_number_encrypted, expiry_month, expiry_year)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $userId,
        $_POST['card_holder_name'],
        $encrypted_card_number,
        $_POST['expiry_month'],
        $_POST['expiry_year']
    ]);
}

function handleDeletePayment($conn, $userId)
{
    $paymentId = $_POST['payment_id'] ?? 0;
    if (!$paymentId) {
        throw new Exception('Invalid payment method ID');
    }

    // Verify the payment method belongs to the user
    $stmt = $conn->prepare("DELETE FROM payment_methods WHERE payment_id = ? AND user_id = ?");
    $stmt->execute([$paymentId, $userId]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Payment method not found or access denied');
    }
}
