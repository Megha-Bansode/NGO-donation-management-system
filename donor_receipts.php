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

$receiptNum = $_GET['receipt'] ?? null;
$viewReceipt = null;

if ($receiptNum) {
    // Validate receipt belongs to this donor
    $stmt = $pdo->prepare("
        SELECT r.*, d.amount, d.transaction_id, d.payment_method, d.donation_date, c.name as campaign_name, u.full_name as donor_name, u.email as donor_email
        FROM donation_receipts r
        JOIN donations d ON r.donation_id = d.id
        LEFT JOIN campaigns c ON d.campaign_id = c.id
        JOIN users u ON d.donor_id = u.id
        WHERE r.receipt_number = ? AND d.donor_id = ?
    ");
    $stmt->execute([$receiptNum, $donor_id]);
    $viewReceipt = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    // List all receipts
    $stmt = $pdo->prepare("
        SELECT r.*, d.amount, c.name as campaign_name
        FROM donation_receipts r
        JOIN donations d ON r.donation_id = d.id
        LEFT JOIN campaigns c ON d.campaign_id = c.id
        WHERE d.donor_id = ?
        ORDER BY r.generated_date DESC
    ");
    $stmt->execute([$donor_id]);
    $receipts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $viewReceipt ? 'Receipt ' . htmlspecialchars($receiptNum) : 'My Receipts'; ?> | <?php echo APP_NAME; ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }
        .receipt-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .receipt-brand {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .receipt-meta {
            text-align: right;
            font-size: 0.9rem;
            color: var(--text-muted);
        }
        .receipt-body {
            margin-bottom: 30px;
        }
        .receipt-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px dashed #e2e8f0;
        }
        .receipt-label {
            color: var(--text-muted);
            font-weight: 600;
        }
        .receipt-val {
            color: var(--text-dark);
            font-weight: 700;
        }
        .receipt-total {
            display: flex;
            justify-content: space-between;
            padding: 20px 0;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-dark);
            background: #f8fafc;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        .receipt-footer {
            text-align: center;
            color: var(--text-muted);
            font-size: 0.85rem;
            padding-top: 20px;
            border-top: 2px solid #f1f5f9;
        }
        @media print {
            .sidebar, .topbar, .page-header, .btn-primary, .btn-secondary, .print-hide {
                display: none !important;
            }
            .dashboard-layout {
                display: block;
            }
            .main-content, .page-content {
                margin: 0; padding: 0; width: 100%;
            }
            .receipt-container {
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>
<body>
<div class="dashboard-layout">
    <?php include __DIR__ . '/includes/dashboard/sidebar.php'; ?>
    <main class="main-content">
        <?php include __DIR__ . '/includes/dashboard/topbar.php'; ?>
        <div class="page-content">
            
            <?php if ($viewReceipt): ?>
                <div class="print-hide" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; max-width: 800px; margin-left: auto; margin-right: auto;">
                    <a href="donor_receipts.php" style="text-decoration: none; color: var(--text-muted); font-size: 0.9rem;">
                        <i class="fas fa-arrow-left"></i> Back to Receipts
                    </a>
                    <button onclick="window.print()" class="btn-primary" style="padding: 8px 15px;">
                        <i class="fas fa-print"></i> Print Receipt
                    </button>
                </div>

                <div class="receipt-container">
                    <div class="receipt-header">
                        <div class="receipt-brand">
                            <i class="fas fa-hands-helping"></i> <?php echo APP_NAME; ?>
                        </div>
                        <div class="receipt-meta">
                            <strong>Receipt No:</strong> <?php echo htmlspecialchars($viewReceipt['receipt_number']); ?><br>
                            <strong>Date:</strong> <?php echo date('F d, Y', strtotime($viewReceipt['generated_date'])); ?>
                        </div>
                    </div>

                    <div class="receipt-body">
                        <div style="margin-bottom: 30px;">
                            <strong style="color: var(--text-dark); display: block; margin-bottom: 5px;">Received From:</strong>
                            <div style="color: var(--text-body);">
                                <?php echo htmlspecialchars($viewReceipt['donor_name']); ?><br>
                                <?php echo htmlspecialchars($viewReceipt['donor_email']); ?>
                            </div>
                        </div>

                        <div class="receipt-row">
                            <span class="receipt-label">Campaign</span>
                            <span class="receipt-val"><?php echo htmlspecialchars($viewReceipt['campaign_name'] ?? 'General Fund'); ?></span>
                        </div>
                        <div class="receipt-row">
                            <span class="receipt-label">Transaction ID</span>
                            <span class="receipt-val"><?php echo htmlspecialchars($viewReceipt['transaction_id']); ?></span>
                        </div>
                        <div class="receipt-row">
                            <span class="receipt-label">Payment Method</span>
                            <span class="receipt-val"><?php echo htmlspecialchars($viewReceipt['payment_method']); ?></span>
                        </div>
                        <div class="receipt-row">
                            <span class="receipt-label">Status</span>
                            <span class="receipt-val" style="color: var(--success);">Completed</span>
                        </div>

                        <div class="receipt-total">
                            <span>Total Donation Amount</span>
                            <span style="color: var(--success);"><?php echo formatIndianCurrency($viewReceipt['amount']); ?></span>
                        </div>
                    </div>

                    <div class="receipt-footer">
                        <p>Thank you for your generous contribution. This receipt is computer generated and does not require a signature.</p>
                        <p style="margin-top: 5px; font-weight: 600;">All donations are tax-deductible to the extent permitted by law.</p>
                    </div>
                </div>

            <?php else: ?>
                <div class="page-header">
                    <div class="page-title">
                        <h1>My Receipts</h1>
                        <p style="color: var(--text-muted); margin-top: 5px;">View and download your official donation receipts.</p>
                    </div>
                </div>

                <div class="glass-card">
                    <?php if (empty($receipts)): ?>
                        <?php render_empty_state('No Receipts', 'No receipts available.', 'fas fa-file-invoice-dollar'); ?>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>Receipt Number</th>
                                        <th>Date Generated</th>
                                        <th>Campaign</th>
                                        <th>Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($receipts as $r): ?>
                                    <tr>
                                        <td>
                                            <a href="donor_receipts.php?receipt=<?php echo urlencode($r['receipt_number']); ?>" style="text-decoration: none; color: var(--primary); font-weight: 600;">
                                                <i class="fas fa-file-pdf" style="margin-right: 5px;"></i> <?php echo htmlspecialchars($r['receipt_number']); ?>
                                            </a>
                                        </td>
                                        <td><span style="font-size: 0.85rem; color: var(--text-muted);"><?php echo date('M d, Y', strtotime($r['generated_date'])); ?></span></td>
                                        <td><span style="color: var(--text-dark);"><?php echo htmlspecialchars($r['campaign_name'] ?? 'General Fund'); ?></span></td>
                                        <td><strong style="color: var(--success);"><?php echo formatIndianCurrency($r['amount']); ?></strong></td>
                                        <td>
                                            <a href="donor_receipts.php?receipt=<?php echo urlencode($r['receipt_number']); ?>" class="btn-secondary" style="padding: 5px 12px; font-size: 0.8rem; text-decoration: none;">View</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
    </main>
</div>
<script src="assets/js/dashboard.js"></script>
</body>
</html>
