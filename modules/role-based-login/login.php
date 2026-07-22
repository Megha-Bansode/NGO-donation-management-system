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

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    $role = $_SESSION['user']['role'];
    switch ($role) {
        case 'super_admin': header('Location: ' . $basePath . 'dashboard/super-admin'); exit;
        case 'ngo_admin': header('Location: ' . $basePath . 'dashboard/ngo-admin'); exit;
        case 'volunteer': header('Location: ' . $basePath . 'dashboard/volunteer'); exit;
        case 'donor': header('Location: ' . $basePath . 'dashboard/donor'); exit;
        case 'event_coordinator': header('Location: ' . $basePath . 'dashboard/event-coordinator'); exit;
    }
}

require_once __DIR__ . '/../../../backend/controllers/AuthController.php';
use Backend\Controllers\AuthController;

$error = '';
$success = '';

// Handle Login Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "Please fill in all credentials.";
    } else {
        $user = AuthController::login($email, $password);
        if ($user) {
            $_SESSION['user'] = $user;
            // Check if there was a redirect path requested
            $redirect = $_GET['redirect'] ?? '';
            if (!empty($redirect)) {
                header('Location: ' . urldecode($redirect));
            } else {
                // Route to appropriate dashboard
                switch ($user['role']) {
                    case 'super_admin': header('Location: ' . $basePath . 'dashboard/super-admin'); exit;
                    case 'ngo_admin': header('Location: ' . $basePath . 'dashboard/ngo-admin'); exit;
                    case 'volunteer': header('Location: ' . $basePath . 'dashboard/volunteer'); exit;
                    case 'donor': header('Location: ' . $basePath . 'dashboard/donor'); exit;
                    case 'event_coordinator': header('Location: ' . $basePath . 'dashboard/event-coordinator'); exit;
                }
            }
            exit;
        } else {
            $error = "Invalid email or password combination.";
        }
    }
}

$pageTitle = "Role-Based Login";
include __DIR__ . '/../../components/header.php';
?>

<div class="auth-page">
    <!-- Branding Sidebar (Desktop) -->
    <div class="auth-sidebar">
        <a href="<?php echo $basePath; ?>" class="logo-container" style="color: white; display: flex; align-items: center; text-decoration: none;">
            <img src="<?php echo $basePath; ?>assets/images/logo.png" alt="Arohan Foundation Logo" style="height: 40px; width: auto; object-fit: contain; margin-right: 10px;" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
            <div class="logo-icon d-none" style="background: white; color: var(--primary);">
                <i class="fa-solid fa-hand-holding-heart"></i>
            </div>
            <span style="font-weight: 700; font-size: 1.25rem;">Arohan Foundation</span>
        </a>
        
        <div>
            <blockquote class="auth-sidebar-quote">
                "Every act of kindness creates a ripple of positive change. Thank you for empowering communities with Arohan Foundation."
            </blockquote>
            <p class="auth-sidebar-author">— Executive Board, Arohan Foundation</p>
        </div>
        
        <div style="font-size: 0.85rem; color: #94a3b8;">
            &copy; <?php echo date('Y'); ?> Arohan Foundation Portal.
        </div>
    </div>

    <!-- Login Content Panel -->
    <div class="auth-content">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo-icon" style="margin: 0 auto 16px;">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <h2 class="auth-title">Welcome Back</h2>
                <p class="text-muted">Choose your role or login with your credentials</p>
            </div>

            <!-- Developer helper role quick-select buttons -->
            <div class="role-selector" id="roleSelector">
                <button type="button" class="role-btn" data-role="donor" title="Donor Profile">
                    <i class="fa-solid fa-heart"></i>
                    Donor
                </button>
                <button type="button" class="role-btn" data-role="volunteer" title="Volunteer Profile">
                    <i class="fa-solid fa-hands-helping"></i>
                    Volunteer
                </button>
                <button type="button" class="role-btn" data-role="coordinator" title="Coordinator Profile">
                    <i class="fa-solid fa-calendar-check"></i>
                    Event Coord
                </button>
                <button type="button" class="role-btn" data-role="ngo_admin" title="NGO Admin Profile">
                    <i class="fa-solid fa-user-shield"></i>
                    NGO Admin
                </button>
                <button type="button" class="role-btn" data-role="super_admin" title="Super Admin Profile">
                    <i class="fa-solid fa-users-gear"></i>
                    Super Admin
                </button>
            </div>

            <!-- Message Alerts -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" style="display: block;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form id="loginForm" method="POST" action="" onsubmit="return validateLoginForm(event)">
                <div class="form-group">
                    <label class="form-label" for="loginEmail">Email Address</label>
                    <div class="input-container">
                        <i class="fa-solid fa-envelope input-icon"></i>
                        <input type="email" name="email" id="loginEmail" class="form-control" placeholder="user@example.com" value="<?php echo htmlspecialchars($email ?? ''); ?>">
                    </div>
                    <span class="error-feedback" id="errEmail">Please enter a valid email address</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="loginPassword">Password</label>
                    <div class="input-container">
                        <i class="fa-solid fa-lock input-icon"></i>
                        <input type="password" name="password" id="loginPassword" class="form-control" placeholder="••••••••">
                        <button type="button" class="password-toggle" id="passwordToggle" aria-label="Toggle Password Visibility">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <span class="error-feedback" id="errPassword">Password must be at least 6 characters</span>
                </div>

                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" class="checkbox-input">
                        <span>Remember Me</span>
                    </label>
                    <a href="<?php echo $basePath; ?>forgot-password" class="btn-link">Forgot Password?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Sign In</button>
            </form>
        </div>
    </div>
