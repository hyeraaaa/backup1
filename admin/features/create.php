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
?>

<!doctype html>
<html lang="en">

<head>
    <title>Create Announcement</title>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- head CDN links -->
    <?php include '../../cdn/head.html'; ?>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/create.css">
    <link rel="stylesheet" href="../css/tags-modal.css">
    <link rel="stylesheet" href="../css/sidebar.css">
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
                <?php include '../../cdn/sidebar.php'; ?>

                <!-- main content -->
                <div class="col-lg-6 pt-5 px-5 main-content" style="margin: 0 auto;">
                    <div class="form-container d-flex align-items-center" style="min-height: 85vh;">
                        <form class="card shadow p-3" action="upload.php" method="POST" enctype="multipart/form-data" data-action='post'>
                            <input type="text" id="admin_id" name="admin_id" value="<?php echo $admin_id; ?>" style="display: none;">

                            <!-- Title input -->
                            <div class="form-floating mb-4">
                                <input type="text" class="form-control form-control-lg border-0 border-bottom rounded-0"
                                    id="title" name="title" placeholder="Enter title">
                                <label for="title" class="text-muted">Title</label>
                            </div>

                            <!-- Description textarea -->
                            <div class="form-floating mb-4">
                                <textarea class="form-control border-0 border-bottom rounded-0"
                                    id="description" name="description"
                                    placeholder="Enter description"
                                    style="min-height: 100px;"></textarea>
                                <label for="description" class="text-muted">Description</label>
                            </div>

                            <!-- Tags button -->
                            <div class="modal-container d-flex justify-content-between">
                                <button type="button" class="btn btn-danger rounded-pill px-3 mb-3" data-bs-toggle="modal" data-bs-target="#tagsModal">
                                    <i class="bi bi-tags me-2"></i>Tags
                                </button>

                                <!-- Tags Modal -->
                                <?php include 'tags.php'; ?>
                            </div>

                            <!-- Upload image container -->
                            <div class="form-group mb-4">
                                <div class="upload-image-container d-flex flex-column align-items-center justify-content-center bg-light border rounded-3 p-4"
                                    ondrop="dropHandler(event)"
                                    ondragover="dragOverHandler(event)"
                                    ondragleave="dragLeaveHandler(event)">
                                    <div class="d-flex">
                                        <p id="upload-text" class="mt-3">Upload Photo</p>
                                        <input type="file" class="form-control-file" id="image" name="image" accept="image/*"
                                            style="display: none;" onchange="imagePreview()">
                                        <button class="btn btn-light ms-2" id="file-upload-btn">
                                            <i class="bi bi-upload"></i>
                                        </button>
                                        <img class="img-fluid rounded-3" id="image-preview" src="#"
                                            alt="Image Preview" style="display: none; max-width: 100%; position: relative; z-index: 1;">
                                    </div>
                                    <div class="blur-background" style="display: none;"></div>

                                    <i id="delete-icon" class="bi bi-trash"
                                        style="position: absolute; top: 10px; right: 10px; display: none; cursor: pointer;"
                                        onclick="deleteImage()"></i>
                                </div>
                            </div>

                            <!-- Notification section -->
                            <div class="notification-section mb-4">
                                <div class="form-check form-switch custom-switch">
                                    <input class="form-check-input" type="checkbox" id="sendSms" name="sendSms" value="1" role="switch">
                                    <label class="form-check-label ms-2" for="sendSms">
                                        Send SMS notifications
                                    </label>
                                </div>

                                <div id="smsInfo" style="display: none;" class="alert alert-info alert-dismissible fade show mt-2">
                                    <i class="bi bi-info-circle-fill me-2"></i>
                                    <span class="fw-medium">Estimated recipients:</span>
                                    <span id="recipientCount" class="badge bg-primary">0</span>
                                </div>
                            </div>


                            <!-- Submit button -->
                            <div class="button-container d-flex justify-content-end">
                                <button type="submit" class="btn btn-danger px-4 py-2 rounded-pill" id="submitBtn">
                                    <i class="bi bi-send-fill me-2"></i>Post
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>

        <nav class="navbar nav-bottom fixed-bottom d-block d-xxl-none mt-5">
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
    </main>
    <!-- Body CDN links -->

    <script src="../js/create-post-validation.js"></script>
    <?php include '../../cdn/body.html'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/create.js"></script>
</body>

</html>