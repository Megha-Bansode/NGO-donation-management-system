<?php
// volunteer_attendance.php
session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Middleware.php';
require_once __DIR__ . '/includes/dashboard/components.php';
require_once __DIR__ . '/includes/dashboard/volunteer_queries.php';

// Protect this dashboard: Only Volunteer (Role ID 4) can access
Middleware::role([4]);

$pdo = getDatabase();
$volunteer_id = $_SESSION['user_id'];

// Fetch KPI for total hours
$kpis = get_volunteer_kpis($pdo, $volunteer_id);

// Fetch Attendance History
$attendance = get_volunteer_attendance_history($pdo, $volunteer_id, 50); // Get up to 50 records
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Attendance | <?php echo APP_NAME; ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>

<div class="dashboard-layout">
    <?php include __DIR__ . '/includes/dashboard/sidebar.php'; ?>
    <main class="main-content">
        <?php include __DIR__ . '/includes/dashboard/topbar.php'; ?>

        <div class="page-content">
            <div class="page-header">
                <div class="page-title">
                    <h1>Hours contributed</h1>
                    <p style="color: var(--text-muted); margin-top: 5px;">Attendance performance</p>
                </div>
            </div>

            <!-- Total Hours Summary -->
            <div class="glass-card" style="margin-bottom: 2rem; display: flex; align-items: center; gap: 20px;">
                <div style="width: 60px; height: 60px; border-radius: 12px; background: rgba(124,154,134,0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <h3 style="margin: 0; color: var(--text-dark); font-size: 1.1rem;">Total Volunteering Hours</h3>
                    <div style="font-family: var(--font-stats); font-weight: 700; font-size: 2rem; color: var(--primary);">
                        <?php echo number_format($kpis['total_hours'], 1); ?> <span style="font-size: 1rem; color: var(--text-muted); font-weight: 500;">hrs</span>
                    </div>
                </div>
                
                <div style="margin-left: auto; width: 60px; height: 60px; border-radius: 12px; background: rgba(16,185,129,0.1); color: var(--success); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="fas fa-percentage"></i>
                </div>
                <div style="margin-right: 20px;">
                    <h3 style="margin: 0; color: var(--text-dark); font-size: 1.1rem;">Attendance Rate</h3>
                    <div style="font-family: var(--font-stats); font-weight: 700; font-size: 2rem; color: var(--success);">
                        <?php echo $kpis['attendance_percentage']; ?>%
                    </div>
                </div>
            </div>

            <div class="glass-card">
                <div class="card-header">
                    <h3 class="card-title">Attendance History</h3>
                </div>
                <?php if (empty($attendance)): ?>
                    <?php render_empty_state('No Records', 'No attendance records available.', 'fas fa-clipboard-list'); ?>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Event Name</th>
                                    <th>Event Date</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Hours</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($attendance as $att): ?>
                                <tr>
                                    <td>
                                        <strong style="color: var(--text-dark); display: block;"><?php echo htmlspecialchars($att['event_title']); ?></strong>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.85rem; color: var(--text-dark); font-weight: 600;"><?php echo date('M d, Y', strtotime($att['event_date'])); ?></span>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.85rem; color: var(--text-muted);"><i class="fas fa-sign-in-alt"></i> <?php echo $att['check_in'] ? date('g:i A', strtotime($att['check_in'])) : '-'; ?></span>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.85rem; color: var(--text-muted);"><i class="fas fa-sign-out-alt"></i> <?php echo $att['check_out'] ? date('g:i A', strtotime($att['check_out'])) : '-'; ?></span>
                                    </td>
                                    <td>
                                        <strong style="color: var(--primary);"><?php echo $att['hours'] !== null ? number_format($att['hours'], 1) : '-'; ?></strong>
                                    </td>
                                    <td>
                                        <?php 
                                        $statusClass = 'status-pending';
                                        if ($att['attendance_status'] == 'present') $statusClass = 'status-active';
                                        if ($att['attendance_status'] == 'absent') $statusClass = 'status-inactive';
                                        ?>
                                        <span class="status-badge <?php echo $statusClass; ?>"><?php echo ucfirst($att['attendance_status']); ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>
</div>

<script src="assets/js/dashboard.js"></script>

</body>
</html>
