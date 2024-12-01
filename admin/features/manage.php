<?php
require_once '../../login/dbh.inc.php';
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user_type'] !== 'admin') {
    $_SESSION = [];
    session_destroy();
    header("Location: ../../login/login.php");
    exit();
}

//Get info from admin session
$user = $_SESSION['user'];
$admin_id = $_SESSION['user']['admin_id'];
$first_name = $_SESSION['user']['first_name'];
$last_name = $_SESSION['user']['last_name'];
$email = $_SESSION['user']['email'];
$contact_number = $_SESSION['user']['contact_number'];
$department_id = $_SESSION['user']['department_id'];
$bio = $_SESSION['user']['bio'];

// Fetch admin details including photos
$query = "SELECT cover_photo, profile_picture FROM admin WHERE admin_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$admin_id]);
$adminPhotos = $stmt->fetch(PDO::FETCH_ASSOC);

$cover_photo = $adminPhotos['cover_photo'] ?? 'default_cover.jpg';

// Add this block to handle the bio update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bio'])) {
    try {
        $bio = $_POST['bio'];
        $query = "UPDATE admin SET bio = :bio WHERE admin_id = :admin_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':bio', $bio);
        $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
        $stmt->execute();
        echo "<script>alert('Bio updated successfully!');</script>";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

?>

<!doctype html>
<html lang="en">

<head>
    <title>Manage Posts</title>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- head CDN links -->
    <?php include '../../cdn/head.html'; ?>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/modals.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/feeds-card.css">
    <link rel="stylesheet" href="../css/bsu-bg.css">
    <link rel="stylesheet" href="../css/cover-photo.css">
    <link rel="stylesheet" href="../css/nav-bottom.css">
    <link rel="icon" href="../img/brand.png" type="image/x-icon">
</head>

<body>
    <header>
        <?php include '../../cdn/navbar.php'; ?>
    </header>
    <main>
        <div class="container-fluid pt-5">
            <div class="row g-4">
                <!-- left sidebar -->
                <div class="col-lg-3 sidebar sidebar-left d-none d-lg-block">
                    <div class="sticky-sidebar">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a href="dashboard.php"><i class="fas fa-chart-line me-2"></i>Dashboard</a>
                            </li>

                            <li class="nav-item">
                                <a href="../admin.php"><i class="fas fa-newspaper me-2"></i>Feed</a>
                            </li>

                            <li class="nav-item">
                                <a class="active" href="manage.php"><i class="fas fa-user me-2"></i>My Profile</a>
                            </li>

                            <li class="nav-item">
                                <a href="create.php"><i class="fas fa-bullhorn me-2"></i>Create Announcement</a>
                            </li>

                            <li class="nav-item">
                                <a href="logPage.php"><i class="fas fa-clipboard-list me-2"></i>Logs</a>
                            </li>

                            <li class="nav-item">
                                <a href="manage_student.php"><i class="fas fa-users-cog me-2"></i>Manage Accounts</a>
                            </li>

                            <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'superadmin'): ?>
                                <li class="nav-item">
                                    <a href="manage_admin.php"><i class="fas fa-user-shield me-2"></i>Manage Admins</a>
                                </li>
                                <li class="nav-item">
                                    <a href="feedbackPage.php">
                                        <i class="fas fa-comments me-2"></i>
                                        <span class="menu-text">Feedback</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <!-- main content -->
                <div class="col-12 col-xxl-9 col-lg-8 main-content pt-4 px-4">
                    <div class="row g-0">
                        <?php
                        try {
                            // Query to fetch admin data
                            $query = "SELECT first_name, last_name FROM admin WHERE admin_id = :admin_id";

                            // Prepare and execute the query
                            $stmt = $pdo->prepare($query);
                            $stmt->bindParam(':admin_id', $_SESSION['user']['admin_id'], PDO::PARAM_INT);
                            $stmt->execute();

                            // Fetch the admin data
                            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

                            // Display the admin name if found
                            if ($admin) {
                                $admin_name = $admin['first_name'] . " " . $admin['last_name'];
                            } else {
                                echo "<p>Admin not found.</p>";
                            }
                        } catch (PDOException $e) {
                            // Handle database errors
                            echo "Error: " . $e->getMessage();
                        }
                        ?>


                        <!-- Desktop Layout -->
                        <div class="col-12 col-xxl-12 cover desktop-layout">
                            <div class="cover-photo-container" style="position: relative;">
                                <a href="<?php echo '../uploads/' . htmlspecialchars($cover_photo); ?>" data-lightbox="cover" data-title="Cover Photo">
                                    <img src="<?php echo '../uploads/' . htmlspecialchars($cover_photo); ?>" alt="Cover Photo">
                                </a>
                                <!-- Button to change cover photo -->
                                <form action="upload_photo.php" method="post" enctype="multipart/form-data" style="position: absolute; bottom: 10px; right: 10px;">
                                    <input type="file" name="coverPhoto" id="coverPhotoInput" style="display: none;" onchange="this.form.submit()">
                                    <button type="button" class="edit-btn btn btn-light" onclick="document.getElementById('coverPhotoInput').click()">Edit Cover Photo</button>
                                </form>
                            </div>
                            <div class="profile-section">
                                <div class="profile-photo-container" style="position: relative;">
                                    <a href="<?php echo '../uploads/' . htmlspecialchars($profile_picture); ?>" data-lightbox="profile" data-title="Profile Photo">
                                        <img src="<?php echo '../uploads/' . htmlspecialchars($profile_picture); ?>" alt="Profile Photo">
                                    </a>
                                </div>
                                <div class="username-container">
                                    <h5 class="name"><?php echo htmlspecialchars($admin_name); ?></h5>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Layout -->
                        <div class="col-12 mobile-layout">
                            <div class="cover-photo-container">
                                <img src="<?php echo '../uploads/' . htmlspecialchars($cover_photo); ?>" alt="">
                            </div>
                            <div class="profile-section">
                                <div class="profile-photo-container">
                                    <img src="<?php echo '../uploads/' . htmlspecialchars($profile_picture); ?>" alt="">
                                </div>
                                <div class="username-container">
                                    <h5 class="name"><?php echo htmlspecialchars($admin_name); ?></h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-7 col-lg-12 feed-container">
                            <?php
                            require_once '../../login/dbh.inc.php';

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
                                            <div class="profile-container-2 d-flex px-3 pt-3">
                                                <div class="profile-pic">
                                                    <img src="<?php echo '../uploads/' . htmlspecialchars($profile_picture); ?>" alt="Profile Picture" style="width: 30px;height: 30px; object-fit: cover;border-radius: 50%">
                                                </div>
                                                <p class="ms-1 mt-1"><?php echo htmlspecialchars($admin_name); ?></p>
                                                <?php if (isset($admin_id) && isset($announcement_admin_id) && (string)$admin_id === (string)$announcement_admin_id) : ?>
                                                    <div class="dropdown ms-auto">
                                                        <span id="dropdownMenuButton<?php echo $announcement_id; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="bi bi-three-dots"></i>
                                                        </span>
                                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton<?php echo $announcement_id; ?>">
                                                            <li><a class="dropdown-item" href="edit_announcement.php?id=<?php echo $announcement_id; ?>">Edit</a></li>
                                                            <li>
                                                                <a class="dropdown-item text-danger" href="#"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#deletePost"
                                                                    data-announcement-id="<?php echo $announcement_id; ?>">Delete</a>
                                                            </li>

                                                        </ul>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <?php if (!empty($row['image'])): ?>
                                                <div class="image-container mx-3" style="position: relative; overflow: hidden;">
                                                    <div class="blur-background"></div>
                                                    <a href="../uploads/<?php echo htmlspecialchars($row['image']); ?>" data-lightbox="image-<?php echo $row['announcement_id']; ?>" data-title="<?php echo htmlspecialchars($row['title']); ?>">
                                                        <img src="../uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Post Image" class="img-fluid">
                                                    </a>
                                                </div>
                                            <?php endif; ?>

                                            <script src="../js/blur.js"></script>

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
                                            <div class="bio-display-container">
                                                <h6>My Bio:</h6>
                                                <p><?php echo htmlspecialchars($bio); ?></p>
                                            </div>
                                            <div class="bio-form-container d-none">
                                                <form action="" method="post">
                                                    <textarea id="bio" name="bio" class="form-control text-center" rows="4" placeholder="Describe who you are"><?php echo htmlspecialchars($bio); ?></textarea>
                                                    <div class="button-container d-flex justify-content-end mt-2">
                                                        <div class="btn btn-danger me-2" id="cancelBio">Cancel</div>
                                                        <button type="submit" class="btn btn-danger">Save</button>
                                                    </div>
                                                </form>
                                            </div>

                                            <button class="btn btn-danger" id="addBio">Edit Bio</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.getElementById('addBio').addEventListener('click', function() {
                document.querySelector('.bio-form-container').classList.remove('d-none');
                this.style.display = 'none'; // Hide the "Add bio" button
            });

            document.getElementById('cancelBio').addEventListener('click', function() {
                document.querySelector('.bio-form-container').classList.add('d-none');
                document.getElementById('addBio').style.display = 'block'; // Show the "Add bio" button
            });
        </script>

        <!-- Delete modal -->
        <div class="modal fade" id="deletePost" tabindex="-1" aria-labelledby="deletePost" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content custom" style="border-radius: 15px;">
                    <div class="modal-header pb-1" style="border: none">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Delete Post?</h1>
                        <button type="button" class="btn-close delete-modal-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-0" style="border: none;">
                        <p style="font-size: 15px;">Once you delete this post, it can't be restored.</p>
                    </div>
                    <div class="modal-footer pt-0" style="border: none;">
                        <button type="button" class="btn go-back-btn" data-bs-dismiss="modal">Go Back</button>
                        <button type="button" class="btn delete-btn" id="confirm-delete-btn">Yes, Delete</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- after deletion modal -->
        <div class="modal fade" id="postDelete" tabindex="-1" aria-labelledby="deleteConfirmationModal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content text-center">
                    <div class="modal-body p-4">
                        <div class="mb-2">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 2rem;"></i>
                        </div>
                        <h6 class="modal-title mb-1" id="deleteConfirmationModal">Success</h6>
                        <p class="small mb-3">The announcement has been successfully deleted.</p>
                        <button type="button" class="btn btn-success btn-sm w-100" data-bs-dismiss="modal">OK</button>
                    </div>
                </div>
            </div>
        </div>

        <nav class="navbar nav-bottom fixed-bottom d-block d-lg-none mt-5">
            <div class="container-fluid d-flex justify-content-around">
                <a href="dashboard.php" class="btn nav-bottom-btn">
                    <i class="fas fa-chart-line"></i>
                </a>

                <a href="../admin.php" class="btn nav-bottom-btn">
                    <i class="fas fa-newspaper"></i>
                </a>

                <a href="create.php" class="btn nav-bottom-btn">
                    <i class="fas fa-bullhorn"></i>
                </a>

                <a href="logPage.php" class="btn nav-bottom-btn">
                    <i class="fas fa-clipboard-list"></i>
                </a>

                <a href="manage_student.php" class="btn nav-bottom-btn">
                    <i class="fas fa-users-cog"></i>
                </a>

                <a href="manage.php" class="btn nav-bottom-btn active-btn">
                    <i class="fas fa-user"></i>
                </a>
            </div>
        </nav>

        <?php include 'changePassOtherPage.html'; ?>
    </main>
    <!-- Body CDN links -->
    <?php include '../../cdn/body.html'; ?>
    <script src="../js/admin.js"></script>
    <script src="../js/manage.js"></script>
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