<?php
require_once '../../login/dbh.inc.php';
require 'log.php';
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../login/login.php");
    exit();
} else if ($_SESSION['user']['role'] !== 'superadmin') {
    echo '<script>
        alert("You are not a superadmin. Logging out in 3 seconds...");
        let count = 3;
        const countdown = setInterval(() => {
            count--;
            if (count === 0) {
                clearInterval(countdown);
                window.location.href = "../../login/logout.php";
            }
        }, 1000);
    </script>';
    exit();
}

// Get info from admin session
$user = $_SESSION['user'];
$admin_id = $_SESSION['user']['admin_id'];
$first_name = $_SESSION['user']['first_name'];
$last_name = $_SESSION['user']['last_name'];
$email = $_SESSION['user']['email'];
$contact_number = $_SESSION['user']['contact_number'];
$department_id = $_SESSION['user']['department_id'];
$profile_picture = $_SESSION['user']['profile_picture'];

// Handle admin updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['admin_id'])) {
    $target_admin_id = $_POST['admin_id'];

    // Prevent role modification for currently logged-in superadmin
    if ($target_admin_id == $_SESSION['user']['admin_id'] && $_SESSION['user']['role'] === 'superadmin') {
        $a_role = 'superadmin'; // Force role to remain as superadmin
    } else {
        $a_role = $_POST['role'];
    }

    $a_first_name = $_POST['firstName'];
    $a_last_name = $_POST['lastName'];
    $a_email = $_POST['email'];
    $a_contact_number = "+63" . $_POST['contactNumber'];
    $a_department = $_POST['department'];

    // Check for duplicates in both admin and student tables
    $duplicateCheck = $pdo->prepare("
        SELECT 'admin' as source, email, contact_number 
        FROM admin 
        WHERE (email = :email OR contact_number = :contact_number) 
        AND admin_id != :admin_id
        UNION ALL
        SELECT 'student' as source, email, contact_number 
        FROM student 
        WHERE email = :email OR contact_number = :contact_number
    ");

    $duplicateCheck->execute([
        ':email' => $a_email,
        ':contact_number' => $a_contact_number,
        ':admin_id' => $target_admin_id
    ]);

    if ($duplicateCheck->rowCount() > 0) {
        $duplicateData = $duplicateCheck->fetch(PDO::FETCH_ASSOC);
        $userType = $duplicateData['source'];

        if ($duplicateData['email'] === $a_email && $duplicateData['contact_number'] === $a_contact_number) {
            $message = "Both email and contact number are already in use by another " . $userType . ".";
        } else {
            $duplicateField = ($duplicateData['email'] === $a_email) ? "email" : "contact number";
            $message = "The " . $duplicateField . " is already in use by another " . $userType . ".";
        }
        echo "<script>
            alert('Update failed: " . $message . "');
            window.location.href = 'manage_admin.php';
        </script>";
        exit;
    }

    // Get department ID
    $dept_stmt = $pdo->prepare("SELECT department_id FROM department WHERE department_name = :dname");
    $dept_stmt->execute([':dname' => $a_department]);
    $dept_id = $dept_stmt->fetchColumn();

    // Fetch current admin data for comparison
    $currentStmt = $pdo->prepare("SELECT first_name, last_name, email, contact_number, department_id, role FROM admin WHERE admin_id = :admin_id");
    $currentStmt->execute([':admin_id' => $target_admin_id]);
    $currentAdmin = $currentStmt->fetch(PDO::FETCH_ASSOC);

    // Compare and log changes
    $changes = 0;
    $changeDetails = [];

    if ($a_first_name !== $currentAdmin['first_name']) {
        $changes++;
        $changeDetails[] = "First Name: From '{$currentAdmin['first_name']}' to '{$a_first_name}'";
    }
    if ($a_last_name !== $currentAdmin['last_name']) {
        $changes++;
        $changeDetails[] = "Last Name: From '{$currentAdmin['last_name']}' to '{$a_last_name}'";
    }
    if ($a_email !== $currentAdmin['email']) {
        $changes++;
        $changeDetails[] = "Email: From '{$currentAdmin['email']}' to '{$a_email}'";
    }
    if ($a_contact_number !== $currentAdmin['contact_number']) {
        $changes++;
        $changeDetails[] = "Contact Number: From '{$currentAdmin['contact_number']}' to '{$a_contact_number}'";
    }
    if ($dept_id != $currentAdmin['department_id']) {
        $changes++;
        $changeDetails[] = "Department updated";
    }
    if ($a_role !== $currentAdmin['role']) {
        $changes++;
        $changeDetails[] = "Role: From '{$currentAdmin['role']}' to '{$a_role}'";
    }

    // Update admin information
    $updateStmt = $pdo->prepare("UPDATE admin SET 
        first_name = :first_name, 
        last_name = :last_name, 
        email = :email, 
        contact_number = :contact_number, 
        department_id = :department_id,
        role = :role 
        WHERE admin_id = :admin_id");

    $updateStmt->execute([
        ':first_name' => $a_first_name,
        ':last_name' => $a_last_name,
        ':email' => $a_email,
        ':contact_number' => $a_contact_number,
        ':department_id' => $dept_id,
        ':role' => $a_role,
        ':admin_id' => $target_admin_id
    ]);

    // Log the changes
    if ($changes > 0) {
        $changeLog = implode(", ", $changeDetails);
        logAction(
            $pdo,
            $admin_id,
            'admin',
            'UPDATE',
            'admin',
            $target_admin_id,
            "Updated admin information: $changeLog"
        );
    }

    header("Location: manage_admin.php?error=Admin information updated successfully");
    exit();
}

// Handle new admin creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['admin_id'])) {
    $a_first_name = $_POST['firstName'];
    $a_last_name = $_POST['lastName'];
    $a_email = $_POST['email'];
    $a_contact_number = "+63" . $_POST['contactNumber'];
    $a_role = $_POST['role'];

    // Set default profile and cover photos
    $default_profile = 'default_profile.jpg';
    $default_cover = 'default_cover.jpg';

    // Check for duplicates in both admin and student tables
    $duplicateCheck = $pdo->prepare("
        SELECT 'admin' as source, email, contact_number 
        FROM admin 
        WHERE email = :email OR contact_number = :contact_number
        UNION ALL
        SELECT 'student' as source, email, contact_number 
        FROM student 
        WHERE email = :email OR contact_number = :contact_number
    ");

    $duplicateCheck->execute([
        ':email' => $a_email,
        ':contact_number' => $a_contact_number
    ]);

    if ($duplicateCheck->rowCount() > 0) {
        $duplicateData = $duplicateCheck->fetch(PDO::FETCH_ASSOC);
        $userType = $duplicateData['source'];

        if ($duplicateData['email'] === $a_email && $duplicateData['contact_number'] === $a_contact_number) {
            $message = "Both email and contact number are already in use by another " . $userType . ".";
        } else {
            $duplicateField = ($duplicateData['email'] === $a_email) ? "email" : "contact number";
            $message = "The " . $duplicateField . " is already in use by another " . $userType . ".";
        }
        echo "<script>alert('Registration failed: " . $message . "');
            window.location.href = 'manage_admin.php';</script>";
        exit;
    }

    // Get department ID
    $dept_stmt = $pdo->prepare("SELECT department_id FROM department WHERE department_name = :dname");
    $dept_stmt->execute([':dname' => $_POST['department']]);
    $dept_id = $dept_stmt->fetchColumn();

    // Generate default password
    $default_password = password_hash("Admin@123", PASSWORD_DEFAULT);

    $insertStmt = $pdo->prepare("INSERT INTO admin (first_name, last_name, email, contact_number, department_id, role, password, profile_picture, cover_photo) 
        VALUES (:first_name, :last_name, :email, :contact_number, :department_id, :role, :password, :profile_picture, :cover_photo)");

    $insertStmt->execute([
        ':first_name' => $a_first_name,
        ':last_name' => $a_last_name,
        ':email' => $a_email,
        ':contact_number' => $a_contact_number,
        ':department_id' => $dept_id,
        ':role' => $a_role,
        ':password' => $default_password,
        ':profile_picture' => $default_profile,
        ':cover_photo' => $default_cover
    ]);

    // Log the action
    $new_admin_id = $pdo->lastInsertId();
    logAction(
        $pdo,
        $admin_id,
        'admin',
        'ADD',
        'admin',
        $new_admin_id,
        "Added new admin: $a_first_name $a_last_name"
    );

    echo "<script>alert('Admin added successfully. Default password is Admin@123');</script>";
    echo "<script>window.location.href = 'manage_admin.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Manage Admins - ISMS Portal</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
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
                <!-- Sidebar -->
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

                <!-- Main content -->
                <div class="col-12 col-xxl-9 col-lg-8 main-content pt-5 px-5" style="margin: 0 auto;">
                    <div class="d-flex justify-content-between align-items-center mb-4 pt-5">
                        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#adminModal">
                            <i class="bi bi-person-plus-fill me-2"></i>Add New Admin
                        </button>
                    </div>
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger py-2">
                            <?php echo htmlspecialchars($_GET['error']); ?>
                        </div>
                        <script>
                            $(document).ready(function() {
                                setTimeout(function() {
                                    $(".alert").fadeOut('slow');
                                }, 3000);
                            });
                        </script>
                    <?php endif; ?>
                    <div class="card shadow">
                        <div class="card-body">
                            <?php
                            try {
                                $query = "SELECT a.*, d.department_name 
                                         FROM admin a
                                         JOIN department d ON d.department_id = a.department_id
                                         ORDER BY a.last_name ASC";

                                $stmt = $pdo->prepare($query);
                                $stmt->execute();
                                $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                                <?php if (count($admins) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <tr class="bg-primary text-white">
                                                    <th>Admin ID</th>
                                                    <th>Full Name</th>
                                                    <th>Email</th>
                                                    <th>Contact Number</th>
                                                    <th>Department</th>
                                                    <th>Role</th>
                                                    <th class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($admins as $admin):
                                                    // Skip the current superadmin
                                                    if ($admin['admin_id'] === $admin_id) continue;
                                                ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($admin['admin_id']) ?></td>
                                                        <td class="fw-semibold">
                                                            <?= htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) ?>
                                                        </td>
                                                        <td><?= htmlspecialchars($admin['email']) ?></td>
                                                        <td><?= htmlspecialchars($admin['contact_number']) ?></td>
                                                        <td>
                                                            <span class="badge bg-secondary">
                                                                <?= htmlspecialchars($admin['department_name']) ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge <?= $admin['role'] === 'superadmin' ? 'bg-danger' : 'bg-primary' ?>">
                                                                <?= htmlspecialchars($admin['role']) ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="btn-group">
                                                                <button type="button" class="btn btn-sm btn-outline-primary edit-admin"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#editAdminModal"
                                                                    data-admin-id="<?= $admin['admin_id'] ?>"
                                                                    data-first-name="<?= $admin['first_name'] ?>"
                                                                    data-last-name="<?= $admin['last_name'] ?>"
                                                                    data-email="<?= $admin['email'] ?>"
                                                                    data-contact="<?= $admin['contact_number'] ?>"
                                                                    data-department="<?= $admin['department_name'] ?>"
                                                                    data-role="<?= $admin['role'] ?>">
                                                                    <i class="bi bi-pencil-square"></i>
                                                                </button>
                                                                <?php if ($admin['admin_id'] !== $_SESSION['user']['admin_id']): ?>
                                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#deleteAdmin"
                                                                        data-admin-id="<?= $admin['admin_id'] ?>">
                                                                        <i class="bi bi-trash"></i>
                                                                    </button>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i>No other admins found.
                                    </div>
                                <?php endif; ?>
                            <?php
                            } catch (PDOException $e) {
                                echo '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Admin Modal -->
        <div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="adminModalLabel">Add New Admin</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="addAdminForm" method="POST">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="firstName" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="firstName" name="firstName" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="lastName" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="lastName" name="lastName" required>
                                </div>
                                <div class="col-md-12">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="col-md-12">
                                    <label for="contactNumber" class="form-label">Contact Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text">+63</span>
                                        <input type="text" class="form-control" id="contactNumber" name="contactNumber"
                                            pattern="9[0-9]{9}" title="Enter 10 digits starting with 9" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="department" class="form-label">Department</label>
                                    <select class="form-select" id="department" name="department" required>
                                        <option value="">Select Department</option>
                                        <option value="CICS">CICS</option>
                                        <option value="CABE">CABE</option>
                                        <option value="CAS">CAS</option>
                                        <option value="CIT">CIT</option>
                                        <option value="CTE">CTE</option>
                                        <option value="CE">CE</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="role" class="form-label">Role</label>
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="admin">Admin</option>
                                        <option value="superadmin">Superadmin</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Admin</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Admin Modal -->
        <div class="modal fade" id="editAdminModal" tabindex="-1" aria-labelledby="editAdminModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editAdminModalLabel">Edit Admin</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="editAdminForm" method="POST">
                        <input type="hidden" id="editAdminId" name="admin_id">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="editFirstName" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="editFirstName" name="firstName" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="editLastName" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="editLastName" name="lastName" required>
                                </div>
                                <div class="col-md-12">
                                    <label for="editEmail" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="editEmail" name="email" required>
                                </div>
                                <div class="col-md-12">
                                    <label for="editContactNumber" class="form-label">Contact Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text">+63</span>
                                        <input type="text" class="form-control" id="editContactNumber" name="contactNumber"
                                            pattern="9[0-9]{9}" title="Enter 10 digits starting with 9" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="editDepartment" class="form-label">Department</label>
                                    <select class="form-select" id="editDepartment" name="department" required>
                                        <option value="">Select Department</option>
                                        <option value="CICS">CICS</option>
                                        <option value="CABE">CABE</option>
                                        <option value="CAS">CAS</option>
                                        <option value="CIT">CIT</option>
                                        <option value="CTE">CTE</option>
                                        <option value="CE">CE</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="editRole" class="form-label">Role</label>
                                    <select class="form-select" id="editRole" name="role" required
                                        <?php if ($admin['admin_id'] === $_SESSION['user']['admin_id'] && $_SESSION['user']['role'] === 'superadmin'): ?>
                                        disabled
                                        <?php endif; ?>>
                                        <option value="admin">Admin</option>
                                        <option value="superadmin">Superadmin</option>
                                    </select>
                                    <?php if ($admin['admin_id'] === $_SESSION['user']['admin_id'] && $_SESSION['user']['role'] === 'superadmin'): ?>
                                        <input type="hidden" name="role" value="superadmin">
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Admin Modal -->
        <div class="modal fade" id="deleteAdmin" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Admin?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this admin? This action cannot be undone.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirm-delete-admin-btn">Delete</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation -->
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
                <a href="manage.php" class="btn nav-bottom-btn">
                    <i class="fas fa-user"></i>
                </a>
                <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'superadmin'): ?>
                    <a href="manage_admin.php" class="btn nav-bottom-btn active-btn">
                        <i class="fas fa-user-shield"></i>
                    </a>
                <?php endif; ?>
            </div>
        </nav>
    </main>

    <?php include '../../cdn/body.html'; ?>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('.table').DataTable({
                responsive: true,
                order: [
                    [1, 'asc']
                ], // Sort by Full Name column by default
                pageLength: 10,
                lengthChange: false
            });

            // Handle edit button clicks
            $('.edit-admin').click(function() {
                $('#editAdminId').val($(this).data('admin-id'));
                $('#editFirstName').val($(this).data('first-name'));
                $('#editLastName').val($(this).data('last-name'));
                $('#editEmail').val($(this).data('email'));
                $('#editContactNumber').val($(this).data('contact').replace(/^\+63/, ''));
                $('#editDepartment').val($(this).data('department'));
                $('#editRole').val($(this).data('role'));
            });

            // Handle delete button clicks
            let adminIdToDelete;
            $('#deleteAdmin').on('show.bs.modal', function(event) {
                adminIdToDelete = $(event.relatedTarget).data('admin-id');
            });

            $('#confirm-delete-admin-btn').click(function() {
                if (adminIdToDelete) {
                    $.ajax({
                        url: 'delete_admin.php',
                        method: 'POST',
                        data: {
                            admin_id: adminIdToDelete
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                alert('Admin deleted successfully');
                                location.reload();
                            } else {
                                alert('Error: ' + (response.error || 'Failed to delete admin'));
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'Error deleting admin';
                            try {
                                const response = JSON.parse(xhr.responseText);
                                errorMessage = response.error || errorMessage;
                            } catch (e) {}
                            alert(errorMessage);
                        }
                    });
                    $('#deleteAdmin').modal('hide');
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
</body>

</html>