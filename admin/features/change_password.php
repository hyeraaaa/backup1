<?php
require_once '../../login/dbh.inc.php'; // Database connection
require 'log.php';
session_start();

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'User  not logged in.']);
    exit();
}

$user_id = $_SESSION['user']['admin_id']; // Assuming admin_id is used for user identification
$data = json_decode(file_get_contents('php://input'), true);

$currentPassword = $data['currentPassword'];
$newPassword = $data['newPassword'];

// Fetch the current password hash from the database
$stmt = $pdo->prepare("SELECT password FROM admin WHERE admin_id = :admin_id");
$stmt->execute(['admin_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($currentPassword, $user['password'])) {
    // Update the password
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    $updateStmt = $pdo->prepare("UPDATE admin SET password = :password WHERE admin_id = :admin_id");
    $updateStmt->execute(['password' => $hashedPassword, 'admin_id' => $user_id]);
    logAction($pdo, $user_id, 'admin', 'UPDATE', 'admin', $user_id, 'Password Change Attempt Success');
    
    // Return success response with countdown
    echo json_encode(['success' => true, 'message' => 'Password changed successfully.', 'countdown' => 5]);
    
} else {
    logAction($pdo, $user_id, 'admin', 'UPDATE', 'admin', $user_id, 'Password Change Attempt Failed');
    echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
}