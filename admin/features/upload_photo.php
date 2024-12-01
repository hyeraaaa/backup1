<?php
require_once '../../login/dbh.inc.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../../login/login.php");
    exit();
}

$admin_id = $_SESSION['user']['admin_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        if (isset($_FILES['coverPhoto']) && $_FILES['coverPhoto']['error'] == UPLOAD_ERR_OK) {
            $coverPhoto = $_FILES['coverPhoto'];
            $coverPhotoPath = '../uploads/' . basename($coverPhoto['name']);
            move_uploaded_file($coverPhoto['tmp_name'], $coverPhotoPath);

            $stmt = $pdo->prepare("UPDATE admin SET cover_photo = ? WHERE admin_id = ?");
            $stmt->execute([$coverPhoto['name'], $admin_id]);
        }

        header("Location: manage.php");
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?> 