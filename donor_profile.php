<?php
session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Middleware.php';
require_once __DIR__ . '/includes/dashboard/components.php';

Middleware::role([3]);

$pdo = getDatabase();
$donor_id = $_SESSION['user_id'];

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $phone     = trim($_POST['phone']     ?? '');
    $address   = trim($_POST['address']   ?? '');
    $bio       = trim($_POST['bio']       ?? '');

    // Validation
    if (empty($full_name)) {
        $error = "Full Name is required.";
    } elseif (mb_strlen($full_name) < 2 || mb_strlen($full_name) > 100) {
        $error = "Full Name must be between 2 and 100 characters.";
    } elseif (!empty($phone) && !preg_match('/^[6-9]\d{9}$/', $phone)) {
        $error = "Please enter a valid 10-digit Indian mobile number starting with 6–9.";
    } elseif (mb_strlen($bio) > 500) {
        $error = "About Me must not exceed 500 characters.";
    } else {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET full_name = ?, phone = ?, address = ?, bio = ?, updated_at = NOW()
            WHERE id = ?
        ");
        if ($stmt->execute([$full_name, $phone, $address, $bio, $donor_id])) {
            $_SESSION['full_name'] = $full_name;
            $message = "Profile updated successfully!";
        } else {
            $error = "Failed to update profile. Please try again.";
        }
    }
}

// Fetch current details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$donor_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$initials = strtoupper(substr($user['full_name'] ?? 'D', 0, 1));
if (!empty($user['full_name']) && str_contains($user['full_name'], ' ')) {
    $parts = explode(' ', trim($user['full_name']));
    $initials = strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | <?php echo APP_NAME; ?></title>
    <meta name="description" content="Manage your donor account details, contact information, and preferences.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/donor.css">
</head>
<body data-donor-page="profile">
<div class="dashboard-layout">
    <?php include __DIR__ . '/includes/dashboard/sidebar.php'; ?>
    <main class="main-content">
        <?php include __DIR__ . '/includes/dashboard/topbar.php'; ?>
        <div class="page-content">

            <div class="page-header">
                <div class="page-title">
                    <h1>My Profile</h1>
                    <p style="color:var(--text-muted); margin-top:6px;">Manage your account details and preferences.</p>
                </div>
            </div>

            <div style="max-width:820px;">

                <?php if ($message): ?>
                    <div class="donor-alert donor-alert-success" role="alert">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="donor-alert donor-alert-error" role="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <!-- Avatar Section -->
                <div class="donor-avatar-section">
                    <div class="donor-avatar-circle" id="avatarCircle" aria-label="Profile initials"><?php echo htmlspecialchars($initials); ?></div>
                    <div class="donor-avatar-name" id="avatarName"><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></div>
                    <div class="donor-avatar-email"><?php echo htmlspecialchars($user['email'] ?? ''); ?></div>
                </div>

                <div class="glass-card">
                    <form action="" method="POST" id="profileForm" novalidate>

                        <!-- Row 1: Name & Email -->
                        <div class="donor-profile-grid">
                            <div class="form-group">
                                <label for="full_name" style="font-weight:600; color:var(--text-dark); margin-bottom:6px; display:block;">
                                    Full Name <span style="color:var(--danger);">*</span>
                                </label>
                                <input type="text" name="full_name" id="full_name" class="form-control"
                                       value="<?php echo htmlspecialchars($user['full_name']); ?>"
                                       required minlength="2" maxlength="100"
                                       aria-required="true"
                                       oninput="document.getElementById('avatarName').textContent=this.value; updateInitials(this.value);">
                            </div>
                            <div class="form-group">
                                <label for="email" style="font-weight:600; color:var(--text-dark); margin-bottom:6px; display:block;">
                                    Email Address
                                    <span style="font-size:0.72rem; color:var(--text-muted); font-weight:400; margin-left:4px;">(Read only)</span>
                                </label>
                                <input type="email" id="email" class="form-control"
                                       value="<?php echo htmlspecialchars($user['email']); ?>"
                                       readonly disabled
                                       style="background:#f1f5f9; cursor:not-allowed; opacity:0.7;"
                                       aria-label="Email address (cannot be changed here)">
                            </div>
                        </div>

                        <!-- Row 2: Phone & Address -->
                        <div class="donor-profile-grid">
                            <div class="form-group">
                                <label for="phone" style="font-weight:600; color:var(--text-dark); margin-bottom:6px; display:block;">
                                    Phone Number
                                    <span style="font-size:0.72rem; color:var(--text-muted); font-weight:400; margin-left:4px;">(Optional)</span>
                                </label>
                                <input type="tel" name="phone" id="phone" class="form-control"
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                       placeholder="10-digit mobile number"
                                       maxlength="10"
                                       pattern="[6-9][0-9]{9}"
                                       aria-describedby="phoneError">
                                <div id="phoneError" class="donor-inline-error" style="display:none; margin-top:8px; margin-bottom:0;">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span class="error-text">Please enter a valid 10-digit Indian mobile number starting with 6, 7, 8, or 9.</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="address" style="font-weight:600; color:var(--text-dark); margin-bottom:6px; display:block;">
                                    Mailing Address
                                    <span style="font-size:0.72rem; color:var(--text-muted); font-weight:400; margin-left:4px;">(For tax receipts)</span>
                                </label>
                                <input type="text" name="address" id="address" class="form-control"
                                       value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>"
                                       placeholder="Street, City, State, PIN">
                            </div>
                        </div>

                        <!-- Bio -->
                        <div class="form-group" style="margin-bottom:28px;">
                            <label for="bio" style="font-weight:600; color:var(--text-dark); margin-bottom:6px; display:block;">
                                About Me
                                <span style="font-size:0.72rem; color:var(--text-muted); font-weight:400; margin-left:4px;">(Optional)</span>
                            </label>
                            <textarea name="bio" id="bio" class="form-control" rows="4"
                                      maxlength="500"
                                      placeholder="Tell us a bit about yourself and why you support our causes…"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                            <div class="char-counter" id="bioCharCount">0 / 500 characters</div>
                        </div>

                        <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                            <button type="submit" class="btn-primary" style="padding:12px 28px; display:inline-flex; align-items:center; gap:8px;">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <a href="donor_dashboard.php" class="btn-secondary" style="padding:12px 24px; text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </main>
</div>

<script>
    function updateInitials(name) {
        const parts = name.trim().split(/\s+/);
        let initials = '';
        if (parts.length >= 2) {
            initials = (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
        } else if (parts[0]) {
            initials = parts[0][0].toUpperCase();
        }
        const el = document.getElementById('avatarCircle');
        if (el) el.textContent = initials || '?';
    }
</script>
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/donor.js"></script>
</body>
</html>
