<?php
// Volunteer Attendance Page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - Volunteer Dashboard</title>
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
            <a href="dashboard.php">Dashboard</a> <i class="fas fa-chevron-right"></i> <span>Attendance</span>
        </div>

        <!-- Page Header -->
        <div class="dashboard-header-section">
            <div class="dashboard-titles">
                <h1>Attendance</h1>
                <p>View your attendance history and participation across NGO events.</p>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-icon primary"><i class="fas fa-user-check"></i></div>
                <div class="kpi-info">
                    <h3>92%</h3>
                    <h4>Attendance %</h4>
                    <p>Overall present rate</p>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon info"><i class="fas fa-calendar-check"></i></div>
                <div class="kpi-info">
                    <h3>28</h3>
                    <h4>Events Attended</h4>
                    <p>Total participations</p>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon success"><i class="fas fa-check-circle"></i></div>
                <div class="kpi-info">
                    <h3>24</h3>
                    <h4>Present Days</h4>
                    <p>On-time attendance</p>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon danger"><i class="fas fa-times-circle"></i></div>
                <div class="kpi-info">
                    <h3>04</h3>
                    <h4>Absent Days</h4>
                    <p>Missed events</p>
                </div>
            </div>
        </div>

        <!-- Layout (Dashboard Content) -->
        <div class="attendance-layout">
            <!-- Left Column: Table & Filters -->
            <div class="attendance-main-col dashboard-card" style="padding: 0;">
                
                <div style="padding: 20px; border-bottom: 1px solid #E2E8F0;">
                    <!-- Filter Bar -->
                    <div class="filter-bar" style="margin-bottom: 0; box-shadow: none; padding: 0;">
                        <div class="filter-item search-filter">
                            <i class="fas fa-search"></i>
                            <input type="text" id="att-search" placeholder="Search event...">
                        </div>
                        <div class="filter-item">
                            <select id="att-status">
                                <option value="all">All Status</option>
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="late">Late</option>
                                <option value="excused">Excused</option>
                            </select>
                        </div>
                        <div class="filter-item">
                            <select id="att-month">
                                <option value="all">All Months</option>
                                <option value="01">January</option>
                                <option value="02">February</option>
                                <option value="03">March</option>
                                <option value="04">April</option>
                                <option value="05">May</option>
                                <option value="06">June</option>
                                <option value="07">July</option>
                                <option value="08">August</option>
                                <option value="09">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        </div>
                        <div class="filter-item">
                            <select id="att-year">
                                <option value="all">All Years</option>
                                <option value="2026">2026</option>
                                <option value="2027">2027</option>
                            </select>
                        </div>
                        <div class="filter-actions">
                            <button id="att-filter-btn" class="action-btn btn-primary"><i class="fas fa-search"></i> Search</button>
                            <button id="att-reset-btn" class="action-btn btn-outline"><i class="fas fa-undo"></i> Reset</button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="data-table att-table" id="att-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Event Name</th>
                                <th>Category</th>
                                <th>Date</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Coordinator</th>
                                <th>Remarks</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="att-tbody">
                            <!-- 15 dummy rows -->
                            <!-- Row 1 -->
                            <tr class="att-row" data-att-id="A-101" data-status="present" data-month="10" data-year="2026">
                                <td>#A-101</td>
                                <td class="att-event-name">Tree Plantation Drive</td>
                                <td>Environment</td>
                                <td>2026-10-15</td>
                                <td>09:00 AM</td>
                                <td>02:00 PM</td>
                                <td>5 hrs</td>
                                <td><span class="badge badge-present">Present</span></td>
                                <td>Sarah Jenkins</td>
                                <td>Excellent work</td>
                                <td><button class="icon-btn btn-view" title="View Details"><i class="fas fa-eye"></i></button></td>
                            </tr>
                            <!-- Row 2 -->
                            <tr class="att-row" data-att-id="A-102" data-status="late" data-month="10" data-year="2026">
                                <td>#A-102</td>
                                <td class="att-event-name">Food Distribution</td>
                                <td>Welfare</td>
                                <td>2026-10-18</td>
                                <td>12:30 PM</td>
                                <td>04:00 PM</td>
                                <td>3.5 hrs</td>
                                <td><span class="badge badge-late">Late</span></td>
                                <td>Mike Johnson</td>
                                <td>Traffic delay</td>
                                <td><button class="icon-btn btn-view" title="View Details"><i class="fas fa-eye"></i></button></td>
                            </tr>
                            <!-- Row 3 -->
                            <tr class="att-row" data-att-id="A-103" data-status="present" data-month="10" data-year="2026">
                                <td>#A-103</td>
                                <td class="att-event-name">Blood Donation Camp</td>
                                <td>Health</td>
                                <td>2026-10-22</td>
                                <td>08:45 AM</td>
                                <td>05:00 PM</td>
                                <td>8 hrs</td>
                                <td><span class="badge badge-present">Present</span></td>
                                <td>Dr. Smith</td>
                                <td>Handled registration</td>
                                <td><button class="icon-btn btn-view" title="View Details"><i class="fas fa-eye"></i></button></td>
                            </tr>
                            <!-- Row 4 -->
                            <tr class="att-row" data-att-id="A-104" data-status="absent" data-month="10" data-year="2026">
                                <td>#A-104</td>
                                <td class="att-event-name">Teaching Session</td>
                                <td>Education</td>
                                <td>2026-10-25</td>
                                <td>-</td>
                                <td>-</td>
                                <td>0 hrs</td>
                                <td><span class="badge badge-absent">Absent</span></td>
                                <td>Anita Roy</td>
                                <td>No show</td>
                                <td><button class="icon-btn btn-view" title="View Details"><i class="fas fa-eye"></i></button></td>
                            </tr>
                            <!-- Row 5 -->
                            <tr class="att-row" data-att-id="A-105" data-status="excused" data-month="11" data-year="2026">
                                <td>#A-105</td>
                                <td class="att-event-name">Medical Camp Prep</td>
                                <td>Health</td>
                                <td>2026-11-02</td>
                                <td>-</td>
                                <td>-</td>
                                <td>0 hrs</td>
                                <td><span class="badge badge-excused">Excused</span></td>
                                <td>Dr. Smith</td>
                                <td>Sick leave approved</td>
                                <td><button class="icon-btn btn-view" title="View Details"><i class="fas fa-eye"></i></button></td>
                            </tr>
                            <!-- Row 6 -->
                            <tr class="att-row" data-att-id="A-106" data-status="present" data-month="11" data-year="2026">
                                <td>#A-106</td>
                                <td class="att-event-name">Community Cleanup</td>
                                <td>Environment</td>
                                <td>2026-11-05</td>
                                <td>07:00 AM</td>
                                <td>11:00 AM</td>
                                <td>4 hrs</td>
                                <td><span class="badge badge-present">Present</span></td>
                                <td>Sarah Jenkins</td>
                                <td>-</td>
                                <td><button class="icon-btn btn-view" title="View Details"><i class="fas fa-eye"></i></button></td>
                            </tr>
                            <!-- Row 7 -->
                            <tr class="att-row" data-att-id="A-107" data-status="present" data-month="11" data-year="2026">
                                <td>#A-107</td>
                                <td class="att-event-name">Animal Shelter Visit</td>
                                <td>Animal Welfare</td>
                                <td>2026-11-12</td>
                                <td>10:00 AM</td>
                                <td>02:00 PM</td>
                                <td>4 hrs</td>
                                <td><span class="badge badge-present">Present</span></td>
                                <td>Raj Patel</td>
                                <td>Cleaned enclosures</td>
                                <td><button class="icon-btn btn-view" title="View Details"><i class="fas fa-eye"></i></button></td>
                            </tr>
                            <!-- Row 8 -->
                            <tr class="att-row" data-att-id="A-108" data-status="late" data-month="11" data-year="2026">
                                <td>#A-108</td>
                                <td class="att-event-name">Fundraising Campaign</td>
                                <td>Events</td>
                                <td>2026-11-18</td>
                                <td>09:30 AM</td>
                                <td>04:00 PM</td>
                                <td>6.5 hrs</td>
                                <td><span class="badge badge-late">Late</span></td>
                                <td>Nina Desai</td>
                                <td>Bus breakdown</td>
                                <td><button class="icon-btn btn-view" title="View Details"><i class="fas fa-eye"></i></button></td>
                            </tr>
                            <!-- Row 9 -->
                            <tr class="att-row" data-att-id="A-109" data-status="present" data-month="11" data-year="2026">
                                <td>#A-109</td>
                                <td class="att-event-name">Health Awareness Drive</td>
                                <td>Health</td>
                                <td>2026-11-22</td>
                                <td>09:00 AM</td>
                                <td>03:00 PM</td>
                                <td>6 hrs</td>
                                <td><span class="badge badge-present">Present</span></td>
                                <td>Dr. Smith</td>
                                <td>Distributed flyers</td>
                                <td><button class="icon-btn btn-view" title="View Details"><i class="fas fa-eye"></i></button></td>
                            </tr>
                            <!-- Row 10 -->
                            <tr class="att-row" data-att-id="A-110" data-status="present" data-month="12" data-year="2026">
                                <td>#A-110</td>
                                <td class="att-event-name">Environmental Workshop</td>
                                <td>Environment</td>
                                <td>2026-12-05</td>
                                <td>10:00 AM</td>
                                <td>01:00 PM</td>
                                <td>3 hrs</td>
                                <td><span class="badge badge-present">Present</span></td>
                                <td>Sarah Jenkins</td>
                                <td>-</td>
                                <td><button class="icon-btn btn-view" title="View Details"><i class="fas fa-eye"></i></button></td>
                            </tr>
                            <!-- Row 11 -->
                            <tr class="att-row" data-att-id="A-111" data-status="absent" data-month="12" data-year="2026">
                                <td>#A-111</td>
                                <td class="att-event-name">Food Distribution</td>
                                <td>Welfare</td>
                                <td>2026-12-10</td>
                                <td>-</td>
                                <td>-</td>
                                <td>0 hrs</td>
                                <td><span class="badge badge-absent">Absent</span></td>
                                <td>Mike Johnson</td>
                                <td>Uninformed</td>
                                <td><button class="icon-btn btn-view" title="View Details"><i class="fas fa-eye"></i></button></td>
                            </tr>
                            <!-- Row 12 -->
                            <tr class="att-row" data-att-id="A-112" data-status="present" data-month="12" data-year="2026">
                                <td>#A-112</td>
                                <td class="att-event-name">Winter Clothes Drive</td>
                                <td>Welfare</td>
                                <td>2026-12-15</td>
                                <td>08:00 AM</td>
                                <td>02:00 PM</td>
                                <td>6 hrs</td>
                                <td><span class="badge badge-present">Present</span></td>
                                <td>Mike Johnson</td>
                                <td>Sorted clothes</td>
                                <td><button class="icon-btn btn-view" title="View Details"><i class="fas fa-eye"></i></button></td>
                            </tr>
                            <!-- Row 13 -->
                            <tr class="att-row" data-att-id="A-113" data-status="present" data-month="12" data-year="2026">
                                <td>#A-113</td>
                                <td class="att-event-name">Orphanage Visit</td>
                                <td>Education</td>
                                <td>2026-12-20</td>
                                <td>10:00 AM</td>
                                <td>04:00 PM</td>
                                <td>6 hrs</td>
                                <td><span class="badge badge-present">Present</span></td>
                                <td>Anita Roy</td>
                                <td>Organized games</td>
                                <td><button class="icon-btn btn-view" title="View Details"><i class="fas fa-eye"></i></button></td>
                            </tr>
                            <!-- Row 14 -->
                            <tr class="att-row" data-att-id="A-114" data-status="present" data-month="12" data-year="2026">
                                <td>#A-114</td>
                                <td class="att-event-name">New Year Prep</td>
                                <td>Events</td>
                                <td>2026-12-28</td>
                                <td>09:00 AM</td>
                                <td>05:00 PM</td>
                                <td>8 hrs</td>
                                <td><span class="badge badge-present">Present</span></td>
                                <td>Nina Desai</td>
                                <td>Decorations</td>
                                <td><button class="icon-btn btn-view" title="View Details"><i class="fas fa-eye"></i></button></td>
                            </tr>
                            <!-- Row 15 -->
                            <tr class="att-row" data-att-id="A-115" data-status="present" data-month="01" data-year="2027">
                                <td>#A-115</td>
                                <td class="att-event-name">New Year Food Camp</td>
                                <td>Welfare</td>
                                <td>2027-01-01</td>
                                <td>07:00 AM</td>
                                <td>03:00 PM</td>
                                <td>8 hrs</td>
                                <td><span class="badge badge-present">Present</span></td>
                                <td>Mike Johnson</td>
                                <td>Excellent start to year</td>
                                <td><button class="icon-btn btn-view" title="View Details"><i class="fas fa-eye"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div class="empty-state" id="att-empty-state" style="display: none; padding: 40px;">
                    <i class="fas fa-search" style="font-size: 3rem; color: #CBD5E1; margin-bottom: 16px;"></i>
                    <h3 style="margin: 0 0 8px 0; color: var(--text-main); font-size: 1.5rem;">No attendance records found.</h3>
                    <p style="color: var(--text-muted); margin: 0;">Try adjusting your search or filters.</p>
                </div>
            </div>

            <!-- Right Column: Chart & Timeline -->
            <div class="attendance-sidebar">
                
                <!-- Progress Visualization -->
                <div class="dashboard-card text-center">
                    <h3 class="form-section-title" style="text-align: left;"><i class="fas fa-tachometer-alt text-primary"></i> Attendance Progress</h3>
                    <div class="circular-progress" style="padding-bottom: 0;">
                        <div class="progress-circle" id="att-progress-circle" style="background: conic-gradient(var(--success-color, #10B981) 0deg, #E2E8F0 0deg); transition: background 1.5s ease-out;">
                            <div class="progress-inner-value">92%</div>
                        </div>
                    </div>
                    <p style="margin-top: 16px; color: var(--text-muted); font-size: 0.9rem;">Excellent! Keep up the good work.</p>
                </div>

                <!-- Monthly Chart Placeholder -->
                <div class="dashboard-card mt-4">
                    <h3 class="form-section-title"><i class="fas fa-chart-bar text-primary"></i> Monthly Attendance Overview</h3>
                    <div class="chart-placeholder">
                        <div class="chart-bar-container">
                            <div class="chart-bar" style="height: 40%;" title="Aug: 4 events"></div>
                            <span>Aug</span>
                        </div>
                        <div class="chart-bar-container">
                            <div class="chart-bar" style="height: 60%;" title="Sep: 6 events"></div>
                            <span>Sep</span>
                        </div>
                        <div class="chart-bar-container">
                            <div class="chart-bar" style="height: 80%;" title="Oct: 8 events"></div>
                            <span>Oct</span>
                        </div>
                        <div class="chart-bar-container">
                            <div class="chart-bar" style="height: 100%;" title="Nov: 10 events"></div>
                            <span>Nov</span>
                        </div>
                        <div class="chart-bar-container">
                            <div class="chart-bar" style="height: 70%;" title="Dec: 7 events"></div>
                            <span>Dec</span>
                        </div>
                        <div class="chart-bar-container">
                            <div class="chart-bar" style="height: 30%;" title="Jan: 3 events"></div>
                            <span>Jan</span>
                        </div>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="dashboard-card mt-4">
                    <h3 class="form-section-title"><i class="fas fa-history text-primary"></i> Recent Activity</h3>
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h5>Present at New Year Food Camp</h5>
                                <span class="timeline-date">Jan 01, 2027</span>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h5>Certificate Earned</h5>
                                <span class="timeline-date">Dec 31, 2026</span>
                                <p class="timeline-desc">Received 'Volunteer of the Month'</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h5>Completed New Year Prep</h5>
                                <span class="timeline-date">Dec 28, 2026</span>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h5>Attendance Approved</h5>
                                <span class="timeline-date">Dec 20, 2026</span>
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
