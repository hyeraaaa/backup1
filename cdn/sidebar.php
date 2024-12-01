<div class="col-lg-2 sidebar sidebar-left d-none d-lg-block">
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
                <a class="active" href="create.php"><i class="fas fa-bullhorn me-2"></i>Create Announcement</a>
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