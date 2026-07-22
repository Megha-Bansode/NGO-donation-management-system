<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($basePath)) {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $baseDir = dirname(dirname($scriptName));
    $baseDir = str_replace('\\', '/', $baseDir);
    $basePath = rtrim($baseDir, '/') . '/';
}

require_once __DIR__ . '/../../api/controllers/AuthController.php';
use Backend\Controllers\AuthController;

$error = '';
$success = '';

// Handle Password Recovery Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identity = $_POST['identity'] ?? '';
    
    if (empty($identity)) {
        $error = "Please enter your email address or username.";
    } else {
        $isValid = AuthController::validateResetRequest($identity);
        if ($isValid) {
            $success = "A password recovery link has been sent to " . htmlspecialchars($identity) . ". Please check your inbox.";
        } else {
            $error = "We couldn't find an account matching that username or email.";
        }
    }
}

$pageTitle = "Forgot Password";
include __DIR__ . '/../../includes/header.php';
?>

<div class="auth-page" style="justify-content: center; align-items: center; padding: 40px; background-color: var(--bg-main);">
    <div class="auth-card" style="margin: 0;">
        <div class="auth-header">
            <div class="logo-icon" style="margin: 0 auto 16px; background-color: rgba(234, 88, 12, 0.1); color: var(--secondary);">
                <i class="fa-solid fa-key"></i>
            </div>
            <h2 class="auth-title">Recover Password</h2>
            <p class="text-muted">Enter your email or username to receive a reset link</p>
        </div>

        <!-- Alert Notification -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="display: block;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success" style="display: block;"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form id="forgotForm" method="POST" action="" onsubmit="return validateForgotForm(event)">
            <div class="form-group">
                <label class="form-label" for="identity">Username or Email Address</label>
                <div class="input-container">
                    <i class="fa-solid fa-envelope input-icon"></i>
                    <input type="text" name="identity" id="identity" class="form-control" placeholder="user@example.com" value="<?php echo htmlspecialchars($_POST['identity'] ?? ''); ?>">
                </div>
                <span class="error-feedback" id="errIdentity">Please enter your username or email</span>
            </div>

            <button type="submit" class="btn btn-secondary btn-block" style="margin-top: 12px; margin-bottom: 20px;">Send Recovery Link</button>
            
            <div class="text-center" style="font-size: 0.9rem;">
                <a href="<?php echo $basePath; ?>login" class="btn-link"><i class="fa-solid fa-arrow-left"></i> Back to Sign In</a>
            </div>
        </form>
    </div>
</div>

<script>
function validateForgotForm(event) {
    const identity = document.getElementById('identity');
    let isValid = true;

    // Reset error feedbacks
    document.getElementById('errIdentity').style.display = 'none';

    if (identity.value.trim() === '') {
        document.getElementById('errIdentity').style.display = 'block';
        isValid = false;
    }

    return isValid;
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
