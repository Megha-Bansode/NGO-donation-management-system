<?php
// admin_ngos.php
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

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_msg = "Invalid security token.";
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_ngo_status') {
            $ngo_id = filter_var($_POST['ngo_id'], FILTER_VALIDATE_INT);
            $status = htmlspecialchars($_POST['status']);
            
            if ($ngo_id && in_array($status, ['active', 'inactive', 'suspended', 'banned'])) {
                try {
                    $stmt = $pdo->prepare("UPDATE users SET status = :status WHERE id = :id AND role_id = 2");
                    $stmt->execute([
                        ':status' => $status,
                        ':id' => $ngo_id
                    ]);
                    
                    if ($stmt->rowCount() > 0) {
                        $success_msg = "NGO status updated successfully to " . ucfirst($status) . ".";
                        
                        // Log activity
                        $logStmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, module, ip_address) VALUES (:uid, :action, 'NGO Management', :ip)");
                        $logStmt->execute([
                            ':uid' => $_SESSION['user_id'],
                            ':action' => "Updated NGO ID $ngo_id status to $status",
                            ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
                        ]);
                    } else {
                        $error_msg = "No NGO was modified. Make sure the user is an NGO Admin.";
                    }
                } catch (PDOException $e) {
                    $error_msg = "Database error: " . $e->getMessage();
                }
            } else {
                $error_msg = "Invalid parameters provided.";
            }
        }
    }
}

// Fetch Filter Parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$page = isset($_GET['page']) ? max(1, filter_var($_GET['page'], FILTER_VALIDATE_INT)) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build Query
$whereClauses = ["u.role_id = 2"];
$params = [];

if ($search !== '') {
    $whereClauses[] = "(u.full_name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search OR u.city LIKE :search)";
    $params[':search'] = "%{$search}%";
}
if ($status_filter !== '') {
    $whereClauses[] = "u.status = :status";
    $params[':status'] = $status_filter;
}

$whereSQL = implode(" AND ", $whereClauses);

