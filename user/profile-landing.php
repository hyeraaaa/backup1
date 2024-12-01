<?php
require_once '../login/dbh.inc.php';
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

$viewed_admin_id = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['user']['admin_id'];

$user = $_SESSION['user'];
$student_id = $_SESSION['user']['student_id'];
$user_id = $_SESSION['user']['student_id'];
$first_name = $_SESSION['user']['first_name'];
$last_name = $_SESSION['user']['last_name'];
$email = $_SESSION['user']['email'];
$contact_number = $_SESSION['user']['contact_number'];
$department_id = $_SESSION['user']['department_id'];
$profile_picture = $_SESSION['user']['profile_picture'];

if (isset($_GET['id'])) {
    $query = "SELECT cover_photo, profile_picture FROM admin WHERE admin_id = ?";
    $stmt = $pdo->prepare($query);
    $adminPhotos = $stmt->fetch(PDO::FETCH_ASSOC);

    $cover_photo = $adminPhotos['cover_photo'] ?? 'default_cover.jpg';
}

?>

<!doctype html>
<html lang="en">

<head>
    <title>Admin Posts</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- head CDN links -->
    <?php include '../cdn/head.html'; ?>
    <link rel="stylesheet" href="../admin/css/admin.css">
    <link rel="stylesheet" href="../admin/css/modals.css">
    <link rel="stylesheet" href="../admin/css/sidebar.css">
    <link rel="stylesheet" href="../admin/css/feeds-card.css">
    <link rel="stylesheet" href="../admin/css/bsu-bg.css">
    <link rel="stylesheet" href="../admin/css/filter-modal.css">
    <link rel="stylesheet" href="../admin/css/cover-photo.css">
    <link rel="stylesheet" href="../admin/css/nav-bottom.css">
    <link rel="icon" href="img/brand.png" type="image/x-icon">
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg bg-white text-black fixed-top mb-5" style="border-bottom: 1px solid #e9ecef; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">
            <div class="container-fluid">
                <div class="user-left d-flex">
                    <a class="navbar-brand d-flex align-items-center" href="#"><img src="img/brand.png" class="img-fluid branding" alt=""></a>
                </div>

                <div class="user-right d-flex align-items-center justify-content-center">
                    <p class="username d-flex align-items-center m-0"><?php echo $first_name ?></p>
                    <div class="user-profile">
                        <div class="dropdown">
                            <button class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" style="border: none; background: none; padding: 0;">
                                <img src="<?php echo "uploads/" . htmlspecialchars($profile_picture); ?>" alt="Profile Picture" style="height: 40px; width: 40px; border-radius; 50%;">
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end mt-2 py-2 shadow-sm" style="width: 300px;">

                                <li>
                                    <div class="px-2 py-2 d-flex align-items-center">
                                        <img class="rounded-circle me-2" src="<?php echo 'uploads/' . htmlspecialchars($profile_picture); ?>" alt="Profile Picture" style="width: 40px; height: 40px; object-fit: cover;">
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
    </header>
    <main>
        <div class="container-fluid pt-5">
            <div class="row g-4">
                <!-- left sidebar -->
                <div class="col-lg-3 sidebar sidebar-left d-none d-lg-block">
                    <div class="sticky-sidebar">
                        <ul class="nav flex-column">

                            <li class="nav-item">
                                <a href="user.php"><i class="fas fa-newspaper me-2"></i>Feed</a>
                            </li>

                            <li class="nav-item">
                                <a href="features/logPage.php"><i class="fas fa-clipboard-list me-2"></i>Logs</a>
                            </li>

                        </ul>
                    </div>
                </div>

                <!-- main content -->
                <div class="col-12 col-xxl-9 col-lg-8 main-content pt-4 px-4">
                    <div class="row g-0">
                        <?php
                        try {
                            $query = "SELECT admin_id, first_name, last_name, profile_picture, bio FROM admin WHERE admin_id = :admin_id";

                            $stmt = $pdo->prepare($query);
                            $stmt->bindParam(':admin_id', $viewed_admin_id, PDO::PARAM_INT);
                            $stmt->execute();

                            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

                            if ($admin) {
                                $admin_id = $admin['admin_id'];
                                $admin_name = $admin['first_name'] . " " . $admin['last_name'];
                                $admin_profile_picture = $admin['profile_picture'];
                                $admin_bio = $admin['bio'];
                            } else {
                                echo "<p>Admin not found.</p>";
                            }
                        } catch (PDOException $e) {
                            echo "Error: " . $e->getMessage();
                        }
                        ?>

                        <!-- Desktop Layout -->
                        <div class="col-12 col-xxl-12 cover desktop-layout">
                            <div class="cover-photo-container" style="position: relative;">
                                <a href="<?php echo '../admin/uploads/' . htmlspecialchars($cover_photo); ?>" data-lightbox="cover" data-title="Cover Photo">
                                    <img src="<?php echo '../admin/uploads/' . htmlspecialchars($cover_photo); ?>" alt="Cover Photo">
                                </a>
                            </div>
                            <div class="profile-section">
                                <div class="profile-photo-container" style="position: relative;">
                                    <a href="<?php echo '../admin/uploads/' . htmlspecialchars($admin_profile_picture); ?>" data-lightbox="profile" data-title="Profile Photo">
                                        <img src="<?php echo '../admin/uploads/' . htmlspecialchars($admin_profile_picture); ?>" alt="Profile Photo">
                                    </a>
                                </div>
                                <div class="username-container">
                                    <h5 class="name"><?php echo htmlspecialchars($admin_name); ?></h5>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mobile-layout">
                            <div class="cover-photo-container">
                                <img src="<?php echo '../admin/uploads/' . htmlspecialchars($cover_photo); ?>" alt="">
                            </div>
                            <div class="profile-section">
                                <div class="profile-photo-container">
                                    <img src="<?php echo '../admin/uploads/' . htmlspecialchars($admin_profile_picture); ?>" alt="">
                                </div>
                                <div class="username-container">
                                    <h5 class="name"><?php echo htmlspecialchars($admin_name); ?></h5>

                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-7 col-lg-12 feed-container">
                            <?php
                            try {
                                $query = "
                                SELECT a.*, ad.first_name, ad.last_name,
                                    STRING_AGG(DISTINCT yl.year_level, ', ') AS year_levels,
                                    STRING_AGG(DISTINCT d.department_name, ', ') AS departments,
                                    STRING_AGG(DISTINCT c.course_name, ', ') AS courses
                                FROM announcement a
                                JOIN admin ad ON a.admin_id = ad.admin_id
                                LEFT JOIN announcement_year_level ayl ON a.announcement_id = ayl.announcement_id
                                LEFT JOIN year_level yl ON ayl.year_level_id = yl.year_level_id
                                LEFT JOIN announcement_department adp ON a.announcement_id = adp.announcement_id
                                LEFT JOIN department d ON adp.department_id = d.department_id
                                LEFT JOIN announcement_course ac ON a.announcement_id = ac.announcement_id
                                LEFT JOIN course c ON ac.course_id = c.course_id 
                                WHERE a.admin_id = :admin_id
                                GROUP BY a.announcement_id, ad.first_name, ad.last_name 
                                ORDER BY a.updated_at DESC";

                                $stmt = $pdo->prepare($query);
                                $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
                                $stmt->execute();

                                $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                if ($announcements) {
                                    foreach ($announcements as $row) {
                                        $announcement_id = $row['announcement_id'];
                                        $title = $row['title'];
                                        $description = $row['description'];
                                        $image = $row['image'];
                                        $announcement_admin_id = $row['admin_id'];
                                        $admin_first_name = $row['first_name'];
                                        $admin_last_name = $row['last_name'];
                                        $admin_name =  $admin_first_name . ' ' . $admin_last_name;
                                        $updated_at = date('F d, Y', strtotime($row['updated_at']));

                                        $year_levels = !empty($row['year_levels']) ? explode(',', $row['year_levels']) : [''];
                                        $departments = !empty($row['departments']) ? explode(',', $row['departments']) : [''];
                                        $courses = !empty($row['courses']) ? explode(',', $row['courses']) : [''];
                            ?>


                                        <div class="card mb-3">
                                            <div class="profile-container d-flex px-3 pt-3">
                                                <div class="profile-pic">
                                                    <img src="<?php echo '../admin/uploads/' . htmlspecialchars($admin_profile_picture); ?>" alt="Profile Picture" style="width: 30px;height: 30px;object-fit: cover;border-radius: 50%">
                                                </div>
                                                <p class="ms-1 mt-1"><?php echo htmlspecialchars($admin_name); ?></p>
                                                <?php if (isset($admin_id) && isset($announcement_admin_id) && (string)$admin_id === (string)$announcement_admin_id) : ?>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!empty($row['image'])): ?>
                                                <div class="image-container mx-3" style="position: relative; overflow: hidden;">
                                                    <div class="blur-background"></div>
                                                    <a href="../admin/uploads/<?php echo htmlspecialchars($row['image']); ?>" data-lightbox="image-<?php echo $row['announcement_id']; ?>" data-title="<?php echo htmlspecialchars($row['title']); ?>">
                                                        <img src="../admin/uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Post Image" class="img-fluid">
                                                        <script src="../admin/js/blur.js"></script>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo htmlspecialchars($title); ?></h5>
                                                <div class="card-text">
                                                    <p class="mb-2"><?php echo htmlspecialchars($description); ?></p>

                                                    Tags:
                                                    <?php

                                                    $all_tags = array_merge($year_levels, $departments, $courses);

                                                    foreach ($all_tags as $tag) : ?>
                                                        <span class="badge rounded-pill bg-danger mb-2"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                                    <?php endforeach; ?>
                                                </div>

                                                <small>Updated at <?php echo htmlspecialchars($updated_at); ?></small>
                                            </div>
                                        </div>

                            <?php
                                    }
                                } else {
                                    echo '<p class="text-center">No announcements found.</p>';
                                }
                            } catch (PDOException $e) {
                                echo "Error: " . $e->getMessage();
                            }
                            ?>

                        </div>
                        <div class="col-lg-5 info-card d-none d-xxl-block">
                            <div class="sticky-card m-0 w-100">
                                <div class="card card-info p-4">
                                    <div class="left-card">
                                        <div class="d-flex flex-column">
                                            <h6 class="text-center">My Bio:</h6>
                                            <p><?php echo htmlspecialchars($admin_bio); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <nav class="navbar nav-bottom fixed-bottom d-block d-lg-none mt-5">
            <div class="container-fluid d-flex justify-content-around">
                <a href="user.php" class="btn nav-bottom-btn">
                    <i class="fas fa-newspaper"></i>
                </a>

                <a href="features/logPage.php" class="btn nav-bottom-btn">
                    <i class="fas fa-clipboard-list"></i>
                </a>
            </div>
        </nav>

        <?php include 'features/changePassMainPage.html' ?>
    </main>

    <!-- Body CDN links -->
    <?php include '..//cdn/body.html'; ?>
    <script src="js/admin.js"></script>
    <script src="js/manage.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function confirmLogout() {
            if (confirm('Are you sure you want to sign out?')) {
                window.location.href = '../../login/logout.php';
            }
            return false;
        }
    </script>
</body>

</html>