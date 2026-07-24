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

// Collect unique categories for filter dropdown
$categories = [];
foreach ($campaigns as $camp) {
    $cat = $camp['category_name'] ?? '';
    if ($cat && !in_array($cat, $categories)) {
        $categories[] = $cat;
    }
}
sort($categories);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Campaigns | <?php echo APP_NAME; ?></title>
    <meta name="description" content="Browse and support active campaigns on <?php echo APP_NAME; ?>. Filter by category and find causes that matter to you.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/donor.css">
    <style>
        .campaign-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-top: 0;
        }
        .campaign-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            transition: transform 0.25s cubic-bezier(0.4,0,0.2,1), box-shadow 0.25s cubic-bezier(0.4,0,0.2,1);
        }
        .campaign-card:hover {
            transform: translateY(-6px) scale(1.01);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .campaign-img {
            height: 180px;
            position: relative;
            overflow: hidden;
            background: #e2e8f0;
        }
        .campaign-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.35s ease;
        }
        .campaign-card:hover .campaign-img img {
            transform: scale(1.04);
        }
        .campaign-content {
            padding: 1.25rem 1.5rem 1.5rem;
            display: flex;
            flex-direction: column;
            flex: 1;
        }
        .campaign-title {
            font-size: 1rem;
            color: var(--text-dark);
            margin: 0 0 8px;
            font-weight: 700;
            line-height: 1.4;
        }
        .campaign-desc {
            font-size: 0.83rem;
            color: var(--text-body);
            margin-bottom: 14px;
            flex: 1;
            line-height: 1.5;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }
        .campaign-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-bottom: 12px;
            padding-top: 12px;
            border-top: 1px solid #f1f5f9;
        }
        #campaignNoResults {
            display: none;
            grid-column: 1 / -1;
        }
    </style>
