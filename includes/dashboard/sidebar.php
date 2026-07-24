<?php
// includes/dashboard/sidebar.php

$currentPage = basename($_SERVER['PHP_SELF']);
$role_id = $_SESSION['role_id'] ?? 0;
?>
<!-- Mobile Overlay -->
<div class="sidebar-overlay" id="sidebar-overlay"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="index.php" class="sidebar-logo">
            <img src="assets/images/logo/arohan-logo.jpeg" alt="Arohan Logo">
            <span>Arohan Foundation</span>
        </a>
    </div>

    <div class="sidebar-menu">
        <?php if ($role_id == 1 || $role_id == 2): ?>
            <div class="menu-group">
                <div class="menu-label">Main</div>
                <?php $prefix = ($role_id == 2) ? 'ngo' : 'admin'; ?>
                <a href="<?php echo $prefix; ?>_dashboard.php" class="menu-item <?php echo $currentPage == $prefix.'_dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?php echo $prefix; ?>_donations.php" class="menu-item <?php echo $currentPage == $prefix.'_donations.php' ? 'active' : ''; ?>">
                    <i class="fas fa-hand-holding-heart"></i>
                    <span>Donations</span>
                </a>
                <a href="<?php echo $prefix; ?>_campaigns.php" class="menu-item <?php echo $currentPage == $prefix.'_campaigns.php' ? 'active' : ''; ?>">
                    <i class="fas fa-bullhorn"></i>
                    <span>Campaigns</span>
                </a>
            </div>

            <div class="menu-group">
                <div class="menu-label">Community</div>
                <a href="<?php echo $prefix; ?>_events.php" class="menu-item <?php echo $currentPage == $prefix.'_events.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Events</span>
                </a>
                <a href="<?php echo $prefix; ?>_volunteers.php" class="menu-item <?php echo $currentPage == $prefix.'_volunteers.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Volunteers</span>
                </a>
                <?php if ($role_id == 2): ?>
                <a href="ngo_coordinators.php" class="menu-item <?php echo $currentPage == 'ngo_coordinators.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-tie"></i>
                    <span>Coordinators</span>
                </a>
                <?php endif; ?>
            </div>
            
            <?php if ($role_id == 1): ?>
                <div class="menu-group">
                    <div class="menu-label">System</div>
                    <a href="analytics_dashboard.php" class="menu-item <?php echo $currentPage == 'analytics_dashboard.php' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-pie"></i>
                        <span>Analytics</span>
                    </a>
                    <a href="admin_users.php" class="menu-item <?php echo $currentPage == 'admin_users.php' ? 'active' : ''; ?>">
                        <i class="fas fa-user-shield"></i>
                        <span>User Management</span>
                    </a>
                    <a href="admin_reports.php" class="menu-item <?php echo $currentPage == 'admin_reports.php' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-line"></i>
                        <span>Reports</span>
                    </a>
                    <a href="admin_notifications.php" class="menu-item <?php echo $currentPage == 'admin_notifications.php' ? 'active' : ''; ?>">
                        <i class="fas fa-bell"></i>
                        <span>Notifications</span>
                    </a>
                    <a href="admin_activity_logs.php" class="menu-item <?php echo $currentPage == 'admin_activity_logs.php' ? 'active' : ''; ?>">
                        <i class="fas fa-history"></i>
                        <span>Activity Logs</span>
                    </a>
                    <a href="admin_settings.php" class="menu-item <?php echo $currentPage == 'admin_settings.php' ? 'active' : ''; ?>">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </div>
            <?php else: ?>
                <div class="menu-group">
                    <div class="menu-label">Account</div>
                    <a href="ngo_notifications.php" class="menu-item <?php echo $currentPage == 'ngo_notifications.php' ? 'active' : ''; ?>">
                        <i class="fas fa-bell"></i>
                        <span>Notifications</span>
                    </a>
                    <a href="ngo_profile.php" class="menu-item <?php echo $currentPage == 'ngo_profile.php' ? 'active' : ''; ?>">
                        <i class="fas fa-user-circle"></i>
                        <span>My Profile</span>
                    </a>
                </div>
            <?php endif; ?>

        <?php elseif ($role_id == 3): // Donor ?>
            <div class="menu-group">
                <div class="menu-label">Donor Portal</div>
                <a href="donor_dashboard.php" class="menu-item <?php echo $currentPage == 'donor_dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="donor_campaigns.php" class="menu-item <?php echo $currentPage == 'donor_campaigns.php' || $currentPage == 'donor_campaign_details.php' || $currentPage == 'donor_donate.php' ? 'active' : ''; ?>">
                    <i class="fas fa-bullhorn"></i>
                    <span>Campaigns</span>
                </a>
                <a href="donor_donations.php" class="menu-item <?php echo $currentPage == 'donor_donations.php' ? 'active' : ''; ?>">
                    <i class="fas fa-hand-holding-heart"></i>
                    <span>My Donations</span>
                </a>
                <a href="donor_receipts.php" class="menu-item <?php echo $currentPage == 'donor_receipts.php' ? 'active' : ''; ?>">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Receipts</span>
                </a>
                <a href="donor_notifications.php" class="menu-item <?php echo $currentPage == 'donor_notifications.php' ? 'active' : ''; ?>">
                    <i class="fas fa-bell"></i>
                    <span style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                        Notifications
                        <?php
                        if (!isset($pdo)) { $pdo = getDatabase(); }
                        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE recipient_id = ? AND read_status = 0");
                        $stmtCount->execute([$_SESSION['user_id']]);
                        $sidebarUnread = $stmtCount->fetchColumn();
                        if ($sidebarUnread > 0) {
                            echo '<span style="background: var(--danger); color: white; border-radius: 50%; padding: 2px 6px; font-size: 0.75rem; font-weight: bold;">'.$sidebarUnread.'</span>';
                        }
                        ?>
                    </span>
                </a>
                <a href="donor_profile.php" class="menu-item <?php echo $currentPage == 'donor_profile.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-circle"></i>
                    <span>My Profile</span>
                </a>
            </div>

        <?php elseif ($role_id == 4): // Volunteer ?>
            <div class="menu-group">
                <div class="menu-label">Volunteer</div>
                <a href="volunteer_dashboard.php" class="menu-item <?php echo $currentPage == 'volunteer_dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="volunteer_events.php" class="menu-item <?php echo $currentPage == 'volunteer_events.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-alt"></i>
                    <span>My Events</span>
                </a>
                <a href="volunteer_tasks.php" class="menu-item <?php echo $currentPage == 'volunteer_tasks.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tasks"></i>
                    <span>My Tasks</span>
                </a>
                <a href="volunteer_attendance.php" class="menu-item <?php echo $currentPage == 'volunteer_attendance.php' ? 'active' : ''; ?>">
                    <i class="fas fa-clock"></i>
                    <span>Attendance</span>
                </a>
                <a href="volunteer_notifications.php" class="menu-item <?php echo $currentPage == 'volunteer_notifications.php' ? 'active' : ''; ?>">
                    <i class="fas fa-bell"></i>
                    <span style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                        Notifications
                        <?php
                        if (!isset($pdo)) { $pdo = getDatabase(); }
                        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE recipient_id = ? AND read_status = 0");
                        $stmtCount->execute([$_SESSION['user_id']]);
                        $sidebarUnread = $stmtCount->fetchColumn();
                        if ($sidebarUnread > 0) {
                            echo '<span style="background: var(--danger); color: white; border-radius: 50%; padding: 2px 6px; font-size: 0.75rem; font-weight: bold;">'.$sidebarUnread.'</span>';
                        }
                        ?>
                    </span>
                </a>
                <a href="volunteer_profile.php" class="menu-item <?php echo $currentPage == 'volunteer_profile.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-circle"></i>
                    <span>My Profile</span>
                </a>
            </div>

        <?php elseif ($role_id == 5): // Event Coordinator ?>
            <div class="menu-group">
                <div class="menu-label">Event Coordinator</div>
                <a href="coordinator_dashboard.php" class="menu-item <?php echo $currentPage == 'coordinator_dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="coordinator_events.php" class="menu-item <?php echo $currentPage == 'coordinator_events.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-alt"></i>
                    <span>My Events</span>
                </a>
                <a href="coordinator_volunteers.php" class="menu-item <?php echo $currentPage == 'coordinator_volunteers.php' ? 'active' : ''; ?>">
                    <i class="fas fa-hands-helping"></i>
                    <span>Volunteers</span>
                </a>
                <a href="coordinator_tasks.php" class="menu-item <?php echo $currentPage == 'coordinator_tasks.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tasks"></i>
                    <span>Tasks</span>
                </a>
                <a href="coordinator_attendance.php" class="menu-item <?php echo $currentPage == 'coordinator_attendance.php' ? 'active' : ''; ?>">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Attendance</span>
                </a>
                <a href="coordinator_notifications.php" class="menu-item <?php echo $currentPage == 'coordinator_notifications.php' ? 'active' : ''; ?>">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                </a>
                <a href="coordinator_profile.php" class="menu-item <?php echo $currentPage == 'coordinator_profile.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-circle"></i>
                    <span>My Profile</span>
                </a>
            </div>
        <?php endif; ?>

        <div class="menu-group" style="margin-top: auto; padding-top: 20px;">
            <a href="logout.php" class="menu-item" style="color: var(--danger);">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
</aside>
