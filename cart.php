<?php
session_start();
require_once __DIR__ . '/db.php';
$conn = $mysqli;

$checkout_customer = [
    'full_name' => $_SESSION['user']['full_name'] ?? '',
    'phone' => '',
    'address' => ''
];
$checkout_user_logged_in = isset($_SESSION['user']['id']);
if ($checkout_user_logged_in) {
    $customer_id = (int) $_SESSION['user']['id'];
    $customer_stmt = mysqli_prepare($conn, 'SELECT full_name, phone, address, city, state, pincode FROM customers WHERE id = ?');
    if ($customer_stmt) {
        mysqli_stmt_bind_param($customer_stmt, 'i', $customer_id);
        mysqli_stmt_execute($customer_stmt);
        $customer_result = mysqli_stmt_get_result($customer_stmt);
        if ($customer_row = mysqli_fetch_assoc($customer_result)) {
            $address_parts = array_filter([
                trim((string) $customer_row['address']),
                trim((string) $customer_row['city']),
                trim((string) $customer_row['state']),
                trim((string) $customer_row['pincode'])
            ]);
            $clean_phone = preg_replace('/[^0-9]/', '', (string) $customer_row['phone']);
            if (strlen($clean_phone) > 10) {
                $clean_phone = substr($clean_phone, -10);
            }
            $checkout_customer = [
                'full_name' => $customer_row['full_name'],
                'phone' => $clean_phone,
                'address' => implode(', ', $address_parts)
            ];
        }
        mysqli_stmt_close($customer_stmt);
    }
}


// Seed if empty
$res = mysqli_query($conn, "SELECT COUNT(*) as count FROM coupons");
$row = mysqli_fetch_assoc($res);
if ($row['count'] == 0) {
    $dummy = [
        "('SAVE10', 10, '10% off on all items', '2026-07-01', '2026-12-31')",
        "('SAVE20', 20, '20% discount on purchases', '2026-07-15', '2026-08-15')",
        "('WELCOME', 15, 'Welcome offer for new customers', '2026-06-01', '2026-09-30')",
        "('SUMMER', 25, 'Summer sale - extra 25% off', '2026-07-01', '2026-08-31')",
        "('FESTIVE30', 30, 'Festive Special 30% Off', '2026-08-01', '2026-12-31')"
    ];
    foreach ($dummy as $val) {
        mysqli_query($conn, "INSERT IGNORE INTO coupons (code, discount_percent, description, start_date, expire_date) VALUES $val");
    }
}

