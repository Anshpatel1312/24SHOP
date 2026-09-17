<?php
session_start();

require_once __DIR__ . '/db.php';
$conn = $mysqli;

$message = "";
$msg_type = "";

// -------------------------------------------------------------------------
// POST HANDLERS: CRUD Operations for Super Admin
// -------------------------------------------------------------------------

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // --- ADD CATEGORY ---
    if ($action === 'add_category') {
        $cat_name = mysqli_real_escape_string($conn, trim($_POST['cat_name']));
        $cat_icon = mysqli_real_escape_string($conn, trim($_POST['cat_icon'] ?? 'fa-tag'));
        if (!empty($cat_name)) {
            $chk = mysqli_query($conn, "SELECT id FROM categories WHERE name='$cat_name'");
            if (mysqli_num_rows($chk) > 0) {
                $message = "Category '$cat_name' already exists.";
                $msg_type = "danger";
            } else {
                if (mysqli_query($conn, "INSERT INTO categories (name, icon) VALUES ('$cat_name','$cat_icon')")) {
                    $message = "Category '$cat_name' added successfully!";
                    $msg_type = "success";
                } else {
                    $message = "Error: " . mysqli_error($conn);
                    $msg_type = "danger";
                }
            }
        } else {
            $message = "Category name cannot be empty.";
            $msg_type = "danger";
        }
    }

    // --- EDIT CATEGORY ---
    elseif ($action === 'edit_category') {
        $cat_id   = intval($_POST['cat_id']);
        $cat_name = mysqli_real_escape_string($conn, trim($_POST['cat_name']));
        $cat_icon = mysqli_real_escape_string($conn, trim($_POST['cat_icon'] ?? 'fa-tag'));
        if (!empty($cat_name)) {
            if (mysqli_query($conn, "UPDATE categories SET name='$cat_name', icon='$cat_icon' WHERE id=$cat_id")) {
                $message = "Category updated successfully!";
                $msg_type = "success";
            } else {
                $message = "Error: " . mysqli_error($conn);
                $msg_type = "danger";
            }
        }
    }

    // --- DELETE CATEGORY ---
    elseif ($action === 'delete_category') {
        $cat_id = intval($_POST['cat_id']);
        if (mysqli_query($conn, "DELETE FROM categories WHERE id=$cat_id")) {
            $message = "Category deleted successfully.";
            $msg_type = "success";
        } else {
            $message = "Error: " . mysqli_error($conn);
            $msg_type = "danger";
        }
    }

    // --- DELETE CUSTOMER ---
    elseif ($action === 'delete_customer') {
        $cid = intval($_POST['customer_id']);
        if (mysqli_query($conn, "DELETE FROM customers WHERE id = $cid")) {
            $message = "Customer #$cid deleted successfully.";
            $msg_type = "success";
        } else {
            $message = "Error deleting customer: " . mysqli_error($conn);
            $msg_type = "danger";
        }
    }

    // --- SHOW OR HIDE PRODUCT ---
    elseif ($action === 'show_product' || $action === 'hide_product') {
        $pid = intval($_POST['product_id']);
        $visible = $action === 'show_product' ? 1 : 0;
        if (mysqli_query($conn, "UPDATE product_details SET visible = $visible WHERE product_id = $pid")) {
            $message = "Product #$pid " . ($visible ? 'shown' : 'hidden') . " successfully.";
            $msg_type = "success";
        } else {
            $message = "Error changing product visibility: " . mysqli_error($conn);
            $msg_type = "danger";
        }
    }

    // --- DELETE SELLER ---
    elseif ($action === 'delete_seller') {
        $sid = intval($_POST['seller_id']);
        if (mysqli_query($conn, "DELETE FROM seller_details WHERE seller_id = $sid")) {
            $message = "Seller #$sid and associated shop removed successfully.";
            $msg_type = "success";
        } else {
            $message = "Error removing seller: " . mysqli_error($conn);
            $msg_type = "danger";
        }
    }

    // --- DELETE PRODUCT ---
    elseif ($action === 'delete_product') {
        $pid = intval($_POST['product_id']);
        if (mysqli_query($conn, "DELETE FROM product_details WHERE product_id = $pid")) {
            $message = "Product #$pid deleted successfully.";
            $msg_type = "success";
        } else {
            $message = "Error deleting product: " . mysqli_error($conn);
            $msg_type = "danger";
        }
    }
}

