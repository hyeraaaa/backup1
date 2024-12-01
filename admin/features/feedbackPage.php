<?php
require_once '../../login/dbh.inc.php';
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
    <title>Feedback</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <?php include '../../cdn/head.html'; ?>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/tables.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/nav-bottom.css">
    <link rel="icon" href="../img/brand.png" type="image/x-icon">
</head>

<body>
    <header>
        <?php include '../../cdn/navbar.php'; ?>
    </header>
    <main>
        <div class="container-fluid">
            <div class="row g-4 pt-4">
                <!-- left sidebar -->
                <div class="col-lg-3 sidebar sidebar-left d-none d-xl-block mt-5" id="sidebar">
                    <div class="sticky-sidebar">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a href="dashboard.php">
                                    <i class="fas fa-chart-line me-2"></i>
                                    <span class="menu-text">Dashboard</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="../admin.php">
                                    <i class="fas fa-newspaper me-2"></i>
                                    <span class="menu-text">Feed</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="manage.php">
                                    <i class="fas fa-user me-2"></i>
                                    <span class="menu-text">My Profile</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="create.php">
                                    <i class="fas fa-bullhorn me-2"></i>
                                    <span class="menu-text">Create Announcement</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="logPage.php">
                                    <i class="fas fa-clipboard-list me-2"></i>
                                    <span class="menu-text">Logs</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="manage_student.php">
                                    <i class="fas fa-users-cog me-2"></i>
                                    <span class="menu-text">Manage Accounts</span>
                                </a>
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
                <div class="col-12 col-lg-9 main-content px-5 mt-5" style="margin: 0 auto;">
                    <div class="card shadow mt-5">
                        <div class="card-body">
                            <div class="table-responsive feedback-table">
                                <table class="table table-bordered table-hover display nowrap">
                                    <thead>
                                        <tr class="bg-primary text-white">
                                            <th class="align-middle">Feedback ID</th>
                                            <th class="align-middle">Name</th>
                                            <th class="align-middle">Email</th>
                                            <th class="align-middle">Message</th>
                                            <th class="align-middle">Date Submitted</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        try {
                                            $query = "SELECT * FROM feedback ORDER BY created_at DESC";
                                            $stmt = $pdo->prepare($query);
                                            $stmt->execute();

                                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                $feedback_id = htmlspecialchars($row['feedback_id']);
                                                $name = htmlspecialchars($row['name']);
                                                $email = htmlspecialchars($row['email']);
                                                $message = htmlspecialchars($row['message']);
                                                $created_at = htmlspecialchars($row['created_at']);
                                        ?>
                                                <tr>
                                                    <td class="align-middle"><?= $feedback_id ?></td>
                                                    <td class="align-middle"><?= $name ?></td>
                                                    <td class="align-middle"><?= $email ?></td>
                                                    <td class="align-middle"><?= $message ?></td>
                                                    <td class="align-middle"><?= formatTimestamp($created_at) ?></td>
                                                </tr>
                                        <?php
                                            }
                                        } catch (PDOException $e) {
                                            echo '<tr><td colspan="5" class="text-center text-danger">Error fetching feedback: ' . $e->getMessage() . '</td></tr>';
                                        }

                                        function formatTimestamp($timestamp)
                                        {
                                            return date('M d, Y h:i A', strtotime($timestamp));
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <nav class="navbar nav-bottom fixed-bottom d-block d-xl-none mt-5">
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

                <a href="feedbackPage.php" class="btn nav-bottom-btn active-btn">
                    <i class="fas fa-comments"></i>
                </a>
            </div>
        </nav>
        <?php include 'changePassOtherPage.html'; ?>
    </main>

    <?php include '../../cdn/body.html'; ?>
    <script>
        function confirmLogout() {
            if (confirm('Are you sure you want to sign out?')) {
                window.location.href = '../../login/logout.php';
            }
            return false;
        }

        $(document).ready(function() {
            $('.table').DataTable({
                responsive: false,
                order: [
                    [4, 'desc']
                ], // Sort by date submitted by default
                pageLength: 15,
                lengthChange: false,
                language: {
                    search: "Search feedback:",
                    info: "Showing _START_ to _END_ of _TOTAL_ feedback entries",
                    infoEmpty: "Showing 0 to 0 of 0 feedback entries",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                },
                columnDefs: [{
                        orderable: false,
                        targets: [3] // Disable sorting for message column
                    },
                    {
                        width: "30%",
                        targets: [3] // Make message column wider
                    },
                    {
                        width: "5%",
                        targets: [0]
                    },
                    {
                        width: "15%",
                        targets: [1, 2, 4]
                    },
                    {
                        className: "text-nowrap",
                        targets: [0, 1, 2, 4]
                    },
                    {
                        targets: [4],
                        type: 'date'
                    }
                ],
                dom: '<"top"f>rt<"bottom"ip><"clear">',
                initComplete: function() {
                    $('.dataTables_filter input').addClass('form-control form-control-sm');
                }
            });
        });
    </script>
</body>

</html>