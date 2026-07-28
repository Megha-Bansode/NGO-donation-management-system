<?php
session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Middleware.php';
require_once __DIR__ . '/includes/dashboard/components.php';
require_once __DIR__ . '/includes/dashboard/donor_queries.php';
require_once __DIR__ . '/modules/payment/RazorpayService.php';

Middleware::role([3]);

$pdo = getDatabase();
$donor_id = $_SESSION['user_id'];

// Fetch Donor Details for prefill
$stmtUser = $pdo->prepare("SELECT full_name, email, phone FROM users WHERE id = ?");
$stmtUser->execute([$donor_id]);
$donor = $stmtUser->fetch(PDO::FETCH_ASSOC);

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
        } else {
            try {
                $razorpayService = new RazorpayService();
                $receiptId = 'rcpt_' . bin2hex(random_bytes(8));
                
                $orderData = $razorpayService->createOrder($amount, $receiptId, $campaign_id);
                
                $_SESSION['order_created'] = true;
                $_SESSION['order_data'] = $orderData;
                header("Location: donor_donate.php?campaign_id={$campaign_id}&status=order_created");
                exit;
            } catch (Exception $e) {
                $error = "Failed to initiate payment: " . $e->getMessage();
            }
        }
    }
}

// Handle PRG order created state
$orderCreated = false;
$orderData = null;
if (isset($_GET['status']) && $_GET['status'] === 'order_created' && isset($_SESSION['order_created'])) {
    $orderCreated = true;
    $orderData = $_SESSION['order_data'];
    unset($_SESSION['order_created'], $_SESSION['order_data']);
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
        .secure-payment-panel {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .secure-payment-panel:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.15);
            border-color: var(--primary-light);
        }
        .panel-header {
            background: rgba(59, 130, 246, 0.05);
            padding: 15px 20px;
            display: flex;
            align-items: center;
            border-bottom: 2px solid #e2e8f0;
        }
        .panel-header i {
            color: var(--primary);
            font-size: 1.2rem;
            margin-right: 10px;
        }
        .panel-header span {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 1.1rem;
            flex-grow: 1;
        }
        .powered-by {
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        .powered-by strong {
            color: #0b2239; /* Razorpay brand color */
        }
        .panel-body {
            padding: 20px;
        }
        .accepted-text {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .methods-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 15px;
        }
        .method-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 15px 10px;
            background: #f8fafc;
            border-radius: 10px;
            text-align: center;
            gap: 8px;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 0.85rem;
            border: 1px solid transparent;
            transition: all 0.2s;
        }
        .method-item:hover {
            background: white;
            border-color: var(--primary-light);
            color: var(--primary);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .method-item i {
            font-size: 1.5rem;
            color: var(--text-muted);
            transition: color 0.2s;
        }
        .method-item:hover i {
            color: var(--primary);
        }
        .trust-section {
            margin-top: 25px;
            text-align: center;
        }
        .trust-title {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 15px;
            font-weight: 500;
        }
        .trust-badges {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .trust-badges span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-dark);
            background: #f1f5f9;
            padding: 6px 12px;
            border-radius: 20px;
        }
        .trust-badges span i {
            color: var(--success);
        }
        .pulse-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .pulse-hover:hover {
            transform: scale(1.02);
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
                <?php if ($orderCreated): ?>
                    <div class="success-box" style="background: rgba(59,130,246,0.1); border-color: var(--primary);">
                        <i class="fas fa-shield-alt" style="font-size: 4rem; color: var(--primary); margin-bottom: 20px;"></i>
                        <h2 style="color: var(--text-dark); margin: 0 0 15px 0;">Secure Payment Initialized</h2>
                        <p style="color: var(--text-body); margin-bottom: 25px;">Please wait while we redirect you to our secure payment gateway...</p>
                        
                        <div id="payment-status-message" style="margin-top: 20px; font-weight: 500;"></div>
                        
                        <button id="rzp-button1" class="btn-primary" style="display: none;">Complete Payment</button>
                    </div>

                    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
                    <script>
                        var options = {
                            "key": "<?php echo htmlspecialchars(RAZORPAY_KEY_ID); ?>",
                            "amount": "<?php echo htmlspecialchars($orderData['amount']); ?>",
                            "currency": "<?php echo htmlspecialchars($orderData['currency']); ?>",
                            "name": "<?php echo htmlspecialchars(APP_NAME); ?>",
                            "description": "Donation for <?php echo addslashes(htmlspecialchars($campaign['name'])); ?>",
                            "order_id": "<?php echo htmlspecialchars($orderData['id']); ?>",
                            "handler": function (response){
                                document.getElementById('payment-status-message').innerHTML = '<span style="color: var(--success);"><i class="fas fa-spinner fa-spin"></i> Verifying payment...</span>';
                                
                                // Send to backend for verification (Phase 3 requirement)
                                fetch('verify_payment.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify({
                                        razorpay_payment_id: response.razorpay_payment_id,
                                        razorpay_order_id: response.razorpay_order_id,
                                        razorpay_signature: response.razorpay_signature
                                    })
                                })
                                .then(res => res.json())
                                .then(data => {
                                    document.getElementById('payment-status-message').innerHTML = '<span style="color: var(--success);"><i class="fas fa-check-circle"></i> Payment successful! (Verification step pending phase 4)</span>';
                                    setTimeout(() => { window.location.href = 'donor_dashboard.php'; }, 3000);
                                })
                                .catch(err => {
                                    document.getElementById('payment-status-message').innerHTML = '<span style="color: var(--danger);"><i class="fas fa-exclamation-circle"></i> Verification failed. Please contact support.</span>';
                                });
                            },
                            "prefill": {
                                "name": "<?php echo htmlspecialchars($donor['full_name'] ?? ''); ?>",
                                "email": "<?php echo htmlspecialchars($donor['email'] ?? ''); ?>",
                                "contact": "<?php echo htmlspecialchars($donor['phone'] ?? ''); ?>"
                            },
                            "theme": {
                                "color": "#3b82f6"
                            },
                            "modal": {
                                "ondismiss": function(){
                                    document.getElementById('payment-status-message').innerHTML = '<span style="color: var(--warning);"><i class="fas fa-exclamation-triangle"></i> Payment cancelled by user.</span>';
                                    document.getElementById('rzp-button1').style.display = 'inline-block';
                                    document.getElementById('rzp-button1').innerText = 'Retry Payment';
                                }
                            }
                        };
                        
                        var rzp1 = new Razorpay(options);
                        
                        rzp1.on('payment.failed', function (response){
                            document.getElementById('payment-status-message').innerHTML = '<span style="color: var(--danger);"><i class="fas fa-times-circle"></i> Payment Failed: ' + response.error.description + '</span>';
                            document.getElementById('rzp-button1').style.display = 'inline-block';
                            document.getElementById('rzp-button1').innerText = 'Try Again';
                        });

                        document.getElementById('rzp-button1').onclick = function(e){
                            rzp1.open();
                            e.preventDefault();
                        }
                        
                        // Automatically open on load
                        window.onload = function() {
                            rzp1.open();
                        };
                    </script>
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

                                <input type="hidden" name="payment_method" value="Razorpay Checkout">

                                <div class="secure-payment-panel">
                                    <div class="panel-header">
                                        <i class="fas fa-lock"></i>
                                        <span>Secure Payment Options</span>
                                        <div class="powered-by">Powered by <strong>Razorpay</strong></div>
                                    </div>
                                    <div class="panel-body">
                                        <p class="accepted-text">Automatically accept any of the following</p>
                                        <div class="methods-grid">
                                            <div class="method-item"><i class="fas fa-qrcode"></i> UPI & QR</div>
                                            <div class="method-item"><i class="fab fa-google-pay"></i> GPay</div>
                                            <div class="method-item"><i class="fas fa-mobile-alt"></i> PhonePe</div>
                                            <div class="method-item"><i class="fas fa-credit-card"></i> Cards</div>
                                            <div class="method-item"><i class="fas fa-university"></i> Net Banking</div>
                                            <div class="method-item"><i class="fas fa-wallet"></i> Wallets</div>
                                        </div>
                                    </div>
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

                                <div style="margin-top: 40px; text-align: center;">
                                    <button type="submit" class="btn-primary pulse-hover" style="width: 100%; padding: 18px; font-size: 1.3rem; border-radius: 12px; box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);" id="submitBtn">
                                        Proceed to Secure Payment <i class="fas fa-arrow-right" style="margin-left: 10px;"></i>
                                    </button>
                                </div>

                                <div class="trust-section">
                                    <p class="trust-title">Your payment is securely processed by Razorpay.</p>
                                    <div class="trust-badges">
                                        <span><i class="fas fa-shield-alt"></i> 256-bit SSL</span>
                                        <span><i class="fas fa-check-circle"></i> PCI DSS Compliant</span>
                                        <span><i class="fas fa-lock"></i> Secure UPI Transactions</span>
                                    </div>
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
