<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($basePath)) {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $baseDir = dirname($scriptName);
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

require_once __DIR__ . '/../../api/controllers/AuthController.php';
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
// Keep the main header/navbar visible
include __DIR__ . '/../../includes/header.php';
?>

<div class="auth-page">
    <!-- Branding Sidebar (Desktop) -->
    <div class="auth-sidebar">
        <a href="<?php echo $basePath; ?>" class="logo-container" style="color: white; display: flex; align-items: center; text-decoration: none;">
            <div style="background: white; border-radius: 12px; padding: 6px; display: flex; align-items: center; justify-content: center; margin-right: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                <img src="<?php echo $basePath; ?>assets/images/logo.jpeg"
                     alt="Arohan Foundation Logo"
                     style="height: 46px; width: 46px; object-fit: contain; border-radius: 8px;"
                     onerror="this.parentElement.innerHTML='<i class=\'fa-solid fa-hand-holding-heart\' style=\'font-size:1.5rem;color:#059669;\'></i>';">
            </div>
            <div>
                <span style="font-weight: 700; font-size: 1.3rem; display: block; line-height: 1.2;">Arohan<span style="color:#86efac;">Foundation</span></span>
                <span style="font-size: 0.72rem; color: #cbd5e1; letter-spacing: 0.08em; text-transform: uppercase;">Empowering Lives</span>
            </div>
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
                <div class="alert alert-danger" style="display: block;" id="serverAlert">
                    <i class="fa-solid fa-circle-exclamation me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form id="loginForm" method="POST" action="" novalidate>

                <!-- ── Email Field ── -->
                <div class="form-group">
                    <label class="form-label" for="loginEmail">Email Address</label>
                    <div class="input-container">
                        <i class="fa-solid fa-envelope input-icon"></i>
                        <input
                            type="email"
                            name="email"
                            id="loginEmail"
                            class="form-control"
                            placeholder="user@example.com"
                            value="<?php echo htmlspecialchars($email ?? ''); ?>"
                            autocomplete="email"
                            aria-describedby="errEmail"
                            required>
                    </div>
                    <span class="error-feedback" id="errEmail" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>Please enter a valid email address.
                    </span>
                </div>

                <!-- ── Password Field ── -->
                <div class="form-group">
                    <label class="form-label" for="loginPassword">Password</label>
                    <div class="input-container">
                        <i class="fa-solid fa-lock input-icon"></i>
                        <input
                            type="password"
                            name="password"
                            id="loginPassword"
                            class="form-control"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            aria-describedby="errPassword"
                            required>
                        <button type="button" class="password-toggle" id="passwordToggle" aria-label="Toggle Password Visibility" tabindex="-1">
                            <i class="fa-solid fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                    <span class="error-feedback" id="errPassword" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i><span id="errPasswordText">Password does not meet requirements.</span>
                    </span>

                    <!-- ── Password Strength Meter ── -->
                    <div class="password-strength-wrapper" id="strengthWrapper">
                        <div class="strength-bar-track">
                            <div class="strength-bar-fill" id="strengthBar"></div>
                        </div>
                        <ul class="strength-rules" id="strengthRules">
                            <li id="rule-length"  class="rule"><i class="fa-solid fa-circle-xmark rule-icon rule-fail"></i> At least 8 characters</li>
                            <li id="rule-upper"   class="rule"><i class="fa-solid fa-circle-xmark rule-icon rule-fail"></i> One uppercase letter (A–Z)</li>
                            <li id="rule-lower"   class="rule"><i class="fa-solid fa-circle-xmark rule-icon rule-fail"></i> One lowercase letter (a–z)</li>
                            <li id="rule-number"  class="rule"><i class="fa-solid fa-circle-xmark rule-icon rule-fail"></i> One number (0–9)</li>
                            <li id="rule-special" class="rule"><i class="fa-solid fa-circle-xmark rule-icon rule-fail"></i> One special character (@#$%&!)</li>
                        </ul>
                    </div>
                </div>

                <!-- ── Options Row ── -->
                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" class="checkbox-input" id="rememberMe" name="remember">
                        <span>Remember Me</span>
                    </label>
                    <a href="<?php echo $basePath; ?>forgot-password" class="btn-link">Forgot Password?</a>
                </div>

                <!-- ── Submit Button ── -->
                <button type="submit" class="btn btn-primary btn-block" id="loginBtn" disabled>
                    <span id="btnText"><i class="fa-solid fa-right-to-bracket me-2"></i>Sign In</span>
                    <span id="btnSpinner" style="display:none;">
                        <span class="login-spinner"></span> Signing In…
                    </span>
                </button>

            </form>
        </div>
    </div>
</div>

<!-- ================================================================
     VALIDATION SCRIPT — Fixed & Clean
     Bugs fixed:
       • setFieldState broken ternary never updated error text
       • validatePassword double-wrote #errPassword innerHTML,
         destroying the #errPasswordText child span
       • blur listener always showed "required" before user typed
       • Enter key double-submitted via requestSubmit
================================================================ -->
<script>
(function () {
    'use strict';

    /* ── Cached DOM References ── */
    const loginForm      = document.getElementById('loginForm');
    const emailInput     = document.getElementById('loginEmail');
    const passInput      = document.getElementById('loginPassword');
    const loginBtn       = document.getElementById('loginBtn');
    const btnText        = document.getElementById('btnText');
    const btnSpinner     = document.getElementById('btnSpinner');
    const passToggleBtn  = document.getElementById('passwordToggle');
    const toggleIcon     = document.getElementById('toggleIcon');
    const strengthBar    = document.getElementById('strengthBar');
    const strengthWrapper= document.getElementById('strengthWrapper');
    const errEmailEl     = document.getElementById('errEmail');
    const errPassEl      = document.getElementById('errPassword');

    /* ── Tracks whether the user has interacted with password field ── */
    let passwordTouched = false;

    /* ── Password Rules ── */
    const rules = {
        length : { regex: /.{8,}/,                                          el: document.getElementById('rule-length')  },
        upper  : { regex: /[A-Z]/,                                          el: document.getElementById('rule-upper')   },
        lower  : { regex: /[a-z]/,                                          el: document.getElementById('rule-lower')   },
        number : { regex: /[0-9]/,                                          el: document.getElementById('rule-number')  },
        special: { regex: /[@#$%&!^*()\-_=+\[\]{};:'",.<>?\/\\|`~]/,       el: document.getElementById('rule-special') }
    };

    const strengthColors = ['#ef4444', '#f97316', '#eab308', '#22c55e', '#16a34a'];

    /* ══════════════════════════════════════════════════════════════
       setFieldState — Apply green/red border + show/hide error msg
       FIX: directly set innerHTML on the error element here,
            no broken child-span lookup via ternary.
    ══════════════════════════════════════════════════════════════ */
    function setFieldState(inputEl, errEl, state, message) {
        inputEl.classList.remove('field-valid', 'field-invalid');

        if (state === 'valid') {
            inputEl.classList.add('field-valid');
            errEl.style.display = 'none';
            errEl.innerHTML = '';

        } else if (state === 'invalid') {
            inputEl.classList.add('field-invalid');
            errEl.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i>' + (message || 'This field is required.');
            errEl.style.display = 'block';

        } else {
            /* idle — clear everything */
            errEl.style.display = 'none';
            errEl.innerHTML = '';
        }
    }

    /* ══════════════════════════════════════════════════════════════
       Email Validation
    ══════════════════════════════════════════════════════════════ */
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    function validateEmail() {
        const val = emailInput.value.trim();

        if (val === '') {
            setFieldState(emailInput, errEmailEl, 'invalid', 'Email address is required.');
            return false;
        }
        if (!emailRegex.test(val)) {
            setFieldState(emailInput, errEmailEl, 'invalid', 'Please enter a valid email (e.g. user@example.com).');
            return false;
        }
        setFieldState(emailInput, errEmailEl, 'valid');
        return true;
    }

    /* ══════════════════════════════════════════════════════════════
       Password Strength Meter — update rules + bar (no error logic)
    ══════════════════════════════════════════════════════════════ */
    function updateStrengthMeter(val) {
        strengthWrapper.style.display = val.length > 0 ? 'block' : 'none';

        let passCount = 0;
        for (const key in rules) {
            const ok   = rules[key].regex.test(val);
            const icon = rules[key].el.querySelector('.rule-icon');
            if (ok) {
                passCount++;
                icon.className = 'fa-solid fa-circle-check rule-icon rule-pass';
                rules[key].el.classList.add('rule-passed');
            } else {
                icon.className = 'fa-solid fa-circle-xmark rule-icon rule-fail';
                rules[key].el.classList.remove('rule-passed');
            }
        }

        const pct = (passCount / 5) * 100;
        strengthBar.style.width           = pct + '%';
        strengthBar.style.backgroundColor = strengthColors[passCount - 1] || '#e5e7eb';

        return passCount === 5; // returns true only when ALL rules pass
    }

    /* ══════════════════════════════════════════════════════════════
       Password Validation
       FIX: Single code path for error display — no double innerHTML
            write. showError flag controls whether we mark the field
            state; meter always updates.
    ══════════════════════════════════════════════════════════════ */
    function validatePassword(showError) {
        const val    = passInput.value;            // read CURRENT value
        const allOk  = updateStrengthMeter(val);   // always update meter

        if (!showError) {
            /* Just update meter, don't change border or error text */
            return allOk;
        }

        if (val === '') {
            setFieldState(passInput, errPassEl, 'invalid', 'Password is required.');
            return false;
        }
        if (!allOk) {
            setFieldState(passInput, errPassEl, 'invalid', 'Password does not meet all the requirements listed below.');
            return false;
        }
        setFieldState(passInput, errPassEl, 'valid');
        return true;
    }

    /* ══════════════════════════════════════════════════════════════
       Sync submit-button enabled/disabled state
    ══════════════════════════════════════════════════════════════ */
    function syncSubmitButton() {
        const emailOk = emailRegex.test(emailInput.value.trim());
        const passOk  = Object.values(rules).every(r => r.regex.test(passInput.value));
        loginBtn.disabled = !(emailOk && passOk);
    }

    /* ══════════════════════════════════════════════════════════════
       Real-time Listeners
    ══════════════════════════════════════════════════════════════ */
    emailInput.addEventListener('input', () => {
        validateEmail();
        syncSubmitButton();
    });
    emailInput.addEventListener('blur', validateEmail);

    passInput.addEventListener('input', () => {
        passwordTouched = true;
        /* Show live meter + border feedback while typing */
        validatePassword(passInput.value.length > 0);
        syncSubmitButton();
    });

    /* FIX: Only show "required" error on blur if field was previously
       touched (user typed and cleared it), never on first focus-out */
    passInput.addEventListener('blur', () => {
        if (passwordTouched) {
            validatePassword(true);
        }
    });

    /* ══════════════════════════════════════════════════════════════
       Show / Hide Password Toggle
       FIX: does NOT affect passInput.value — only changes type attr
    ══════════════════════════════════════════════════════════════ */
    passToggleBtn.addEventListener('click', () => {
        const isPassword = passInput.getAttribute('type') === 'password';
        passInput.setAttribute('type', isPassword ? 'text' : 'password');
        toggleIcon.className = isPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
        passInput.focus();
    });

    /* ══════════════════════════════════════════════════════════════
       Form Submit — Final guard + loading spinner
    ══════════════════════════════════════════════════════════════ */
    loginForm.addEventListener('submit', (e) => {
        /* Mark password as touched so errors show on submit attempt */
        passwordTouched = true;

        const emailOk = validateEmail();
        const passOk  = validatePassword(true);

        if (!emailOk || !passOk) {
            e.preventDefault();
            /* Focus the first invalid field */
            if (!emailOk) emailInput.focus();
            else           passInput.focus();
            return;
        }

        /* Show loading spinner */
        btnText.style.display    = 'none';
        btnSpinner.style.display = 'inline-flex';
        loginBtn.disabled        = true;
        loginBtn.style.opacity   = '0.85';
    });

    /* ══════════════════════════════════════════════════════════════
       Enter Key → Submit
       FIX: uses form.requestSubmit() only when button not disabled.
            No double-fire because submit listener is the single
            source of truth.
    ══════════════════════════════════════════════════════════════ */
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && document.activeElement !== loginBtn) {
            if (!loginBtn.disabled) {
                loginForm.requestSubmit();
            }
        }
    });

    /* ══════════════════════════════════════════════════════════════
       Role Quick-fill
       Updated demo passwords all pass the 5 validation rules.
    ══════════════════════════════════════════════════════════════ */
    const demoCredentials = {
        super_admin : { email: 'superadmin@ngo.org', pass: 'Admin@1234'    },
        ngo_admin   : { email: 'ngoadmin@ngo.org',   pass: 'NgoAdmin@1'    },
        volunteer   : { email: 'volunteer@ngo.org',  pass: 'Volunteer#1'   },
        donor       : { email: 'donor@ngo.org',      pass: 'Donor@1234'    },
        coordinator : { email: 'coordinator@ngo.org',pass: 'Coord@1234'    }
    };

    document.getElementById('roleSelector')
        ?.querySelectorAll('.role-btn')
        .forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const creds = demoCredentials[btn.getAttribute('data-role')];
                if (creds) {
                    emailInput.value = creds.email;
                    passInput.value  = creds.pass;
                    passwordTouched  = true;

                    /* Re-run validation so borders and button update */
                    validateEmail();
                    validatePassword(true);
                    syncSubmitButton();
                }
            });
        });

})(); // END IIFE — no globals polluted
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
