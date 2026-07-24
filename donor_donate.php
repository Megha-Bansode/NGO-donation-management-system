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
$campaign_id = isset($_GET['campaign_id']) ? (int)$_GET['campaign_id'] : 0;

$campaign = getCampaignDetails($pdo, $campaign_id);

if (!$campaign) {
    header("Location: donor_campaigns.php");
    exit;
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = false;
$receipt = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = "Invalid security token. Please try again.";
    } else {
        $amount = (float)($_POST['amount'] ?? 0);
        if (isset($_POST['custom_amount']) && !empty($_POST['custom_amount'])) {
            $amount = (float)$_POST['custom_amount'];
        }

        $payment_method = $_POST['payment_method'] ?? '';
        $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;
        $message = trim($_POST['message'] ?? '');

        if ($amount <= 0) {
            $error = "Please enter a valid donation amount greater than zero.";
        } elseif (empty($payment_method)) {
            $error = "Please select a payment method.";
        } else {
            // Process via Transaction
            $result = process_donation($pdo, $donor_id, $campaign_id, $amount, $payment_method, $is_anonymous, $message);
            
            if ($result['success']) {
                // Post/Redirect/Get to prevent resubmission
                $_SESSION['donation_success'] = true;
                $_SESSION['donation_receipt'] = $result['receipt'];
                header("Location: donor_donate.php?campaign_id={$campaign_id}&status=success");
                exit;
            } else {
                $error = $result['error'] ?? 'An error occurred during payment processing.';
            }
        }
    }
}

