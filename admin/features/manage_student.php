<?php
require_once '../../login/dbh.inc.php'; // DATABASE CONNECTION
require '../../login/vendor/autoload.php';
require 'functions.php';
require 'log.php';
require 'config.php';

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['student_id'])) {
    $student_id = $_POST['student_id'];
    $s_first_name = $_POST['firstName'];
    $s_last_name = $_POST['lastName'];
    $s_email = $_POST['email'];
    $s_contact_number = "+63" . $_POST['contactNumber'];

    // Check for duplicates excluding the current student
    $duplicateCheck = $pdo->prepare("SELECT * FROM student WHERE (email = :email OR contact_number = :contact_number) AND student_id != :student_id");
    $duplicateCheck->execute([
        ':email' => $s_email,
        ':contact_number' => $s_contact_number,
        ':student_id' => $student_id
    ]);

    if ($duplicateCheck->rowCount() > 0) {
        $duplicateData = $duplicateCheck->fetch(PDO::FETCH_ASSOC);
        if ($duplicateData['email'] === $s_email && $duplicateData['contact_number'] === $s_contact_number) {
            $message = "Both email and contact number are already in use by another student.";
        } else {
            $duplicateField = ($duplicateData['email'] === $s_email) ? "email" : "contact number";
            $message = "The " . $duplicateField . " is already in use by another student.";
        }
        echo "<script>
            alert('Update failed: " . $message . "');
            window.location.href = 'manage_student.php';
        </script>";
        exit;
    }

    $s_year_level_id = $pdo->prepare("SELECT year_level_id FROM year_level WHERE year_level = :ylevel");
    $s_year_level_id->execute([':ylevel' => $_POST['yearLevel']]);
    $s_year = (int)$s_year_level_id->fetchColumn();

    $s_dept_id = $pdo->prepare("SELECT department_id FROM department WHERE department_name = :dname");
    $s_dept_id->execute([':dname' => $_POST['department']]);
    $s_dept = (int)$s_dept_id->fetchColumn();

    $s_course_id = $pdo->prepare("SELECT course_id FROM course WHERE course_name = :cname");
    $s_course_id->execute([':cname' => $_POST['course']]);
    $s_course = (int)$s_course_id->fetchColumn();

    // Fetch current student data
    $currentStmt = $pdo->prepare("SELECT first_name, last_name, email, contact_number FROM student WHERE student_id = :student_id");
    $currentStmt->execute([':student_id' => $student_id]);
    $currentStudent = $currentStmt->fetch(PDO::FETCH_ASSOC);

    // Compare new values with current values
    $changes = 0;
    $changeDetails = [];

    if ($s_first_name !== $currentStudent['first_name']) {
        $changes++;
        $changeDetails[] = "First Name: From '{$currentStudent['first_name']}' to '{$s_first_name}'";
    }
    if ($s_last_name !== $currentStudent['last_name']) {
        $changes++;
        $changeDetails[] = "Last Name: From '{$currentStudent['last_name']}' to '{$s_last_name}'";
    }
    if ($s_email !== $currentStudent['email']) {
        $changes++;
        $changeDetails[] = "Email: From '{$currentStudent['email']}' to '{$s_email}'";
    }
    if ($s_contact_number !== $currentStudent['contact_number']) {
        $changes++;
        $changeDetails[] = "Contact Number: From '{$currentStudent['contact_number']}' to '{$s_contact_number}'";
    }

    // Update student information
    $updateStmt = $pdo->prepare("UPDATE student SET first_name = :first_name, last_name = :last_name, email = :email, contact_number = :contact_number, year_level_id = :year_level_id, department_id = :department_id, course_id = :course_id WHERE student_id = :student_id");
    $updateStmt->execute([
        ':first_name' => $s_first_name,
        ':last_name' => $s_last_name,
        ':email' => $s_email,
        ':contact_number' => $s_contact_number,
        ':year_level_id' => $s_year,
        ':department_id' => $s_dept,
        ':course_id' => $s_course,
        ':student_id' => $student_id
    ]);

    // Add logging for the update
    if ($changes >= 1) {
        $changeLog = implode(", ", $changeDetails);
        logAction(
            $pdo,
            $admin_id,
            'admin',
            'UPDATE',
            'student',
            $student_id,
            "Updated student information: $changeLog"
        );
    }

    // Send email if at least 2 fields were modified
    if ($changes >= 1) {
        $subject = "Your Student Information has been Updated";
        $body = "Dear $s_first_name $s_last_name,<br><br>";
        $body .= " Your student information has been updated. The following changes were made:<br><br>";
        $body .= implode("<br>", $changeDetails);
        $body .= "<br><br>Best regards,<br>Your School Administration";

        $result = sendEmail($currentStudent['email'], "$s_first_name $s_last_name", $subject, $body);

        if ($result !== true) {
            echo $result; // Output error message if sending failed
        }
    }

    echo "<script>alert('Student information updated successfully.');</script>";
    echo "<script>window.location.href = 'manage_student.php';</script>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_FILES['excelFile']) && $_FILES['excelFile']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['excelFile']['tmp_name'];
        $fileName = $_FILES['excelFile']['name'];
        $fileSize = $_FILES['excelFile']['size'];
        $fileType = $_FILES['excelFile']['type'];

        // Check file size (5MB limit)
        if ($fileSize > 5 * 1024 * 1024) {
            echo "<script>alert('File size exceeds 5MB limit.');</script>";
            echo "<script>window.location.href = 'manage_student.php';</script>";
            exit;
        }

        // Check file type
        $allowedTypes = [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        if (!in_array($fileType, $allowedTypes)) {
            echo "<script>alert('Invalid file type. Please upload an Excel file (.xls or .xlsx).');</script>";
            echo "<script>window.location.href = 'manage_student.php';</script>";
            exit;
        }

        try {
            $spreadsheet = IOFactory::load($fileTmpPath);
            $sheetData = $spreadsheet->getActiveSheet()->toArray();

            // Check if file is empty
            if (count($sheetData) <= 1) {
                echo "<script>alert('The Excel file is empty or contains only headers.');</script>";
                echo "<script>window.location.href = 'manage_student.php';</script>";
                exit;
            }

            // Check if number of rows exceeds limit (21 rows + 1 header row = 22)
            if (count($sheetData) > 21) {
                echo "<script>alert('Excel file contains more than 20 student records. Please limit the number of students to 20 per upload.');</script>";
                echo "<script>window.location.href = 'manage_student.php';</script>";
                exit;
            }

            $successfulRows = [];
            $failedRows = [];
            $errorDetails = [];

            // Skip header row if it exists
            $startRow = 1;

            for ($i = $startRow; $i < count($sheetData); $i++) {
                $row = $sheetData[$i];

                // Check if row is empty
                if (empty(array_filter($row))) {
                    continue;
                }

                // Validate required fields
                if (
                    empty($row[0]) || empty($row[1]) || empty($row[2]) || empty($row[3]) ||
                    empty($row[4]) || empty($row[5]) || empty($row[6])
                ) {
                    $failedRows[] = $i + 1;
                    $errorDetails[$i + 1] = "Missing required fields";
                    continue;
                }

                // Validate email format
                if (!filter_var($row[2], FILTER_VALIDATE_EMAIL)) {
                    $failedRows[] = $i + 1;
                    $errorDetails[$i + 1] = "Invalid email format";
                    continue;
                }

                // Validate phone number format (should start with 9 and be 10 digits total)
                if (!preg_match('/^9\d{9}$/', $row[3])) {
                    $failedRows[] = $i + 1;
                    $errorDetails[$i + 1] = "Invalid phone number format. Must start with 9 and be 10 digits total";
                    continue;
                }

                // Validate year level, department, and course
                $s_year_level_id = $pdo->prepare("SELECT year_level_id FROM year_level WHERE year_level = :ylevel");
                $s_year_level_id->execute([':ylevel' => $row[4]]);
                $s_year = (int)$s_year_level_id->fetchColumn();

                $s_dept_id = $pdo->prepare("SELECT department_id FROM department WHERE department_name = :dname");
                $s_dept_id->execute([':dname' => $row[5]]);
                $s_dept = (int)$s_dept_id->fetchColumn();

                $s_course_id = $pdo->prepare("SELECT course_id FROM course WHERE course_name = :cname");
                $s_course_id->execute([':cname' => $row[6]]);
                $s_course = (int)$s_course_id->fetchColumn();

                // Check if year level, department, or course are valid
                if (!$s_year || !$s_dept || !$s_course) {
                    $failedRows[] = $i + 1;
                    $errorDetails[$i + 1] = "Incorrect year level, department, or course";
                    continue;
                }

                $s_first_name = $row[0];
                $s_last_name = $row[1];
                $s_email = $row[2];
                $s_contact_number = $row[3];

                $duplicateCheck = $pdo->prepare("SELECT * FROM student WHERE email = :email OR contact_number = :contact_number");
                $duplicateCheck->execute([':email' => $s_email, ':contact_number' => $s_contact_number]);
                if ($duplicateCheck->rowCount() > 0) {
                    $failedRows[] = $i + 1; // Row number
                    $errorDetails[$i + 1] = "Duplicate student detected";
                    continue; // Skip duplicate
                }

                // Attempt to add the student
                try {
                    addNewStudent($s_first_name, $s_last_name, $s_email, $s_contact_number, $s_year, $s_dept, $s_course);
                    $successfulRows[] = $i + 1;
                    // Add logging for each successful addition
                    logAction(
                        $pdo,
                        $admin_id,
                        'admin',
                        'ADD',
                        'student',
                        null,
                        "Added student via Excel import: $s_first_name $s_last_name"
                    );
                } catch (Exception $e) {
                    $failedRows[] = $i + 1; // Row number
                    $errorDetails[$i + 1] = "Error adding student: " . addslashes($e->getMessage());
                }
            }

            // Prepare detailed error message
            $errorMessage = "Upload complete.\n\n";
            $errorMessage .= "Successful rows: " . implode(", ", $successfulRows) . "\n";
            $errorMessage .= "Failed rows: " . implode(", ", $failedRows) . "\n\n";

            if (!empty($errorDetails)) {
                $errorMessage .= "Error details:\n";
                foreach ($errorDetails as $row => $error) {
                    $errorMessage .= "Row $row: $error\n";
                }
            }

            echo "<script>alert(`$errorMessage`);</script>";
            echo "<script>window.location.href = 'manage_student.php';</script>";
            exit;
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            echo "<script>alert('Error reading Excel file: " . addslashes($e->getMessage()) . "');</script>";
            echo "<script>window.location.href = 'manage_student.php';</script>";
            exit;
        } catch (Exception $e) {
            echo "<script>alert('An unexpected error occurred: " . addslashes($e->getMessage()) . "');</script>";
            echo "<script>window.location.href = 'manage_student.php';</script>";
            exit;
        }
    }

    $s_first_name = $_POST['firstName'];
    $s_last_name = $_POST['lastName'];
    $s_email = $_POST['email'];
    $s_contact_number = $_POST['contactNumber'];

    $s_year_level_id = $pdo->prepare("SELECT year_level_id FROM year_level WHERE year_level = :ylevel");
    $s_year_level_id->execute([':ylevel' => $_POST['yearLevel']]);
    $s_year = (int)$s_year_level_id->fetchColumn();

    $s_dept_id = $pdo->prepare("SELECT department_id FROM department WHERE department_name = :dname");
    $s_dept_id->execute([':dname' => $_POST['department']]);
    $s_dept = (int)$s_dept_id->fetchColumn();

    $s_course_id = $pdo->prepare("SELECT course_id FROM course WHERE course_name = :cname");
    $s_course_id->execute([':cname' => $_POST['course']]);
    $s_course = (int)$s_course_id->fetchColumn();

    // Duplicate check for individual entry
    $stmt = $pdo->prepare("SELECT * FROM student WHERE email = :email OR contact_number = :contact_number");
    $stmt->execute([':email' => $s_email, ':contact_number' => $s_contact_number]);
    if ($stmt->rowCount() > 0) {
        $duplicateData = $stmt->fetch(PDO::FETCH_ASSOC);
        $duplicateField = ($duplicateData['email'] === $s_email) ? "email" : "contact number";
        echo "<script>alert('Error: This email address is already registered.');
            window.location.href = 'manage_student.php';</script>";
    } else {
        if ($s_year && $s_dept && $s_course) {
            addNewStudent($s_first_name, $s_last_name, $s_email, $s_contact_number, $s_year, $s_dept, $s_course);
            // Add logging
            logAction(
                $pdo,
                $admin_id,
                'admin',
                'ADD',
                'student',
                null,
                "Added new student: $s_first_name $s_last_name"
            );
            echo "<script>alert('Student added successfully.');</script>";
            echo "<script>window.location.href = 'manage_student.php';</script>";
        } else {
            echo "<script>alert('Error: One or more of the selected values are invalid.');</script>";
            echo "<script>window.location.href = 'manage_student.php';</script>";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <title>ISMS Portal</title>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- head CDN links -->
    <?php include '../../cdn/head.html'; ?>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/modals.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/tables.css">
    <link rel="stylesheet" href="../css/nav-bottom.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="icon" href="../img/brand.png" type="image/x-icon">
</head>

<body>
    <header>
        <?php include '../../cdn/navbar.php' ?>
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
                                <a href="manage.php"><i class="fas fa-user me-2"></i>My Profile</a>
                            </li>

                            <li class="nav-item">
                                <a href="create.php"><i class="fas fa-bullhorn me-2"></i>Create Announcement</a>
                            </li>

                            <li class="nav-item">
                                <a href="logPage.php"><i class="fas fa-clipboard-list me-2"></i>Logs</a>
                            </li>

                            <li class="nav-item">
                                <a class="active" href="manage_student.php"><i class="fas fa-users-cog me-2"></i>Manage Accounts</a>
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
                <div class="col-12 col-xxl-9 col-lg-8 main-content pt-5 px-5" style="margin: 0 auto;">
                    <div class="d-flex justify-content-between align-items-center mb-4 pt-5">
                        <div class="d-flex gap-2">
                            <button class="btn btn-danger" id="addNewStudent" data-bs-toggle="modal" data-bs-target="#studentModal">
                                <i class="bi bi-person-plus-fill me-2"></i>Add New Student
                            </button>

                            <!-- Upload Excel Form -->
                            <form id="uploadExcelForm" method="POST" action="" enctype="multipart/form-data" class="d-flex gap-3 align-items-center">
                                <input type="hidden" name="uploadExcel" value="1">
                                <div class="input-group">
                                    <input type="file" class="form-control" id="excelFile" name="excelFile" accept=".xlsx, .xls" required>
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-file-earmark-excel-fill me-2"></i>Upload
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                    <div class="card shadow">
                        <div class="card-body">

                            <?php
                            require_once '../../login/dbh.inc.php';

                            try {
                                $query = "SELECT s.*, yl.year_level, d.department_name, c.course_name 
                        FROM student s
                        JOIN year_level yl ON s.year_level_id = yl.year_level_id
                        JOIN department d ON d.department_id = s.department_id
                        JOIN course c ON c.course_id = s.course_id
                        ORDER BY last_name ASC";

                                $stmt = $pdo->prepare($query);
                                $stmt->execute();
                                $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                                <?php if (count($students) > 0): ?>
                                    <div class="table-responsive student-table">
                                        <table class="table table-bordered table-hover display nowrap">
                                            <thead>
                                                <tr class="bg-primary text-white">
                                                    <th class="align-middle">Student Number</th>
                                                    <th class="align-middle">Full Name</th>
                                                    <th class="align-middle">Email</th>
                                                    <th class="align-middle">Contact Number</th>
                                                    <th class="align-middle">Year Level</th>
                                                    <th class="align-middle">Department</th>
                                                    <th class="align-middle">Course</th>
                                                    <th class="align-middle text-center" style="width: 100px;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($students as $row):
                                                    $student_id = $row['student_id'];
                                                    $fname = $row['first_name'];
                                                    $lname = $row['last_name'];
                                                    $email = $row['email'];
                                                    $contact = $row['contact_number'];
                                                    $year_level = $row['year_level'];
                                                    $department = $row['department_name'];
                                                    $course = $row['course_name'];
                                                    $student_name = $fname . ' ' . $lname;
                                                ?>
                                                    <tr>
                                                        <td class="align-middle"><?= $student_id ?></td>
                                                        <td class="align-middle fw-semibold"><?= $student_name ?></td>
                                                        <td class="align-middle"><?= $email ?></td>
                                                        <td class="align-middle"><?= $contact ?></td>
                                                        <td class="align-middle">
                                                            <span class="badge bg-info text-dark p-2"><?= $year_level ?></span>
                                                        </td>
                                                        <td class="align-middle">
                                                            <span class="badge bg-secondary p-2"><?= $department ?></span>
                                                        </td>
                                                        <td class="align-middle">
                                                            <span class="badge bg-primary p-2"><?= $course ?></span>
                                                        </td>
                                                        <td class="align-middle text-center">
                                                            <div class="btn-group">
                                                                <button type="button" class="btn btn-sm btn-outline-primary edit-student"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#editStudentModal"
                                                                    data-student-id="<?= $student_id ?>"
                                                                    data-first-name="<?= $fname ?>"
                                                                    data-last-name="<?= $lname ?>"
                                                                    data-email="<?= $email ?>"
                                                                    data-contact="<?= $contact ?>"
                                                                    data-year-level="<?= $year_level ?>"
                                                                    data-department="<?= $department ?>"
                                                                    data-course="<?= $course ?>">
                                                                    <i class="bi bi-pencil-square"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#deleteStudent"
                                                                    data-student-id="<?= $student_id ?>">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i>No students found.
                                    </div>
                                <?php endif; ?>

                            <?php
                            } catch (PDOException $e) {
                                echo '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>Error: ' . $e->getMessage() . '</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <?php include 'modals.php' ?>

                <script>
                    $(document).ready(function() {
                        $('#addStudentForm').on('submit', function(e) {
                            var contactNumber = $('#contactNumber').val();
                            // Regular expression for validating PH mobile numbers starting with 09 or +639
                            var regex = /^9\d{9}$/;

                            if (!regex.test(contactNumber)) {
                                e.preventDefault(); // Prevent form submission
                                $('#errorMsg').show().text('Invalid contact number. Enter exactly 10 digits after +63.');
                            } else {
                                $('#errorMsg').hide(); // Hide error if valid
                            }
                        });
                    });
                </script>


                <!-- Delete Post Modal -->
                <div class="modal fade" id="deleteStudent" tabindex="-1" aria-labelledby="deleteStudent" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-sm">
                        <div class="modal-content custom" style="border-radius: 15px;">
                            <div class="modal-header pb-1" style="border: none">
                                <h1 class="modal-title fs-5" id="exampleModalLabel">Delete Student Data?</h1>
                                <button type="button" class="btn-close delete-modal-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body py-0" style="border: none;">
                                <p style="font-size: 15px;">Once you proceed, this can't be restored.</p>
                            </div>
                            <div class="modal-footer pt-0" style="border: none;">
                                <button type="button" class="btn go-back-btn" data-bs-dismiss="modal">Go Back</button>
                                <button type="button" class="btn delete-btn" id="confirm-delete-student-btn">Confirm Delete</button>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- after deletion modal -->
                <div class="modal fade" id="studentDelete" tabindex="-1" aria-labelledby="student-deleted" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-sm">
                        <div class="modal-content text-center">
                            <div class="modal-body p-4">
                                <div class="mb-2">
                                    <i class="bi bi-check-circle-fill text-success" style="font-size: 2rem;"></i>
                                </div>
                                <h6 class="modal-title mb-1" id="student-deleted">Success</h6>
                                <p class="small mb-3">Student record was deleted successfully.</p>
                                <button type="button" class="btn btn-success btn-sm w-100" data-bs-dismiss="modal">OK</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- offcanvas  -->

                <script src="../js/admin.js"></script>
                <script>
                    $(document).on('click', '.edit-student', function() {
                        $('#editStudentId').val($(this).data('student-id'));
                        $('#editFirstName').val($(this).data('first-name'));
                        $('#editLastName').val($(this).data('last-name'));
                        $('#editEmail').val($(this).data('email'));
                        $('#editContactNumber').val($(this).data('contact').replace(/^\+63/, ''));
                        $('#editYearLevel').val($(this).data('year-level'));
                        $('#editDepartment').val($(this).data('department'));
                        $('#editCourse').val($(this).data('course'));
                    });
                </script>
                <?php include 'changePassOtherPage.html'; ?>

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

                        <a href="manage_student.php" class="btn nav-bottom-btn active-btn">
                            <i class="fas fa-users-cog"></i>
                        </a>

                        <a href="manage.php" class="btn nav-bottom-btn">
                            <i class="fas fa-user"></i>
                        </a>
                    </div>
                </nav>
    </main>
    <!-- Body CDN links -->
    <?php include '../../cdn/body.html'; ?>

    <script>
        $(document).ready(function() {
            $('.table').DataTable({
                responsive: false,
                order: [
                    [1, 'asc']
                ], // Sort by Full Name column by default
                pageLength: 15, // Show 15 entries per page
                lengthChange: false, // Remove "Show entries" dropdown
                language: {
                    search: "Search students:",
                    info: "Showing _START_ to _END_ of _TOTAL_ students",
                    infoEmpty: "Showing 0 to 0 of 0 students",
                    infoFiltered: "(filtered from _MAX_ total students)",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                },
                columnDefs: [{
                        orderable: false,
                        targets: [7] // Disable sorting for Action column
                    },
                    {
                        className: "text-nowrap",
                        targets: [0, 1, 2, 3, 4, 5, 6] // Prevent text wrapping in other columns
                    }
                ],
                dom: '<"top"f>rt<"bottom"ip><"clear">', // Removed 'l' for length menu
                initComplete: function() {
                    // Add custom styling to search
                    $('.dataTables_filter input').addClass('form-control form-control-sm');
                }
            });
        });

        function confirmLogout() {
            if (confirm('Are you sure you want to sign out?')) {
                window.location.href = '../../login/logout.php';
            }
            return false;
        }
    </script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
</body>

</html>