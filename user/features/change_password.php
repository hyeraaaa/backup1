<?php
require_once '../../login/dbh.inc.php'; // Database connection
require '../../admin/features/log.php';
session_start();

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'User  not logged in.']);
    exit();
}

$user_id = $_SESSION['user']['student_id'];
$data = json_decode(file_get_contents('php://input'), true);

$currentPassword = $data['currentPassword'];
$newPassword = $data['newPassword'];

// Fetch the current password hash from the database
$stmt = $pdo->prepare("SELECT password FROM student WHERE student_id = :student_id");
$stmt->execute(['student_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($currentPassword, $user['password'])) {
    // Update the password
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    $updateStmt = $pdo->prepare("UPDATE student SET password = :password WHERE student_id = :student_id");
    $updateStmt->execute(['password' => $hashedPassword, 'student_id' => $user_id]);
    logAction($pdo, $user_id, 'Student', 'UPDATE', 'student', $user_id, 'Password Change Attempt Success');
    
    // Return success response with countdown
    echo json_encode(['success' => true, 'message' => 'Password changed successfully.', 'countdown' => 5]);
    
} else {
    logAction($pdo, $user_id, 'Student', 'UPDATE', 'student', $user_id, 'Password Change Attempt Failed');
    echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
}