<?php
session_start();
if (!isset($_SESSION['seller_id'])) {
    header("Location: shoplogin.php");
    exit();
}
$seller_id = intval($_SESSION['seller_id']);

require_once __DIR__ . '/db.php';
$conn = $mysqli;

$message = "";
$msg_type = "";

// Ensure upload directory exists
$upload_dir = __DIR__ . "/product image/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// -------------------------------------------------------------------------
// POST HANDLERS: CRUD Operations for Products & Sellers
// -------------------------------------------------------------------------

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // --- ADD PRODUCT ---
    if ($action === 'add_product') {
        $pname        = mysqli_real_escape_string($conn, trim($_POST['product_name']));
        $category     = mysqli_real_escape_string($conn, trim($_POST['category']));
        $gender       = mysqli_real_escape_string($conn, trim($_POST['gender']));
        $description  = mysqli_real_escape_string($conn, trim($_POST['description']));
        $brand        = mysqli_real_escape_string($conn, trim($_POST['brand']));
        $price        = floatval($_POST['price']);
        $discountprice = !empty($_POST['discountprice']) ? floatval($_POST['discountprice']) : NULL;
        $stockqty     = intval($_POST['stockqty']);
        $color        = mysqli_real_escape_string($conn, trim($_POST['color']));
        $size         = mysqli_real_escape_string($conn, trim($_POST['size']));

        // Validate: discount must be strictly less than full price
        if ($discountprice !== NULL && $discountprice >= $price) {
            $discountprice = NULL; // ignore invalid discount
        }

        $image_name = '';
        if (!empty($_FILES['image']['name'])) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = time() . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($_FILES['image']['name'], PATHINFO_FILENAME)) . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name);
        }

        $stmt = mysqli_prepare($conn, "INSERT INTO product_details (seller_id, product_name, category, gender, description, brand, price, discountprice, stockqty, color, size, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "issssssdisss", $seller_id, $pname, $category, $gender, $description, $brand, $price, $discountprice, $stockqty, $color, $size, $image_name);
            if (mysqli_stmt_execute($stmt)) {
                $message = "Product '$pname' added successfully!";
                $msg_type = "success";
            } else {
                $message = "Error adding product: " . mysqli_error($conn);
                $msg_type = "danger";
            }
            mysqli_stmt_close($stmt);
        } else {
            $message = "Error preparing statement: " . mysqli_error($conn);
            $msg_type = "danger";
        }
    }

    // --- EDIT PRODUCT ---
    elseif ($action === 'edit_product') {
        $pid          = intval($_POST['product_id']);
        $pname        = mysqli_real_escape_string($conn, trim($_POST['product_name']));
        $category     = mysqli_real_escape_string($conn, trim($_POST['category']));
        $gender       = mysqli_real_escape_string($conn, trim($_POST['gender']));
        $description  = mysqli_real_escape_string($conn, trim($_POST['description']));
        $brand        = mysqli_real_escape_string($conn, trim($_POST['brand']));
        $price        = floatval($_POST['price']);
        $discountprice = !empty($_POST['discountprice']) ? floatval($_POST['discountprice']) : NULL;
        $stockqty     = intval($_POST['stockqty']);
        $color        = mysqli_real_escape_string($conn, trim($_POST['color']));
        $size         = mysqli_real_escape_string($conn, trim($_POST['size']));

        // Validate: discount must be strictly less than full price
        if ($discountprice !== NULL && $discountprice >= $price) {
            $discountprice = NULL; // ignore invalid discount
        }

        // Check image update
        if (!empty($_FILES['image']['name'])) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = time() . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($_FILES['image']['name'], PATHINFO_FILENAME)) . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name);
            $stmt = mysqli_prepare($conn, "UPDATE product_details SET product_name=?, category=?, gender=?, description=?, brand=?, price=?, discountprice=?, stockqty=?, color=?, size=?, image=? WHERE product_id=? AND seller_id=?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssssssdissii", $pname, $category, $gender, $description, $brand, $price, $discountprice, $stockqty, $color, $size, $image_name, $pid, $seller_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE product_details SET product_name=?, category=?, gender=?, description=?, brand=?, price=?, discountprice=?, stockqty=?, color=?, size=? WHERE product_id=? AND seller_id=?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssssssdisii", $pname, $category, $gender, $description, $brand, $price, $discountprice, $stockqty, $color, $size, $pid, $seller_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }

        if (mysqli_affected_rows($conn) >= 0) {
            $message = "Product #$pid updated successfully!";
            $msg_type = "success";
        } else {
            $message = "Error updating product: " . mysqli_error($conn);
            $msg_type = "danger";
        }
    }

    // --- DELETE PRODUCT ---
    elseif ($action === 'delete_product') {
        $pid = intval($_POST['product_id']);
        if (mysqli_query($conn, "DELETE FROM product_details WHERE product_id = $pid AND seller_id = $seller_id")) {
            $message = "Product #$pid deleted successfully.";
            $msg_type = "success";
        } else {
            $message = "Error deleting product: " . mysqli_error($conn);
            $msg_type = "danger";
        }
    }

    // --- SHOW OR HIDE PRODUCT ---
    elseif ($action === 'show_product' || $action === 'hide_product') {
        $pid = intval($_POST['product_id']);
        $visible = $action === 'show_product' ? 1 : 0;
        if (mysqli_query($conn, "UPDATE product_details SET visible = $visible WHERE product_id = $pid AND seller_id = $seller_id")) {
            $message = "Product #$pid " . ($visible ? 'shown' : 'hidden') . " successfully.";
            $msg_type = "success";
        } else {
            $message = "Error changing product visibility: " . mysqli_error($conn);
            $msg_type = "danger";
        }
    }

    // --- RESTOCK PRODUCT ---
    elseif ($action === 'restock') {
        $pid = intval($_POST['product_id']);
        $add_qty = intval($_POST['add_qty']);
        if ($add_qty > 0) {
            if (mysqli_query($conn, "UPDATE product_details SET stockqty = stockqty + $add_qty WHERE product_id = $pid AND seller_id = $seller_id")) {
                $message = "Added $add_qty units to stock for Product #$pid.";
                $msg_type = "success";
            } else {
                $message = "Error updating stock: " . mysqli_error($conn);
                $msg_type = "danger";
            }
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
}

// -------------------------------------------------------------------------
// QUERY DATA FOR DASHBOARD
// -------------------------------------------------------------------------

// Products List
$products = [];
$prod_res = mysqli_query($conn, "SELECT * FROM product_details WHERE seller_id = $seller_id ORDER BY product_id DESC");
if ($prod_res) {
    while ($r = mysqli_fetch_assoc($prod_res)) {
        $products[] = $r;
    }
}

// Sellers & Shops Joined List
// $sellers = [];
// $seller_res = mysqli_query($conn, "SELECT s.*, sh.shop_id, sh.shop_name, sh.owner_name, sh.gst_number, sh.shop_address, sh.shop_city, sh.shop_state, sh.shop_pincode, sh.registration_certificate 
//                                   FROM seller_details s 
//                                   LEFT JOIN shop_details sh ON s.seller_id = sh.seller_id 
//                                   ORDER BY s.seller_id DESC");
// if ($seller_res) {
//     while ($r = mysqli_fetch_assoc($seller_res)) {
//         $sellers[] = $r;
//     }
// }

// Stats & Metrics
$total_products = count($products);
// $total_sellers  = count($sellers);
$total_stock    = 0;
$total_val      = 0.0;
$low_stock_cnt  = 0;
$out_stock_cnt  = 0;
$categories_cnt = [];

foreach ($products as $p) {
    $sqty = intval($p['stockqty']);
    $price = floatval($p['price']);
    $cat = !empty($p['category']) ? $p['category'] : 'Uncategorized';

    $total_stock += $sqty;
    $total_val += ($sqty * $price);

    if ($sqty == 0) {
        $out_stock_cnt++;
    } elseif ($sqty <= 10) {
        $low_stock_cnt++;
    }

    if (!isset($categories_cnt[$cat])) {
        $categories_cnt[$cat] = 0;
    }
    $categories_cnt[$cat]++;
}

$category_labels_json = json_encode(array_keys($categories_cnt));
$category_counts_json = json_encode(array_values($categories_cnt));

// Resolve orders through this seller's product IDs stored in each cart snapshot.
$seller_product_ids = [];
foreach ($products as $product) {
    $seller_product_ids[(int) $product['product_id']] = true;
}
$seller_orders = [];
$order_res = mysqli_query($conn, "SELECT * FROM orders ORDER BY created_at DESC");
if ($order_res) {
    while ($order = mysqli_fetch_assoc($order_res)) {
        $order_items = json_decode($order['items_json'], true);
        $seller_items = [];
        if (is_array($order_items)) {
            foreach ($order_items as $item) {
                $item_product_id = (int) ($item['id'] ?? $item['product_id'] ?? 0);
                if (isset($seller_product_ids[$item_product_id])) {
                    $item['product_id'] = $item_product_id;
                    $seller_items[] = $item;
                }
            }
        }
        if (!empty($seller_items)) {
            $order['seller_items'] = $seller_items;
            $order['seller_total'] = 0;
            foreach ($seller_items as $seller_item) {
                $seller_item_qty = max(1, (int) ($seller_item['qty'] ?? $seller_item['quantity'] ?? 1));
                $order['seller_total'] += (float) ($seller_item['price'] ?? 0) * $seller_item_qty;
            }
            $seller_orders[] = $order;
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST['action'] ?? '') === 'update_order_status') {
    $order_id = trim($_POST['order_id'] ?? '');
    $allowed_statuses = ['Order Confirmed', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
    $new_status = $_POST['status'] ?? '';
    $owned_order = false;
    foreach ($seller_orders as $seller_order) {
        if ($seller_order['order_id'] === $order_id) {
            $owned_order = true;
            break;
        }
    }
    if ($owned_order && in_array($new_status, $allowed_statuses, true)) {
        $status_stmt = mysqli_prepare($conn, "UPDATE orders SET status = ? WHERE order_id = ?");
        mysqli_stmt_bind_param($status_stmt, "ss", $new_status, $order_id);
        if (mysqli_stmt_execute($status_stmt)) {
            $message = "Order $order_id updated to $new_status.";
            $msg_type = "success";
        } else {
            $message = "Unable to update order status.";
            $msg_type = "danger";
        }
        mysqli_stmt_close($status_stmt);
    } else {
        $message = "That order is not connected to this seller.";
        $msg_type = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>24SHOP | Admin Dashboard</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --navy-dark: #0f172a;
            --navy-sidebar: #1e293b;
            --navy-border: #334155;
            --brand-primary: #2563eb;
            --brand-accent: #f59e0b;
            --brand-success: #10b981;
            --brand-danger: #ef4444;
            --bg-canvas: #f8fafc;
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
            color: #0f172a;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 800;
            font-size: 18px;
        }

        .brand-text h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 18px;
            font-weight: 700;
            color: #ffffff;
        }

        .brand-text span {
            font-size: 11px;
            color: var(--text-light);
            letter-spacing: 1px;
            text-transform: uppercase;
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
            background: rgba(255, 255, 255, 0.08);
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

        .btn-primary {
            background: var(--brand-primary);
            color: #fff;
            padding: 10px 18px;
            border-radius: 8px;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-accent {
            background: var(--brand-accent);
            color: #0f172a;
            padding: 10px 18px;
            border-radius: 8px;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s;
        }

        .btn-accent:hover {
            background: #d97706;
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
            border-radius: 16px;
            padding: 22px 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            cursor: default;
        }

        .kpi-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.1);
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
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .kpi-value {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 32px;
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1.2;
            margin-top: 4px;
        }

        /* --- Tab View Panels --- */
        .tab-panel {
            display: none;
        }

        .tab-panel.active {
            display: block;
        }

        /* --- Content Cards & Tables --- */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-line);
            border-radius: 16px;
            padding: 28px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
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
            padding: 13px 16px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
            border-bottom: 2px solid var(--border-line);
            letter-spacing: 0.6px;
        }

        td {
            padding: 16px 16px;
            font-size: 13.5px;
            border-bottom: 1px solid var(--border-line);
            vertical-align: middle;
            transition: background 0.15s ease;
        }

        tr:hover td {
            background: #f0f6ff;
        }

        .badge-pill {
            display: inline-flex;
            align-items: center;
            padding: 5px 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
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

        .img-thumb {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            object-fit: cover;
            border: 1px solid var(--border-line);
            background: #f1f5f9;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        }

        /* --- Filter Controls --- */
        .controls-row {
            display: flex;
            gap: 12px;
            margin-bottom: 18px;
            align-items: center;
        }

        .search-box {
            position: relative;
            flex: 1;
        }

        .search-box input {
            width: 100%;
            padding: 10px 14px 10px 40px;
            border: 1.5px solid var(--border-line);
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            transition: all 0.25s ease;
            font-family: 'Inter', sans-serif;
        }

        .search-box input:focus {
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
        }

        .select-box {
            padding: 10px 14px;
            border: 1.5px solid var(--border-line);
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            background: #fff;
            transition: all 0.25s ease;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
        }

        .select-box:focus {
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        /* --- Modals --- */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            backdrop-filter: blur(4px);
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-box {
            background: #fff;
            width: 100%;
            max-width: 600px;
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 24px 48px -8px rgba(0, 0, 0, 0.2), 0 8px 16px -4px rgba(0,0,0,0.08);
            max-height: 92vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h3 {
            font-family: 'Outfit', sans-serif;
            font-size: 20px;
            font-weight: 700;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 18px;
            color: var(--text-muted);
            cursor: pointer;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 14px;
        }

        .form-group.full {
            grid-column: span 2;
        }

        .form-group label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 11px 14px;
            border: 1.5px solid var(--border-line);
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: all 0.25s ease;
            font-family: 'Inter', sans-serif;
            line-height: 1.5;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-cancel {
            background: #f1f5f9;
            color: var(--text-muted);
            padding: 10px 16px;
            border-radius: 8px;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="brand-badge">24</div>
            <div class="brand-text">
                <h2>24SHOP</h2>
                <span>Store Control</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-item active" onclick="switchTab('dashboard', this)">
                <i class="fa-solid fa-chart-line"></i> Dashboard
            </div>
            <div class="nav-item" onclick="switchTab('products', this)">
                <i class="fa-solid fa-box"></i> Products Catalog
            </div>

            <div class="nav-item" onclick="switchTab('inventory', this)">
                <i class="fa-solid fa-warehouse"></i> Inventory
            </div>
            <div class="nav-item" onclick="switchTab('analytics', this)">
                <i class="fa-solid fa-chart-pie"></i> Analytics
            </div>
            <div class="nav-item" onclick="switchTab('orders', this)">
                <i class="fa-solid fa-receipt"></i> Orders
                <?php if (!empty($seller_orders)): ?><span class="nav-count"><?php echo count($seller_orders); ?></span><?php endif; ?>
            </div>
            <a href="index.php" class="nav-item" style="margin-top: auto;">
                <i class="fa-solid fa-globe"></i> View Storefront
            </a>
            <a href="logout.php?type=seller" class="nav-item" style="color: #f87171;">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </nav>

        <div class="sidebar-footer">
            <i class="fa-solid fa-user-shield" style="font-size: 18px; color: var(--brand-accent);"></i>
            <div>
                <strong style="color: #fff; display: block;">Administrator</strong>
                <span>admin@24shop.com</span>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="main-wrapper">

        <!-- Top Bar Header -->
        <div class="top-bar">
            <div class="page-title">
                <h1 id="tabTitle">Overview Dashboard</h1>
                <p id="tabSub">Live metrics and store management system</p>
            </div>
            <div class="top-actions">
                <button class="btn-accent" onclick="openAddProductModal()">
                    <i class="fa-solid fa-plus"></i> Add New Product
                </button>
            </div>
        </div>

        <!-- Alert Notification -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $msg_type; ?>">
                <i class="fa-solid <?php echo $msg_type === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- KPI Metrics Grid -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-title">Total Products</span>
                    <div class="kpi-icon" style="background: #dbeafe; color: #1e40af;"><i class="fa-solid fa-boxes-stacked"></i></div>
                </div>
                <div class="kpi-value"><?php echo $total_products; ?></div>
            </div>



            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-title">Total Inventory Units</span>
                    <div class="kpi-icon" style="background: #d1fae5; color: #065f46;"><i class="fa-solid fa-cubes"></i></div>
                </div>
                <div class="kpi-value"><?php echo number_format($total_stock); ?></div>
            </div>

            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-title">Total Valuation</span>
                    <div class="kpi-icon" style="background: #fef3c7; color: #92400e;"><i class="fa-solid fa-indian-rupee-sign"></i></div>
                </div>
                <div class="kpi-value">₹<?php echo number_format($total_val, 2); ?></div>
            </div>

            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-title">Stock Alerts</span>
                    <div class="kpi-icon" style="background: #fee2e2; color: #991b1b;"><i class="fa-solid fa-triangle-exclamation"></i></div>
                </div>
                <div class="kpi-value" style="color: <?php echo ($low_stock_cnt + $out_stock_cnt > 0) ? 'var(--brand-danger)' : 'var(--text-dark)'; ?>;">
                    <?php echo ($low_stock_cnt + $out_stock_cnt); ?>
                </div>
            </div>
        </div>

        <!-- TAB 1: OVERVIEW DASHBOARD -->
        <?php include_once __DIR__ . '/admin_pages/dashboard.php'; ?>

        <!-- TAB 2: PRODUCTS CATALOG -->
        <?php include_once __DIR__ . '/admin_pages/products.php'; ?>

        <!-- TAB 3: SELLERS & SHOPS -->


        <!-- TAB 4: INVENTORY & RESTOCK -->
        <?php include_once __DIR__ . '/admin_pages/inventory.php'; ?>

        <!-- TAB 5: ANALYTICS -->
        <?php include_once __DIR__ . '/admin_pages/analytics.php'; ?>

        <!-- TAB 6: ORDERS -->
        <?php include_once __DIR__ . '/admin_pages/orders.php'; ?>

    </main>

    <!-- Modal: Add / Edit Product -->
    <?php include_once __DIR__ . '/admin_pages/modal.php'; ?>

    <!-- JavaScript Interactive Code -->
    <script>
        // Tab Switcher
        const tabTitles = {
            dashboard: {
                title: "Overview Dashboard",
                sub: "Live metrics and store management system"
            },
            products: {
                title: "Products Catalog",
                sub: "Manage, search, edit, and delete product listings"
            },
            sellers: {
                title: "Sellers & Shops",
                sub: "View registered seller profiles and shop details"
            },
            inventory: {
                title: "Inventory Management",
                sub: "Monitor stock quantities and execute restock orders"
            },
            analytics: {
                title: "Store Analytics",
                sub: "Visual category distribution and pricing insights"
            },
            orders: {
                title: "Order Management",
                sub: "Track customer orders containing your products"
            }
        };

        function switchTab(tabId, elem) {
            document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
            if (elem) elem.classList.add('active');

            document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.remove('active'));
            document.getElementById('tab-' + tabId).classList.add('active');

            if (tabTitles[tabId]) {
                document.getElementById('tabTitle').innerText = tabTitles[tabId].title;
                document.getElementById('tabSub').innerText = tabTitles[tabId].sub;
            }
        }

        function switchTabById(tabId) {
            const navItems = document.querySelectorAll('.nav-item');
            navItems.forEach(item => {
                if (item.innerText.toLowerCase().includes(tabId)) {
                    switchTab(tabId, item);
                }
            });
        }

        // Search Filter for Products Table
        function filterProducts() {
            const query = document.getElementById('prodSearch').value.toLowerCase();
            const cat = document.getElementById('catFilter').value.toLowerCase();
            const rows = document.querySelectorAll('#productsTable tbody tr');

            rows.forEach(row => {
                const name = row.getAttribute('data-name');
                const rowCat = row.getAttribute('data-cat').toLowerCase();

                const matchQuery = name.includes(query);
                const matchCat = cat === "" || rowCat === cat;

                if (matchQuery && matchCat) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }

        // Modal Operations
        function openAddProductModal() {
            document.getElementById('modalTitle').innerText = "Add New Product";
            document.getElementById('formAction').value = "add_product";
            document.getElementById('formProductId').value = "";
            document.getElementById('btnSubmitForm').innerText = "Add Product";
            document.getElementById('productForm').reset();
            document.getElementById('productModal').classList.add('active');
        }

        function openEditProductModal(product) {
            document.getElementById('modalTitle').innerText = "Edit Product #" + product.product_id;
            document.getElementById('formAction').value = "edit_product";
            document.getElementById('formProductId').value = product.product_id;
            document.getElementById('btnSubmitForm').innerText = "Update Product";

            document.getElementById('inpName').value = product.product_name || '';
            document.getElementById('inpCat').value = product.category || '';
            document.getElementById('inpBrand').value = product.brand || '';
            document.getElementById('inpGender').value = product.gender || 'male';
            document.getElementById('inpStock').value = product.stockqty || 0;
            document.getElementById('inpPrice').value = product.price || 0;
            document.getElementById('inpDiscount').value = product.discountprice || '';
            document.getElementById('inpColor').value = product.color || '';
            document.getElementById('inpSize').value = product.size || '';
            document.getElementById('inpDesc').value = product.description || '';

            document.getElementById('productModal').classList.add('active');
        }

        function closeProductModal() {
            document.getElementById('productModal').classList.remove('active');
        }

        // Render Chart.js Analytics
        document.addEventListener('DOMContentLoaded', () => {
            const catLabels = <?php echo $category_labels_json; ?>;
            const catCounts = <?php echo $category_counts_json; ?>;
            const palette = ['#2563eb', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6', '#06b6d4', '#64748b'];

            if (document.getElementById('categoryPieChart')) {
                new Chart(document.getElementById('categoryPieChart'), {
                    type: 'doughnut',
                    data: {
                        labels: catLabels,
                        datasets: [{
                            data: catCounts,
                            backgroundColor: palette
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }

            if (document.getElementById('categoryBarChart')) {
                new Chart(document.getElementById('categoryBarChart'), {
                    type: 'bar',
                    data: {
                        labels: catLabels,
                        datasets: [{
                            label: 'Product Count',
                            data: catCounts,
                            backgroundColor: '#2563eb',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>

</html>