</div>

<script>
// Mock Credentials Mapping for quick-filling
const demoCredentials = {
    super_admin: { email: 'superadmin@ngo.org', pass: 'admin123' },
    ngo_admin: { email: 'ngoadmin@ngo.org', pass: 'ngo123' },
    volunteer: { email: 'volunteer@ngo.org', pass: 'volunteer123' },
    donor: { email: 'donor@ngo.org', pass: 'donor123' },
    coordinator: { email: 'coordinator@ngo.org', pass: 'coord123' }
};

document.addEventListener('DOMContentLoaded', () => {
    const roleSelector = document.getElementById('roleSelector');
    const emailInput = document.getElementById('loginEmail');
    const passInput = document.getElementById('loginPassword');
    const passwordToggle = document.getElementById('passwordToggle');
    
    // Quick-Fill credentials when clicking role cards
    if (roleSelector) {
        roleSelector.querySelectorAll('.role-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                // Clear active states
                roleSelector.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
                
                // Set active
                btn.classList.add('active');
                
                // Get role info and fill form
                const role = btn.getAttribute('data-role');
                if (demoCredentials[role]) {
                    emailInput.value = demoCredentials[role].email;
                    passInput.value = demoCredentials[role].pass;
                    
                    // Clear error feedbacks if populated
                    document.querySelectorAll('.error-feedback').forEach(el => el.style.display = 'none');
                }
            });
        });
    }

    // Toggle Password Visibility
    if (passwordToggle) {
        passwordToggle.addEventListener('click', () => {
            const type = passInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passInput.setAttribute('type', type);
            
            const icon = passwordToggle.querySelector('i');
            if (type === 'text') {
                icon.className = 'fa-solid fa-eye-slash';
            } else {
                icon.className = 'fa-solid fa-eye';
            }
        });
    }
});

// Client side validation
function validateLoginForm(event) {
    const email = document.getElementById('loginEmail');
    const pass = document.getElementById('loginPassword');
    let isValid = true;

    // Reset error feedbacks
    document.querySelectorAll('.error-feedback').forEach(el => el.style.display = 'none');

    // Check email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email.value.trim())) {
        document.getElementById('errEmail').style.display = 'block';
        isValid = false;
    }

    // Check password
    if (pass.value.length < 6) {
        document.getElementById('errPassword').style.display = 'block';
        isValid = false;
    }

    return isValid;
}
</script>

<?php include __DIR__ . '/../../components/footer.php'; ?>
