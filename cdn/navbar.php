<?php
require_once '../../login/dbh.inc.php';
//Get info from admin session
$user = $_SESSION['user'];
if ($_SESSION['user_type'] === 'admin') {
    $admin_id = $_SESSION['user']['admin_id'];
} else {
    $user_id = $_SESSION['user']['student_id'];
}
$first_name = $_SESSION['user']['first_name'];
$last_name = $_SESSION['user']['last_name'];
$email = $_SESSION['user']['email'];
$contact_number = $_SESSION['user']['contact_number'];
$department_id = $_SESSION['user']['department_id'];
$profile_picture = $_SESSION['user']['profile_picture'];
?>

<nav class="navbar navbar-expand-lg bg-white text-black fixed-top" style="border-bottom: 1px solid #e9ecef; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">
    <div class="container-fluid">
        <div class="user-left d-flex">


            <a class="navbar-brand d-flex align-items-center" href="#"><img src="../img/brand.png" class="img-fluid branding" alt=""></a>
        </div>

        <div class="user-right d-flex align-items-center justify-content-center">
            <p class="username d-flex align-items-center m-0 me-2"><?php echo $first_name ?></p>
            <div class="user-profile">
                <div class="dropdown">
                    <button class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" style="border: none; background: none; padding: 0;">
                        <img src="<?php echo '../uploads/' . htmlspecialchars($profile_picture); ?>" alt="Profile Picture" style="width: 40px;height: 40px;object-fit: cover;border-radius: 50%">
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end mt-2 py-2 shadow-sm">
                        <li>
                            <div class="px-3 py-2 d-flex align-items-center">
                                <img class="rounded-circle me-2" src="<?php echo '../uploads/' . htmlspecialchars($profile_picture); ?>" alt="Profile Picture" style="width: 40px; height: 40px; object-fit: cover;">
                                <div>
                                    <p class="mb-0 small"><?php echo htmlspecialchars($first_name . " " . $last_name); ?></p>
                                    <p class="mb-0 small text-muted"><?php echo htmlspecialchars($email); ?></p>
                                </div>
                            </div>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center py-2" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                <i class="bi bi-key me-2"></i>
                                Change Password
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center py-2" href="#" data-bs-toggle="modal" data-bs-target="#changeProfilePictureModal">
                                <i class="bi bi-person-circle me-2"></i>
                                Change Profile Picture
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center py-2 text-danger" href="#" onclick="return confirmLogout()">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
</nav>