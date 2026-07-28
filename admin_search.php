<?php
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Middleware.php';

// Super Admin or NGO Admin
Middleware::role([1, 2]);

$pdo = getDatabase();
$query = trim($_GET['q'] ?? '');

$results = [
    'users' => [],
    'campaigns' => [],
    'donations' => [],
    'events' => []
];

if (!empty($query)) {
    try {
        $likeQuery = '%' . $query . '%';

        // Search Users
        $stmt = $pdo->prepare("SELECT id, full_name as name, email as detail, 'user' as type FROM users WHERE full_name LIKE ? OR email LIKE ? LIMIT 5");
        $stmt->execute([$likeQuery, $likeQuery]);
        $results['users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Search Campaigns
        $stmt = $pdo->prepare("SELECT id, name, short_description as detail, 'campaign' as type FROM campaigns WHERE name LIKE ? OR description LIKE ? LIMIT 5");
        $stmt->execute([$likeQuery, $likeQuery]);
        $results['campaigns'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Search Donations (Transaction ID or Receipt)
        $stmt = $pdo->prepare("SELECT id, transaction_id as name, CONCAT('$', amount, ' via ', payment_method) as detail, 'donation' as type FROM donations WHERE transaction_id LIKE ? OR receipt_number LIKE ? LIMIT 5");
        $stmt->execute([$likeQuery, $likeQuery]);
        $results['donations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Search Events
        $stmt = $pdo->prepare("SELECT id, title as name, venue as detail, 'event' as type FROM events WHERE title LIKE ? OR description LIKE ? LIMIT 5");
        $stmt->execute([$likeQuery, $likeQuery]);
        $results['events'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        $error = "Search Error: " . $e->getMessage();
    }
}

$total_results = count($results['users']) + count($results['campaigns']) + count($results['donations']) + count($results['events']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results | <?php echo APP_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        .result-group { margin-bottom: 30px; }
        .result-group h3 { font-size: 1.1rem; color: var(--primary); margin-bottom: 15px; border-bottom: 1px solid rgba(0,0,0,0.1); padding-bottom: 5px; }
        .result-item { padding: 15px; background: rgba(0,0,0,0.02); border-radius: 8px; margin-bottom: 10px; border-left: 4px solid var(--primary); }
        .result-item a { text-decoration: none; color: inherit; display: block; }
        .result-item:hover { background: rgba(0,0,0,0.05); }
    </style>
</head>
<body>
<div class="dashboard-layout">
    <?php include __DIR__ . '/includes/dashboard/sidebar.php'; ?>
    <main class="main-content">
        <?php include __DIR__ . '/includes/dashboard/topbar.php'; ?>
        <div class="page-content">
            <div class="page-header">
                <div class="page-title">
                    <h1>Search Results for "<?php echo htmlspecialchars($query); ?>"</h1>
                    <p style="color: var(--text-muted); margin-top: 5px;">Found <?php echo $total_results; ?> results</p>
                </div>
            </div>
            
            <div class="glass-card">
                <?php if (empty($query)): ?>
                    <p style="text-align: center; color: var(--text-muted); padding: 40px;">Please enter a search query.</p>
                <?php elseif ($total_results === 0): ?>
                    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                        <i class="fas fa-search" style="font-size: 3rem; color: #ccc; margin-bottom: 15px;"></i>
                        <h3>No results found</h3>
                        <p>Try adjusting your search terms.</p>
                    </div>
                <?php else: ?>
                    
                    <?php if (!empty($results['users'])): ?>
                    <div class="result-group">
                        <h3><i class="fas fa-users"></i> Users</h3>
                        <?php foreach($results['users'] as $item): ?>
                            <div class="result-item">
                                <a href="admin_users.php">
                                    <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                    <p style="font-size: 0.85rem; color: var(--text-muted); margin: 5px 0 0;"><?php echo htmlspecialchars($item['detail']); ?></p>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($results['campaigns'])): ?>
                    <div class="result-group">
                        <h3><i class="fas fa-bullhorn"></i> Campaigns</h3>
                        <?php foreach($results['campaigns'] as $item): ?>
                            <div class="result-item">
                                <a href="admin_campaigns.php">
                                    <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                    <p style="font-size: 0.85rem; color: var(--text-muted); margin: 5px 0 0;"><?php echo htmlspecialchars($item['detail']); ?></p>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($results['donations'])): ?>
                    <div class="result-group">
                        <h3><i class="fas fa-hand-holding-heart"></i> Donations</h3>
                        <?php foreach($results['donations'] as $item): ?>
                            <div class="result-item">
                                <a href="admin_donations.php">
                                    <strong>Transaction: <?php echo htmlspecialchars($item['name']); ?></strong>
                                    <p style="font-size: 0.85rem; color: var(--text-muted); margin: 5px 0 0;"><?php echo htmlspecialchars($item['detail']); ?></p>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($results['events'])): ?>
                    <div class="result-group">
                        <h3><i class="far fa-calendar-alt"></i> Events</h3>
                        <?php foreach($results['events'] as $item): ?>
                            <div class="result-item">
                                <a href="admin_events.php">
                                    <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                    <p style="font-size: 0.85rem; color: var(--text-muted); margin: 5px 0 0;"><?php echo htmlspecialchars($item['detail']); ?></p>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
<script src="assets/js/dashboard.js"></script>
</body>
</html>
