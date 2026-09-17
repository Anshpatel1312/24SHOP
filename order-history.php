<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit;
}

$customer_id = (int) $_SESSION['user']['id'];
$stmt = $mysqli->prepare('SELECT full_name FROM customers WHERE id = ?');
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
$stmt = $mysqli->prepare('SELECT order_id, total_amount, status, items_json, created_at FROM orders WHERE customer_id = ? OR (customer_id IS NULL AND customer_name = ?) ORDER BY created_at DESC');
$stmt->bind_param('is', $customer_id, $customer['full_name']);
$stmt->execute();
$result = $stmt->get_result();
while ($order = $result->fetch_assoc()) {
    $order['items'] = json_decode($order['items_json'], true) ?: [];
    $orders[] = $order;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History | 24SHOP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="index.css">
    <style>
        .history-main { max-width: 950px; margin: auto; padding: 3rem 40px 5rem; min-height: 70vh; }
        .history-heading { margin-bottom: 2rem; }
        .history-heading h1 { margin-top: .35rem; color: var(--primary-blue); }
        .history-panel { padding: 1.5rem; border: 1px solid var(--gray-border); border-radius: 12px; background: #fff; box-shadow: var(--card-shadow); }
        .order-card { margin-bottom: 1rem; border: 1px solid var(--gray-border); border-radius: 10px; overflow: hidden; }
        .order-card:last-child { margin-bottom: 0; }
        .order-top { display: flex; justify-content: space-between; gap: 1rem; padding: 1rem; background: #f8fafc; }
        .order-top strong { color: var(--primary-blue); }
        .order-status { color: #067d62; font-weight: 700; font-size: .85rem; }
        .order-body { padding: 1rem; }
        .order-item, .order-total { display: flex; justify-content: space-between; gap: 1rem; padding: .45rem 0; }
        .order-item { color: #64748b; font-size: .9rem; }
        .order-item-info { display: flex; align-items: center; gap: .7rem; }
        .order-item-image { width: 52px; height: 52px; object-fit: contain; border: 1px solid var(--gray-border); border-radius: 7px; background: #f8fafc; }
        .order-total { margin-top: .75rem; padding-top: .75rem; border-top: 1px solid var(--gray-border); color: var(--primary-blue); font-weight: 800; }
        .repeat-order { margin-top: 1rem; padding: .65rem 1rem; border: 0; border-radius: 8px; background: var(--primary-accent); color: #fff; font-weight: 700; cursor: pointer; }
        .empty-orders { color: #64748b; }
        @media (max-width: 600px) { .history-main { padding: 2rem 18px 4rem; } .order-top { flex-direction: column; gap: .25rem; } }
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
                <a href="home.php" class="action-btn" title="Shop Home"><i class="fa-solid fa-house"></i></a>
                <a href="categories.php" class="action-btn" title="All Categories"><i class="fa-solid fa-layer-group"></i></a>
                <a href="account.php" class="action-btn" title="Customer Account"><i class="fa-regular fa-user"></i></a>
                <a href="cart.php" class="action-btn" title="Shopping Cart"><i class="fa-solid fa-bag-shopping"></i></a>
            </div>
        </div>
    </header>

    <main class="history-main">
        <div class="history-heading"><span class="sub-heading">Customer area</span><h1>Order History</h1></div>
        <section class="history-panel">
            <?php if (empty($orders)): ?>
                <p class="empty-orders">You have not placed any orders yet. <a href="home.php" style="color: var(--primary-accent); font-weight: 700;">Start shopping now</a>.</p>
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
    </main>
    <script>
        document.querySelectorAll('.repeat-order').forEach(button => {
            button.addEventListener('click', () => {
                localStorage.setItem('24shop_cart', JSON.stringify(JSON.parse(button.dataset.items || '[]')));
                window.location.href = 'cart.php';
            });
        });
    </script>
</body>
</html>
