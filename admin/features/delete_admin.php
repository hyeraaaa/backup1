<?php
require_once '../../login/dbh.inc.php';
require 'log.php';
session_start();

// Check if user is logged in and is a superadmin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'superadmin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_id'])) {
    try {
        // Prevent deletion of currently logged-in superadmin
        if ($_POST['admin_id'] == $_SESSION['user']['admin_id']) {
            http_response_code(403);
            echo json_encode(['error' => 'Cannot delete your own account']);
            exit();
        }

        // Get admin details before deletion for logging
        $getAdminStmt = $pdo->prepare("SELECT first_name, last_name FROM admin WHERE admin_id = :admin_id");
        $getAdminStmt->execute([':admin_id' => $_POST['admin_id']]);
        $adminInfo = $getAdminStmt->fetch(PDO::FETCH_ASSOC);

        if (!$adminInfo) {
            throw new Exception('Admin not found');
        }

        // Delete the admin
        $deleteStmt = $pdo->prepare("DELETE FROM admin WHERE admin_id = :admin_id");
        $result = $deleteStmt->execute([':admin_id' => $_POST['admin_id']]);

        if ($result) {
            // Log the deletion
            logAction(
                $pdo,
                $_SESSION['user']['admin_id'],
                'admin',
                'DELETE',
                'admin',
                $_POST['admin_id'],
                "Deleted admin: {$adminInfo['first_name']} {$adminInfo['last_name']}"
            );

            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Failed to delete admin');
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
}