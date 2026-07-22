<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Dashboard</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Volunteer CSS -->
    <link rel="stylesheet" href="../../assets/css/volunteer.css">
</head>
<body>
    <!-- Header -->
    <?php include '../../includes/header.php'; ?>
    
    <!-- Sidebar -->
    <?php include '../../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Search and Header Section -->
        <div class="dashboard-header-section">
            <div class="dashboard-titles">
                <h1>Volunteer Dashboard</h1>
                <p>Welcome back! Here's an overview of your volunteer activities.</p>
                <h2 class="greeting">Good Morning, John Doe 👋</h2>
            </div>
            <div class="dashboard-search">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search events or tasks...">
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="#" class="action-btn btn-primary"><i class="fas fa-calendar-plus"></i> Apply for Event</a>
            <a href="#" class="action-btn btn-secondary"><i class="fas fa-tasks"></i> View Assigned Tasks</a>
            <a href="#" class="action-btn btn-outline"><i class="fas fa-edit"></i> Update Work Status</a>
            <a href="#" class="action-btn btn-outline"><i class="fas fa-clipboard-check"></i> Attendance</a>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-icon primary"><i class="fas fa-clipboard-list"></i></div>
                <div class="kpi-info">
                    <h3 id="kpi-tasks">0</h3>
                    <h4>Assigned Tasks</h4>
                    <p>Total pending and in-progress</p>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon warning"><i class="fas fa-calendar-alt"></i></div>
                <div class="kpi-info">
                    <h3 id="kpi-events">0</h3>
                    <h4>Active Campaigns</h4>
                    <p>Total active events</p>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon success"><i class="fas fa-clock"></i></div>
                <div class="kpi-info">
                    <h3 id="kpi-hours">0</h3>
                    <h4>Hours Contributed</h4>
                    <p>Total volunteer hours</p>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon success-alt"><i class="fas fa-check-circle"></i></div>
                <div class="kpi-info">
                    <h3 id="kpi-completed">0</h3>
                    <h4>Completed Activities</h4>
                    <p>Tasks finished</p>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <!-- Left Column: Events & Tasks -->
            <div class="dashboard-main-col">
                <!-- Upcoming Events -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Upcoming Events</h3>
                        <a href="#" class="view-all">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Event Name</th>
                                    <th>Date</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="dashboard-events-tbody">
                                <!-- Data will be loaded via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Assigned Tasks -->
                <div class="dashboard-card mt-4">
                    <div class="card-header">
                        <h3>Assigned Tasks</h3>
                        <a href="#" class="view-all">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Deadline</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="dashboard-tasks-tbody">
                                <!-- Data will be loaded via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column: Recent Activity -->
            <div class="dashboard-side-col">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Recent Activity</h3>
                    </div>
                    <div class="activity-timeline">
                        <div class="timeline-item">
                            <div class="timeline-icon success"><i class="fas fa-check"></i></div>
                            <div class="timeline-content">
                                <h4>Registered for Tree Plantation</h4>
                                <span class="time">Today, 10:30 AM</span>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-icon primary"><i class="fas fa-clipboard-check"></i></div>
                            <div class="timeline-content">
                                <h4>Attendance marked</h4>
                                <span class="time">Yesterday, 09:00 AM</span>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-icon warning"><i class="fas fa-edit"></i></div>
                            <div class="timeline-content">
                                <h4>Work status updated</h4>
                                <span class="time">Oct 12, 2026, 04:15 PM</span>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-icon success"><i class="fas fa-check-circle"></i></div>
                            <div class="timeline-content">
                                <h4>Completed Food Distribution</h4>
                                <span class="time">Oct 10, 2026, 02:00 PM</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include '../../includes/footer.php'; ?>

    <!-- JS -->
    <script src="../../assets/js/volunteer.js"></script>
</body>
</html>
