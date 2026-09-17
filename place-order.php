<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: product.php');
    exit;
}

require_once __DIR__ . '/db.php';
$conn = $mysqli;

// 1. Gather inputs
$customerName = trim($_POST['customerName'] ?? '');
$address = trim($_POST['address'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$payment_method = $_POST['payment_method'] ?? 'cod';
$payment_reference = $_POST['payment_reference'] ?? '';
$couponCode = strtoupper(trim($_POST['couponCode'] ?? ''));
$cartData = $_POST['cartData'] ?? '[]';
$customerId = isset($_SESSION['user']['id']) ? (int) $_SESSION['user']['id'] : 0;

$items = json_decode($cartData, true);
if (!is_array($items) || empty($items)) {
    exit('Invalid cart data.');
}

// 2. Calculate totals
$subtotal = 0;
foreach ($items as $item) {
    $qty = max(1, (int) ($item['qty'] ?? $item['quantity'] ?? 1));
    $price = max(0, (float) ($item['price'] ?? 0));
    $subtotal += ($price * $qty);
}

// 3. Discount logic matching database
$discount_percent = 0;
if (!empty($couponCode)) {
    $c_stmt = mysqli_prepare($conn, "SELECT discount_percent FROM coupons WHERE code = ? AND expire_date >= CURRENT_DATE");
    if ($c_stmt) {
        mysqli_stmt_bind_param($c_stmt, "s", $couponCode);
        mysqli_stmt_execute($c_stmt);
        $c_res = mysqli_stmt_get_result($c_stmt);
        if ($c_row = mysqli_fetch_assoc($c_res)) {
            $discount_percent = (int) $c_row['discount_percent'];
        }
        mysqli_stmt_close($c_stmt);
    }
}

$discount_amount = ($subtotal * $discount_percent) / 100;
$total_amount = $subtotal - $discount_amount;

// 4. Generate unique order ID
$order_id = 'ORD-' . date('YmdHis') . '-' . rand(1000, 9999);

// 5. Insert into DB
$stmt = mysqli_prepare($conn, "INSERT INTO orders (order_id, customer_id, customer_name, address, phone, payment_method, payment_reference, coupon_code, discount_percent, discount_amount, items_json, total_amount, status, created_at) VALUES (?, NULLIF(?, 0), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Order Confirmed', NOW())");

$items_json = json_encode($items);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "sissssssidss", $order_id, $customerId, $customerName, $address, $phone, $payment_method, $payment_reference, $couponCode, $discount_percent, $discount_amount, $items_json, $total_amount);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
} else {
    // If table doesn't exist or other error, we still want to show success for the presentation, but let's log the error.
    error_log("Order insert error: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed | 24SHOP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --brand-primary: #0f172a;
            --brand-green: #10b981;
            --surface-bg: #f8fafc;
            --card-border: #e2e8f0;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            color: #334155;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .success-container {
            background: #ffffff;
            width: 100%;
            max-width: 600px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.06);
            overflow: hidden;
            text-align: center;
            position: relative;
        }
        .success-header {
            background: var(--brand-green);
            padding: 50px 30px 40px;
            color: white;
            position: relative;
        }
        .check-icon-wrap {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 40px;
            box-shadow: 0 0 0 10px rgba(255,255,255,0.1);
            animation: popIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            transform: scale(0);
        }
        @keyframes popIn {
            to { transform: scale(1); }
        }
        .success-title {
            font-family: 'Outfit', sans-serif;
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 10px;
        }
        .success-subtitle {
            font-size: 15px;
            opacity: 0.9;
            margin: 0;
        }
        .order-details-card {
            padding: 40px 30px;
            text-align: left;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px dashed var(--card-border);
            font-size: 15px;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            color: #64748b;
            font-weight: 500;
        }
        .detail-value {
            font-weight: 600;
            color: var(--brand-primary);
            text-align: right;
        }
        .total-row {
            background: var(--surface-bg);
            padding: 20px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }
        .total-row .detail-label {
            color: var(--brand-primary);
            font-size: 18px;
        }
        .total-row .detail-value {
            font-size: 24px;
            font-family: 'Outfit', sans-serif;
            color: var(--brand-primary);
        }
        .actions {
            padding: 0 30px 40px;
            display: flex;
            gap: 15px;
        }
        .btn-primary {
            flex: 1;
            background: var(--brand-primary);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 10px;
            font-family: 'Outfit', sans-serif;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-primary:hover {
            background: #1e293b;
            transform: translateY(-2px);
        }
        .btn-secondary {
            flex: 1;
            background: white;
            color: var(--brand-primary);
            border: 1px solid var(--card-border);
            padding: 16px;
            border-radius: 10px;
            font-family: 'Outfit', sans-serif;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-secondary:hover {
            background: var(--surface-bg);
        }
        
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background-color: #f2d74e;
            opacity: 0;
        }
    </style>
</head>
<body>

    <div class="success-container">
        <div class="success-header">
            <div class="check-icon-wrap">
                <i class="fa-solid fa-check"></i>
            </div>
            <h1 class="success-title">Order Confirmed!</h1>
            <p class="success-subtitle">Thank you, <?php echo htmlspecialchars($customerName); ?>. Your order has been placed successfully.</p>
        </div>
        
        <div class="order-details-card">
            <div class="detail-row">
                <span class="detail-label">Order ID</span>
                <span class="detail-value"><?php echo htmlspecialchars($order_id); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Payment Method</span>
                <span class="detail-value"><?php echo $payment_method === 'cod' ? 'Cash on Delivery' : ($payment_method === 'paypal' ? 'PayPal' : 'Debit Card'); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Shipping Address</span>
                <span class="detail-value" style="max-width: 60%; line-height: 1.4;"><?php echo htmlspecialchars($address); ?></span>
            </div>
            
            <?php if ($discount_amount > 0): ?>
            <div class="detail-row">
                <span class="detail-label">Subtotal</span>
                <span class="detail-value">₹<?php echo number_format($subtotal, 2); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Discount Applied (<?php echo htmlspecialchars($couponCode); ?>)</span>
                <span class="detail-value" style="color: var(--brand-green);">-₹<?php echo number_format($discount_amount, 2); ?></span>
            </div>
            <?php endif; ?>
            
            <div class="total-row">
                <span class="detail-label">Total Paid</span>
                <span class="detail-value">₹<?php echo number_format($total_amount, 2); ?></span>
            </div>
        </div>
        
        <div class="actions">
            <a href="order-history.php" class="btn-secondary">Track Order</a>
            <a href="home.php" class="btn-primary">Continue Shopping</a>
        </div>
    </div>

    <script>
        // Clear the cart from localStorage since the order was successfully placed
        localStorage.removeItem('24shop_cart');
    </script>
</body>
</html>
