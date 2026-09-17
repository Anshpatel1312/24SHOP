<?php
session_start();
require_once __DIR__ . '/db.php';

$selected_category = trim($_GET['category'] ?? '');
$search_query = trim($_GET['q'] ?? $_GET['search'] ?? '');
$categories = [];
$products = [];

$category_result = $mysqli->query("SELECT DISTINCT category FROM product_details WHERE visible = 1 AND category <> '' ORDER BY category");
if ($category_result) {
    while ($row = $category_result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}

if ($search_query !== '' && $selected_category !== '') {
    $search_param = '%' . $search_query . '%';
    $stmt = $mysqli->prepare('SELECT product_id, product_name, price, discountprice, image, category, brand FROM product_details WHERE visible = 1 AND category = ? AND (product_name LIKE ? OR brand LIKE ? OR description LIKE ?) ORDER BY product_id DESC');
    $stmt->bind_param('ssss', $selected_category, $search_param, $search_param, $search_param);
    $stmt->execute();
    $product_result = $stmt->get_result();
    $stmt->close();
} elseif ($search_query !== '') {
    $search_param = '%' . $search_query . '%';
    $stmt = $mysqli->prepare('SELECT product_id, product_name, price, discountprice, image, category, brand FROM product_details WHERE visible = 1 AND (product_name LIKE ? OR category LIKE ? OR brand LIKE ? OR description LIKE ?) ORDER BY product_id DESC');
    $stmt->bind_param('ssss', $search_param, $search_param, $search_param, $search_param);
    $stmt->execute();
    $product_result = $stmt->get_result();
    $stmt->close();
} elseif ($selected_category !== '') {
    $stmt = $mysqli->prepare('SELECT product_id, product_name, price, discountprice, image, category, brand FROM product_details WHERE visible = 1 AND category = ? ORDER BY product_id DESC');
    $stmt->bind_param('s', $selected_category);
    $stmt->execute();
    $product_result = $stmt->get_result();
    $stmt->close();
} else {
    $product_result = $mysqli->query('SELECT product_id, product_name, price, discountprice, image, category, brand FROM product_details WHERE visible = 1 ORDER BY product_id DESC');
}

if ($product_result) {
    while ($row = $product_result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $search_query !== '' ? 'Search: ' . htmlspecialchars($search_query) . ' | 24SHOP' : ($selected_category !== '' ? htmlspecialchars($selected_category) . ' | 24SHOP' : 'All Categories | 24SHOP'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="index.css">
    <style>
        .page-main { padding: 3rem 45px 5rem; min-height: 70vh; }
        .page-heading { margin-bottom: 2rem; }
        .page-heading h1 { color: var(--primary-blue); font-size: 2.2rem; }
        .category-links { display: flex; flex-wrap: wrap; gap: .75rem; margin-bottom: 2.5rem; }
        .category-links a { padding: .7rem 1.1rem; border: 1px solid var(--gray-border); border-radius: 8px; background: #fff; color: var(--primary-blue); font-weight: 700; text-decoration: none; transition: all 0.2s; }
        .category-links a.active, .category-links a:hover { background: var(--primary-accent); border-color: var(--primary-accent); color: #fff; }
        .category-products { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1.5rem; }
        .category-product { overflow: hidden; border: 1px solid var(--gray-border); border-radius: 12px; background: #fff; box-shadow: var(--card-shadow); transition: transform 0.2s, box-shadow 0.2s; }
        .category-product:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.08); }
        .category-product img { width: 100%; height: 220px; display: block; object-fit: contain; background: #f8fafc; }
        .category-product-info { padding: 1rem; }
        .category-product h2 { margin: 0 0 .5rem; color: var(--primary-blue); font-size: 1rem; }
        .category-name { margin-bottom: .5rem; color: #64748b; font-size: .85rem; }
        .price { color: var(--primary-accent); font-weight: 800; }
        .old-price { margin-left: .4rem; color: #94a3b8; font-weight: 400; text-decoration: line-through; }
        .empty-state { grid-column: 1 / -1; padding: 3rem 2rem; border: 1px dashed var(--gray-border); border-radius: 12px; color: #64748b; background: #fff; text-align: center; }
        .empty-state i { display: block; margin-bottom: .8rem; color: var(--accent-orange); font-size: 2rem; }
        .empty-state strong { display: block; margin-bottom: .35rem; color: var(--primary-blue); font-size: 1.25rem; }
        .cart-badge { position: absolute; top: -6px; right: -6px; min-width: 18px; height: 18px; padding: 0 4px; display: grid; place-items: center; border-radius: 999px; background: #f97316; color: #fff; font-size: 10px; font-weight: 700; }
        @media (max-width: 600px) { .page-main { padding: 2rem 18px 4rem; } .category-products { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .8rem; } .category-product img { height: 150px; } }
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
                <form action="categories.php" method="GET" class="search-bar" style="margin:0;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Search products...">
                </form>
                <a href="account.php" class="action-btn" title="Customer Account"><i class="fa-regular fa-user"></i></a>
                <a href="order-history.php" class="action-btn" title="Order History"><i class="fa-solid fa-clock-rotate-left"></i></a>
                <a href="cart.php" class="action-btn" title="Shopping Cart">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span class="cart-badge" id="cartCount">0</span>
                </a>
            </div>
        </div>
    </header>

    <main class="page-main">
        <div class="page-heading">
            <span class="sub-heading">Browse</span>
            <h1>
                <?php
                if ($search_query !== '') {
                    echo 'Search Results for "' . htmlspecialchars($search_query) . '"';
                } elseif ($selected_category !== '') {
                    echo htmlspecialchars($selected_category);
                } else {
                    echo 'All Categories';
                }
                ?>
            </h1>
        </div>
        <nav class="category-links" aria-label="Product categories">
            <a href="categories.php<?php echo $search_query !== '' ? '?q=' . urlencode($search_query) : ''; ?>" class="<?php echo $selected_category === '' ? 'active' : ''; ?>">All</a>
            <?php foreach ($categories as $category): ?>
                <?php
                $cat_link = 'categories.php?category=' . urlencode($category);
                if ($search_query !== '') $cat_link .= '&q=' . urlencode($search_query);
                ?>
                <a href="<?php echo $cat_link; ?>" class="<?php echo $selected_category === $category ? 'active' : ''; ?>"><?php echo htmlspecialchars($category); ?></a>
            <?php endforeach; ?>
        </nav>

        <div class="category-products">
            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-box-open" aria-hidden="true"></i>
                    <strong>No products found</strong>
                    <span>Try selecting another category or changing your search terms.</span>
                </div>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <?php 
                    $image = !empty($product['image']) ? 'product image/' . $product['image'] : 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500&auto=format&fit=crop&q=60';
                    $has_discount = !empty($product['discountprice']) && floatval($product['discountprice']) < floatval($product['price']);
                    $final_price = $has_discount ? $product['discountprice'] : $product['price'];
                    $orig_price = $product['price'];
                    ?>
                    <article class="category-product">
                        <a href="product.php?product_id=<?php echo (int) $product['product_id']; ?>" style="text-decoration: none; color: inherit;">
                            <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500&auto=format&fit=crop&q=60';">
                            <div class="category-product-info">
                                <h2><?php echo htmlspecialchars($product['product_name']); ?></h2>
                                <p class="category-name"><?php echo htmlspecialchars($product['category']); ?></p>
                                <span class="price">₹<?php echo number_format($final_price, 2); ?></span>
                                <?php if ($has_discount): ?>
                                    <span class="old-price">₹<?php echo number_format($orig_price, 2); ?></span>
                                <?php endif; ?>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
    <script>
        function updateCartBadge() {
            let cartItems = [];
            try {
                cartItems = JSON.parse(localStorage.getItem('24shop_cart') || '[]');
            } catch (error) {
                cartItems = [];
            }
            const totalCount = cartItems.reduce((sum, item) => sum + Number(item.qty || item.quantity || 1), 0);
            const cartCount = document.getElementById('cartCount');
            if (cartCount) {
                cartCount.textContent = totalCount;
            }
        }
        updateCartBadge();
        window.addEventListener('storage', updateCartBadge);
    </script>
</body>
</html>
