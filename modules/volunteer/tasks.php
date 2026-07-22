<?php
// Volunteer Assigned Tasks Page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assigned Tasks - Volunteer Dashboard</title>
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
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="dashboard.php">Dashboard</a> <i class="fas fa-chevron-right"></i> <span>Assigned Tasks</span>
        </div>

        <!-- Page Header -->
        <div class="dashboard-header-section">
            <div class="dashboard-titles">
                <h1>Assigned Tasks</h1>
                <p>View all assigned volunteer tasks, monitor deadlines, priorities, and progress.</p>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-icon primary"><i class="fas fa-list-ul"></i></div>
                <div class="kpi-info">
                    <h3>18</h3>
                    <h4>Total Tasks</h4>
                    <p>All time tasks assigned</p>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon warning"><i class="fas fa-hourglass-start"></i></div>
                <div class="kpi-info">
                    <h3>06</h3>
                    <h4>Pending</h4>
                    <p>Awaiting start</p>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon primary"><i class="fas fa-spinner"></i></div>
                <div class="kpi-info">
                    <h3>08</h3>
                    <h4>In Progress</h4>
                    <p>Currently working on</p>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon success"><i class="fas fa-check-double"></i></div>
                <div class="kpi-info">
                    <h3>04</h3>
                    <h4>Completed</h4>
                    <p>Successfully finished</p>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="filter-item search-filter">
                <i class="fas fa-search"></i>
                <input type="text" id="task-search" placeholder="Search task...">
            </div>
            <div class="filter-item">
                <select id="task-status">
                    <option value="all">All Tasks</option>
                    <option value="pending">Pending</option>
                    <option value="progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            <div class="filter-item">
                <select id="task-priority">
                    <option value="all">All Priorities</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>
            <div class="filter-item">
                <select id="task-sort">
                    <option value="deadline">Deadline</option>
                    <option value="priority">Priority</option>
                    <option value="newest">Newest</option>
                    <option value="oldest">Oldest</option>
                </select>
            </div>
            <div class="filter-actions">
                <button id="task-filter-btn" class="action-btn btn-primary"><i class="fas fa-search"></i> Search</button>
                <button id="task-reset-btn" class="action-btn btn-outline"><i class="fas fa-undo"></i> Reset</button>
            </div>
        </div>

        <!-- Tasks Table Container -->
        <div class="dashboard-card" style="padding: 0; overflow: hidden;">
            <div class="table-responsive">
                <table class="data-table task-table" id="tasks-table">
                    <thead>
                        <tr>
                            <th>Task ID</th>
                            <th>Task Name</th>
                            <th>Event</th>
                            <th>Assigned Date</th>
                            <th>Deadline</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="task-tbody">
                        <!-- Task 1 -->
                        <tr class="task-row" data-task-id="T-101" data-status="pending" data-priority="high" data-deadline="2026-10-14" data-assigned="2026-10-10">
                            <td>#T-101</td>
                            <td class="task-name">Prepare Registration Desk</td>
                            <td>Blood Donation Camp</td>
                            <td>Oct 10, 2026</td>
                            <td>Oct 14, 2026</td>
                            <td><span class="badge badge-high">High</span></td>
                            <td><span class="badge badge-pending">Pending</span></td>
                            <td>
                                <div class="progress-wrapper">
                                    <span class="progress-text">0%</span>
                                    <div class="progress-bar"><div class="progress-fill bg-danger" style="width: 0%"></div></div>
                                </div>
                            </td>
                            <td class="action-cell">
                                <button class="icon-btn btn-view" title="View"><i class="fas fa-eye"></i></button>
                                <button class="icon-btn btn-edit" title="Update Status"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <!-- Task 2 -->
                        <tr class="task-row" data-task-id="T-102" data-status="progress" data-priority="high" data-deadline="2026-10-17" data-assigned="2026-10-11">
                            <td>#T-102</td>
                            <td class="task-name">Food Distribution Logistics</td>
                            <td>Food Drive</td>
                            <td>Oct 11, 2026</td>
                            <td>Oct 17, 2026</td>
                            <td><span class="badge badge-high">High</span></td>
                            <td><span class="badge badge-progress">In Progress</span></td>
                            <td>
                                <div class="progress-wrapper">
                                    <span class="progress-text">40%</span>
                                    <div class="progress-bar"><div class="progress-fill bg-primary" style="width: 40%"></div></div>
                                </div>
                            </td>
                            <td class="action-cell">
                                <button class="icon-btn btn-view" title="View"><i class="fas fa-eye"></i></button>
                                <button class="icon-btn btn-edit" title="Update Status"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <!-- Task 3 -->
                        <tr class="task-row" data-task-id="T-103" data-status="progress" data-priority="medium" data-deadline="2026-10-20" data-assigned="2026-10-12">
                            <td>#T-103</td>
                            <td class="task-name">Tree Plantation Support</td>
                            <td>Green Earth</td>
                            <td>Oct 12, 2026</td>
                            <td>Oct 20, 2026</td>
                            <td><span class="badge badge-medium">Medium</span></td>
                            <td><span class="badge badge-progress">In Progress</span></td>
                            <td>
                                <div class="progress-wrapper">
                                    <span class="progress-text">65%</span>
                                    <div class="progress-bar"><div class="progress-fill bg-warning" style="width: 65%"></div></div>
                                </div>
                            </td>
                            <td class="action-cell">
                                <button class="icon-btn btn-view" title="View"><i class="fas fa-eye"></i></button>
                                <button class="icon-btn btn-edit" title="Update Status"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <!-- Task 4 -->
                        <tr class="task-row" data-task-id="T-104" data-status="completed" data-priority="low" data-deadline="2026-10-09" data-assigned="2026-10-01">
                            <td>#T-104</td>
                            <td class="task-name">Teaching Session Material Prep</td>
                            <td>Online Tutoring</td>
                            <td>Oct 01, 2026</td>
                            <td>Oct 09, 2026</td>
                            <td><span class="badge badge-low">Low</span></td>
                            <td><span class="badge badge-completed">Completed</span></td>
                            <td>
                                <div class="progress-wrapper">
                                    <span class="progress-text">100%</span>
                                    <div class="progress-bar"><div class="progress-fill bg-success" style="width: 100%"></div></div>
                                </div>
                            </td>
                            <td class="action-cell">
                                <button class="icon-btn btn-view" title="View"><i class="fas fa-eye"></i></button>
                                <button class="icon-btn btn-edit" title="Update Status"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <!-- Task 5 -->
                        <tr class="task-row" data-task-id="T-105" data-status="pending" data-priority="medium" data-deadline="2026-10-22" data-assigned="2026-10-15">
                            <td>#T-105</td>
                            <td class="task-name">Medical Assistance Coordination</td>
                            <td>Medical Checkup Camp</td>
                            <td>Oct 15, 2026</td>
                            <td>Oct 22, 2026</td>
                            <td><span class="badge badge-medium">Medium</span></td>
                            <td><span class="badge badge-pending">Pending</span></td>
                            <td>
                                <div class="progress-wrapper">
                                    <span class="progress-text">15%</span>
                                    <div class="progress-bar"><div class="progress-fill bg-danger" style="width: 15%"></div></div>
                                </div>
                            </td>
                            <td class="action-cell">
                                <button class="icon-btn btn-view" title="View"><i class="fas fa-eye"></i></button>
                                <button class="icon-btn btn-edit" title="Update Status"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <!-- Task 6 -->
                        <tr class="task-row" data-task-id="T-106" data-status="progress" data-priority="high" data-deadline="2026-10-25" data-assigned="2026-10-16">
                            <td>#T-106</td>
                            <td class="task-name">Fundraising Campaign Strategy</td>
                            <td>Annual Charity Gala</td>
                            <td>Oct 16, 2026</td>
                            <td>Oct 25, 2026</td>
                            <td><span class="badge badge-high">High</span></td>
                            <td><span class="badge badge-progress">In Progress</span></td>
                            <td>
                                <div class="progress-wrapper">
                                    <span class="progress-text">80%</span>
                                    <div class="progress-bar"><div class="progress-fill bg-primary" style="width: 80%"></div></div>
                                </div>
                            </td>
                            <td class="action-cell">
                                <button class="icon-btn btn-view" title="View"><i class="fas fa-eye"></i></button>
                                <button class="icon-btn btn-edit" title="Update Status"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <!-- Task 7 -->
                        <tr class="task-row" data-task-id="T-107" data-status="pending" data-priority="low" data-deadline="2026-10-28" data-assigned="2026-10-18">
                            <td>#T-107</td>
                            <td class="task-name">Community Awareness Posters</td>
                            <td>Awareness Drive</td>
                            <td>Oct 18, 2026</td>
                            <td>Oct 28, 2026</td>
                            <td><span class="badge badge-low">Low</span></td>
                            <td><span class="badge badge-pending">Pending</span></td>
                            <td>
                                <div class="progress-wrapper">
                                    <span class="progress-text">0%</span>
                                    <div class="progress-bar"><div class="progress-fill bg-danger" style="width: 0%"></div></div>
                                </div>
                            </td>
                            <td class="action-cell">
                                <button class="icon-btn btn-view" title="View"><i class="fas fa-eye"></i></button>
                                <button class="icon-btn btn-edit" title="Update Status"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <!-- Task 8 -->
                        <tr class="task-row" data-task-id="T-108" data-status="completed" data-priority="medium" data-deadline="2026-10-12" data-assigned="2026-10-05">
                            <td>#T-108</td>
                            <td class="task-name">Cleanliness Drive Equipment Setup</td>
                            <td>River Cleanup</td>
                            <td>Oct 05, 2026</td>
                            <td>Oct 12, 2026</td>
                            <td><span class="badge badge-medium">Medium</span></td>
                            <td><span class="badge badge-completed">Completed</span></td>
                            <td>
                                <div class="progress-wrapper">
                                    <span class="progress-text">100%</span>
                                    <div class="progress-bar"><div class="progress-fill bg-success" style="width: 100%"></div></div>
                                </div>
                            </td>
                            <td class="action-cell">
                                <button class="icon-btn btn-view" title="View"><i class="fas fa-eye"></i></button>
                                <button class="icon-btn btn-edit" title="Update Status"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <!-- Task 9 -->
                        <tr class="task-row" data-task-id="T-109" data-status="progress" data-priority="low" data-deadline="2026-11-02" data-assigned="2026-10-20">
                            <td>#T-109</td>
                            <td class="task-name">Clothes Distribution Sorting</td>
                            <td>Winter Clothes Drive</td>
                            <td>Oct 20, 2026</td>
                            <td>Nov 02, 2026</td>
                            <td><span class="badge badge-low">Low</span></td>
                            <td><span class="badge badge-progress">In Progress</span></td>
                            <td>
                                <div class="progress-wrapper">
                                    <span class="progress-text">35%</span>
                                    <div class="progress-bar"><div class="progress-fill bg-primary" style="width: 35%"></div></div>
                                </div>
                            </td>
                            <td class="action-cell">
                                <button class="icon-btn btn-view" title="View"><i class="fas fa-eye"></i></button>
                                <button class="icon-btn btn-edit" title="Update Status"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <!-- Task 10 -->
                        <tr class="task-row" data-task-id="T-110" data-status="pending" data-priority="medium" data-deadline="2026-11-05" data-assigned="2026-10-22">
                            <td>#T-110</td>
                            <td class="task-name">Animal Shelter Cage Cleaning</td>
                            <td>Weekend Animal Care</td>
                            <td>Oct 22, 2026</td>
                            <td>Nov 05, 2026</td>
                            <td><span class="badge badge-medium">Medium</span></td>
                            <td><span class="badge badge-pending">Pending</span></td>
                            <td>
                                <div class="progress-wrapper">
                                    <span class="progress-text">0%</span>
                                    <div class="progress-bar"><div class="progress-fill bg-danger" style="width: 0%"></div></div>
                                </div>
                            </td>
                            <td class="action-cell">
                                <button class="icon-btn btn-view" title="View"><i class="fas fa-eye"></i></button>
                                <button class="icon-btn btn-edit" title="Update Status"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <!-- Task 11 -->
                        <tr class="task-row" data-task-id="T-111" data-status="completed" data-priority="high" data-deadline="2026-10-08" data-assigned="2026-10-02">
                            <td>#T-111</td>
                            <td class="task-name">Event Photography and Editing</td>
                            <td>Charity Gala Setup</td>
                            <td>Oct 02, 2026</td>
                            <td>Oct 08, 2026</td>
                            <td><span class="badge badge-high">High</span></td>
                            <td><span class="badge badge-completed">Completed</span></td>
                            <td>
                                <div class="progress-wrapper">
                                    <span class="progress-text">100%</span>
                                    <div class="progress-bar"><div class="progress-fill bg-success" style="width: 100%"></div></div>
                                </div>
                            </td>
                            <td class="action-cell">
                                <button class="icon-btn btn-view" title="View"><i class="fas fa-eye"></i></button>
                                <button class="icon-btn btn-edit" title="Update Status"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <!-- Task 12 -->
                        <tr class="task-row" data-task-id="T-112" data-status="pending" data-priority="high" data-deadline="2026-11-10" data-assigned="2026-10-25">
                            <td>#T-112</td>
                            <td class="task-name">Volunteer Briefing Coordination</td>
                            <td>School Renovation</td>
                            <td>Oct 25, 2026</td>
                            <td>Nov 10, 2026</td>
                            <td><span class="badge badge-high">High</span></td>
                            <td><span class="badge badge-pending">Pending</span></td>
                            <td>
                                <div class="progress-wrapper">
                                    <span class="progress-text">10%</span>
                                    <div class="progress-bar"><div class="progress-fill bg-danger" style="width: 10%"></div></div>
                                </div>
                            </td>
                            <td class="action-cell">
                                <button class="icon-btn btn-view" title="View"><i class="fas fa-eye"></i></button>
                                <button class="icon-btn btn-edit" title="Update Status"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Empty State -->
            <div class="empty-state" id="task-empty-state" style="display: none; padding: 40px;">
                <i class="fas fa-search" style="font-size: 3rem; color: #CBD5E1; margin-bottom: 16px;"></i>
                <h3 style="margin: 0 0 8px 0; color: var(--text-main); font-size: 1.5rem;">No tasks found.</h3>
                <p style="color: var(--text-muted); margin: 0;">Try adjusting your search or filters.</p>
            </div>
        </div>

        <!-- Task Details Panel (Sample) -->
        <div class="dashboard-card mt-4 task-details-panel">
            <div class="card-header">
                <h3><i class="fas fa-info-circle text-primary"></i> Task Information</h3>
                <span class="badge badge-progress">#T-102</span>
            </div>
            <div class="task-details-grid">
                <div class="detail-group">
                    <span class="detail-label">Task Description</span>
                    <p class="detail-value">Coordinate logistics for the upcoming food distribution drive. This includes sorting food packets, organizing transport vehicles, and managing the volunteer list for the day.</p>
                </div>
                <div class="detail-row-group">
                    <div class="detail-group">
                        <span class="detail-label">Assigned By</span>
                        <p class="detail-value">Sarah Jenkins (NGO Admin)</p>
                    </div>
                    <div class="detail-group">
                        <span class="detail-label">Required Skills</span>
                        <p class="detail-value">Logistics, Communication, Management</p>
                    </div>
                    <div class="detail-group">
                        <span class="detail-label">Expected Hours</span>
                        <p class="detail-value">12 Hours total</p>
                    </div>
                    <div class="detail-group">
                        <span class="detail-label">Location</span>
                        <p class="detail-value">Downtown Main Shelter</p>
                    </div>
                </div>
                <div class="detail-group mt-3">
                    <span class="detail-label">Notes</span>
                    <p class="detail-value">Please contact driver Mike before 9 AM on the day of the event to confirm the route.</p>
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
