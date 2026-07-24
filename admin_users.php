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
        .tabs-container {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding-bottom: 10px;
        }
        .tab-item {
            padding: 10px 24px;
            border-radius: 30px;
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 600;
            transition: all var(--transition-fast);
            font-size: 0.95rem;
        }
        .tab-item.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 10px rgba(124, 154, 134, 0.2);
        }
        .tab-item:hover:not(.active) {
            background: rgba(0,0,0,0.04);
            color: var(--text-dark);
        }
        
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
            font-size: 0.9rem;
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
            background: rgba(15, 23, 42, 0.5);
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
            padding: 30px;
            border-radius: 16px;
            width: 100%;
            max-width: 550px;
            box-shadow: var(--shadow-lg);
            transform: scale(0.95);
            transition: all 0.3s ease;
            max-height: 85vh;
            overflow-y: auto;
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
            font-size: 1.25rem;
            color: var(--text-dark);
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 1.3rem;
            color: var(--text-muted);
            cursor: pointer;
            transition: color 0.2s;
        }
        .modal-close:hover {
            color: var(--danger);
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-dark);
        }
        .form-group select, .form-group input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 8px;
            background: white;
            font-family: var(--font-body);
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(124, 154, 134, 0.2);
        }
        
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        .profile-field {
            margin-bottom: 10px;
        }
        .profile-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
        }
        .profile-val {
            font-size: 0.95rem;
            color: var(--text-dark);
            font-weight: 500;
            margin-top: 2px;
        }
        
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            font-size: 0.75rem;
            font-weight: 700;
            border-radius: 30px;
            text-transform: uppercase;
        }
        .badge-role {
            background: rgba(124, 154, 134, 0.1);
            color: var(--primary);
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
            
            <!-- Navigation Tabs -->
            <div class="tabs-container">
                <a href="admin_users.php" class="tab-item active">All Users</a>
                <a href="admin_ngos.php" class="tab-item">NGO Directory</a>
            </div>

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
                    <?php if ($search !== '' || $role_filter !== '' || $status_filter !== ''): ?>
                        <a href="admin_users.php" class="btn-primary" style="padding: 10px 20px; background: white; color: var(--text-dark); border: 1px solid rgba(0,0,0,0.1); text-decoration: none;"><i class="fas fa-undo"></i> Clear</a>
                    <?php endif; ?>
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
                                    <th style="text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($users as $u): ?>
                                <tr>
                                    <td>#<?php echo $u['id']; ?></td>
                                    <td>
                                        <div style="font-weight: 700; color: var(--text-dark); cursor: pointer;" onclick='openViewModal(<?php echo json_encode($u); ?>)'>
                                            <?php echo htmlspecialchars($u['full_name']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td>
                                        <span class="badge badge-role">
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
                                    <td style="text-align: right;">
                                        <div style="display: inline-flex; gap: 5px;">
                                            <button onclick='openViewModal(<?php echo json_encode($u); ?>)' class="btn-primary" style="padding: 6px 12px; font-size: 0.8rem; background: rgba(124,154,134,0.1); color: var(--primary); border: none;" title="View Details">
                                                <i class="far fa-eye"></i> Details
                                            </button>
                                            <button onclick='openEditModal(<?php echo json_encode($u); ?>)' class="btn-primary" style="padding: 6px 12px; font-size: 0.8rem; background: rgba(0,0,0,0.05); color: var(--text-dark); border: none;" title="Edit Role & Status">
                                                <i class="fas fa-edit"></i> Status
                                            </button>
                                            <a href="admin_user_activity.php?id=<?php echo $u['id']; ?>" class="btn-primary" style="padding: 6px 12px; font-size: 0.8rem; background: rgba(0,0,0,0.05); color: var(--text-dark); border: none; text-decoration: none;" title="Activity Logs">
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

<!-- Modal: View User Details -->
<div class="modal" id="viewUserModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>User Details</h3>
            <button class="modal-close" onclick="closeModal('viewUserModal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="profile-grid">
            <div class="profile-field">
                <div class="profile-label">Full Name</div>
                <div class="profile-val" id="view_full_name"></div>
            </div>
            <div class="profile-field">
                <div class="profile-label">Email Address</div>
                <div class="profile-val" id="view_email"></div>
            </div>
            <div class="profile-field">
                <div class="profile-label">Phone Number</div>
                <div class="profile-val" id="view_phone"></div>
            </div>
            <div class="profile-field">
                <div class="profile-label">Role</div>
                <div class="profile-val" id="view_role"></div>
            </div>
            <div class="profile-field">
                <div class="profile-label">Account Status</div>
                <div class="profile-val" id="view_status"></div>
            </div>
            <div class="profile-field">
                <div class="profile-label">Date Joined</div>
                <div class="profile-val" id="view_joined"></div>
            </div>
            <div class="profile-field" style="grid-column: 1 / -1;">
                <div class="profile-label">Address</div>
                <div class="profile-val" id="view_address"></div>
            </div>
            <div class="profile-field" style="grid-column: 1 / -1;">
                <div class="profile-label">Bio / Profile Info</div>
                <div class="profile-val" id="view_bio" style="background: rgba(0,0,0,0.02); padding: 12px; border-radius: 8px; border-left: 3px solid var(--primary); line-height: 1.5; font-size: 0.9rem;"></div>
            </div>
        </div>
        <div style="display: flex; justify-content: flex-end; margin-top: 15px; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 15px;">
            <button class="btn-primary" onclick="closeModal('viewUserModal')" style="background: var(--text-muted); padding: 8px 20px;">Close Details</button>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal" id="editUserModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Update User Role & Status</h3>
            <button class="modal-close" onclick="closeModal('editUserModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="admin_users.php">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="action" value="update_user">
            <input type="hidden" name="user_id" id="edit_user_id" value="">
            
            <div class="form-group">
                <label>User Name</label>
                <input type="text" id="edit_user_name" readonly style="background: #f9f9f9;">
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
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 25px; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 15px;">
                <button type="button" class="btn-primary" style="background: white; color: var(--text-dark); border: 1px solid rgba(0,0,0,0.1);" onclick="closeModal('editUserModal')">Cancel</button>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/dashboard.js"></script>
<script>
    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
    }

    function openEditModal(user) {
        document.getElementById('edit_user_id').value = user.id;
        document.getElementById('edit_user_name').value = user.full_name;
        document.getElementById('edit_role_id').value = user.role_id;
        document.getElementById('edit_status').value = user.status;
        
        document.getElementById('editUserModal').classList.add('active');
    }
    
    function openViewModal(user) {
        document.getElementById('view_full_name').innerText = user.full_name;
        document.getElementById('view_email').innerText = user.email;
        document.getElementById('view_phone').innerText = user.phone || 'N/A';
        document.getElementById('view_role').innerText = user.role_name || 'User';
        
        // Status formatting
        const statusElement = document.getElementById('view_status');
        statusElement.innerText = user.status.toUpperCase();
        statusElement.className = 'badge';
        if (user.status === 'active') statusElement.style.cssText = 'background: rgba(16,185,129,0.1); color: var(--success);';
        else if (user.status === 'inactive') statusElement.style.cssText = 'background: rgba(107,114,128,0.1); color: var(--text-muted);';
        else if (user.status === 'suspended') statusElement.style.cssText = 'background: rgba(245,158,11,0.1); color: var(--warning);';
        else statusElement.style.cssText = 'background: rgba(239,68,68,0.1); color: var(--danger);';
        
        // Date formatting
        const date = new Date(user.created_at);
        document.getElementById('view_joined').innerText = date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        
        // Address compilation
        const address = [user.address, user.city, user.state, user.country, user.postal_code].filter(Boolean).join(', ');
        document.getElementById('view_address').innerText = address || 'N/A';
        
        // Bio
        document.getElementById('view_bio').innerText = user.bio || 'No profile biography provided.';
        
        document.getElementById('viewUserModal').classList.add('active');
    }
</script>
</body>
</html>
