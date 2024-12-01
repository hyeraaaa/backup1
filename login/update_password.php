<?php
include 'dbh.inc.php';
include '../admin/features/log.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); 
    $type = $_POST['type'];

    if ($type === 'student') {
        $sql = $pdo->prepare("SELECT * FROM student WHERE email = :email");
        $sql->execute(['email' => $email]);
        $student = $sql->fetch(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare("UPDATE student SET password = :password, otp = NULL, otp_expiry = NULL WHERE email = :email");
        
        logAction($pdo, $student['student_id'], 'Student', 'Password Reset Attempt', 'student', $student['student_id'], 'Password has been updated');
    } else {
        $sql = $pdo->prepare("SELECT * FROM admin WHERE email = :email");
        $sql->execute(['email' => $email]);
        $admin = $sql->fetch(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare("UPDATE student SET password = :password, otp = NULL, otp_expiry = NULL WHERE email = :email");
        $stmt = $pdo->prepare("UPDATE admin SET password = :password, otp = NULL, otp_expiry = NULL WHERE email = :email");
        if($result_staff['role'] === 'superadmin'){
            $role = 'superadmin';
        } else {
            $role = 'admin';
        }
        logAction($pdo, $admin['admin_id'], $role, 'Password Reset Attempt', 'admin', $admin['admin_id'], 'Password has been updated');

    }

    $stmt->execute([
        'password' => $password,
        'email' => $email
    ]);

    if ($stmt->rowCount()) {
        $message = "Password has been updated successfully.";
        header("Location: login.php?message=$message");
    } else {
        echo "An error occurred. Please try again.";
    }
}
?>
