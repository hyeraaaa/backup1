<?php
require_once '../../login/dbh.inc.php'; // DATABASE CONNECTION
session_start();
if (!isset($_SESSION['user'])) {
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
?>
<!doctype html>
<html lang="en">

<head>
    <title>Edit Announcement</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Include your head CDN links -->
    <?php include '../../cdn/head.html'; ?>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/create.css">
    <link rel="stylesheet" href="../css/tags-modal.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/nav-bottom.css">
</head>

<body>
    <header>
        <?php include '../../cdn/navbar.php'; ?>
    </header>

    <main>
        <div class="container-fluid pt-5">
            <div class="row g-4">
                <!-- Sidebar -->
                <?php include '../../cdn/sidebar.php'; ?>

                <!-- Main content -->
                <div class="col-lg-6 pt-5 px-5 main-content" style="margin: 0 auto;">

                    <?php
                    require_once '../../login/dbh.inc.php';
                    require 'log.php';

                    if (isset($_GET['id'])) {
                        $announcement_id = $_GET['id'];

                        $query = "
                        SELECT a.*, 
                            STRING_AGG(DISTINCT yl.year_level_id::TEXT, ',') AS selected_year_levels, 
                            STRING_AGG(DISTINCT d.department_id::TEXT, ',') AS selected_departments, 
                            STRING_AGG(DISTINCT c.course_id::TEXT, ',') AS selected_courses 
                        FROM announcement a
                        LEFT JOIN announcement_year_level ayl ON a.announcement_id = ayl.announcement_id
                        LEFT JOIN year_level yl ON ayl.year_level_id = yl.year_level_id
                        LEFT JOIN announcement_department adp ON a.announcement_id = adp.announcement_id
                        LEFT JOIN department d ON adp.department_id = d.department_id
                        LEFT JOIN announcement_course ac ON a.announcement_id = ac.announcement_id
                        LEFT JOIN course c ON ac.course_id = c.course_id
                        WHERE a.announcement_id = :id
                        GROUP BY a.announcement_id";
                        $stmt = $pdo->prepare($query);
                        $stmt->bindParam(':id', $announcement_id, PDO::PARAM_INT);
                        $stmt->execute();
                        $announcement = $stmt->fetch(PDO::FETCH_ASSOC);

                        // Fetch all year levels
                        $year_level_query = "SELECT * FROM year_level";
                        $year_level_stmt = $pdo->prepare($year_level_query);
                        $year_level_stmt->execute();
                        $year_levels = $year_level_stmt->fetchAll(PDO::FETCH_ASSOC);

                        // Fetch all departments
                        $department_query = "SELECT * FROM department";
                        $department_stmt = $pdo->prepare($department_query);
                        $department_stmt->execute();
                        $departments = $department_stmt->fetchAll(PDO::FETCH_ASSOC);

                        // Fetch all courses
                        $course_query = "SELECT * FROM course";
                        $course_stmt = $pdo->prepare($course_query);
                        $course_stmt->execute();
                        $courses = $course_stmt->fetchAll(PDO::FETCH_ASSOC);

                        if ($announcement) {
                            $title = $announcement['title'];
                            $description = $announcement['description'];
                            $image = $announcement['image'];

                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                $new_title = $_POST['title'];
                                $new_description = $_POST['description'];
                                $new_year_levels = $_POST['year_level'] ?? [];
                                $new_departments = $_POST['department'] ?? [];
                                $new_courses = $_POST['course'] ?? [];

                                // Update the announcement table
                                $update_query = "UPDATE announcement SET title = :title, description = :description, updated_at = NOW() WHERE announcement_id = :id";
                                $stmt = $pdo->prepare($update_query);
                                $stmt->bindParam(':title', $new_title);
                                $stmt->bindParam(':description', $new_description);
                                $stmt->bindParam(':id', $announcement_id);
                                $stmt->execute();
                                logAction($pdo, $admin_id, 'admin', 'update', 'announcement', $announcement_id, 'updated an announcement');

                                // Update the year levels
                                $delete_query = "DELETE FROM announcement_year_level WHERE announcement_id = :id";
                                $stmt = $pdo->prepare($delete_query);
                                $stmt->bindParam(':id', $announcement_id);
                                $stmt->execute();

                                foreach ($new_year_levels as $year_level_id) {
                                    $insert_query = "INSERT INTO announcement_year_level (announcement_id, year_level_id) VALUES (:announcement_id, :year_level_id)";
                                    $stmt = $pdo->prepare($insert_query);
                                    $stmt->bindParam(':announcement_id', $announcement_id);
                                    $stmt->bindParam(':year_level_id', $year_level_id);
                                    $stmt->execute();
                                    logAction($pdo, $admin_id, 'admin', 'update', 'announcement_year_level', $year_level_id, 'updated an announcement, modified year level');
                                }

                                // Update the department 
                                $delete_query = "DELETE FROM announcement_department WHERE announcement_id = :id";
                                $stmt = $pdo->prepare($delete_query);
                                $stmt->bindParam(':id', $announcement_id);
                                $stmt->execute();

                                foreach ($new_departments as $new_department_id) {
                                    $insert_query = "INSERT INTO announcement_department (announcement_id, department_id) VALUES (:announcement_id, :department_id)";
                                    $stmt = $pdo->prepare($insert_query);
                                    $stmt->bindParam(':announcement_id', $announcement_id);
                                    $stmt->bindParam(':department_id', $new_department_id);
                                    $stmt->execute();
                                    logAction($pdo, $admin_id, 'admin', 'update', 'announcement_department', $new_department_id, 'updated an announcement, modified department');
                                }

                                // Update the courses
                                $delete_query = "DELETE FROM announcement_course WHERE announcement_id = :id";
                                $stmt = $pdo->prepare($delete_query);
                                $stmt->bindParam(':id', $announcement_id);
                                $stmt->execute();

                                foreach ($new_courses as $new_courses_id) {
                                    $insert_query = "INSERT INTO announcement_course (announcement_id, course_id) VALUES (:announcement_id, :course_id)";
                                    $stmt = $pdo->prepare($insert_query);
                                    $stmt->bindParam(':announcement_id', $announcement_id);
                                    $stmt->bindParam(':course_id', $new_courses_id);
                                    $stmt->execute();
                                    logAction($pdo, $admin_id, 'admin', 'update', 'announcement_course', $new_courses_id, 'updated an announcement, modified courses');
                                }
                                // Handle image upload
                                if (isset($_POST['remove_image'])) {
                                    $new_image = null; // Set image to NULL if checkbox is checked
                                    logAction($pdo, $admin_id, 'admin', 'update', 'announcement', $announcement_id, 'removed the image from the announcement');
                                } elseif (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                                    $image_tmp_name = $_FILES['image']['tmp_name'];
                                    $image_name = $_FILES['image']['name'];
                                    $uploadDir = '../uploads/';

                                    // Get the file extension
                                    $ext = pathinfo($image_name, PATHINFO_EXTENSION);
                                    $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];

                                    if (in_array(strtolower($ext), $allowedExt)) {
                                        // Create a unique filename
                                        $filename = uniqid('', true) . '.' . $ext;
                                        $uploadFilePath = $uploadDir . $filename;

                                        // Move the file to the upload directory
                                        move_uploaded_file($image_tmp_name, $uploadFilePath);
                                        $new_image = $filename; // Set the new image filename
                                    }
                                    logAction($pdo, $admin_id, 'admin', 'update', 'announcement', $announcement_id, 'updated an announcement, modified image');
                                } else {
                                    $new_image = $image; // Keep the old image
                                }

                                // Update the announcement
                                $update_query = "UPDATE announcement SET title = :title, description = :description, image = :image, updated_at = NOW() WHERE announcement_id = :id";
                                $stmt = $pdo->prepare($update_query);
                                $stmt->bindParam(':title', $new_title);
                                $stmt->bindParam(':description', $new_description);
                                $stmt->bindParam(':image', $new_image);
                                $stmt->bindParam(':id', $announcement_id);

                                if ($stmt->execute()) {
                                    echo "<script>
                                            alert('Announcement was updated successfully!');
                                            window.location.href = '../admin.php';
                                          </script>";
                                } else {
                                    echo "<div class='alert alert-danger'>Error updating announcement.</div>";
                                }
                            }
                        } else {
                            echo "<div class='alert alert-danger'>Announcement not found.</div>";
                        }
                    } else {
                        echo "<div class='alert alert-danger'>No announcement ID provided.</div>";
                    }
                    ?>

                    <!-- Form to edit the announcement -->
                    <?php if ($announcement): ?>
                        <div class="form-container d-flex align-items-center" style="min-height: 85vh;">
                            <form class="card shadow p-3" action="" method="POST" enctype="multipart/form-data" data-action='update'>
                                <!-- Title input -->
                                <div class="form-floating mb-4">
                                    <input type="text" class="form-control form-control-lg border-0 border-bottom rounded-0"
                                        id="title" name="title" value="<?php echo htmlspecialchars($title); ?>"
                                        placeholder="Enter title">
                                    <label for="title" class="text-muted">Title</label>
                                </div>

                                <!-- Description textarea -->
                                <div class="form-floating mb-4">
                                    <textarea class="form-control border-0 border-bottom rounded-0"
                                        id="description" name="description"
                                        placeholder="Enter description"
                                        style="min-height: 100px;"><?php echo htmlspecialchars($description); ?></textarea>
                                    <label for="description" class="text-muted">Description</label>
                                </div>

                                <!-- Tags button -->
                                <div class="modal-container d-flex justify-content-between">
                                    <!-- Button to trigger Tags modal -->
                                    <button type="button" class="btn btn-danger rounded-pill px-3 mb-3" data-bs-toggle="modal" data-bs-target="#tagsModal">
                                        <i class="bi bi-tags me-2"></i>Tags
                                    </button>

                                    <?php include "edit_announcement_modal.php" ?>
                                </div>



                                <!-- Upload image container -->
                                <div class="form-group mb-4">
                                    <div class="upload-image-container d-flex flex-column align-items-center justify-content-center bg-light border rounded-3 p-4"
                                        style="min-height: 200px;">
                                        <div class="d-flex">
                                            <p id="upload-text" class="mt-3">Upload Photo</p>
                                            <input type="file" class="form-control-file" id="image" name="image"
                                                style="display: none;" onchange="imagePreview()">
                                            <button class="btn btn-light ms-2" id="file-upload-btn">
                                                <i class="bi bi-upload"></i>
                                            </button>
                                            <img class="img-fluid rounded-3" id="image-preview"
                                                src="../uploads/<?php echo htmlspecialchars($image); ?>"
                                                alt="Image Preview" style="display: block; max-width: 100%; position: relative; z-index: 1;">
                                        </div>
                                        <div class="blur-background" style="display: none;"></div>
                                        <i id="delete-icon" class="bi bi-trash"
                                            style="position: absolute; top: 10px; right: 10px; display: none; cursor: pointer;"
                                            onclick="deleteImage()"></i>
                                    </div>
                                </div>

                                <!-- Checkbox to remove image -->
                                <div class="form-check mb-4">
                                    <input type="checkbox" class="form-check-input" id="removeImage" name="remove_image">
                                    <label class="form-check-label" for="removeImage">Remove current image</label>
                                </div>

                                <!-- Submit button -->
                                <div class="button-container d-flex justify-content-end">
                                    <button type="submit" class="btn btn-danger px-4 py-2 rounded-pill" id="submitBtn" data-action="update">
                                        <i class="bi bi-arrow-clockwise me-2"></i>Update
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
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

                    <a href="create.php" class="btn nav-bottom-btn active-btn">
                        <i class="fas fa-bullhorn"></i>
                    </a>

                    <a href="logPage.php" class="btn nav-bottom-btn">
                        <i class="fas fa-clipboard-list"></i>
                    </a>

                    <a href="manage_student.php" class="btn nav-bottom-btn">
                        <i class="fas fa-users-cog"></i>
                    </a>

                    <a href="manage.php" class="btn nav-bottom-btn">
                        <i class="fas fa-user"></i>
                    </a>
                </div>
            </nav>
        </div>
        <script src="../js/create-post-validation.js"></script>
        <script src="../js/create.js"></script>
        <script src="../js/edit.js"></script>
    </main>

    <!-- Body CDN links -->
    <?php include '../../cdn/body.html'; ?>
</body>

</html>