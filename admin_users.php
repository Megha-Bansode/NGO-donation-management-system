<?php
// admin_users.php
session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Middleware.php';
require_once __DIR__ . '/includes/dashboard/components.php';

// Protect this dashboard: Only Super Admin (Role ID 1) can access
Middleware::role([1]);

// Initialize Database
$pdo = getDatabase();

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success_msg = '';
$error_msg = '';

// Controller Logic: Handle POST Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_msg = "Invalid security token.";
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_user') {
            $user_id = filter_var($_POST['user_id'], FILTER_VALIDATE_INT);
            $role_id = filter_var($_POST['role_id'], FILTER_VALIDATE_INT);
            $status = htmlspecialchars($_POST['status']);
            
            if ($user_id && $role_id && in_array($status, ['active', 'inactive', 'suspended', 'banned'])) {
                try {
                    // Prevent Super Admin from changing their own role/status from this UI
                    if ($user_id == $_SESSION['user_id']) {
                        $error_msg = "You cannot modify your own account status or role from this page.";
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET role_id = :role_id, status = :status WHERE id = :id");
                        $stmt->execute([
                            ':role_id' => $role_id,
                            ':status' => $status,
                            ':id' => $user_id
                        ]);
                        $success_msg = "User successfully updated.";
                        
                        // Log activity
                        $logStmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, module, ip_address) VALUES (:uid, :action, 'User Management', :ip)");
                        $logStmt->execute([
                            ':uid' => $_SESSION['user_id'],
                            ':action' => "Updated user ID $user_id (Role: $role_id, Status: $status)",
                            ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
                        ]);
                    }
                } catch (PDOException $e) {
                    $error_msg = "Database error: " . $e->getMessage();
                }
            } else {
                $error_msg = "Invalid data provided.";
            }
        }
    }
}

// Fetch Filter Parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role_filter = isset($_GET['role']) ? filter_var($_GET['role'], FILTER_VALIDATE_INT) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$page = isset($_GET['page']) ? max(1, filter_var($_GET['page'], FILTER_VALIDATE_INT)) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Build Query
$whereClauses = ["1=1"];
$params = [];

if ($search !== '') {
    $whereClauses[] = "(u.full_name LIKE :search OR u.email LIKE :search)";
    $params[':search'] = "%{$search}%";
}
if ($role_filter) {
    $whereClauses[] = "u.role_id = :role_id";
    $params[':role_id'] = $role_filter;
}
if ($status_filter) {
    $whereClauses[] = "u.status = :status";
    $params[':status'] = $status_filter;
}

$whereSQL = implode(" AND ", $whereClauses);

try {
    // Total Count for Pagination
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM users u WHERE $whereSQL");
    foreach ($params as $key => $val) {
        $countStmt->bindValue($key, $val);
    }
    $countStmt->execute();
    $totalUsers = $countStmt->fetchColumn();
    $totalPages = ceil($totalUsers / $limit);

    // Fetch Users
    $query = "SELECT u.*, r.name as role_name 
              FROM users u 
              LEFT JOIN roles r ON u.role_id = r.id 
              WHERE $whereSQL 
              ORDER BY u.created_at DESC 
              LIMIT :limit OFFSET :offset";
              
    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch All Roles for Dropdowns
    $rolesStmt = $pdo->query("SELECT * FROM roles ORDER BY name ASC");
    $allRoles = $rolesStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_msg = "Failed to fetch users: " . $e->getMessage();
    $users = [];
    $allRoles = [];
    $totalPages = 1;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management | <?php echo htmlspecialchars(APP_NAME); ?></title>
    
    <!-- Premium Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Dashboard Core CSS -->
    <link rel="stylesheet" href="assets/css/dashboard.css">
    
    <style>
        .filter-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-bar input, .filter-bar select {
            padding: 10px 15px;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 8px;
            font-family: var(--font-body);
            background: white;
            min-width: 200px;
        }
        .filter-bar input:focus, .filter-bar select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(124, 154, 134, 0.2);
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
        }
        .page-btn {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid rgba(0,0,0,0.1);
            background: white;
            color: var(--text-dark);
            text-decoration: none;
            transition: all 0.2s;
        }
        .page-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        .page-btn:hover:not(.active) {
            background: rgba(0,0,0,0.02);
        }
        
        /* Modal Styles */
        .modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(6px);
            z-index: 1050;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        .modal.active {
            opacity: 1;
            visibility: visible;
        }
        .modal-content {
            background: white;
            padding: 25px;
            border-radius: 16px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            transform: scale(0.95);
            transition: all 0.3s ease;
        }
        .modal.active .modal-content {
            transform: scale(1);
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .modal-header h3 {
            margin: 0;
            font-size: 1.2rem;
            color: var(--text-dark);
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: var(--text-muted);
            cursor: pointer;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-dark);
        }
        .form-group select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 8px;
            background: white;
            font-family: var(--font-body);
        }
    </style>
</head>
<body>