// Handle PRG success state
if (isset($_GET['status']) && $_GET['status'] === 'success' && isset($_SESSION['donation_success'])) {
    $success = true;
    $receipt = $_SESSION['donation_receipt'];
    unset($_SESSION['donation_success'], $_SESSION['donation_receipt']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate to <?php echo htmlspecialchars($campaign['name']); ?> | <?php echo APP_NAME; ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        .donation-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .preset-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .preset-btn {
            background: white;
            border: 2px solid #e2e8f0;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text-dark);
            cursor: pointer;
            transition: all 0.2s;
        }
        .preset-btn:hover {
            border-color: var(--primary-light);
            background: #f8fafc;
        }
        .preset-btn.active {
            border-color: var(--primary);
            background: rgba(59,130,246,0.05);
            color: var(--primary);
        }
        .custom-amount-wrapper {
            position: relative;
            margin-bottom: 30px;
        }
        .custom-amount-wrapper span {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-muted);
        }
        .custom-amount-input {
            width: 100%;
            padding: 20px 20px 20px 40px;
            font-size: 1.5rem;
            font-weight: 700;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            transition: border-color 0.2s;
            font-family: inherit;
        }
        .custom-amount-input:focus {
            outline: none;
            border-color: var(--primary);
        }
        .payment-methods {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 30px;
        }
        .pm-option {
            background: white;
            border: 2px solid #e2e8f0;
            padding: 15px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .pm-option input[type="radio"] {
            display: none;
        }
        .pm-option:hover {
            border-color: var(--primary-light);
        }
        .pm-option.active {
            border-color: var(--primary);
            background: rgba(59,130,246,0.05);
        }
        .success-box {
            background: rgba(16,185,129,0.1);
            border: 2px solid var(--success);
            padding: 40px;
            border-radius: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="dashboard-layout">
    <?php include __DIR__ . '/includes/dashboard/sidebar.php'; ?>
    <main class="main-content">
        <?php include __DIR__ . '/includes/dashboard/topbar.php'; ?>
        <div class="page-content">
            
            <a href="donor_campaign_details.php?id=<?php echo $campaign['id']; ?>" style="display: inline-block; margin-bottom: 15px; text-decoration: none; color: var(--text-muted); font-size: 0.9rem;">
                <i class="fas fa-arrow-left"></i> Back to Campaign
            </a>

            <div class="donation-container">
                <?php if ($success): ?>
                    <div class="success-box">
                        <i class="fas fa-check-circle" style="font-size: 4rem; color: var(--success); margin-bottom: 20px;"></i>
                        <h2 style="color: var(--text-dark); margin: 0 0 15px 0;">Thank You for Your Donation!</h2>
                        <p style="color: var(--text-body); margin-bottom: 25px;">Your contribution has been securely processed and applied to the <strong><?php echo htmlspecialchars($campaign['name']); ?></strong> campaign.</p>
                        
                        <div style="background: white; display: inline-block; padding: 15px 30px; border-radius: 10px; margin-bottom: 25px;">
                            <span style="font-size: 0.85rem; color: var(--text-muted); display: block; margin-bottom: 5px;">Receipt Number</span>
                            <strong style="font-size: 1.2rem; color: var(--text-dark);"><?php echo htmlspecialchars($receipt); ?></strong>
                        </div>

                        <div>
                            <a href="donor_receipts.php" class="btn-primary" style="text-decoration: none; display: inline-block; margin-right: 15px;">View Receipt</a>
                            <a href="donor_dashboard.php" class="btn-secondary" style="text-decoration: none; display: inline-block;">Return to Dashboard</a>
                        </div>
                    </div>
                <?php else: ?>

                    <?php if ($campaign['status'] !== 'active'): ?>
                        <div class="glass-card" style="text-align: center; padding: 40px;">
                            <i class="fas fa-exclamation-triangle fa-3x" style="color: var(--warning); margin-bottom: 20px;"></i>
                            <h2>Campaign Inactive</h2>
                            <p style="color: var(--text-muted);">This campaign is currently marked as <strong><?php echo htmlspecialchars($campaign['status']); ?></strong> and is not accepting new donations.</p>
                        </div>
                    <?php else: ?>
                        
                        <div class="glass-card" style="margin-bottom: 20px; padding: 20px; background: rgba(59,130,246,0.05); border-left: 4px solid var(--primary);">
                            <h3 style="margin: 0 0 5px 0; color: var(--text-dark); font-size: 1.1rem;">You are donating to:</h3>
                            <strong style="color: var(--primary); font-size: 1.2rem;"><?php echo htmlspecialchars($campaign['name']); ?></strong>
                        </div>

                        <?php if ($error): ?>
                            <div style="background: rgba(239,68,68,0.1); color: var(--danger); padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
                                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <div class="glass-card">
                            <form action="" method="POST" id="donationForm">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="amount" id="presetAmount" value="">

                                <h3 style="margin-top: 0; color: var(--text-dark); margin-bottom: 20px;">Select Amount</h3>
                                
                                <div class="preset-grid">
                                    <div class="preset-btn" data-amount="50">₹50</div>
                                    <div class="preset-btn" data-amount="100">₹100</div>
                                    <div class="preset-btn" data-amount="250">₹250</div>
                                    <div class="preset-btn" data-amount="500">₹500</div>
                                    <div class="preset-btn" data-amount="1000">₹1000</div>
                                    <div class="preset-btn" data-amount="custom">Custom</div>
                                </div>

                                <div class="custom-amount-wrapper" id="customAmountWrapper" style="display: none;">
                                    <span>₹</span>
                                    <input type="number" name="custom_amount" class="custom-amount-input" placeholder="0.00" min="1" step="0.01" id="customAmountInput">
                                </div>

                                <h3 style="color: var(--text-dark); margin-bottom: 20px;">Payment Method</h3>
                                <div class="payment-methods">
                                    <label class="pm-option">
                                        <input type="radio" name="payment_method" value="Credit Card" required>
                                        <i class="fas fa-credit-card" style="font-size: 1.5rem; color: var(--text-dark);"></i>
                                        <div>
                                            <strong style="display: block;">Credit/Debit Card</strong>
                                            <span style="font-size: 0.75rem; color: var(--text-muted);">Secure SSL Processing</span>
                                        </div>
                                    </label>
                                    <label class="pm-option">
                                        <input type="radio" name="payment_method" value="PayPal" required>
                                        <i class="fab fa-paypal" style="font-size: 1.5rem; color: #003087;"></i>
                                        <div>
                                            <strong style="display: block;">PayPal</strong>
                                            <span style="font-size: 0.75rem; color: var(--text-muted);">Redirects to PayPal</span>
                                        </div>
                                    </label>
                                    <label class="pm-option">
                                        <input type="radio" name="payment_method" value="Bank Transfer" required>
                                        <i class="fas fa-university" style="font-size: 1.5rem; color: var(--text-dark);"></i>
                                        <div>
                                            <strong style="display: block;">Bank Transfer</strong>
                                            <span style="font-size: 0.75rem; color: var(--text-muted);">Direct deposit</span>
                                        </div>
                                    </label>
                                </div>

                                <h3 style="color: var(--text-dark); margin-bottom: 20px;">Additional Options</h3>
                                
                                <div class="form-group" style="margin-bottom: 20px;">
                                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                        <input type="checkbox" name="is_anonymous" value="1" style="width: 20px; height: 20px;">
                                        <span style="font-weight: 600; color: var(--text-dark);">Make this donation anonymous</span>
                                    </label>
                                    <p style="font-size: 0.8rem; color: var(--text-muted); margin: 5px 0 0 30px;">Your name will not appear on the public donor wall for this campaign.</p>
                                </div>

                                <div class="form-group">
                                    <label for="message">Leave a message of support (Optional)</label>
                                    <textarea name="message" id="message" rows="3" class="form-control" placeholder="E.g., Keep up the great work!"></textarea>
                                </div>

                                <div style="margin-top: 30px;">
                                    <button type="submit" class="btn-primary" style="width: 100%; padding: 15px; font-size: 1.2rem;" id="submitBtn">
                                        <i class="fas fa-lock"></i> Complete Donation
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

        </div>
    </main>
</div>

<script>
    // JS for donation interactions
    document.addEventListener('DOMContentLoaded', () => {
        const presetBtns = document.querySelectorAll('.preset-btn');
        const customWrapper = document.getElementById('customAmountWrapper');
        const presetInput = document.getElementById('presetAmount');
        const customInput = document.getElementById('customAmountInput');
        
        presetBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                presetBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                const amt = btn.dataset.amount;
                if (amt === 'custom') {
                    customWrapper.style.display = 'block';
                    presetInput.value = '';
                    customInput.focus();
                } else {
                    customWrapper.style.display = 'none';
                    presetInput.value = amt;
                    customInput.value = '';
                }
            });
        });

        const pmOptions = document.querySelectorAll('.pm-option');
        pmOptions.forEach(opt => {
            const radio = opt.querySelector('input[type="radio"]');
            opt.addEventListener('click', () => {
                pmOptions.forEach(o => o.classList.remove('active'));
                opt.classList.add('active');
                radio.checked = true;
            });
        });

        // Form validation
        const form = document.getElementById('donationForm');
        if (form) {
            form.addEventListener('submit', (e) => {
                const pVal = presetInput.value;
                const cVal = customInput.value;
                if (!pVal && !cVal) {
                    e.preventDefault();
                    alert('Please select or enter a donation amount.');
                    return;
                }
                const paymentSelected = document.querySelector('input[name="payment_method"]:checked');
                if (!paymentSelected) {
                    e.preventDefault();
                    alert('Please select a payment method.');
                    return;
                }
                // Optional: disable button to prevent double click
                document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                document.getElementById('submitBtn').style.opacity = '0.7';
                document.getElementById('submitBtn').style.pointerEvents = 'none';
            });
        }
    });
</script>
<script src="assets/js/dashboard.js"></script>
</body>
</html>