// Fetch all active coupons
$couponsData = [];
$res = mysqli_query($conn, "SELECT code, discount_percent as discount, description, start_date as start, expire_date as expire FROM coupons");
while ($row = mysqli_fetch_assoc($res)) {
    // Ensure discount is an integer
    $row['discount'] = (int)$row['discount'];
    $couponsData[$row['code']] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | 24SHOP</title>
    <!-- Google Fonts for modern typography -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="index.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #1e293b;
            line-height: 1.6;
        }

        .cart-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .cart-header {
            margin-bottom: 2rem;
        }

        .cart-header h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #1e293b;
        }

        .cart-header p {
            color: #64748b;
            font-size: 0.95rem;
        }

        .cart-content {
            display: grid;
            grid-template-columns: 1fr 375px;
            gap: 2rem;
        }

        .cart-items {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 0;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .cart-items-header {
            padding: 1.5rem;
            border-bottom: 1px solid #ddd;
            font-size: 0.9rem;
            color: #666;
        }

        .cart-item {
            display: grid;
            grid-template-columns: 100px 1fr auto;
            gap: 1.5rem;
            padding: 1.5rem;
            border-bottom: 1px solid #ddd;
            align-items: flex-start;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 100px;
            height: 100px;
            background: #f0f0f0;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
        }

        .item-details {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .item-name {
            font-weight: 500;
            color: #0f1419;
            font-size: 0.95rem;
            line-height: 1.4;
        }

        .item-price {
            font-size: 1.1rem;
            font-weight: 700;
            color: #111;
        }

        .item-status {
            color: #067d62;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .item-actions-right {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: flex-end;
        }

        .quantity-control {
            display: flex;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;

        }

        .quantity-control button {
            background: #f0f0f0;
            border: none;
            padding: 0.5rem 0.75rem;
            cursor: pointer;
            color: #666;
            font-weight: 600;
            transition: all 0.2s;

        }

        .quantity-control button:hover {
            background: #e8e8e8;
        }

        .quantity-control input {
            border: none;
            width: 45px;
            background: white;
            font-weight: 600;
            text-align: center;
            margin-left: 10px;

        }

        .item-actions-text {
            display: flex;
            gap: 1rem;
            font-size: 0.85rem;
        }

        .item-actions-text button {
            background: none;
            border: none;
            color: #0066c0;
            cursor: pointer;
            padding: 0;
            font-size: 0.85rem;
        }

        .item-actions-text button:hover {
            text-decoration: underline;
            color: #c45911;
        }

        .promotions-section {
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .promotions-title {
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #111;
        }

        .promotions-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .promo-card {
            background: linear-gradient(135deg, #FFD580 0%, #FFC859 100%);
            padding: 1rem;
            border-radius: 6px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid #E5A800;
        }

        .promo-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .promo-percent {
            font-size: 1.8rem;
            font-weight: 900;
            color: #B7860B;
            display: block;
        }

        .promo-text {
            font-size: 0.75rem;
            color: #5C3C1A;
            font-weight: 700;
            margin-top: 0.4rem;
            letter-spacing: 1px;
        }

        .coupon-section {
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .coupon-label {
            font-weight: 700;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
            color: #111;
        }

        .coupon-input-group {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .coupon-input {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9rem;
            background: white;
        }

        .coupon-input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 3px rgba(37, 99, 235, 0.3);
        }

        .coupon-btn {
            padding: 0.75rem 1.25rem;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .coupon-btn:hover {
            background: #1e458a;
        }

        .view-coupons-btn {
            padding: 0.5rem 1rem;
            background: #f8fafc;
            color: #2563eb;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s;
        }

        .view-coupons-btn:hover {
            background: #eff6ff;
            border-color: #2563eb;
        }

        .coupon-applied {
            background: #f0fdf4;
            color: #047857;
            padding: 0.75rem;
            border-radius: 4px;
            font-size: 0.85rem;
            margin-top: 0.75rem;
            display: none;
            border-left: 4px solid #10b981;
            font-weight: 600;
        }

        .coupon-applied.show {
            display: block;
            animation: slideIn 0.3s ease;
        }

        /* Coupon Modal */
        .coupon-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s ease;
        }

        .coupon-modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .coupon-modal-content {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #ddd;
            padding-bottom: 1rem;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
            color: #111;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }

        .modal-close:hover {
            color: #111;
        }

        .coupons-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .coupon-item {
            border: 2px solid #2563eb;
            border-radius: 12px;
            padding: 1.25rem;
            background: linear-gradient(135deg, #f0f6ff 0%, #f8fafc 100%);
            transition: all 0.3s;
            cursor: pointer;
        }

        .coupon-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
        }

        .coupon-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .coupon-code {
            font-size: 1.2rem;
            font-weight: 900;
            color: #1e458a;
            font-family: monospace;
            letter-spacing: 2px;
        }

        .coupon-discount {
            font-size: 1.3rem;
            font-weight: 900;
            color: #fff;
            background: #2563eb;
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
        }

        .coupon-description {
            font-size: 0.9rem;
            color: #333;
            margin-bottom: 0.75rem;
            line-height: 1.4;
        }

        .coupon-dates {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            font-size: 0.85rem;
            color: #666;
            padding-top: 0.75rem;
            border-top: 1px solid #ddd;
        }

        .date-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .date-label {
            font-weight: 700;
            color: #111;
        }

        .coupon-apply-btn {
            width: 100%;
            padding: 0.75rem;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 1rem;
            transition: all 0.2s;
        }

        .coupon-apply-btn:hover {
            background: #1e458a;
        }

        .coupon-apply-btn:disabled {
            background: #ccc;
            color: #999;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .coupon-expired {
            opacity: 0.6;
            border-color: #ccc !important;
        }

        .coupon-expired .coupon-discount {
            background: #ddd;
            color: #999;
        }

        .expired-badge {
            display: inline-block;
            background: #ef4444;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
            font-size: 0.75rem;
            font-weight: 700;
            margin-left: 0.5rem;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .cart-summary {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 1.5rem;
            height: fit-content;
            position: sticky;
            top: 2rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }

        .summary-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #1e293b;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
            color: #1e293b;
        }

        .summary-row.total {
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 0;
            font-weight: 700;
            font-size: 1.1rem;
            color: #1e458a;
            background: #f0f6ff;
            margin: 1rem -1.5rem;
            padding: 1rem 1.5rem;
            border-radius: 8px;
        }

        .summary-row.discount {
            color: #067d62;
            font-weight: 600;
        }

        .checkout-btn {
            width: 100%;
            padding: 0.9rem;
            background: linear-gradient(135deg, #1e458a, #2563eb);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
            margin-top: 1rem;
            font-family: 'Outfit', sans-serif;
        }

        .checkout-btn:hover {
            background: linear-gradient(135deg, #2563eb, #1e458a);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.25);
            transform: translateY(-2px);
        }

        .empty-cart {
            text-align: center;
            padding: 3rem 1.5rem;
            color: #666;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .cart-content {
                grid-template-columns: 1fr;
            }

            .cart-summary {
                position: static;
            }

            .cart-item {
                grid-template-columns: 80px 1fr;
            }

            .item-image {
                width: 80px;
                height: 80px;
            }

            .promotions-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    </style>
</head>

<body>

    <!-- Header / Navigation -->
    <header>
        <div class="nav-container">
            <!-- Brand Logo -->
            <a href="home.php">
                <div class="logo-container">
                    <div class="logo-icon">
                        <i class="fa-solid fa-cart-shopping logo-cart"></i>
                    </div>

                    <div class="logo-text">
                        <span class="brand-num">24</span>
                        <span class="brand-word">SHOP</span>
                    </div>

                </div>
            </a>
        </div>

    </header>

    <div class="cart-container">
        <div class="cart-header">
            <h1>Shopping Cart</h1>
            <p>Review items before checkout</p>
            <?php if ($checkout_user_logged_in): ?>
                <p style="margin-top: 0.75rem; color: #047857; font-weight: 600;"><i class="fa-solid fa-circle-check"></i> Signed in as <?php echo htmlspecialchars($checkout_customer['full_name']); ?>. Your checkout details are ready.</p>
            <?php else: ?>
                <a href="login.php" style="display: inline-block; margin-top: 0.75rem; color: #2563eb; font-weight: 700; text-decoration: none;"><i class="fa-regular fa-user"></i> Customer Sign In</a>
            <?php endif; ?>
        </div>

        <div class="cart-content">
            <div>
                <div class="cart-items" id="cartItems">
                    <!-- Cart items will be populated here -->
                </div>


            </div>

            <div>
                <div class="cart-summary">
                    <div class="summary-title">Order Summary</div>

                    <div class="coupon-section">
                        <div class="coupon-label">
                            <i class="fa-solid fa-tag"></i>
                            Gift Card or Promo Code
                        </div>
                        <div class="coupon-input-group">
                            <input
                                type="text"
                                class="coupon-input"
                                id="couponInput"
                                placeholder="Enter code">
                            <button class="coupon-btn" onclick="applyCoupon()">Apply</button>
                        </div>
                        <button class="view-coupons-btn" onclick="openCouponModal()">View All Coupons</button>
                        <div class="coupon-applied" id="couponMessage"></div>
                    </div>

                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span id="subtotal">$0.00</span>
                    </div>

                    <div class="summary-row discount" id="discountRow" style="display: none;">
                        <span>
                            <i class="fa-solid fa-tag"></i>
                            Discount:
                        </span>
                        <span>-$<span id="discountAmount">0.00</span></span>
                    </div>

                    <div class="summary-row total">
                        <span>Total:</span>
                        <span id="totalPrice">0.00</span>
                    </div>

                    <button class="checkout-btn" onclick="checkout()">Proceed to Checkout</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Coupon Modal -->
    <div class="coupon-modal" id="couponModal">
        <div class="coupon-modal-content">
            <div class="modal-header">
                <h2>Available Coupons</h2>
                <button class="modal-close" onclick="closeCouponModal()">✕</button>
            </div>
            <div class="coupons-list" id="couponsList">
                <!-- Coupons will be populated here -->
            </div>
        </div>
    </div>

    <!-- Checkout Modal -->
    <div class="coupon-modal" id="checkoutModal">
        <div class="coupon-modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h2>Shipping Details</h2>
                <button class="modal-close" onclick="closeCheckoutModal()">✕</button>
            </div>
            <?php if (!$checkout_user_logged_in): ?>
                <div style="margin-bottom: 1rem; padding: 0.75rem; border-radius: 6px; background: #eff6ff; color: #1e40af; font-size: 0.9rem;">
                    <i class="fa-solid fa-circle-info"></i> <a href="login.php" style="color: inherit; font-weight: 700;">Log in</a> to automatically use your saved customer details.
                </div>
            <?php endif; ?>
            <form id="checkoutForm" method="POST" action="payment.php">
                <input type="hidden" name="cartData" id="checkoutCartData">
                <input type="hidden" name="couponCode" id="checkoutCouponCode">
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Full Name</label>
                    <input type="text" name="customerName" value="<?php echo htmlspecialchars($checkout_customer['full_name']); ?>" placeholder="Enter your full name" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;" required>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Shipping Address</label>
                    <input type="text" name="address" value="<?php echo htmlspecialchars($checkout_customer['address']); ?>" placeholder="House no, Street, City, State" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;" required>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Phone Number</label>
                    <input 
                        type="tel" 
                        name="phone" 
                        id="checkoutPhone"
                        value="<?php echo htmlspecialchars($checkout_customer['phone']); ?>" 
                        placeholder="10-digit mobile number" 
                        maxlength="10"
                        minlength="10"
                        pattern="[0-9]{10}"
                        inputmode="numeric"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                        title="Please enter a valid 10-digit mobile number"
                        style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;" 
                        required>
                    <small style="display: block; color: #64748b; font-size: 0.8rem; margin-top: 4px;">Enter exactly 10 digits (numbers only)</small>
                </div>
                <button type="submit" class="coupon-apply-btn" style="margin-top: 0; font-size: 1.1rem;">Continue to Payment <i class="fa-solid fa-arrow-right"></i></button>
            </form>
        </div>
    </div>

    <script>
        // Extended coupon data dynamically loaded from database
        const coupons = <?php echo json_encode($couponsData); ?>;

        let cartData = [];

        function loadCart() {
            const savedCart = localStorage.getItem('24shop_cart');
            if (savedCart) {
                cartData = JSON.parse(savedCart);
            }
        }

        function saveCart() {
            localStorage.setItem('24shop_cart', JSON.stringify(cartData));
        }

        let appliedDiscount = 0;
        let appliedCouponCode = '';

        function isExpired(expireDate) {
            return new Date(expireDate) < new Date();
        }

        function formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }

        function openCouponModal() {
            const modal = document.getElementById('couponModal');
            const couponsList = document.getElementById('couponsList');

            let html = '';
            for (const [code, details] of Object.entries(coupons)) {
                const expired = isExpired(details.expire);
                const expiredClass = expired ? 'coupon-expired' : '';
                const expiredBadge = expired ? '<span class="expired-badge">EXPIRED</span>' : '';

                html += `
                    <div class="coupon-item ${expiredClass}">
                        <div class="coupon-header">
                            <div class="coupon-code">${code}${expiredBadge}</div>
                            <div class="coupon-discount">${details.discount}% OFF</div>
                        </div>
                        <div class="coupon-description">${details.description}</div>
                        <div class="coupon-dates">
                            <div class="date-item">
                                <span class="date-label">Starts:</span>
                                <span>${formatDate(details.start)}</span>
                            </div>
                            <div class="date-item">
                                <span class="date-label">Expires:</span>
                                <span>${formatDate(details.expire)}</span>
                            </div>
                        </div>
                        <button class="coupon-apply-btn" onclick="applyCouponFromModal('${code}')" ${expired ? 'disabled' : ''}>
                            ${expired ? 'Expired' : 'Apply Coupon'}
                        </button>
                    </div>
                `;
            }

            couponsList.innerHTML = html;
            modal.classList.add('show');
        }

        function closeCouponModal() {
            const modal = document.getElementById('couponModal');
            modal.classList.remove('show');
        }

        function applyCouponFromModal(couponCode) {
            const couponDetails = coupons[couponCode];
            if (!isExpired(couponDetails.expire)) {
                appliedDiscount = couponDetails.discount;
                appliedCouponCode = couponCode;
                const couponMessage = document.getElementById('couponMessage');
                couponMessage.textContent = `✓ Coupon "${couponCode}" applied! ${appliedDiscount}% discount`;
                couponMessage.classList.add('show');
                document.getElementById('couponInput').value = '';
                updatePrices();
                closeCouponModal();
            }
        }

        function renderCart() {
            const cartItemsDiv = document.getElementById('cartItems');

            if (cartData.length === 0) {
                cartItemsDiv.innerHTML = '<div class="empty-cart"><p>Your cart is empty</p></div>';
                updatePrices();
                return;
            }

            let html = '<div class="cart-items-header">Item</div>';
            cartData.forEach((item, index) => {
                const qty = Number(item.qty || item.quantity || 1);
                const unitPrice = parseFloat(item.price) || 0;
                const itemTotal = (unitPrice * qty).toFixed(2);
                const colorSizeText = (item.color || item.size) ? `<div style="font-size: 0.8rem; color: #666; margin-top: 4px;">${item.color ? item.color : ''} ${item.size ? '| Size: ' + item.size : ''}</div>` : '';

                html += `
                    <div class="cart-item">
                        <div class="item-image" style="background: transparent;">
                            ${item.image ? `<img src="${item.image}" alt="${item.name}" style="max-width: 100%; max-height: 100%; object-fit: contain;">` : '📦'}
                        </div>
                        <div class="item-details">
                            <div class="item-name">${item.name}</div>
                            ${colorSizeText}
                            <div class="item-price">₹${unitPrice.toFixed(2)}</div>
                            <div class="item-status">✓ In Stock</div>
                        </div>
                        <div class="item-actions-right">
                            <div style="font-weight: 700; color: #111;">₹${itemTotal}</div>
                            <div class="quantity-control">
                                <button onclick="updateQuantity(${index}, -1)">−</button>
                                <input type="number" value="${qty}" readonly>
                                <button onclick="updateQuantity(${index}, 1)">+</button>
                            </div>
                            <div class="item-actions-text">
                                <button onclick="removeItem(${index})">Delete</button>
                            </div>
                        </div>
                    </div>
                `;
            });

            cartItemsDiv.innerHTML = html;
            updatePrices();
        }

        function updateQuantity(index, change) {
            if (cartData[index]) {
                cartData[index].qty = (cartData[index].qty || cartData[index].quantity || 1) + change;
                if (cartData[index].qty <= 0) {
                    removeItem(index);
                } else {
                    saveCart();
                    renderCart();
                }
            }
        }

        function removeItem(index) {
            cartData.splice(index, 1);
            saveCart();
            renderCart();
        }

        function updatePrices() {
            // Calculate subtotal
            const subtotal = cartData.reduce((sum, item) => sum + ((parseFloat(item.price) || 0) * Number(item.qty || item.quantity || 1)), 0);

            // Calculate discount
            const discountAmount = (subtotal * appliedDiscount) / 100;

            // Calculate total
            const total = subtotal - discountAmount;

            // Update display
            document.getElementById('subtotal').textContent = '₹' + subtotal.toFixed(2);
            document.getElementById('totalPrice').textContent = '₹' + total.toFixed(2);
            document.getElementById('discountAmount').textContent = discountAmount.toFixed(2);

            // Show/hide discount row
            const discountRow = document.getElementById('discountRow');
            if (appliedDiscount > 0) {
                discountRow.style.display = 'flex';
            } else {
                discountRow.style.display = 'none';
            }
        }

        function applyCoupon() {
            const couponInput = document.getElementById('couponInput');
            const couponCode = couponInput.value.toUpperCase().trim();
            const couponMessage = document.getElementById('couponMessage');

            if (coupons[couponCode]) {
                const couponDetails = coupons[couponCode];
                if (isExpired(couponDetails.expire)) {
                    couponMessage.textContent = '✗ This coupon has expired';
                    couponMessage.classList.add('show');
                    appliedDiscount = 0;
                } else {
                    appliedDiscount = couponDetails.discount;
                    appliedCouponCode = couponCode;
                    couponMessage.textContent = `✓ Coupon "${couponCode}" applied! ${appliedDiscount}% discount`;
                    couponMessage.classList.add('show');
                    couponInput.value = '';
                }
                updatePrices();
            } else if (couponCode) {
                couponMessage.textContent = '✗ Invalid coupon code';
                couponMessage.classList.add('show');
                appliedDiscount = 0;
                updatePrices();
            }
        }

        function applyCouponFromOffer(couponCode) {
            const couponDetails = coupons[couponCode];
            if (!isExpired(couponDetails.expire)) {
                appliedDiscount = couponDetails.discount;
                appliedCouponCode = couponCode;
                const couponMessage = document.getElementById('couponMessage');
                couponMessage.textContent = `✓ Coupon "${couponCode}" applied! ${appliedDiscount}% discount`;
                couponMessage.classList.add('show');
                document.getElementById('couponInput').value = '';
                updatePrices();
            }
        }

        function removeCoupon() {
            appliedDiscount = 0;
            document.getElementById('couponInput').value = '';
            document.getElementById('couponMessage').classList.remove('show');
            updatePrices();
        }

        // Allow pressing Enter to apply coupon
        document.addEventListener('DOMContentLoaded', function() {
            loadCart(); // Load from localStorage on page load

            document.getElementById('couponInput').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    applyCoupon();
                }
            });

            // Close modal when clicking outside
            document.getElementById('couponModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeCouponModal();
                }
            });

            document.getElementById('checkoutModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeCheckoutModal();
                }
            });

            // Enforce strictly 10 digits on checkout form submit
            const checkoutForm = document.getElementById('checkoutForm');
            if (checkoutForm) {
                checkoutForm.addEventListener('submit', function(e) {
                    const phoneInput = document.getElementById('checkoutPhone');
                    const cleanPhone = phoneInput.value.replace(/[^0-9]/g, '');
                    phoneInput.value = cleanPhone;

                    if (cleanPhone.length !== 10) {
                        e.preventDefault();
                        alert('Please enter a valid 10-digit phone number.');
                        phoneInput.focus();
                        return false;
                    }
                });
            }

            renderCart();
        });

        function closeCheckoutModal() {
            document.getElementById('checkoutModal').classList.remove('show');
        }

        function checkout() {
            if (cartData.length === 0) {
                alert('Your cart is empty!');
                return;
            }

            // Set hidden inputs for payment.php
            document.getElementById('checkoutCartData').value = JSON.stringify(cartData);
            document.getElementById('checkoutCouponCode').value = appliedCouponCode;

            // Show checkout modal
            document.getElementById('checkoutModal').classList.add('show');
        }
    </script>
</body>

</html>