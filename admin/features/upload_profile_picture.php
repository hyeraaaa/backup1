<?php
require_once '../../login/dbh.inc.php';
session_start();

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'User  not logged in.']);
    exit();
}

$admin_id = $_SESSION['user']['admin_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $file = $_FILES['profile_picture'];
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF files are allowed.']);
            exit();
        }

        // Validate file size (e.g., max 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'File size exceeds the maximum limit of 2MB.']);
            exit();
        }

        // Generate a unique file name to avoid overwrites
        $fileName = uniqid('profile_', true) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $uploadDir = '../uploads/';

        // Create the directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filePath = $uploadDir . $fileName;

        // Move the uploaded file to the desired directory
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Update the database with the new profile picture path
            $query = "UPDATE admin SET profile_picture = :profile_picture WHERE admin_id = :admin_id";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['profile_picture' => $fileName, 'admin_id' => $admin_id]);

            // Update the session variable with the new profile picture path
            $_SESSION['user']['profile_picture'] = $fileName;

            // Return success response
            echo json_encode(['success' => true, 'message' => 'Profile picture changed successfully!']);
            exit();
        } else {
            echo json_encode(['success' => false, 'message' => 'Error uploading file.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No file uploaded or there was an upload error.']);
    }
}