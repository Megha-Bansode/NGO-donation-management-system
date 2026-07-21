<?php
    $current_page = basename($_SERVER['PHP_SELF']);
?>
<aside id="volunteer-sidebar" class="volunteer-sidebar">
    <div class="sidebar-header">
        <h2 class="sidebar-title">Menu</h2>
        <button id="sidebar-close-btn" class="close-btn mobile-only">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <nav class="sidebar-nav">
        <ul>
            <li><a href="dashboard.php" class="nav-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
            <li><a href="register.php" class="nav-item <?php echo $current_page == 'register.php' ? 'active' : ''; ?>"><i class="fas fa-user-plus"></i> <span>Volunteer Registration</span></a></li>
            <li><a href="events.php" class="nav-item <?php echo $current_page == 'events.php' ? 'active' : ''; ?>"><i class="fas fa-calendar-check"></i> <span>Event Application</span></a></li>
            <li><a href="tasks.php" class="nav-item <?php echo $current_page == 'tasks.php' ? 'active' : ''; ?>"><i class="fas fa-tasks"></i> <span>Assigned Tasks</span></a></li>
            <li><a href="work-status.php" class="nav-item <?php echo $current_page == 'work-status.php' ? 'active' : ''; ?>"><i class="fas fa-edit"></i> <span>Update Work Status</span></a></li>
            <li><a href="attendance.php" class="nav-item <?php echo $current_page == 'attendance.php' ? 'active' : ''; ?>"><i class="fas fa-clipboard-list"></i> <span>Attendance</span></a></li>
        </ul>
    </nav>
    <div class="sidebar-footer">
        <a href="#" class="nav-item logout"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
    </div>
</aside>
<div id="sidebar-overlay" class="sidebar-overlay"></div>
