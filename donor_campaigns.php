<?php
session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Middleware.php';
require_once __DIR__ . '/includes/dashboard/components.php';
require_once __DIR__ . '/includes/dashboard/donor_queries.php';

Middleware::role([3]);

$pdo = getDatabase();
$donor_id = $_SESSION['user_id'];
$campaigns = getDonorCampaigns($pdo, 100);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Campaigns | <?php echo APP_NAME; ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        .campaign-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        .campaign-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
        }
        .campaign-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        }
        .campaign-img {
            height: 180px;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .campaign-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .campaign-cat {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255,255,255,0.9);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--primary);
        }
        .campaign-content {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            flex: 1;
        }
        .campaign-title {
            font-size: 1.1rem;
            color: var(--text-dark);
            margin: 0 0 10px 0;
            font-weight: 700;
        }
        .campaign-desc {
            font-size: 0.85rem;
            color: var(--text-body);
            margin-bottom: 15px;
            flex: 1;
        }
        .progress-wrapper {
            margin-bottom: 15px;
        }
        .progress-stats {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .progress-bar {
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: var(--primary);
            border-radius: 4px;
        }
        .campaign-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 15px;
            padding-top: 15px;
            border-top: 1px solid #f1f5f9;
        }
    </style>
</head>
<body>
<div class="dashboard-layout">
    <?php include __DIR__ . '/includes/dashboard/sidebar.php'; ?>
    <main class="main-content">
        <?php include __DIR__ . '/includes/dashboard/topbar.php'; ?>
        <div class="page-content">
            
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div class="page-title">
                    <h1>Active Campaigns</h1>
                    <p style="color: var(--text-muted); margin-top: 5px;">Discover and support causes that matter to you.</p>
                </div>
            </div>

            <?php if (empty($campaigns)): ?>
                <div class="glass-card" style="margin-top: 2rem;">
                    <?php render_empty_state('No Campaigns', 'No active campaigns available.', 'far fa-calendar-times'); ?>
                </div>
            <?php else: ?>
                <div class="campaign-grid">
                    <?php foreach ($campaigns as $camp): 
                        $percent = $camp['target_amount'] > 0 ? ($camp['collected_amount'] / $camp['target_amount']) * 100 : 0;
                        $percent = min(100, $percent);
                        $days_remaining = max(0, (strtotime($camp['end_date']) - time()) / (60 * 60 * 24));
                    ?>
                        <div class="campaign-card">
                            <div class="campaign-img">
                                <?php if (!empty($camp['campaign_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($camp['campaign_image']); ?>" alt="Campaign">
                                <?php else: ?>
                                    <i class="fas fa-image fa-3x" style="color: #cbd5e1;"></i>
                                <?php endif; ?>
                                <span class="campaign-cat"><i class="fas <?php echo htmlspecialchars($camp['category_icon']); ?>"></i> <?php echo htmlspecialchars($camp['category_name']); ?></span>
                            </div>
                            <div class="campaign-content">
                                <h3 class="campaign-title"><?php echo htmlspecialchars($camp['name']); ?></h3>
                                <p class="campaign-desc"><?php echo htmlspecialchars($camp['short_description']); ?></p>
                                
                                <div class="progress-wrapper">
                                    <div class="progress-stats">
                                        <span style="color: var(--primary);"><?php echo formatIndianCurrency($camp['collected_amount']); ?> raised</span>
                                        <span style="color: var(--text-muted);">Goal: <?php echo formatIndianCurrency($camp['target_amount']); ?></span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $percent; ?>%;"></div>
                                    </div>
                                </div>
                                
                                <div class="campaign-meta">
                                    <span><i class="fas fa-users"></i> <?php echo htmlspecialchars($camp['organization_name']); ?></span>
                                    <span><i class="far fa-clock"></i> <?php echo floor($days_remaining); ?> days left</span>
                                </div>
                                
                                <div style="display: flex; gap: 10px; margin-top: auto;">
                                    <a href="donor_campaign_details.php?id=<?php echo $camp['id']; ?>" class="btn-secondary" style="flex: 1; text-align: center; text-decoration: none;">Details</a>
                                    <a href="donor_donate.php?campaign_id=<?php echo $camp['id']; ?>" class="btn-primary" style="flex: 1; text-align: center; text-decoration: none;">Donate Now</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </main>
</div>
<script src="assets/js/dashboard.js"></script>
</body>
</html>
