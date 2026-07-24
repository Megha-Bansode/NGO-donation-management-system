<?php
// volunteer_events.php
session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Middleware.php';
require_once __DIR__ . '/includes/dashboard/components.php';

// Protect this dashboard: Only Volunteer (Role ID 4) can access
Middleware::role([4]);

$pdo = getDatabase();
$volunteer_id = $_SESSION['user_id'];

// Fetch all assigned events
$events = [];
try {
    $stmt = $pdo->prepare("
        SELECT e.*, u.full_name as coordinator_name, vr.approval_status, vr.attendance_status 
        FROM events e
        JOIN volunteer_registrations vr ON e.id = vr.event_id
        JOIN users u ON e.coordinator_id = u.id
        WHERE vr.volunteer_id = ? 
        ORDER BY e.event_date DESC
    ");
    $stmt->execute([$volunteer_id]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Volunteer Events Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Events | <?php echo APP_NAME; ?></title>
    
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
                    <h1>Assigned Events</h1>
                    <p style="color: var(--text-muted); margin-top: 5px;">Events scheduled for you</p>
                </div>
            </div>

            <div class="glass-card">
                <?php if (empty($events)): ?>
                    <?php render_empty_state('No Events', 'No events have been assigned yet.', 'far fa-calendar-times'); ?>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Event Name</th>
                                    <th>Coordinator</th>
                                    <th>Date & Time</th>
                                    <th>Venue</th>
                                    <th>Registration Status</th>
                                    <th>Event Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($events as $evt): ?>
                                <tr>
                                    <td>
                                        <strong style="color: var(--text-dark); display: block;"><?php echo htmlspecialchars($evt['title']); ?></strong>
                                        <span style="font-size: 0.75rem; color: var(--text-muted);"><?php echo htmlspecialchars($evt['event_type'] ?? 'General'); ?></span>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.85rem; font-weight: 600; color: var(--primary-dark);"><?php echo htmlspecialchars($evt['coordinator_name']); ?></span>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.85rem; color: var(--text-dark); font-weight: 600;"><?php echo date('M d, Y', strtotime($evt['event_date'])); ?></div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?php echo date('g:i A', strtotime($evt['event_time'])); ?></div>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.85rem; color: var(--text-muted);"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($evt['venue']); ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                        $regClass = 'status-pending';
                                        if ($evt['approval_status'] == 'approved') $regClass = 'status-active';
                                        if ($evt['approval_status'] == 'rejected') $regClass = 'status-inactive';
                                        ?>
                                        <span class="status-badge <?php echo $regClass; ?>"><?php echo ucfirst($evt['approval_status']); ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                        $statusClass = 'status-pending';
                                        if ($evt['status'] == 'completed') $statusClass = 'status-active';
                                        if ($evt['status'] == 'cancelled') $statusClass = 'status-inactive';
                                        ?>
                                        <span class="status-badge <?php echo $statusClass; ?>"><?php echo ucfirst($evt['status']); ?></span>
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