</head>
<body data-donor-page="campaigns">
<div class="dashboard-layout">
    <?php include __DIR__ . '/includes/dashboard/sidebar.php'; ?>
    <main class="main-content">
        <?php include __DIR__ . '/includes/dashboard/topbar.php'; ?>
        <div class="page-content">

            <div class="page-header">
                <div class="page-title">
                    <h1>Active Campaigns</h1>
                    <p style="color:var(--text-muted); margin-top:6px;">Discover and support causes that matter to you.</p>
                </div>
            </div>

            <?php if (empty($campaigns)): ?>
                <div class="glass-card" style="margin-top:2rem;">
                    <div class="donor-empty-state">
                        <div class="donor-empty-icon"><i class="far fa-calendar-times"></i></div>
                        <div class="donor-empty-title">No Active Campaigns</div>
                        <div class="donor-empty-text">There are no active campaigns available right now. Please check back later.</div>
                    </div>
                </div>
            <?php else: ?>

                <!-- Search & Filter Toolbar -->
                <div class="donor-filter-bar">
                    <div class="donor-search-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="campaignSearch" class="donor-search-input" placeholder="Search campaigns by name…" aria-label="Search campaigns">
                    </div>
                    <select id="campaignCatFilter" class="donor-filter-select" aria-label="Filter by category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo strtolower(htmlspecialchars($cat)); ?>"><?php echo htmlspecialchars($cat); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="filter-result-count" id="campaignResultCount"><?php echo count($campaigns); ?> campaigns found</span>
                </div>

                <div class="campaign-grid">
                    <?php foreach ($campaigns as $camp):
                        $percent = $camp['target_amount'] > 0 ? ($camp['collected_amount'] / $camp['target_amount']) * 100 : 0;
                        $percent = min(100, round($percent, 1));
                        $days_remaining = max(0, (strtotime($camp['end_date']) - time()) / (60 * 60 * 24));
                        $remaining_amount = max(0, $camp['target_amount'] - $camp['collected_amount']);

                        // Determine status badge
                        if ($percent >= 100) {
                            $statusBadge = '<span class="campaign-badge badge-goal-met"><i class="fas fa-trophy"></i> Goal Met</span>';
                        } elseif ($days_remaining <= 7 && $days_remaining > 0) {
                            $statusBadge = '<span class="campaign-badge badge-ending-soon"><i class="fas fa-fire"></i> Ending Soon</span>';
                        } else {
                            $statusBadge = '<span class="campaign-badge badge-active"><i class="fas fa-circle" style="font-size:0.5rem;"></i> Active</span>';
                        }
                    ?>
                    <div class="campaign-card-wrapper"
                         data-title="<?php echo htmlspecialchars(strtolower($camp['name'])); ?>"
                         data-cat="<?php echo htmlspecialchars(strtolower($camp['category_name'])); ?>">
                        <div class="campaign-card">
                            <div class="campaign-img">
                                <?php if (!empty($camp['campaign_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($camp['campaign_image']); ?>" alt="<?php echo htmlspecialchars($camp['name']); ?> campaign image">
                                <?php else: ?>
                                    <div class="campaign-hero-placeholder">
                                        <i class="fas fa-<?php echo htmlspecialchars($camp['category_icon']); ?>" style="color:rgba(255,255,255,0.5);"></i>
                                    </div>
                                <?php endif; ?>
                                <span class="campaign-cat-badge">
                                    <i class="fas <?php echo htmlspecialchars($camp['category_icon']); ?>"></i>
                                    <?php echo htmlspecialchars($camp['category_name']); ?>
                                </span>
                            </div>

                            <div class="campaign-content">
                                <div style="margin-bottom:10px;"><?php echo $statusBadge; ?></div>

                                <h3 class="campaign-title"><?php echo htmlspecialchars($camp['name']); ?></h3>
                                <p class="campaign-desc"><?php echo htmlspecialchars($camp['short_description']); ?></p>

                                <div style="margin-bottom:14px;">
                                    <div style="display:flex; justify-content:space-between; font-size:0.8rem; font-weight:600; margin-bottom:6px;">
                                        <span style="color:var(--primary);"><?php echo formatIndianCurrency($camp['collected_amount']); ?> raised</span>
                                        <span style="color:var(--text-muted);">Goal: <?php echo formatIndianCurrency($camp['target_amount']); ?></span>
                                    </div>
                                    <div class="donor-progress-bar">
                                        <div class="donor-progress-fill" data-progress="<?php echo $percent; ?>"></div>
                                    </div>
                                    <div class="campaign-remaining">
                                        <i class="fas fa-bullseye" style="color:var(--warning);"></i>
                                        <span style="color:var(--warning);"><?php echo formatIndianCurrency($remaining_amount); ?></span> still needed
                                    </div>
                                </div>

                                <div class="campaign-meta">
                                    <span><i class="fas fa-building"></i> <?php echo htmlspecialchars($camp['organization_name']); ?></span>
                                    <span><i class="far fa-clock"></i> <?php echo floor($days_remaining); ?> days left</span>
                                </div>

                                <div style="display:flex; gap:10px; margin-top:auto;">
                                    <a href="donor_campaign_details.php?id=<?php echo $camp['id']; ?>" class="btn-secondary" style="flex:1; text-align:center; text-decoration:none;">Details</a>
                                    <a href="donor_donate.php?campaign_id=<?php echo $camp['id']; ?>" class="btn-primary" style="flex:1; text-align:center; text-decoration:none;">Donate Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <!-- No search results message -->
                    <div id="campaignNoResults" style="padding:48px; text-align:center; color:var(--text-muted); background:white; border-radius:12px; box-shadow:var(--shadow-sm);">
                        <i class="fas fa-search fa-3x" style="opacity:0.25; margin-bottom:14px; display:block;"></i>
                        <strong style="color:var(--text-dark); display:block; margin-bottom:6px;">No campaigns found</strong>
                        <span style="font-size:0.88rem;">Try adjusting your search or category filter.</span>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </main>
</div>
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/donor.js"></script>
</body>
</html>
