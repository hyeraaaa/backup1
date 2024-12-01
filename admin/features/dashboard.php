<?php
require_once '../../login/dbh.inc.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user_type'] !== 'admin') {
    $_SESSION = [];
    session_destroy();
    header("Location: ../../login/login.php");
    exit();
}

date_default_timezone_set('Asia/Manila');

try {
    // Summary Statistics
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM student");
    $stmt->execute();
    $total_students = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM announcement");
    $stmt->execute();
    $total_announcements = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM department");
    $stmt->execute();
    $total_departments = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sms_log WHERE status = 'sent'");
    $stmt->execute();
    $total_sms = $stmt->fetchColumn();

    // Department Statistics
    $stmt = $pdo->prepare("
        SELECT d.department_name, COUNT(ad.announcement_id) as count
        FROM department d
        LEFT JOIN announcement_department ad ON d.department_id = ad.department_id
        GROUP BY d.department_name
        ORDER BY count DESC
    ");
    $stmt->execute();
    $dept_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Year Level Statistics
    $stmt = $pdo->prepare("
        SELECT yl.year_level, COUNT(s.student_id) as count
        FROM year_level yl
        LEFT JOIN student s ON yl.year_level_id = s.year_level_id
        GROUP BY yl.year_level
        ORDER BY yl.year_level
    ");
    $stmt->execute();
    $year_level_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Monthly Trends (Last 6 months)
    $stmt = $pdo->prepare("
        SELECT 
            DATE_TRUNC('month', updated_at) as month,
            COUNT(*) as count
        FROM announcement
        WHERE updated_at >= NOW() - INTERVAL '6 months'
        GROUP BY DATE_TRUNC('month', updated_at)
        ORDER BY month
    ");
    $stmt->execute();
    $monthly_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // SMS Status Distribution
    $stmt = $pdo->prepare("
        SELECT 
            status,
            COUNT(*) as count
        FROM sms_log
        GROUP BY status
        ORDER BY status DESC
    ");
    $stmt->execute();
    $sms_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    exit('Database error occurred');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Analytics Dashboard - ISMS Portal</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include '../../cdn/head.html'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/nav-bottom.css">
    <link rel="icon" href="../img/brand.png" type="image/x-icon">
</head>

<body>
    <header>
        <?php include '../../cdn/navbar.php'; ?>
    </header>
    <!-- Loading Overlay -->
    <div class="loading-overlay">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <main>
        <div class="container-fluid pt-5 parent">
            <div class="row g-4">
                <!-- Left sidebar -->
                <div class="col-lg-3 sidebar sidebar-left d-none d-xxl-block">
                    <div class="sticky-sidebar">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="active" href="dashboard.php"><i class="fas fa-chart-line me-2"></i>Dashboard</a>
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

                <div class="dashboard-wrapper col-xxl-9 main-content" style="margin: 0 auto;">
                    <div class="dashboard-header">
                        <div>
                            <h1 class="dashboard-title mt-5">Analytics Overview</h1>
                            <p class="date-info">Last updated: <?php echo date('F d, Y h:i A'); ?>&nbsp;(PHT)</p>
                        </div>
                        <div class="refresh-button">
                            <button class="btn btn-outline-primary btn-sm" onclick="refreshDashboard()">
                                <i class="bi bi-arrow-clockwise"></i> Refresh Data
                            </button>
                        </div>
                    </div>

                    <!-- Summary Stats Cards -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-4 col-sm-6">
                            <div class="stats-card animate__animated animate__fadeIn">
                                <div class="stats-icon bg-purple">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <div class="stats-info">
                                    <h3><?php echo number_format($total_students); ?></h3>
                                    <p>Total Students</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <div class="stats-card animate__animated animate__fadeIn" style="animation-delay: 0.1s">
                                <div class="stats-icon bg-blue">
                                    <i class="bi bi-megaphone-fill"></i>
                                </div>
                                <div class="stats-info">
                                    <h3><?php echo number_format($total_announcements); ?></h3>
                                    <p>Total Announcements</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <div class="stats-card animate__animated animate__fadeIn" style="animation-delay: 0.2s">
                                <div class="stats-icon bg-green">
                                    <i class="bi bi-building"></i>
                                </div>
                                <div class="stats-info">
                                    <h3><?php echo number_format($total_departments); ?></h3>
                                    <p>Departments</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Charts Row -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="chart-card animate__animated animate__fadeIn" style="animation-delay: 0.4s">
                                <div class="chart-header">
                                    <h2 class="chart-title">Announcement Trends</h2>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-secondary active" data-period="month" onclick="updateTrendChart('month')">Monthly</button>
                                        <button class="btn btn-outline-secondary" data-period="week" onclick="updateTrendChart('week')">Weekly</button>
                                    </div>
                                </div>
                                <div class="chart-container">
                                    <canvas id="trendChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="chart-card animate__animated animate__fadeIn" style="animation-delay: 0.5s">
                                <div class="chart-header">
                                    <h2 class="chart-title">SMS Delivery Status</h2>
                                </div>
                                <div class="chart-container">
                                    <canvas id="smsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Secondary Charts Row -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="chart-card animate__animated animate__fadeIn" style="animation-delay: 0.6s">
                                <div class="chart-header">
                                    <h2 class="chart-title">Department Distribution</h2>
                                </div>
                                <div class="chart-container">
                                    <canvas id="deptChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="chart-card animate__animated animate__fadeIn" style="animation-delay: 0.7s">
                                <div class="chart-header">
                                    <h2 class="chart-title">Year Level Distribution</h2>
                                </div>
                                <div class="chart-container">
                                    <canvas id="yearLevelChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <nav class="navbar nav-bottom fixed-bottom d-block d-xxl-none mt-5">
            <div class="container-fluid d-flex justify-content-around">
                <a href="dashboard.php" class="btn nav-bottom-btn active-btn">
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
            </div>
        </nav>

    </main>


    <?php include '../../cdn/body.html'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment"></script>
    <?php include 'get_dashboard.php'; ?>
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