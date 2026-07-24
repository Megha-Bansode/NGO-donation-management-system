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
$campaign_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$campaign = getCampaignDetails($pdo, $campaign_id);

if (!$campaign) {
    header("Location: donor_campaigns.php");
    exit;
}

// Fetch recent public donors for this campaign
$stmtDonors = $pdo->prepare("
    SELECT u.full_name, d.amount, d.donation_date 
    FROM donations d
    JOIN users u ON d.donor_id = u.id
    WHERE d.campaign_id = ? AND d.payment_status = 'completed' AND d.is_anonymous = 0
    ORDER BY d.donation_date DESC 
    LIMIT 5
");
$stmtDonors->execute([$campaign_id]);
$recentDonors = $stmtDonors->fetchAll(PDO::FETCH_ASSOC);

// Fetch total donors count
$stmtCount = $pdo->prepare("SELECT COUNT(DISTINCT donor_id) FROM donations WHERE campaign_id = ? AND payment_status = 'completed'");
$stmtCount->execute([$campaign_id]);
$donorCount = (int)$stmtCount->fetchColumn();

$percent = $campaign['target_amount'] > 0 ? ($campaign['collected_amount'] / $campaign['target_amount']) * 100 : 0;
$percent = min(100, round($percent, 1));
$days_remaining = max(0, (strtotime($campaign['end_date']) - time()) / (60 * 60 * 24));
$remaining_amount = max(0, $campaign['target_amount'] - $campaign['collected_amount']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($campaign['name']); ?> | <?php echo APP_NAME; ?></title>
    <meta name="description" content="<?php echo htmlspecialchars(substr($campaign['short_description'] ?? $campaign['description'] ?? '', 0, 160)); ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/donor.css">
    <style>
        .detail-hero {
            position: relative;
            height: 360px;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 2rem;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
        }
        .detail-hero img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .detail-hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.82) 0%, rgba(0,0,0,0.2) 60%, transparent 100%);
            display: flex;
            align-items: flex-end;
            padding: 2rem 2.5rem;
        }
        .detail-title {
            color: white;
            font-size: 2rem;
            margin: 0 0 10px;
            line-height: 1.3;
            font-family: var(--font-heading), sans-serif;
        }
        .detail-meta {
            color: rgba(255,255,255,0.8);
            font-size: 0.88rem;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .grid-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            align-items: start;
        }
        @media (max-width: 992px) {
            .grid-layout { grid-template-columns: 1fr; }
        }
        .progress-box {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: var(--shadow-md);
            position: sticky;
            top: 90px;
            border: 1px solid rgba(0,0,0,0.04);
        }
        .stat-grid-4 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }
        .stat-item {
            text-align: center;
            background: #f8fafc;
            padding: 14px 10px;
            border-radius: 12px;
            border: 1px solid rgba(0,0,0,0.04);
        }
        .stat-val {
            display: block;
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-dark);
            font-family: var(--font-stats);
        }
        .stat-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 500;
            margin-top: 3px;
        }
        .donor-list { list-style:none; padding:0; margin:0; }
        .donor-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .donor-item:last-child { border-bottom: none; }
        .donor-info { display:flex; align-items:center; gap:12px; }
        .donor-avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            flex-shrink: 0;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 16px;
            text-decoration: none;
            color: var(--text-muted);
            font-size: 0.88rem;
            font-weight: 600;
            transition: color 0.2s;
        }
        .back-link:hover { color: var(--primary); }
    </style>
