<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit;
}

$customer_id = (int) $_SESSION['user']['id'];
$profile_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_profile') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $phone = trim($_POST['phone'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');

    if ($full_name === '' || $email === false) {
        $profile_message = '<div class="profile-alert error">Enter a full name and valid email address.</div>';
    } else {
        $check = $mysqli->prepare('SELECT id FROM customers WHERE email = ? AND id <> ?');
        $check->bind_param('si', $email, $customer_id);
        $check->execute();
        $email_taken = $check->get_result()->num_rows > 0;
        $check->close();

        if ($email_taken) {
            $profile_message = '<div class="profile-alert error">That email address is already in use.</div>';
        } else {
            $stmt = $mysqli->prepare("UPDATE customers SET full_name = ?, email = ?, phone = ?, dob = NULLIF(?, ''), gender = ?, address = ?, city = ?, state = ?, pincode = ? WHERE id = ?");
            $stmt->bind_param('sssssssssi', $full_name, $email, $phone, $dob, $gender, $address, $city, $state, $pincode, $customer_id);
            if ($stmt->execute()) {
                $_SESSION['user']['full_name'] = $full_name;
                $_SESSION['user']['email'] = $email;
                $profile_message = '<div class="profile-alert success">Your personal details were updated.</div>';
            } else {
                $profile_message = '<div class="profile-alert error">Could not update your details. Please try again.</div>';
            }
            $stmt->close();
        }
    }
}

