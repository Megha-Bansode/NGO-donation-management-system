<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../core/Logger.php';

session_start();
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

// Check CSRF
$csrfToken = $_POST['csrf_token'] ?? '';
if (!Security::verifyCSRF($csrfToken)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid or expired security token. Please refresh the page and try again.']);
    exit;
}

// Get Data
$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validation
$errors = [];

if (empty($firstName)) {
    $errors[] = 'First Name is required.';
}
if (empty($lastName)) {
    $errors[] = 'Last Name is required.';
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'A valid Email is required.';
}
if (strlen($message) < 20) {
    $errors[] = 'Message must be at least 20 characters long.';
}
if (strlen($message) > 2000) {
    $errors[] = 'Message cannot exceed 2000 characters.';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => implode(' ', $errors)]);
    exit;
}

try {
    $pdo = getDatabase();
    
    // Insert Inquiry
    $stmt = $pdo->prepare("
        INSERT INTO contact_inquiries (first_name, last_name, email, message, source, status) 
        VALUES (:first_name, :last_name, :email, :message, 'Landing Page', 'pending')
    ");
    
    $stmt->execute([
        ':first_name' => $firstName,
        ':last_name' => $lastName,
        ':email' => $email,
        ':message' => $message
    ]);
    
    $inquiryId = $pdo->lastInsertId();
    
    // Notification for Super Admin (Role 1)
    $stmtNotif = $pdo->prepare("
        INSERT INTO notifications (recipient_id, role_id, title, message, notification_type) 
        VALUES (1, 1, 'New Contact Inquiry Received', :message, 'System')
    ");
    $notificationMsg = "New inquiry from {$firstName} {$lastName}.";
    $stmtNotif->execute([':message' => $notificationMsg]);
    
    // Also notify NGO Admin (Role 2) if applicable, or we just notify super admin.
    // The requirement says: "notify the NGO Admin". Usually Role 1 and Role 2 are admins.
    // We'll notify role 1 and 2 by selecting all admin users.
    $stmtAdmins = $pdo->query("SELECT id, role_id FROM users WHERE role_id IN (1, 2) AND status = 'active'");
    while ($admin = $stmtAdmins->fetch(PDO::FETCH_ASSOC)) {
        if ($admin['id'] != 1) { // We already sent to user ID 1 assuming they are super admin, but let's be safe.
            $stmtNotifGroup = $pdo->prepare("
                INSERT INTO notifications (recipient_id, role_id, title, message, notification_type) 
                VALUES (:user_id, :role_id, 'New Contact Inquiry Received', :message, 'System')
            ");
            $stmtNotifGroup->execute([
                ':user_id' => $admin['id'],
                ':role_id' => $admin['role_id'],
                ':message' => $notificationMsg
            ]);
        }
    }
    
    // Log Activity (Since it's a guest, user_id is null, role_id is null)
    Logger::logActivity($pdo, null, null, 'Contact', 'Submit Inquiry', "Visitor {$firstName} {$lastName} submitted an inquiry.");

    // OPTIONAL EMAIL (Disabled for now)
    /*
    $subject = "Thank you for contacting Arohan Foundation";
    $body = "Dear {$firstName},\n\nWe have received your inquiry and our team will get back to you shortly.\n\nBest,\nArohan Foundation";
    // mail($email, $subject, $body, "From: no-reply@arohanfoundation.org");
    
    $adminSubject = "New Contact Inquiry - {$firstName} {$lastName}";
    $adminBody = "A new contact inquiry has been submitted.\n\nName: {$firstName} {$lastName}\nEmail: {$email}\nMessage:\n{$message}";
    // mail("admin@arohanfoundation.org", $adminSubject, $adminBody, "From: no-reply@arohanfoundation.org");
    */

    echo json_encode([
        'status' => 'success',
        'message' => 'Thank you for contacting Arohan Foundation. Our team will get back to you shortly.'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Contact Submit Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'An internal server error occurred. Please try again later.']);
}
?>
