<?php
session_start();
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: home.php');
    exit;
}

$customerName = trim($_POST['customerName'] ?? '');
$address = trim($_POST['address'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$couponCode = strtoupper(trim($_POST['couponCode'] ?? ''));
$cartData = $_POST['cartData'] ?? '[]';
$items = json_decode($cartData, true);

if ($customerName === '' || $address === '' || $phone === '' || !is_array($items) || empty($items)) {
    http_response_code(400);
    exit('Please return to checkout and provide your details.');
}

$subtotal = 0;
foreach ($items as $item) {
    $qty = max(1, (int) ($item['qty'] ?? $item['quantity'] ?? 1));
    $price = max(0, (float) ($item['price'] ?? 0));
    $subtotal += ($price * $qty);
}

$discount_percent = 0;
if (!empty($couponCode)) {
    $c_stmt = $mysqli->prepare("SELECT discount_percent FROM coupons WHERE code = ? AND expire_date >= CURRENT_DATE");
    if ($c_stmt) {
        $c_stmt->bind_param("s", $couponCode);
        $c_stmt->execute();
        $c_res = $c_stmt->get_result();
        if ($c_row = $c_res->fetch_assoc()) {
            $discount_percent = (int) $c_row['discount_percent'];
        }
        $c_stmt->close();
    }
}

$discount_amount = ($subtotal * $discount_percent) / 100;
$total_amount = $subtotal - $discount_amount;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment | 24SHOP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #172033;
            --muted: #667085;
            --line: #e4e7ec;
            --blue: #175cd3;
            --orange: #f79009;
            --bg: #f5f8fc;
            --blue-soft: #eff6ff;
            --danger: #b42318;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: var(--ink);
            font-family: 'DM Sans', sans-serif;
            background: radial-gradient(circle at 8% 0%, #e6f0ff 0, transparent 28%), var(--bg);
        }

        .wrap {
            max-width: 1060px;
            margin: 0 auto;
            padding: 34px 20px 60px;
        }

        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
        }

        .brand {
            font: 800 28px 'Outfit', sans-serif;
            letter-spacing: .2px;
        }

        .secure {
            color: #067647;
            font-size: 13px;
            font-weight: 700;
        }

        .progress {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 28px;
        }

        .progress-step {
            display: flex;
            align-items: center;
            gap: 7px;
            white-space: nowrap;
        }

        .progress-step.active {
            color: var(--blue);
        }

        .progress-number {
            display: grid;
            place-items: center;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #e4e7ec;
            color: var(--muted);
        }

        .progress-step.active .progress-number {
            background: var(--blue);
            color: #fff;
        }

        .progress-line {
            height: 1px;
            flex: 1;
            background: var(--line);
        }

        .grid {
            display: grid;
            grid-template-columns: minmax(0, 1.35fr) minmax(280px, .65fr);
            gap: 24px;
        }

        .panel {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 28px;
            box-shadow: 0 15px 35px #1720330b;
        }

        h1 {
            font: 700 30px 'Outfit', sans-serif;
            margin: 0 0 8px;
        }

        h2 {
            font: 700 19px 'Outfit', sans-serif;
            margin: 0 0 20px;
        }

        .section-label {
            display: block;
            font: 700 15px 'Outfit', sans-serif;
            margin: 0 0 12px;
        }

        .muted {
            color: var(--muted);
            margin: 0 0 24px;
            font-size: 14px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            margin: 16px 0 7px;
        }

        input {
            width: 100%;
            padding: 12px 13px;
            border: 1px solid #d0d5dd;
            border-radius: 9px;
            font: inherit;
            outline: none;
        }

        input:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px #175cd31c;
        }

        .methods {
            display: grid;
            gap: 10px;
            margin-bottom: 22px;
        }

        .method {
            display: flex;
            gap: 12px;
            align-items: center;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 15px;
            cursor: pointer;
            transition: border-color .2s, background .2s, transform .2s;
        }

        .method:hover {
            border-color: #98b7ed;
            transform: translateY(-1px);
        }

        .method:has(input:checked) {
            border: 2px solid var(--blue);
            background: var(--blue-soft);
            padding: 14px;
        }

        .method input {
            width: auto;
            accent-color: var(--blue);
        }

        .method-icon {
            display: grid;
            place-items: center;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: #fff;
            color: var(--blue);
            font-size: 17px;
        }

        .method strong {
            display: block;
            margin-bottom: 3px;
        }

        .method small {
            color: var(--muted);
        }

        .details {
            display: none;
            background: #f8fafc;
            padding: 4px 14px 14px;
            border-radius: 10px;
        }

        .details.active {
            display: block;
            border: 1px solid var(--line);
            margin-top: -10px;
            margin-bottom: 18px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 11px 0;
            color: var(--muted);
            font-size: 14px;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid #f0f2f5;
            color: var(--muted);
            font-size: 13px;
        }

        .item-row strong {
            color: var(--ink);
            text-align: right;
        }

        .total {
            border-top: 1px solid var(--line);
            margin-top: 8px;
            padding-top: 17px;
            color: var(--ink);
            font-size: 19px;
            font-weight: 800;
        }

        .pay {
            width: 100%;
            border: 0;
            border-radius: 10px;
            padding: 14px;
            margin-top: 18px;
            color: #fff;
            background: linear-gradient(135deg, var(--blue), #1849a9);
            font: 700 16px 'DM Sans', sans-serif;
            cursor: pointer;
        }

        .pay:hover {
            filter: brightness(1.08);
        }

        .pay:disabled {
            opacity: .65;
            cursor: wait;
        }

        .note {
            color: var(--muted);
            font-size: 12px;
            line-height: 1.5;
            margin-top: 14px;
        }

        .error {
            display: none;
            color: var(--danger);
            background: #fef3f2;
            border: 1px solid #fecdca;
            border-radius: 9px;
            padding: 10px 12px;
            margin-top: 14px;
            font-size: 13px;
        }

        .error.show {
            display: block;
        }

        .back-link {
            display: inline-block;
            color: var(--blue);
            font-size: 13px;
            font-weight: 700;
            margin-top: 18px;
        }

        @media(max-width:720px) {
            .wrap {
                padding: 22px 14px 40px
            }

            .grid {
                grid-template-columns: 1fr
            }

            .panel {
                padding: 20px
            }

            .top {
                margin-bottom: 18px
            }

            .brand {
                font-size: 24px
            }

            .progress {
                margin-bottom: 20px
            }

            .progress-line {
                min-width: 12px
            }

            .progress-step span:last-child {
                display: none
            }
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="top">
            <div class="brand">24SHOP</div>
            <div class="secure">&#128274; Secure checkout</div>
        </div>
        <div class="progress" aria-label="Checkout progress">
            <div class="progress-step active"><span class="progress-number">1</span><span>Payment</span></div>
            <div class="progress-line"></div>
            <div class="progress-step"><span class="progress-number">2</span><span>Confirmation</span></div>
        </div>
        <div class="grid">
            <section class="panel">
                <h1>Choose how to pay</h1>
                <p class="muted">Hi <?php echo htmlspecialchars($customerName); ?>, your order is almost ready.</p>
                <form method="post" action="place-order.php" id="paymentForm">
                    <input type="hidden" name="customerName" value="<?php echo htmlspecialchars($customerName); ?>">
                    <input type="hidden" name="address" value="<?php echo htmlspecialchars($address); ?>">
                    <input type="hidden" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                    <input type="hidden" name="couponCode" value="<?php echo htmlspecialchars($_POST['couponCode'] ?? ''); ?>">
                    <input type="hidden" name="cartData" value="<?php echo htmlspecialchars($cartData); ?>">
                    <input type="hidden" name="payment_reference" id="paymentReference">
                    <span class="section-label">Select a payment method</span>
                    <div class="methods">
                        <label class="method"><input type="radio" name="payment_method" value="cod" checked><span class="method-icon">&#128176;</span><span><strong>Cash on Delivery</strong><small>Pay when your order arrives</small></span></label>
                        <label class="method"><input type="radio" name="payment_method" value="debit_card"><span class="method-icon">&#128179;</span><span><strong>Debit Card</strong><small>Visa, Mastercard, or RuPay</small></span></label>
                        <label class="method"><input type="radio" name="payment_method" value="paypal"><span class="method-icon">P</span><span><strong>PayPal</strong><small>Pay securely with your PayPal account</small></span></label>
                    </div>
                    <div class="details" id="cardDetails">
                        <label for="cardNumber">Card number</label><input id="cardNumber" autocomplete="cc-number" inputmode="numeric" maxlength="19" placeholder="1234 5678 9012 3456">
                        <label for="cardName">Name on card</label><input id="cardName" autocomplete="cc-name" placeholder="Full name">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                            <div><label for="expiry">Expiry</label><input id="expiry" maxlength="5" placeholder="MM/YY"></div>
                            <div><label for="cvv">CVV</label><input id="cvv" inputmode="numeric" maxlength="4" placeholder="123"></div>
                        </div>
                    </div>
                    <div class="details" id="paypalDetails"><label for="paypalEmail">PayPal email</label><input id="paypalEmail" type="email" placeholder="you@example.com"></div>
                    <div class="error" id="paymentError" role="alert"></div>
                    <button class="pay" type="submit" id="completeButton">Complete Order</button>
                    <p class="note">Demo checkout: card details are used only for validation and are never stored. Connect a PCI-compliant payment gateway before accepting real payments.</p>
                </form>
            </section>
            <aside class="panel">
                <h2>Order summary</h2>
                <?php foreach ($items as $item): ?>
                    <div class="item-row">
                        <span><?php echo htmlspecialchars($item['name'] ?? 'Product'); ?> x <?php echo max(1, (int) ($item['qty'] ?? 1)); ?></span>
                        <strong>₹<?php echo number_format((float) ($item['price'] ?? 0) * max(1, (int) ($item['qty'] ?? 1)), 2); ?></strong>
                    </div>
                <?php endforeach; ?>
                <div class="row">
                    <span>Subtotal</span>
                    <strong>₹<?php echo number_format($subtotal, 2); ?></strong>
                </div>
                <?php if ($discount_amount > 0): ?>
                    <div class="row" style="color:#027a48;">
                        <span>Discount (<?php echo htmlspecialchars($couponCode); ?> <?php echo $discount_percent; ?>% off)</span>
                        <strong>-₹<?php echo number_format($discount_amount, 2); ?></strong>
                    </div>
                <?php endif; ?>
                <div class="row">
                    <span>Delivery</span>
                    <strong style="color:#027a48;">Free</strong>
                </div>
                <div class="row total">
                    <span>Total</span>
                    <span>₹<?php echo number_format($total_amount, 2); ?></span>
                </div>
                <a class="back-link" href="home.php">&#8592; Return to shop</a>
            </aside>
        </div>
    </div>
    <script>
        const form = document.getElementById('paymentForm');
        const cardDetails = document.getElementById('cardDetails');
        const paypalDetails = document.getElementById('paypalDetails');
        const paymentError = document.getElementById('paymentError');
        const completeButton = document.getElementById('completeButton');

        function updatePaymentFields() {
            const method = document.querySelector('input[name="payment_method"]:checked').value;
            cardDetails.classList.toggle('active', method === 'debit_card');
            paypalDetails.classList.toggle('active', method === 'paypal');
            document.getElementById('cardNumber').required = method === 'debit_card';
            document.getElementById('cardName').required = method === 'debit_card';
            document.getElementById('expiry').required = method === 'debit_card';
            document.getElementById('cvv').required = method === 'debit_card';
            document.getElementById('paypalEmail').required = method === 'paypal';
            completeButton.textContent = method === 'cod' ? 'Place order' : method === 'paypal' ? 'Continue with PayPal' : 'Pay securely';
        }
        document.querySelectorAll('input[name="payment_method"]').forEach(input => input.addEventListener('change', updatePaymentFields));
        form.addEventListener('submit', event => {
            paymentError.classList.remove('show');
            const method = document.querySelector('input[name="payment_method"]:checked').value;
            if (method === 'debit_card') {
                const digits = document.getElementById('cardNumber').value.replace(/\D/g, '');
                if (digits.length < 12) {
                    event.preventDefault();
                    paymentError.textContent = 'Enter a valid debit card number with at least 12 digits.';
                    paymentError.classList.add('show');
                    document.getElementById('cardNumber').focus();
                    return;
                }
                document.getElementById('paymentReference').value = 'CARD-' + digits.slice(-4);
            } else if (method === 'paypal') {
                document.getElementById('paymentReference').value = document.getElementById('paypalEmail').value.trim();
            }
            completeButton.disabled = true;
        });
        updatePaymentFields();
    </script>
</body>

</html>