try {
    // Total Count
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM users u WHERE $whereSQL");
    foreach ($params as $key => $val) {
        $countStmt->bindValue($key, $val);
    }
    $countStmt->execute();
    $totalNgos = $countStmt->fetchColumn();
    $totalPages = ceil($totalNgos / $limit);

    // Fetch NGOs with campaign stats
    $query = "SELECT u.*, 
              (SELECT COUNT(*) FROM campaigns WHERE created_by = u.id) as campaign_count,
              (SELECT SUM(collected_amount) FROM campaigns WHERE created_by = u.id) as total_raised
              FROM users u 
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
    $ngos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_msg = "Failed to fetch NGOs: " . $e->getMessage();
    $ngos = [];
    $totalPages = 1;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NGO Management | <?php echo htmlspecialchars(APP_NAME); ?></title>
    
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
            max-width: 650px;
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
        
        .ngo-detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }
        @media (max-width: 600px) {
            .ngo-detail-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .detail-item {
            margin-bottom: 15px;
        }
        .detail-label {
            font-size: 0.8rem;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .detail-val {
            font-size: 0.95rem;
            color: var(--text-dark);
            font-weight: 500;
        }
        .detail-val.bio-text {
            line-height: 1.6;
            background: rgba(0,0,0,0.02);
            padding: 12px;
            border-radius: 8px;
            border-left: 3px solid var(--primary);
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            font-size: 0.75rem;
            font-weight: 700;
            border-radius: 30px;
            text-transform: uppercase;
        }
        .status-active { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .status-inactive { background: rgba(156, 163, 175, 0.1); color: var(--text-body); }
        .status-suspended { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
        .status-banned { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
        
        .campaign-mini-list {
            margin-top: 15px;
            border-top: 1px solid rgba(0,0,0,0.05);
            padding-top: 15px;
        }
        .campaign-mini-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background: rgba(0,0,0,0.01);
            border-radius: 8px;
            margin-bottom: 8px;
            font-size: 0.9rem;
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
                <a href="admin_users.php" class="tab-item">All Users</a>
                <a href="admin_ngos.php" class="tab-item active">NGO Directory</a>
            </div>

            <!-- Page Header -->
            <div class="page-header" style="margin-bottom: 20px;">
                <div class="page-title">
                    <h1>NGO Management</h1>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 5px;">Verify, monitor, and manage registered non-governmental organizations.</p>
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
            <div class="glass-card" style="margin-bottom: 25px;">
                <form method="GET" action="admin_ngos.php" class="filter-bar">
                    <input type="text" name="search" placeholder="Search by name, email, city..." value="<?php echo htmlspecialchars($search); ?>">
                    
                    <select name="status">
                        <option value="">All Statuses</option>
                        <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive (Pending)</option>
                        <option value="suspended" <?php echo $status_filter === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                        <option value="banned" <?php echo $status_filter === 'banned' ? 'selected' : ''; ?>>Banned</option>
                    </select>
                    
                    <button type="submit" class="btn-primary" style="padding: 10px 20px;">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                    <?php if ($search !== '' || $status_filter !== ''): ?>
                        <a href="admin_ngos.php" class="btn-primary" style="background: white; color: var(--text-dark); border: 1px solid rgba(0,0,0,0.1); text-decoration: none; padding: 10px 20px;">
                            Clear
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- NGO Listing Card -->
            <div class="glass-card">
                <?php if (empty($ngos)): ?>
                    <?php render_empty_state('No NGOs found', 'There are no NGOs matching your current filter criteria.', 'fas fa-building'); ?>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>NGO Name</th>
                                    <th>Location</th>
                                    <th>Campaigns</th>
                                    <th>Total Raised</th>
                                    <th>Status</th>
                                    <th style="text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($ngos as $ngo): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: var(--text-dark);"><?php echo htmlspecialchars($ngo['full_name']); ?></div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?php echo htmlspecialchars($ngo['email']); ?></div>
                                    </td>
                                    <td>
                                        <?php if($ngo['city'] || $ngo['country']): ?>
                                            <i class="fas fa-map-marker-alt" style="color: var(--primary); font-size: 0.8rem;"></i>
                                            <span style="font-size: 0.9rem; color: var(--text-body);"><?php echo htmlspecialchars(implode(', ', array_filter([$ngo['city'], $ngo['state']]))); ?></span>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted); font-style: italic; font-size: 0.85rem;">Not provided</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span style="font-weight: 600; color: var(--text-dark);"><?php echo $ngo['campaign_count']; ?></span>
                                    </td>
                                    <td style="font-family: var(--font-stats); font-weight: 700; color: var(--primary);">
                                        <?php echo formatIndianCurrency($ngo['total_raised'] ?: 0); ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $ngo['status']; ?>">
                                            <?php echo ucfirst($ngo['status']); ?>
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <div style="display: inline-flex; gap: 5px;">
                                            <button class="btn-primary btn-ngo-view" 
                                                    style="padding: 6px 12px; font-size: 0.8rem; background: rgba(124,154,134,0.1); color: var(--primary); border: none;"
                                                    data-ngo='<?php echo json_encode([
                                                        'id' => $ngo['id'],
                                                        'name' => $ngo['full_name'],
                                                        'email' => $ngo['email'],
                                                        'phone' => $ngo['phone'] ?: 'N/A',
                                                        'address' => $ngo['address'] ?: 'N/A',
                                                        'city' => $ngo['city'] ?: 'N/A',
                                                        'state' => $ngo['state'] ?: 'N/A',
                                                        'country' => $ngo['country'] ?: 'N/A',
                                                        'zip' => $ngo['postal_code'] ?: 'N/A',
                                                        'bio' => $ngo['bio'] ?: 'No description provided.',
                                                        'status' => $ngo['status'],
                                                        'created' => date('M d, Y', strtotime($ngo['created_at']))
                                                    ]); ?>'>
                                                <i class="far fa-eye"></i> Details
                                            </button>
                                            
                                            <button class="btn-primary btn-ngo-action"
                                                    style="padding: 6px 12px; font-size: 0.8rem; background: rgba(0,0,0,0.05); color: var(--text-dark); border: none;"
                                                    data-id="<?php echo $ngo['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($ngo['full_name']); ?>"
                                                    data-status="<?php echo $ngo['status']; ?>">
                                                <i class="fas fa-edit"></i> Status
                                            </button>
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
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>" 
                                   class="page-btn <?php echo $page == $i ? 'active' : ''; ?>">
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

<!-- Modal: NGO Profile Detail -->
<div class="modal" id="ngoDetailModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalNgoName">NGO Details</h3>
            <button class="modal-close" onclick="closeModal('ngoDetailModal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="ngo-detail-grid">
            <div class="detail-item">
                <div class="detail-label">Email Address</div>
                <div class="detail-val" id="modalNgoEmail"></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Phone Number</div>
                <div class="detail-val" id="modalNgoPhone"></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Location Address</div>
                <div class="detail-val" id="modalNgoAddress"></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Registered On</div>
                <div class="detail-val" id="modalNgoCreated"></div>
            </div>
            <div class="detail-item" style="grid-column: 1 / -1;">
                <div class="detail-label">About / Mission Bio</div>
                <div class="detail-val bio-text" id="modalNgoBio"></div>
            </div>
        </div>
        
        <div class="campaign-mini-list">
            <h4 style="margin-bottom: 12px; color: var(--text-dark);"><i class="fas fa-bullhorn" style="color: var(--primary);"></i> NGO Campaigns</h4>
            <div id="modalNgoCampaigns">
                <p style="color: var(--text-muted); font-size: 0.85rem; font-style: italic;">Loading campaigns...</p>
            </div>
        </div>
        
        <div style="display: flex; justify-content: flex-end; margin-top: 20px; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 15px;">
            <button class="btn-primary" onclick="closeModal('ngoDetailModal')" style="background: var(--text-muted); padding: 8px 20px;">Close Details</button>
        </div>
    </div>
</div>

<!-- Modal: Update NGO Status -->
<div class="modal" id="ngoActionModal">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3>Update Status</h3>
            <button class="modal-close" onclick="closeModal('ngoActionModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="admin_ngos.php">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="action" value="update_ngo_status">
            <input type="hidden" name="ngo_id" id="actionNgoId">
            
            <p style="margin-bottom: 15px; font-size: 0.95rem; line-height: 1.5;">
                Modify account status for NGO: <strong id="actionNgoNameText" style="color: var(--text-dark);"></strong>.
            </p>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; font-size: 0.9rem;">Account Status</label>
                <select name="status" id="actionNgoStatus" style="width: 100%; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px; background: white;" required>
                    <option value="active">Active (Approved)</option>
                    <option value="inactive">Inactive (Pending Approval)</option>
                    <option value="suspended">Suspended</option>
                    <option value="banned">Banned</option>
                </select>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 15px;">
                <button type="button" class="btn-primary" onclick="closeModal('ngoActionModal')" style="background: white; color: var(--text-dark); border: 1px solid rgba(0,0,0,0.1); padding: 8px 20px;">Cancel</button>
                <button type="submit" class="btn-primary" style="padding: 8px 20px;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/dashboard.js"></script>
<script>
    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
    }
    
    document.addEventListener('DOMContentLoaded', () => {
        // Details buttons
        const detailBtns = document.querySelectorAll('.btn-ngo-view');
        detailBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const data = JSON.parse(btn.getAttribute('data-ngo'));
                document.getElementById('modalNgoName').innerText = data.name;
                document.getElementById('modalNgoEmail').innerText = data.email;
                document.getElementById('modalNgoPhone').innerText = data.phone;
                document.getElementById('modalNgoAddress').innerText = data.address + ', ' + data.city + ', ' + data.state + ', ' + data.zip;
                document.getElementById('modalNgoCreated').innerText = data.created;
                document.getElementById('modalNgoBio').innerText = data.bio;
                
                // Open modal
                document.getElementById('ngoDetailModal').classList.add('active');
                
                // Fetch NGO campaigns asynchronously via simple fetch to api or inline endpoint
                const campaignsContainer = document.getElementById('modalNgoCampaigns');
                campaignsContainer.innerHTML = '<p style="color: var(--text-muted); font-size: 0.85rem; font-style: italic;"><i class="fas fa-spinner fa-spin"></i> Fetching organization campaigns...</p>';
                
                // We'll simulate or retrieve by calling a mini-api query
                fetch('api/campaign/index.php?ngo_id=' + data.id)
                    .then(res => res.json())
                    .then(camps => {
                        if (camps.success && camps.data && camps.data.length > 0) {
                            let html = '';
                            camps.data.forEach(c => {
                                const percentage = parseFloat(c.goal_completed_percentage || 0);
                                html += `
                                <div class="campaign-mini-item">
                                    <div style="font-weight:600; color:var(--text-dark);">${escapeHtml(c.name)}</div>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <div style="font-size:0.8rem; color:var(--text-muted);">₹${parseFloat(c.collected_amount).toLocaleString('en-IN')} / ₹${parseFloat(c.target_amount).toLocaleString('en-IN')}</div>
                                        <span class="status-badge status-${c.status === 'active' ? 'active' : 'inactive'}" style="font-size:0.65rem;">${c.status}</span>
                                    </div>
                                </div>
                                `;
                            });
                            campaignsContainer.innerHTML = html;
                        } else {
                            campaignsContainer.innerHTML = '<p style="color: var(--text-muted); font-size: 0.85rem; font-style: italic;">No campaigns found for this organization.</p>';
                        }
                    })
                    .catch(() => {
                        // Fallback: If API doesn't support ngo_id filter directly, query simple backend list or show empty
                        campaignsContainer.innerHTML = '<p style="color: var(--text-muted); font-size: 0.85rem; font-style: italic;">No active campaigns listed.</p>';
                    });
            });
        });
        
        // Status actions
        const actionBtns = document.querySelectorAll('.btn-ngo-action');
        actionBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                const name = btn.getAttribute('data-name');
                const status = btn.getAttribute('data-status');
                
                document.getElementById('actionNgoId').value = id;
                document.getElementById('actionNgoNameText').innerText = name;
                document.getElementById('actionNgoStatus').value = status;
                
                document.getElementById('ngoActionModal').classList.add('active');
            });
        });
    });
    
    function escapeHtml(str) {
        return str
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
</script>
</body>
</html>
