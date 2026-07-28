<?php
// volunteer_applications.php
session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Middleware.php';
require_once __DIR__ . '/includes/dashboard/components.php';

// Protect this dashboard: Only Volunteer (Role ID 4) can access
Middleware::role([4]);

$pdo = getDatabase();
$volunteer_id = $_SESSION['user_id'];

// Fetch Applications
$applications = [];
try {
    $stmt = $pdo->prepare("
        SELECT e.title as event_title, 
               vr.registration_date as applied_date, 
               vr.approval_status 
        FROM volunteer_registrations vr 
        JOIN events e ON vr.event_id = e.id 
        WHERE vr.volunteer_id = ? 
        ORDER BY vr.registration_date DESC
    ");
    $stmt->execute([$volunteer_id]);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Volunteer Applications Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications | <?php echo APP_NAME; ?></title>
    
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
                    <h1>My Applications</h1>
                    <p style="color: var(--text-muted); margin-top: 5px;">Track your event registrations</p>
                </div>
            </div>

            <div class="glass-card">
                <?php if (empty($applications)): ?>
                    <?php render_empty_state('No Applications', 'You have not applied for any events yet.', 'fas fa-file-signature'); ?>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Event Name</th>
                                    <th>Applied Date</th>
                                    <th>Application Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($applications as $app): ?>
                                <tr>
                                    <td>
                                        <strong style="color: var(--text-dark);"><?php echo htmlspecialchars($app['event_title']); ?></strong>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.85rem; color: var(--text-muted);">
                                            <i class="far fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($app['applied_date'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $statusClass = 'status-pending'; // default for pending
                                        if ($app['approval_status'] == 'approved') {
                                            $statusClass = 'status-active'; // green
                                        } elseif ($app['approval_status'] == 'rejected') {
                                            $statusClass = 'status-inactive'; // red
                                        }
                                        ?>
                                        <span class="status-badge <?php echo $statusClass; ?>">
                                            <?php echo ucfirst(htmlspecialchars($app['approval_status'])); ?>
                                        </span>
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