$customer = null;
$stmt = $mysqli->prepare('SELECT full_name, email, phone, dob, gender, address, city, state, pincode FROM customers WHERE id = ?');
$stmt->bind_param('i', $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$customer) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$orders = [];
$stmt = $mysqli->prepare('SELECT order_id, customer_name, address, phone, payment_method, total_amount, status, items_json, created_at FROM orders WHERE customer_id = ? OR (customer_id IS NULL AND customer_name = ?) ORDER BY created_at DESC');
$stmt->bind_param('is', $customer_id, $customer['full_name']);
$stmt->execute();
$order_result = $stmt->get_result();
while ($row = $order_result->fetch_assoc()) {
    $row['items'] = json_decode($row['items_json'], true) ?: [];
    $orders[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account | 24SHOP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="index.css">
    <style>
        .account-main { max-width: 1180px; margin: auto; padding: 3rem 40px 5rem; }
        .account-heading { margin-bottom: 2rem; }
        .account-heading h1 { margin-top: .35rem; color: var(--primary-blue); }
        .account-layout { display: grid; grid-template-columns: 330px 1fr; gap: 1.5rem; align-items: start; }
        .account-panel, .order-panel { padding: 1.5rem; border: 1px solid var(--gray-border); border-radius: 12px; background: #fff; box-shadow: var(--card-shadow); }
        .account-panel h2, .order-panel h2 { margin: 0 0 1.2rem; color: var(--primary-blue); font-size: 1.25rem; }
        .profile-icon { display: grid; width: 64px; height: 64px; margin-bottom: 1rem; place-items: center; border-radius: 50%; background: #eff6ff; color: var(--primary-accent); font-size: 1.6rem; }
        .profile-row { padding: .75rem 0; border-bottom: 1px solid #eef2f7; }
        .profile-row:last-child { border-bottom: 0; }
        .profile-row strong, .profile-row span { display: block; }
        .profile-row strong { color: #64748b; font-size: .78rem; text-transform: uppercase; }
        .profile-row span { color: var(--dark-text); overflow-wrap: anywhere; }
        .profile-form label { display: block; margin: .8rem 0 .3rem; color: #64748b; font-size: .78rem; font-weight: 700; text-transform: uppercase; }
        .profile-form input, .profile-form select, .profile-form textarea { width: 100%; padding: .65rem; border: 1px solid var(--gray-border); border-radius: 7px; font: inherit; }
        .profile-form textarea { min-height: 72px; resize: vertical; }
        .profile-save { width: 100%; margin-top: 1rem; padding: .7rem; border: 0; border-radius: 8px; background: var(--primary-accent); color: #fff; font-weight: 700; cursor: pointer; }
        .profile-alert { margin-bottom: 1rem; padding: .7rem; border-radius: 7px; font-size: .85rem; }
        .profile-alert.success { background: #d1e7dd; color: #0f5132; }
        .profile-alert.error { background: #f8d7da; color: #842029; }
        .order-card { margin-bottom: 1rem; border: 1px solid var(--gray-border); border-radius: 10px; overflow: hidden; }
        .order-card:last-child { margin-bottom: 0; }
        .order-top { display: flex; justify-content: space-between; gap: 1rem; padding: 1rem; background: #f8fafc; }
        .order-top strong { color: var(--primary-blue); }
        .order-status { color: #067d62; font-weight: 700; font-size: .85rem; }
        .order-body { padding: 1rem; }
        .order-item { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .45rem 0; color: #64748b; font-size: .9rem; }
        .order-item-info { display: flex; align-items: center; gap: .7rem; }
        .order-item-image { width: 52px; height: 52px; object-fit: contain; border: 1px solid var(--gray-border); border-radius: 7px; background: #f8fafc; }
        .order-total { display: flex; justify-content: space-between; margin-top: .75rem; padding-top: .75rem; border-top: 1px solid var(--gray-border); color: var(--primary-blue); font-weight: 800; }
        .repeat-order { margin-top: 1rem; padding: .65rem 1rem; border: 0; border-radius: 8px; background: var(--primary-accent); color: #fff; font-weight: 700; cursor: pointer; }
        .empty-orders { color: #64748b; }
        @media (max-width: 760px) { .account-main { padding: 2rem 18px 4rem; } .account-layout { grid-template-columns: 1fr; } .order-top { flex-direction: column; gap: .25rem; } }
    </style>
</head>
<body>
    <header>
        <div class="nav-container">
            <a class="logo-container" href="home.php" aria-label="24SHOP home">
                <div class="logo-icon"><i class="fa-solid fa-cart-shopping logo-cart"></i></div>
                <div class="logo-text"><span class="brand-num">24</span><span class="brand-word">SHOP</span></div>
            </a>
            <div class="nav-actions">
                <a href="categories.php" class="action-btn" title="All Categories"><i class="fa-solid fa-layer-group"></i></a>
                <a href="order-history.php" class="action-btn" title="Order History"><i class="fa-solid fa-clock-rotate-left"></i></a>
                <a href="cart.php" class="action-btn" title="Shopping Cart"><i class="fa-solid fa-bag-shopping"></i></a>
                <a href="logout.php" class="action-btn" title="Log Out" style="color: #ef4444;"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
            </div>
        </div>
    </header>

    <main class="account-main">
        <div class="account-heading" style="display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <span class="sub-heading">Customer area</span>
                <h1>My Account</h1>
            </div>
            <a href="logout.php" style="color: #ef4444; font-weight: 600; text-decoration: none; font-size: 14px; margin-bottom: 5px;">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Log Out
            </a>
        </div>
        <div class="account-layout">
            <section class="account-panel">
                <div class="profile-icon"><i class="fa-regular fa-user"></i></div>
                <h2>Personal details</h2>
                <?php echo $profile_message; ?>
                <form class="profile-form" method="post">
                    <input type="hidden" name="action" value="update_profile">
                    <label for="full_name">Full name</label>
                    <input id="full_name" name="full_name" value="<?php echo htmlspecialchars($customer['full_name']); ?>" required>
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="<?php echo htmlspecialchars($customer['email']); ?>" required>
                    <label for="phone">Phone</label>
                    <input id="phone" name="phone" value="<?php echo htmlspecialchars($customer['phone']); ?>">
                    <label for="address">Address</label>
                    <textarea id="address" name="address"><?php echo htmlspecialchars($customer['address']); ?></textarea>
                    <label for="city">City</label>
                    <input id="city" name="city" value="<?php echo htmlspecialchars($customer['city']); ?>">
                    <label for="state">State</label>
                    <input id="state" name="state" value="<?php echo htmlspecialchars($customer['state']); ?>">
                    <label for="pincode">PIN code</label>
                    <input id="pincode" name="pincode" value="<?php echo htmlspecialchars($customer['pincode']); ?>">
                    <button class="profile-save" type="submit">Save personal details</button>
                </form>
            </section>

            <section class="order-panel">
                <h2>Order history</h2>
                <?php if (empty($orders)): ?>
                    <p class="empty-orders">You have not placed any orders yet.</p>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <article class="order-card">
                            <div class="order-top"><strong><?php echo htmlspecialchars($order['order_id']); ?></strong><span class="order-status"><?php echo htmlspecialchars($order['status']); ?></span><small><?php echo htmlspecialchars($order['created_at']); ?></small></div>
                            <div class="order-body">
                                <?php foreach ($order['items'] as $item): ?>
                                    <div class="order-item"><span class="order-item-info"><?php if (!empty($item['image'])): ?><img class="order-item-image" src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name'] ?? 'Product'); ?>"><?php else: ?><i class="fa-solid fa-box"></i><?php endif; ?><span><?php echo htmlspecialchars($item['name'] ?? 'Product'); ?> x <?php echo (int) ($item['qty'] ?? $item['quantity'] ?? 1); ?></span></span><span>₹<?php echo number_format((float) ($item['price'] ?? 0) * (int) ($item['qty'] ?? $item['quantity'] ?? 1), 2); ?></span></div>
                                <?php endforeach; ?>
                                <div class="order-total"><span>Total</span><span>₹<?php echo number_format((float) $order['total_amount'], 2); ?></span></div>
                                <button class="repeat-order" type="button" data-items="<?php echo htmlspecialchars(json_encode($order['items'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8'); ?>"><i class="fa-solid fa-rotate-right"></i> Repeat order</button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </div>
    </main>
    <script>
        document.querySelectorAll('.repeat-order').forEach(button => {
            button.addEventListener('click', () => {
                const items = JSON.parse(button.dataset.items || '[]');
                localStorage.setItem('24shop_cart', JSON.stringify(items));
                window.location.href = 'cart.php';
            });
        });
    </script>
</body>
</html>
