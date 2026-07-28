<?php
// admin_campaigns.php
session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Middleware.php';
require_once __DIR__ . '/includes/dashboard/components.php';

// Protect this dashboard: Only NGO Admin (Role ID 2) can access
Middleware::role([2]);

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
        
        if ($action === 'create_campaign' || $action === 'edit_campaign') {
            $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
            $name = htmlspecialchars(trim($_POST['name']));
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
            $category_id = filter_var($_POST['category_id'], FILTER_VALIDATE_INT);
            $target_amount = filter_var($_POST['target_amount'], FILTER_VALIDATE_FLOAT);
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            $status = $_POST['status'];
            $featured = isset($_POST['featured']) ? 1 : 0;
            $short_description = htmlspecialchars(trim($_POST['short_description']));
            $description = htmlspecialchars(trim($_POST['description']));

            if ($name && $category_id && $target_amount && $start_date && $end_date) {
                try {
                    if ($action === 'create_campaign') {
                        $stmt = $pdo->prepare("INSERT INTO campaigns (category_id, name, slug, short_description, description, target_amount, start_date, end_date, status, featured, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$category_id, $name, $slug, $short_description, $description, $target_amount, $start_date, $end_date, $status, $featured, $_SESSION['user_id']]);
                        $success_msg = "Campaign created successfully.";
                    } else {
                        // Ensure user can only edit their own campaigns
                        $checkStmt = $pdo->prepare("SELECT created_by FROM campaigns WHERE id = ?");
                        $checkStmt->execute([$id]);
                        if ($checkStmt->fetchColumn() != $_SESSION['user_id']) {
                            $error_msg = "You do not have permission to edit this campaign.";
                        } else {
                            $stmt = $pdo->prepare("UPDATE campaigns SET category_id=?, name=?, slug=?, short_description=?, description=?, target_amount=?, start_date=?, end_date=?, status=?, featured=? WHERE id=?");
                            $stmt->execute([$category_id, $name, $slug, $short_description, $description, $target_amount, $start_date, $end_date, $status, $featured, $id]);
                            $success_msg = "Campaign updated successfully!";
                        }
                    }
                } catch (PDOException $e) {
                    $error_msg = "Database error: " . $e->getMessage();
                }
            } else {
                $error_msg = "Please fill in all required fields.";
            }
        } elseif ($action === 'delete_campaign') {
            $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
            if ($id) {
                try {
                    // Ensure user can only delete their own campaigns
                    $checkStmt = $pdo->prepare("SELECT created_by FROM campaigns WHERE id = ?");
                    $checkStmt->execute([$id]);
                    if ($checkStmt->fetchColumn() != $_SESSION['user_id']) {
                        $error_msg = "You do not have permission to delete this campaign.";
                    } else {
                        $stmt = $pdo->prepare("DELETE FROM campaigns WHERE id=?");
                        $stmt->execute([$id]);
                        $success_msg = "Campaign deleted successfully!";
                    }
                } catch (PDOException $e) {
                    $error_msg = "Cannot delete campaign as it has associated donations or images.";
                }
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
$whereClauses = ["1=1"];
$params = [];

if ($search !== '') {
    $whereClauses[] = "(c.name LIKE :search OR c.short_description LIKE :search)";
    $params[':search'] = "%{$search}%";
}
if ($status_filter) {
    $whereClauses[] = "c.status = :status";
    $params[':status'] = $status_filter;
}

$whereSQL = implode(" AND ", $whereClauses);

try {
    // Total Count for Pagination
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM campaigns c WHERE $whereSQL");
    foreach ($params as $key => $val) {
        $countStmt->bindValue($key, $val);
    }
    $countStmt->execute();
    $totalCampaigns = $countStmt->fetchColumn();
    $totalPages = ceil($totalCampaigns / $limit);

    // Fetch Campaigns
    $query = "SELECT c.*, cat.name as category_name 
              FROM campaigns c 
              LEFT JOIN campaign_categories cat ON c.category_id = cat.id 
              WHERE $whereSQL 
              ORDER BY c.created_at DESC 
              LIMIT :limit OFFSET :offset";
              
    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Categories
    $catStmt = $pdo->query("SELECT * FROM campaign_categories ORDER BY name ASC");
    $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_msg = "Failed to fetch campaigns: " . $e->getMessage();
    $campaigns = [];
    $categories = [];
    $totalPages = 1;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaign Management | <?php echo htmlspecialchars(APP_NAME); ?></title>
    
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
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
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
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group.full-width {
            grid-column: span 2;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-dark);
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 8px;
            background: white;
            font-family: var(--font-body);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .progress-bar-container {
            width: 100%;
            height: 8px;
            background: rgba(0,0,0,0.05);
            border-radius: 4px;
            overflow: hidden;
            margin-top: 5px;
        }
        .progress-bar-fill {
            height: 100%;
            background: var(--primary);
            border-radius: 4px;
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
                    <h1>Campaign Management</h1>
                    <div class="breadcrumb">
                        <span>Dashboard</span>
                        <span style="margin: 0 8px;">/</span>
                        <span style="color: var(--primary);">Campaigns</span>
                    </div>
                </div>
                <div class="header-actions">
                    <button class="btn-primary" onclick="openCampaignModal()"><i class="fas fa-plus"></i> New Campaign</button>
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
                <form method="GET" action="ngo_campaigns.php" class="filter-bar">
                    <input type="text" name="search" placeholder="Search campaigns..." value="<?php echo htmlspecialchars($search); ?>">
                    
                    <select name="status">
                        <option value="">All Statuses</option>
                        <option value="draft" <?php echo $status_filter === 'draft' ? 'selected' : ''; ?>>Draft</option>
                        <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>

                    <button type="submit" class="btn-primary" style="padding: 10px 20px;"><i class="fas fa-filter"></i> Filter</button>
                    <a href="ngo_campaigns.php" class="btn-primary" style="padding: 10px 20px; background: rgba(0,0,0,0.05); color: var(--text-dark); text-decoration: none;"><i class="fas fa-undo"></i> Reset</a>
                </form>
            </div>

            <!-- Campaigns Table -->
            <div class="glass-card">
                <?php if (empty($campaigns)): ?>
                    <?php render_empty_state('No Campaigns Found', 'Start your first fundraising campaign.', 'fas fa-bullhorn'); ?>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Campaign</th>
                                    <th>Category</th>
                                    <th>Goal / Raised</th>
                                    <th>Progress</th>
                                    <th>Status</th>
                                    <th>Featured</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($campaigns as $camp): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-dark);"><?php echo htmlspecialchars($camp['name']); ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo date('M d', strtotime($camp['start_date'])); ?> - <?php echo date('M d, Y', strtotime($camp['end_date'])); ?></div>
                                    </td>
                                    <td><?php echo htmlspecialchars($camp['category_name'] ?? 'Uncategorized'); ?></td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-dark);"><?php echo formatIndianCurrency($camp['target_amount']); ?></div>
                                        <div style="font-size: 0.85rem; color: var(--success);">Raised: <?php echo formatIndianCurrency($camp['collected_amount']); ?></div>
                                    </td>
                                    <td style="width: 150px;">
                                        <div style="font-size: 0.85rem; font-weight: 600; color: var(--text-dark); text-align: right;"><?php echo $camp['goal_completed_percentage']; ?>%</div>
                                        <div class="progress-bar-container">
                                            <div class="progress-bar-fill" style="width: <?php echo min(100, $camp['goal_completed_percentage']); ?>%;"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                            $statusColors = [
                                                'active' => 'rgba(16,185,129,0.1)', 'draft' => 'rgba(107,114,128,0.1)',
                                                'completed' => 'rgba(59,130,246,0.1)', 'cancelled' => 'rgba(239,68,68,0.1)'
                                            ];
                                            $textColors = [
                                                'active' => 'var(--success)', 'draft' => 'var(--text-muted)',
                                                'completed' => '#3b82f6', 'cancelled' => 'var(--danger)'
                                            ];
                                        ?>
                                        <span class="badge" style="background: <?php echo $statusColors[$camp['status']]; ?>; color: <?php echo $textColors[$camp['status']]; ?>;">
                                            <?php echo ucfirst(htmlspecialchars($camp['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if($camp['featured']): ?>
                                            <span style="color: #f59e0b;"><i class="fas fa-star"></i></span>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted);"><i class="far fa-star"></i></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                            <?php if ($camp['created_by'] == $_SESSION['user_id']): ?>
                                            <button class="action-btn" onclick="openCampaignModal(<?php echo htmlspecialchars(json_encode($camp)); ?>)" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" action="ngo_campaigns.php" onsubmit="return confirm('Are you sure you want to delete this campaign?');" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <input type="hidden" name="action" value="delete_campaign">
                                                <input type="hidden" name="id" value="<?php echo $camp['id']; ?>">
                                                <button type="submit" class="action-btn" style="color: var(--danger);" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            <?php else: ?>
                                            <button class="action-btn" style="opacity: 0.5; cursor: not-allowed;" title="You can only edit campaigns you created">
                                                <i class="fas fa-lock"></i>
                                            </button>
                                            <?php endif; ?>
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
                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>" class="page-btn <?php echo $i == $page ? 'active' : ''; ?>">
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

<!-- Add/Edit Campaign Modal -->
<div class="modal" id="campaignModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Create Campaign</h3>
            <button class="modal-close" onclick="closeCampaignModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="ngo_campaigns.php">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="action" id="formAction" value="create_campaign">
            <input type="hidden" name="id" id="camp_id" value="">
            
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Campaign Name *</label>
                    <input type="text" name="name" id="camp_name" required placeholder="e.g., Save the Oceans">
                </div>
                
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category_id" id="camp_category" required>
                        <option value="">Select Category</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Target Amount (₹) *</label>
                    <input type="number" step="0.01" name="target_amount" id="camp_target" required placeholder="50000">
                </div>
                
                <div class="form-group">
                    <label>Start Date *</label>
                    <input type="date" name="start_date" id="camp_start" required>
                </div>
                
                <div class="form-group">
                    <label>End Date *</label>
                    <input type="date" name="end_date" id="camp_end" required>
                </div>
                
                <div class="form-group full-width">
                    <label>Short Description</label>
                    <input type="text" name="short_description" id="camp_short_desc" placeholder="A brief summary for cards...">
                </div>
                
                <div class="form-group full-width">
                    <label>Full Description *</label>
                    <textarea name="description" id="camp_desc" required placeholder="Detailed information about the campaign..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Status *</label>
                    <select name="status" id="camp_status" required>
                        <option value="draft">Draft</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                
                <div class="form-group" style="display: flex; align-items: center; gap: 10px; margin-top: 25px;">
                    <input type="checkbox" name="featured" id="camp_featured" value="1" style="width: auto;">
                    <label for="camp_featured" style="margin: 0;">Mark as Featured</label>
                </div>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn-primary" style="background: rgba(0,0,0,0.05); color: var(--text-dark);" onclick="closeCampaignModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Campaign</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/dashboard.js"></script>
<script>
    function openCampaignModal(campaign = null) {
        if (campaign) {
            document.getElementById('modalTitle').innerText = 'Edit Campaign';
            document.getElementById('formAction').value = 'edit_campaign';
            document.getElementById('camp_id').value = campaign.id;
            document.getElementById('camp_name').value = campaign.name;
            document.getElementById('camp_category').value = campaign.category_id;
            document.getElementById('camp_target').value = campaign.target_amount;
            document.getElementById('camp_start').value = campaign.start_date;
            document.getElementById('camp_end').value = campaign.end_date;
            document.getElementById('camp_short_desc').value = campaign.short_description;
            document.getElementById('camp_desc').value = campaign.description;
            document.getElementById('camp_status').value = campaign.status;
            document.getElementById('camp_featured').checked = campaign.featured == 1;
        } else {
            document.getElementById('modalTitle').innerText = 'Create Campaign';
            document.getElementById('formAction').value = 'create_campaign';
            document.getElementById('camp_id').value = '';
            document.getElementById('camp_name').value = '';
            document.getElementById('camp_category').value = '';
            document.getElementById('camp_target').value = '';
            document.getElementById('camp_start').value = '';
            document.getElementById('camp_end').value = '';
            document.getElementById('camp_short_desc').value = '';
            document.getElementById('camp_desc').value = '';
            document.getElementById('camp_status').value = 'draft';
            document.getElementById('camp_featured').checked = false;
        }
        
        document.getElementById('campaignModal').classList.add('active');
    }
    
    function closeCampaignModal() {
        document.getElementById('campaignModal').classList.remove('active');
    }
</script>
</body>
</html>