</head>
<body data-donor-page="campaign-details">
<div class="dashboard-layout">
    <?php include __DIR__ . '/includes/dashboard/sidebar.php'; ?>
    <main class="main-content">
        <?php include __DIR__ . '/includes/dashboard/topbar.php'; ?>
        <div class="page-content">

            <a href="donor_campaigns.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Campaigns
            </a>

            <!-- Hero Banner -->
            <div class="detail-hero">
                <?php if (!empty($campaign['campaign_image'])): ?>
                    <img src="<?php echo htmlspecialchars($campaign['campaign_image']); ?>" alt="<?php echo htmlspecialchars($campaign['name']); ?>">
                <?php else: ?>
                    <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg, var(--primary-dark), var(--primary));">
                        <i class="fas <?php echo htmlspecialchars($campaign['category_icon']); ?> fa-6x" style="color:rgba(255,255,255,0.2);"></i>
                    </div>
                <?php endif; ?>
                <div class="detail-hero-overlay">
                    <div style="width:100%;">
                        <div style="margin-bottom:10px; display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                            <span style="background:var(--primary); color:white; padding:4px 12px; border-radius:20px; font-size:0.78rem; font-weight:700; backdrop-filter:blur(4px);">
                                <i class="fas <?php echo htmlspecialchars($campaign['category_icon']); ?>"></i>
                                <?php echo htmlspecialchars($campaign['category_name']); ?>
                            </span>
                            <?php if ($campaign['status'] !== 'active'): ?>
                                <span style="background:var(--danger); color:white; padding:4px 12px; border-radius:20px; font-size:0.78rem; font-weight:700;">
                                    <?php echo ucfirst(htmlspecialchars($campaign['status'])); ?>
                                </span>
                            <?php elseif ($days_remaining <= 7): ?>
                                <span class="campaign-badge badge-ending-soon"><i class="fas fa-fire"></i> Ending Soon</span>
                            <?php endif; ?>
                        </div>
                        <h1 class="detail-title"><?php echo htmlspecialchars($campaign['name']); ?></h1>
                        <div class="detail-meta">
                            <span><i class="fas fa-building"></i> <?php echo htmlspecialchars($campaign['organization_name']); ?></span>
                            <span><i class="far fa-calendar-alt"></i> Started <?php echo date('M d, Y', strtotime($campaign['start_date'])); ?></span>
                            <span><i class="far fa-calendar-check"></i> Ends <?php echo date('M d, Y', strtotime($campaign['end_date'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid-layout">
                <!-- Left: Description & Supporters -->
                <div>
                    <div class="glass-card" style="margin-bottom:2rem;">
                        <h3 style="margin:0 0 16px; color:var(--text-dark); font-size:1.1rem; border-bottom:2px solid #f1f5f9; padding-bottom:12px;">
                            <i class="fas fa-info-circle" style="color:var(--primary); margin-right:8px;"></i>About this Campaign
                        </h3>
                        <div style="line-height:1.85; color:var(--text-body); font-size:0.95rem;">
                            <?php echo nl2br(htmlspecialchars($campaign['description'])); ?>
                        </div>
                    </div>

                    <?php if (!empty($recentDonors)): ?>
                    <div class="glass-card">
                        <h3 style="margin:0 0 16px; color:var(--text-dark); font-size:1.1rem; border-bottom:2px solid #f1f5f9; padding-bottom:12px;">
                            <i class="fas fa-heart" style="color:var(--danger); margin-right:8px;"></i>Recent Supporters
                        </h3>
                        <ul class="donor-list">
                            <?php foreach($recentDonors as $donor): ?>
                                <li class="donor-item">
                                    <div class="donor-info">
                                        <div class="donor-avatar">
                                            <?php echo strtoupper(substr($donor['full_name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <strong style="color:var(--text-dark); display:block; font-size:0.9rem;"><?php echo htmlspecialchars($donor['full_name']); ?></strong>
                                            <span style="font-size:0.75rem; color:var(--text-muted);">
                                                <i class="far fa-calendar"></i>
                                                <?php echo date('M d, Y', strtotime($donor['donation_date'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <strong style="color:var(--success); font-size:0.95rem;"><?php echo formatIndianCurrency($donor['amount']); ?></strong>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Right: Progress & Donate -->
                <div>
                    <div class="progress-box">
                        <div style="margin-bottom:12px;">
                            <span style="font-size:1.6rem; font-weight:800; color:var(--text-dark); font-family:var(--font-stats);">
                                <?php echo formatIndianCurrency($campaign['collected_amount']); ?>
                            </span>
                            <span style="font-size:0.9rem; color:var(--text-muted); font-weight:500;"> raised of <?php echo formatIndianCurrency($campaign['target_amount']); ?></span>
                        </div>

                        <div class="donor-progress-bar-lg">
                            <div class="donor-progress-fill-lg" data-progress="<?php echo $percent; ?>"></div>
                        </div>

                        <!-- 4-stat grid -->
                        <div class="stat-grid-4">
                            <div class="stat-item">
                                <span class="stat-val"><?php echo $percent; ?>%</span>
                                <span class="stat-label">Funded</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-val"><?php echo floor($days_remaining); ?></span>
                                <span class="stat-label">Days Left</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-val"><?php echo $donorCount; ?></span>
                                <span class="stat-label">Donors</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-val" style="color:var(--warning); font-size:1rem;"><?php echo formatIndianCurrency($remaining_amount); ?></span>
                                <span class="stat-label">Still Needed</span>
                            </div>
                        </div>

                        <?php if ($campaign['status'] === 'active'): ?>
                            <a href="donor_donate.php?campaign_id=<?php echo $campaign['id']; ?>" class="btn-primary"
                               style="display:block; text-align:center; text-decoration:none; padding:14px; font-size:1.05rem; border-radius:12px; margin-bottom:14px;">
                                <i class="fas fa-heart"></i> Donate Now
                            </a>
                        <?php else: ?>
                            <div style="background:#f8fafc; color:var(--text-muted); text-align:center; padding:14px; border-radius:12px; font-weight:600; margin-bottom:14px;">
                                <i class="fas fa-lock"></i> Campaign not accepting donations
                            </div>
                        <?php endif; ?>

                        <div style="text-align:center; font-size:0.8rem; color:var(--text-muted);">
                            <i class="fas fa-shield-alt" style="color:var(--success);"></i>
                            All payments are securely processed.
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/donor.js"></script>
</body>
</html>