// -------------------------------------------------------------------------
// QUERY DATA FOR DASHBOARD
// -------------------------------------------------------------------------

// Customers List
$customers = [];
$cust_res = mysqli_query($conn, "SELECT * FROM customers ORDER BY id DESC");
if ($cust_res) {
    while ($r = mysqli_fetch_assoc($cust_res)) {
        $customers[] = $r;
    }
}

// Sellers & Shops Joined List
$sellers = [];
$seller_res = mysqli_query($conn, "SELECT s.*, sh.shop_id, sh.shop_name, sh.owner_name, sh.gst_number, sh.shop_city, sh.registration_certificate 
                                  FROM seller_details s 
                                  LEFT JOIN shop_details sh ON s.seller_id = sh.seller_id 
                                  ORDER BY s.seller_id DESC");
if ($seller_res) {
    while ($r = mysqli_fetch_assoc($seller_res)) {
        $sellers[] = $r;
    }
}

// Products List
$products = [];
// Assuming product_details has seller_id in the live DB as used in admin.php
$prod_res = mysqli_query($conn, "SELECT p.*, s.sellername, sh.shop_name 
                                 FROM product_details p
                                 LEFT JOIN seller_details s ON p.seller_id = s.seller_id
                                 LEFT JOIN shop_details sh ON s.seller_id = sh.seller_id
                                 ORDER BY p.product_id DESC");
if ($prod_res) {
    while ($r = mysqli_fetch_assoc($prod_res)) {
        $products[] = $r;
    }
}

// Categories List
$db_categories = [];
$cat_res = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");
if ($cat_res) {
    while ($r = mysqli_fetch_assoc($cat_res)) {
        $db_categories[] = $r;
    }
}
$total_categories = count($db_categories);

// Stats & Metrics
$total_customers = count($customers);
$total_sellers   = count($sellers);
$total_products  = count($products);
$total_stock     = 0;
$total_val       = 0.0;
$categories_cnt  = [];
$gender_cnt      = [];
$stock_status    = ['In Stock' => 0, 'Low Stock' => 0, 'Out of Stock' => 0];

foreach ($products as $p) {
    $sqty = intval($p['stockqty']);
    $price = floatval($p['price']);
    $cat = !empty($p['category']) ? $p['category'] : 'Uncategorized';
    $gen = !empty($p['gender']) ? ucfirst($p['gender']) : 'Unknown';

    $total_stock += $sqty;
    $total_val += ($sqty * $price);

    if (!isset($categories_cnt[$cat])) {
        $categories_cnt[$cat] = 0;
    }
    $categories_cnt[$cat]++;

    if (!isset($gender_cnt[$gen])) {
        $gender_cnt[$gen] = 0;
    }
    $gender_cnt[$gen]++;

    if ($sqty == 0) {
        $stock_status['Out of Stock']++;
    } elseif ($sqty <= 10) {
        $stock_status['Low Stock']++;
    } else {
        $stock_status['In Stock']++;
    }
}

$category_labels_json = json_encode(array_keys($categories_cnt));
$category_counts_json = json_encode(array_values($categories_cnt));

$gender_labels_json = json_encode(array_keys($gender_cnt));
$gender_counts_json = json_encode(array_values($gender_cnt));

$stock_labels_json = json_encode(array_keys($stock_status));
$stock_counts_json = json_encode(array_values($stock_status));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>24SHOP | Master Admin Panel</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --navy-dark: #0f172a;
            --navy-sidebar: #020617;
            /* Even darker for Master Admin */
            --navy-border: #334155;
            --brand-primary: #4f46e5;
            /* Indigo for Master Admin */
            --brand-accent: #f43f5e;
            /* Rose accent */
            --brand-success: #10b981;
            --brand-danger: #ef4444;
            --bg-canvas: #f1f5f9;
            --bg-card: #ffffff;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --text-light: #94a3b8;
            --border-line: #e2e8f0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-canvas);
            color: var(--text-dark);
            display: flex;
            min-height: 100vh;
        }

        /* --- Sidebar Navigation --- */
        .sidebar {
            width: 260px;
            background: var(--navy-sidebar);
            color: #fff;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
        }

        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid var(--navy-border);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-badge {
            width: 38px;
            height: 38px;
            background: var(--brand-accent);
            color: #fff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 800;
            font-size: 18px;
            box-shadow: 0 4px 12px rgba(244, 63, 94, 0.4);
        }

        .brand-text h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 18px;
            font-weight: 700;
            color: #ffffff;
        }

        .brand-text span {
            font-size: 11px;
            color: var(--brand-accent);
            letter-spacing: 1px;
            text-transform: uppercase;
            font-weight: 600;
        }

        .sidebar-nav {
            padding: 16px 12px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex: 1;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .nav-item:hover,
        .nav-item.active {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }

        .nav-item.active {
            border-left: 4px solid var(--brand-accent);
        }

        .nav-item i {
            width: 20px;
            font-size: 16px;
        }

        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid var(--navy-border);
            font-size: 12px;
            color: var(--text-light);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* --- Main Content Layout --- */
        .main-wrapper {
            margin-left: 260px;
            flex: 1;
            padding: 32px 40px;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
        }

        .page-title h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 26px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .page-title p {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .top-actions {
            display: flex;
            gap: 12px;
        }

        /* --- Alert Messages --- */
        .alert {
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        /* --- Stats KPI Grid --- */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .kpi-card {
            background: var(--bg-card);
            border: 1px solid var(--border-line);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease;
        }

        .kpi-card:hover {
            transform: translateY(-2px);
        }

        .kpi-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .kpi-title {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 0.5px;
        }

        .kpi-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .kpi-value {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 32px;
            font-weight: 700;
            color: var(--text-dark);
        }

        /* --- Tab View Panels --- */
        .tab-panel {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .tab-panel.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* --- Content Cards & Tables --- */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-line);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .card-title {
            font-family: 'Outfit', sans-serif;
            font-size: 18px;
            font-weight: 700;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            background: #f8fafc;
            padding: 12px 16px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border-line);
            letter-spacing: 0.5px;
        }

        td {
            padding: 14px 16px;
            font-size: 13.5px;
            border-bottom: 1px solid var(--border-line);
            vertical-align: middle;
        }

        tr:hover td {
            background: #f8fafc;
        }

        .badge-pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-purple {
            background: #e0e7ff;
            color: #3730a3;
        }

        .btn-action {
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 14px;
            transition: color 0.2s;
            padding: 4px;
        }

        .btn-action.delete:hover {
            color: var(--brand-danger);
        }

        .img-thumb {
            width: 44px;
            height: 44px;
            border-radius: 6px;
            object-fit: cover;
            border: 1px solid var(--border-line);
            background: #f1f5f9;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
        }
    </style>
</head>

<body>

    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="brand-badge">SA</div>
            <div class="brand-text">
                <h2>24SHOP</h2>
                <span>Super Admin</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-item active" onclick="switchTab('dashboard', this)">
                <i class="fa-solid fa-earth-americas"></i> Global Dashboard
            </div>
            <div class="nav-item" onclick="switchTab('customers', this)">
                <i class="fa-solid fa-users"></i> Manage Customers
            </div>
            <div class="nav-item" onclick="switchTab('sellers', this)">
                <i class="fa-solid fa-store"></i> Manage Sellers
            </div>
            <div class="nav-item" onclick="switchTab('products', this)">
                <i class="fa-solid fa-boxes-stacked"></i> All Products
            </div>
            <div class="nav-item" onclick="switchTab('categories', this)">
                <i class="fa-solid fa-layer-group"></i> Manage Categories
            </div>
            <a href="index.php" class="nav-item" style="margin-top: auto;">
                <i class="fa-solid fa-globe"></i> View Storefront
            </a>
            <a href="shoplogin.php" class="nav-item" style="color: #ef4444;">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
            </a>
        </nav>

        <div class="sidebar-footer">
            <i class="fa-solid fa-user-shield" style="font-size: 18px; color: var(--brand-accent);"></i>
            <div>
                <strong style="color: #fff; display: block;">Super Admin</strong>
                <span>System Owner</span>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="main-wrapper">

        <!-- Top Bar Header -->
        <div class="top-bar">
            <div class="page-title">
                <h1 id="tabTitle">Global Dashboard</h1>
                <p id="tabSub">System overview and complete platform metrics</p>
            </div>
        </div>

        <!-- Alert Notification -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $msg_type; ?>">
                <i class="fa-solid <?php echo $msg_type === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- ============================================== -->
        <!-- TAB 1: DASHBOARD                               -->
        <!-- ============================================== -->
        <div id="tab-dashboard" class="tab-panel active">
            <!-- KPI Metrics Grid -->
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">Total Customers</span>
                        <div class="kpi-icon" style="background: #e0e7ff; color: #4f46e5;"><i class="fa-solid fa-users"></i></div>
                    </div>
                    <div class="kpi-value"><?php echo number_format($total_customers); ?></div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">Active Sellers</span>
                        <div class="kpi-icon" style="background: #fce7f3; color: #db2777;"><i class="fa-solid fa-store"></i></div>
                    </div>
                    <div class="kpi-value"><?php echo number_format($total_sellers); ?></div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">Total Products</span>
                        <div class="kpi-icon" style="background: #d1fae5; color: #059669;"><i class="fa-solid fa-boxes-stacked"></i></div>
                    </div>
                    <div class="kpi-value"><?php echo number_format($total_products); ?></div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">Est. Stock Value</span>
                        <div class="kpi-icon" style="background: #fef3c7; color: #d97706;"><i class="fa-solid fa-indian-rupee-sign"></i></div>
                    </div>
                    <div class="kpi-value">₹<?php echo number_format($total_val, 2); ?></div>
                </div>
            </div>

            <div class="grid-2" style="margin-bottom: 24px;">
                <div class="card" style="margin-bottom: 0;">
                    <div class="card-header">
                        <div class="card-title">Recent Sellers Joined</div>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Seller Name</th>
                                    <th>Shop Name</th>
                                    <th>City</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $recent = array_slice($sellers, 0, 5);
                                if (count($recent) > 0):
                                    foreach ($recent as $s): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($s['sellername']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($s['shop_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($s['shop_city'] ?? 'N/A'); ?></td>
                                            <td><span class="badge-pill badge-success">Active</span></td>
                                        </tr>
                                    <?php endforeach;
                                else: ?>
                                    <tr>
                                        <td colspan="4">No sellers found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card" style="margin-bottom: 0;">
                    <div class="card-header">
                        <div class="card-title">Product Categories</div>
                    </div>
                    <div class="chart-container">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="grid-2" style="margin-bottom: 24px;">
                <div class="card" style="margin-bottom: 0;">
                    <div class="card-header">
                        <div class="card-title">Stock Levels</div>
                    </div>
                    <div class="chart-container">
                        <canvas id="stockChart"></canvas>
                    </div>
                </div>
                <div class="card" style="margin-bottom: 0;">
                    <div class="card-header">
                        <div class="card-title">Products by Gender</div>
                    </div>
                    <div class="chart-container">
                        <canvas id="genderChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title">Platform Revenue & Sales Activity (Simulated)</div>
                </div>
                <div class="chart-container" style="height: 350px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- TAB 2: MANAGE CUSTOMERS                        -->
        <!-- ============================================== -->
        <div id="tab-customers" class="tab-panel">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">All Registered Customers</div>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Email Address</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($customers) > 0):
                                foreach ($customers as $c): ?>
                                    <tr>
                                        <td>#<?php echo $c['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($c['full_name'] ?? $c['fullname'] ?? $c['name'] ?? 'Customer'); ?></strong></td>
                                        <td><?php echo htmlspecialchars($c['email']); ?></td>
                                        <td>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('WARNING: Are you sure you want to delete this customer? This cannot be undone.');">
                                                <input type="hidden" name="action" value="delete_customer">
                                                <input type="hidden" name="customer_id" value="<?php echo $c['id']; ?>">
                                                <button type="submit" class="btn-action delete" title="Delete Customer">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="4">No customers registered yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- TAB 3: MANAGE SELLERS                          -->
        <!-- ============================================== -->
        <div id="tab-sellers" class="tab-panel">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">All Sellers & Shops</div>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Seller Details</th>
                                <th>Shop Info</th>
                                <th>GST / Cert</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($sellers) > 0):
                                foreach ($sellers as $s): ?>
                                    <tr>
                                        <td>#<?php echo $s['seller_id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($s['sellername']); ?></strong><br>
                                            <span style="color:var(--text-muted); font-size:12px;"><?php echo htmlspecialchars($s['email']); ?> | <?php echo htmlspecialchars($s['mobile']); ?></span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($s['shop_name'] ?? 'N/A'); ?></strong><br>
                                            <span style="color:var(--text-muted); font-size:12px;"><?php echo htmlspecialchars($s['shop_city'] ?? ''); ?></span>
                                        </td>
                                        <td>
                                            <?php if (!empty($s['gst_number'])): ?>
                                                <span class="badge-pill badge-info">GST: <?php echo htmlspecialchars($s['gst_number']); ?></span>
                                            <?php else: ?>
                                                <span style="font-size:12px; color:var(--text-light);">No GST</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('WARNING: Deleting this seller will also delete their shop and all their products. Proceed?');">
                                                <input type="hidden" name="action" value="delete_seller">
                                                <input type="hidden" name="seller_id" value="<?php echo $s['seller_id']; ?>">
                                                <button type="submit" class="btn-action delete" title="Delete Seller & Shop">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="5">No sellers registered yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- TAB 4: MANAGE PRODUCTS                         -->
        <!-- ============================================== -->
        <div id="tab-products" class="tab-panel">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Global Product Catalog</div>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Img</th>
                                <th>Product Details</th>
                                <th>Seller & Shop</th>
                                <th>Price & Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($products) > 0):
                                foreach ($products as $p): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($p['image'])): ?>
                                                <img src="product image/<?php echo htmlspecialchars($p['image']); ?>" class="img-thumb" alt="Prod">
                                            <?php else: ?>
                                                <div class="img-thumb" style="display:flex; align-items:center; justify-content:center; color:#cbd5e1;"><i class="fa-solid fa-image"></i></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($p['product_name']); ?></strong><br>
                                            <span class="badge-pill badge-purple"><?php echo htmlspecialchars($p['category']); ?></span>
                                            <span style="font-size:12px; color:var(--text-muted); margin-left:8px;"><?php echo htmlspecialchars($p['brand']); ?></span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($p['shop_name'] ?? 'Unknown Shop'); ?></strong><br>
                                            <span style="color:var(--text-muted); font-size:12px;"><?php echo htmlspecialchars($p['sellername'] ?? 'Unknown'); ?></span>
                                        </td>
                                        <td>
                                            <strong>₹<?php echo number_format($p['price'], 2); ?></strong><br>
                                            <?php if ($p['stockqty'] > 10): ?>
                                                <span class="badge-pill badge-success">Stock: <?php echo $p['stockqty']; ?></span>
                                            <?php elseif ($p['stockqty'] > 0): ?>
                                                <span class="badge-pill badge-warning">Low: <?php echo $p['stockqty']; ?></span>
                                            <?php else: ?>
                                                <span class="badge-pill badge-danger">Out of Stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to remove this product?');">
                                                <input type="hidden" name="action" value="delete_product">
                                                <input type="hidden" name="product_id" value="<?php echo $p['product_id']; ?>">
                                                <button type="submit" class="btn-action delete" title="Delete Product">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="5">No products found across the platform.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- TAB 5: MANAGE CATEGORIES                       -->
        <!-- ============================================== -->
        <div id="tab-categories" class="tab-panel">

            <!-- Add Category Form -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i class="fa-solid fa-plus" style="color:var(--brand-success);"></i> Add New Category</div>
                </div>
                <form method="POST" style="display:flex; gap:14px; align-items:flex-end; flex-wrap:wrap;">
                    <input type="hidden" name="action" value="add_category">
                    <div style="display:flex; flex-direction:column; gap:6px; flex:1; min-width:180px;">
                        <label style="font-size:12px; font-weight:600; color:var(--text-muted); text-transform:uppercase;">Category Name *</label>
                        <input type="text" name="cat_name" placeholder="e.g. Electronics" required
                            style="padding:10px 14px; border:1.5px solid var(--border-line); border-radius:8px; font-size:14px; outline:none; font-family:inherit;">
                    </div>
                    <div style="display:flex; flex-direction:column; gap:6px; flex:1; min-width:180px;">
                        <label style="font-size:12px; font-weight:600; color:var(--text-muted); text-transform:uppercase;">FontAwesome Icon Class</label>
                        <input type="text" name="cat_icon" placeholder="e.g. fa-laptop" value="fa-tag"
                            style="padding:10px 14px; border:1.5px solid var(--border-line); border-radius:8px; font-size:14px; outline:none; font-family:inherit;">
                    </div>
                    <button type="submit" style="background:var(--brand-primary); color:#fff; border:none; padding:11px 24px; border-radius:8px; font-weight:700; font-size:14px; cursor:pointer; height:42px;">
                        <i class="fa-solid fa-plus"></i> Add Category
                    </button>
                </form>
            </div>

            <!-- Categories Table -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">All Categories (<?php echo $total_categories; ?>)</div>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Icon Preview</th>
                                <th>Category Name</th>
                                <th>Icon Class</th>
                                <th>Products Count</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($db_categories) > 0):
                                foreach ($db_categories as $cat):
                                    // Count products for this category
                                    $cat_name_esc = mysqli_real_escape_string($conn, $cat['name']);
                                    $cnt_res = mysqli_query($conn, "SELECT COUNT(*) as c FROM product_details WHERE category='$cat_name_esc'");
                                    $cnt_row = mysqli_fetch_assoc($cnt_res);
                                    $prod_count = $cnt_row['c'] ?? 0;
                            ?>
                                <tr>
                                    <td>#<?php echo $cat['id']; ?></td>
                                    <td><i class="fa-solid <?php echo htmlspecialchars($cat['icon']); ?>" style="font-size:22px; color:var(--brand-primary);"></i></td>
                                    <td><strong><?php echo htmlspecialchars($cat['name']); ?></strong></td>
                                    <td><code style="background:#f1f5f9; padding:3px 8px; border-radius:6px; font-size:12px;"><?php echo htmlspecialchars($cat['icon']); ?></code></td>
                                    <td><span class="badge-pill badge-info"><?php echo $prod_count; ?> products</span></td>
                                    <td style="display:flex; gap:8px; align-items:center;">
                                        <!-- Edit Button -->
                                        <button onclick="openEditCat(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars($cat['name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($cat['icon'], ENT_QUOTES); ?>')"
                                            style="background:#eff6ff; color:var(--brand-primary); border:none; padding:6px 14px; border-radius:7px; font-size:13px; font-weight:600; cursor:pointer;">
                                            <i class="fa-solid fa-pen"></i> Edit
                                        </button>
                                        <!-- Delete Button -->
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this category? Products assigned to it will not be deleted, but their category will be unaffected.');">
                                            <input type="hidden" name="action" value="delete_category">
                                            <input type="hidden" name="cat_id" value="<?php echo $cat['id']; ?>">
                                            <button type="submit" style="background:#fee2e2; color:#991b1b; border:none; padding:6px 14px; border-radius:7px; font-size:13px; font-weight:600; cursor:pointer;">
                                                <i class="fa-solid fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach;
                            else: ?>
                                <tr><td colspan="6" style="text-align:center; color:var(--text-muted); padding:30px;"><i class="fa-solid fa-layer-group" style="font-size:2rem; display:block; margin-bottom:10px; opacity:0.3;"></i> No categories yet. Add your first one above!</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Edit Category Modal -->
        <div id="editCatModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.55); z-index:2000; align-items:center; justify-content:center; backdrop-filter:blur(4px);">
            <div style="background:#fff; border-radius:18px; padding:32px; max-width:480px; width:100%; box-shadow:0 24px 48px rgba(0,0,0,0.2);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <h3 style="font-family:'Outfit',sans-serif; font-size:20px; font-weight:700;">Edit Category</h3>
                    <button onclick="closeEditCat()" style="background:none; border:none; font-size:20px; cursor:pointer; color:#64748b;">✕</button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="edit_category">
                    <input type="hidden" name="cat_id" id="editCatId">
                    <div style="margin-bottom:14px;">
                        <label style="font-size:12px; font-weight:600; color:#64748b; text-transform:uppercase; display:block; margin-bottom:6px;">Category Name *</label>
                        <input type="text" name="cat_name" id="editCatName" required
                            style="width:100%; padding:11px 14px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:14px; outline:none; font-family:inherit;">
                    </div>
                    <div style="margin-bottom:20px;">
                        <label style="font-size:12px; font-weight:600; color:#64748b; text-transform:uppercase; display:block; margin-bottom:6px;">FontAwesome Icon Class</label>
                        <input type="text" name="cat_icon" id="editCatIcon"
                            style="width:100%; padding:11px 14px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:14px; outline:none; font-family:inherit;">
                        <p style="font-size:12px; color:#94a3b8; margin-top:5px;">e.g. <code>fa-laptop</code>, <code>fa-shirt</code>, <code>fa-gem</code></p>
                    </div>
                    <div style="display:flex; gap:10px; justify-content:flex-end;">
                        <button type="button" onclick="closeEditCat()" style="background:#f1f5f9; color:#64748b; border:none; padding:10px 20px; border-radius:8px; font-weight:600; cursor:pointer;">Cancel</button>
                        <button type="submit" style="background:var(--brand-primary); color:#fff; border:none; padding:10px 24px; border-radius:8px; font-weight:700; cursor:pointer;">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

    </main>

    <!-- JavaScript for Interactivity -->
    <script>
        // Tab Switching Logic
        const tabTitles = {
            'dashboard': {
                title: 'Global Dashboard',
                sub: 'System overview and complete platform metrics'
            },
            'customers': {
                title: 'Manage Customers',
                sub: 'View and manage registered users'
            },
            'sellers': {
                title: 'Manage Sellers',
                sub: 'Oversight of all marketplace sellers and shops'
            },
            'products': {
                title: 'Global Catalog',
                sub: 'Monitor all products across the platform'
            },
            'categories': {
                title: 'Manage Categories',
                sub: 'Add, edit, and delete product categories shown on the website'
            }
        };

        function openEditCat(id, name, icon) {
            document.getElementById('editCatId').value = id;
            document.getElementById('editCatName').value = name;
            document.getElementById('editCatIcon').value = icon;
            const modal = document.getElementById('editCatModal');
            modal.style.display = 'flex';
        }

        function closeEditCat() {
            document.getElementById('editCatModal').style.display = 'none';
        }

        function switchTab(tabId, element) {
            // Update Active Nav Item
            document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
            element.classList.add('active');

            // Hide all panels, show target
            document.querySelectorAll('.tab-panel').forEach(el => el.classList.remove('active'));
            document.getElementById('tab-' + tabId).classList.add('active');

            // Update Page Titles
            document.getElementById('tabTitle').innerText = tabTitles[tabId].title;
            document.getElementById('tabSub').innerText = tabTitles[tabId].sub;
        }

        // Initialize Chart.js for Categories
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Categories Doughnut Chart
            const ctxCat = document.getElementById('categoryChart').getContext('2d');
            const catLabels = <?php echo $category_labels_json; ?>;
            const catData = <?php echo $category_counts_json; ?>;
            if (catLabels.length > 0) {
                new Chart(ctxCat, {
                    type: 'doughnut',
                    data: {
                        labels: catLabels,
                        datasets: [{
                            data: catData,
                            backgroundColor: ['#4f46e5', '#ec4899', '#06b6d4', '#f59e0b', '#10b981', '#8b5cf6'],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    font: {
                                        family: "'Inter', sans-serif",
                                        size: 12
                                    },
                                    usePointStyle: true,
                                    padding: 20
                                }
                            }
                        },
                        cutout: '70%'
                    }
                });
            } else {
                document.getElementById('categoryChart').parentElement.innerHTML = '<div style="display:flex; height:100%; align-items:center; justify-content:center; color:#94a3b8;">No data available.</div>';
            }

            // 2. Stock Levels Bar Chart
            const ctxStock = document.getElementById('stockChart').getContext('2d');
            const stockLabels = <?php echo $stock_labels_json; ?>;
            const stockData = <?php echo $stock_counts_json; ?>;
            if (stockLabels.length > 0) {
                new Chart(ctxStock, {
                    type: 'bar',
                    data: {
                        labels: stockLabels,
                        datasets: [{
                            label: 'Number of Products',
                            data: stockData,
                            backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    borderDash: [5, 5],
                                    color: '#e2e8f0'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }

            // 3. Gender Pie Chart
            const ctxGender = document.getElementById('genderChart').getContext('2d');
            const genderLabels = <?php echo $gender_labels_json; ?>;
            const genderData = <?php echo $gender_counts_json; ?>;
            if (genderLabels.length > 0) {
                new Chart(ctxGender, {
                    type: 'pie',
                    data: {
                        labels: genderLabels,
                        datasets: [{
                            data: genderData,
                            backgroundColor: ['#3b82f6', '#ec4899', '#14b8a6', '#8b5cf6', '#64748b'],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    font: {
                                        family: "'Inter', sans-serif",
                                        size: 12
                                    },
                                    usePointStyle: true,
                                    padding: 20
                                }
                            }
                        }
                    }
                });
            } else {
                document.getElementById('genderChart').parentElement.innerHTML = '<div style="display:flex; height:100%; align-items:center; justify-content:center; color:#94a3b8;">No data available.</div>';
            }

            // 4. Revenue & Sales Activity (Simulated) Line Chart
            const ctxRev = document.getElementById('revenueChart').getContext('2d');
            new Chart(ctxRev, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                    datasets: [{
                            label: 'Total Revenue (₹)',
                            data: [12000, 19000, 15000, 25000, 22000, 30000, 28000],
                            borderColor: '#4f46e5',
                            backgroundColor: 'rgba(79, 70, 229, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#4f46e5',
                            pointBorderWidth: 2,
                            pointRadius: 4
                        },
                        {
                            label: 'Product Sales (units)',
                            data: [150, 230, 180, 320, 290, 400, 350],
                            borderColor: '#06b6d4',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            tension: 0.4,
                            pointRadius: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: {
                                    family: "'Inter', sans-serif",
                                    size: 12
                                },
                                usePointStyle: true
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#e2e8f0',
                                borderDash: [4, 4]
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>

</html>