<div class="dashboard-layout">
    <!-- Sidebar -->
    <?php include __DIR__ . '/includes/dashboard/sidebar.php'; ?>

    <main class="main-content">
        <!-- Topbar -->
        <?php include __DIR__ . '/includes/dashboard/topbar.php'; ?>

        <div class="page-content">
            <!-- Header Section -->
            <div class="page-header">
                <div class="page-title">
                    <h1>User Management</h1>
                    <div class="breadcrumb">
                        <span>Dashboard</span>
                        <span style="margin: 0 8px;">/</span>
                        <span style="color: var(--primary);">Users</span>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            <?php if ($success_msg): ?>
                <div style="background: rgba(16, 185, 129, 0.1); color: var(--success); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(16, 185, 129, 0.2);">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_msg); ?>
                </div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div style="background: rgba(239, 68, 68, 0.1); color: var(--danger); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(239, 68, 68, 0.2);">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_msg); ?>
                </div>
            <?php endif; ?>

            <!-- Filter Bar -->
            <div class="glass-card" style="margin-bottom: 20px;">
                <form method="GET" action="admin_users.php" class="filter-bar">
                    <input type="text" name="search" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
                    
                    <select name="role">
                        <option value="">All Roles</option>
                        <?php foreach($allRoles as $role): ?>
                            <option value="<?php echo $role['id']; ?>" <?php echo $role_filter == $role['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($role['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="status">
                        <option value="">All Statuses</option>
                        <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        <option value="suspended" <?php echo $status_filter === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                        <option value="banned" <?php echo $status_filter === 'banned' ? 'selected' : ''; ?>>Banned</option>
                    </select>

                    <button type="submit" class="btn-primary" style="padding: 10px 20px;"><i class="fas fa-filter"></i> Filter</button>
                    <a href="admin_users.php" class="btn-primary" style="padding: 10px 20px; background: rgba(0,0,0,0.05); color: var(--text-dark); text-decoration: none;"><i class="fas fa-undo"></i> Reset</a>
                </form>
            </div>

            <!-- Users Table -->
            <div class="glass-card">
                <?php if (empty($users)): ?>
                    <?php render_empty_state('No Users Found', 'Try adjusting your search or filters.', 'fas fa-users-slash'); ?>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($users as $u): ?>
                                <tr>
                                    <td>#<?php echo $u['id']; ?></td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-dark);"><?php echo htmlspecialchars($u['full_name']); ?></div>
                                    </td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td>
                                        <span class="badge" style="background: rgba(124, 154, 134, 0.1); color: var(--primary);">
                                            <?php echo htmlspecialchars($u['role_name']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                            $statusColors = [
                                                'active' => 'rgba(16,185,129,0.1)', 'inactive' => 'rgba(107,114,128,0.1)',
                                                'suspended' => 'rgba(245,158,11,0.1)', 'banned' => 'rgba(239,68,68,0.1)'
                                            ];
                                            $textColors = [
                                                'active' => 'var(--success)', 'inactive' => 'var(--text-muted)',
                                                'suspended' => 'var(--warning)', 'banned' => 'var(--danger)'
                                            ];
                                        ?>
                                        <span class="badge" style="background: <?php echo $statusColors[$u['status']]; ?>; color: <?php echo $textColors[$u['status']]; ?>;">
                                            <?php echo ucfirst(htmlspecialchars($u['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                    <td>
                                        <div style="display: flex; gap: 8px;">
                                            <button onclick='openEditModal(<?php echo json_encode($u); ?>)' class="action-btn" style="width: 32px; height: 32px;" title="Edit Role & Status">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="admin_user_activity.php?id=<?php echo $u['id']; ?>" class="action-btn" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; text-decoration: none;" title="View Activity Logs">
                                                <i class="fas fa-history"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php for($i=1; $i<=$totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo $role_filter; ?>&status=<?php echo $status_filter; ?>" class="page-btn <?php echo $i == $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                    <?php endif; ?>

                <?php endif; ?>
            </div>
            
        </div>
    </main>
</div>

<!-- Edit User Modal -->
<div class="modal" id="editUserModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit User</h3>
            <button class="modal-close" onclick="closeEditModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="admin_users.php">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="action" value="update_user">
            <input type="hidden" name="user_id" id="edit_user_id" value="">
            
            <div class="form-group">
                <label>User Name</label>
                <input type="text" id="edit_user_name" readonly style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; background: #f9f9f9; font-family: var(--font-body);">
            </div>
            
            <div class="form-group">
                <label>Role</label>
                <select name="role_id" id="edit_role_id" required>
                    <?php foreach($allRoles as $role): ?>
                        <option value="<?php echo $role['id']; ?>"><?php echo htmlspecialchars($role['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Status</label>
                <select name="status" id="edit_status" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                    <option value="banned">Banned</option>
                </select>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn-primary" style="background: rgba(0,0,0,0.05); color: var(--text-dark);" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/dashboard.js"></script>
<script>
    function openEditModal(user) {
        document.getElementById('edit_user_id').value = user.id;
        document.getElementById('edit_user_name').value = user.full_name;
        document.getElementById('edit_role_id').value = user.role_id;
        document.getElementById('edit_status').value = user.status;
        
        document.getElementById('editUserModal').classList.add('active');
    }
    
    function closeEditModal() {
        document.getElementById('editUserModal').classList.remove('active');
    }
</script>
</body>
</html>
