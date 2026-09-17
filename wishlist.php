<?php
require_once __DIR__ . '/db.php';
$conn = $mysqli;
$products = [];

if ($conn) {
    $result = mysqli_query($conn, "SELECT * FROM product_details WHERE visible = 1 ORDER BY product_id DESC");
    if ($result) {
        while ($product = mysqli_fetch_assoc($result)) {
            $products[] = $product;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist | 24SHOP</title>
    <!-- Google Fonts for modern typography -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="index.css">
    <style>
        :root {
            --blue: #1d4ed8;
            --navy: #1e3a8a;
            --text: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            color: var(--text);
            background: linear-gradient(180deg, #f8fbff, #f5f7ff);
        }

        header {
            background: rgba(255, 255, 255, .92);
            border-bottom: 1px solid var(--line);
        }

        .nav {
            max-width: 1320px;
            margin: auto;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        .logo {
            color: var(--blue);
            font: 800 1.5rem 'Outfit', sans-serif;
            text-decoration: none;
        }

        .back {
            color: var(--muted);
            text-decoration: none;
            font-weight: 600;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .action-btn {
            position: relative;
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #fff;
            color: var(--muted);
            text-decoration: none;
        }

        .action-btn:hover {
            color: var(--blue);
            border-color: var(--blue);
        }

        .cart-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            min-width: 18px;
            height: 18px;
            padding: 0 4px;
            display: grid;
            place-items: center;
            border-radius: 999px;
            background: #f97316;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
        }

        main {
            max-width: 1320px;
            margin: auto;
            padding: 42px 24px 80px;
        }

        h1 {
            margin: 0 0 8px;
            font: 800 2.4rem 'Outfit', sans-serif;
        }

        .intro {
            color: var(--muted);
            margin: 0 0 28px;
        }

        /* .wishlist-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
        } */

        .product-card {
            display: flex;
            flex-direction: row;
            width: 100%;
            align-items: center;
            justify-content: space-between;
            background: rgba(255, 255, 255, .82);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 14px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, .06);
        }

        .product-card img {
            width: 150px;
            height: 120px;
            flex: 0 0 150px;
            object-fit: cover;
            border-radius: 12px;
            background: #f8fafc;
        }

        .product-info {
            flex: 1;
            min-width: 20px;
            margin: 10px 30px 10px 30px;
        }

        .product-name {

            font-weight: 700;
            line-height: 1.4;
        }

        .price {
            color: var(--navy);
            font: 800 1.1rem 'Outfit', sans-serif;
        }

        .card-actions {
            display: flex;
            flex: 0 0 210px;
            gap: 8px;
        }

        .card-actions a,
        .remove-btn {
            flex: 1;
            border: 0;
            border-radius: 10px;
            padding: 10px;
            text-align: center;
            cursor: pointer;
            font-weight: 700;
            text-decoration: none;
        }

        .view-btn {
            background: var(--blue);
            color: white;
        }

        .remove-btn {
            background: #fff1f2;
            color: #be123c;
        }

        .empty {
            padding: 60px 20px;
            text-align: center;
            background: rgba(255, 255, 255, .75);
            border: 1px dashed var(--line);
            border-radius: 16px;
            color: var(--muted);
        }

        .empty i {
            font-size: 42px;
            color: #fb7185;
            margin-bottom: 14px;
        }

        @media (max-width: 620px) {
            .product-card {
                align-items: stretch;
                flex-wrap: wrap;
                gap: 14px;
            }

            .product-card img {
                width: 110px;
                height: 100px;
                flex-basis: 110px;
            }

            .product-info {
                flex-basis: calc(100% - 124px);
            }

            .card-actions {
                flex-basis: 100%;
            }
        }

        @media (max-width: 520px) {
            main {
                padding-left: 16px;
                padding-right: 16px;
            }

            .nav {
                padding-left: 16px;
                padding-right: 16px;
            }

            h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>
    <header>
        <nav class="nav">
            <a href="index.php" class="logo-container">
                <div class="logo-icon">
                    <i class="fa-solid fa-cart-shopping logo-cart"></i>
                </div>
                <div class="logo-text">
                    <span class="brand-num">24</span>
                    <span class="brand-word">SHOP</span>
                </div>
            </a>
            <div class="nav-actions">
                <a class="back" href="home.php"><i class="fa-solid fa-arrow-left"></i> Continue shopping</a>
                <a href="cart.php" class="action-btn" title="Shopping Cart">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span class="cart-badge" id="cartCount">0</span>
                </a>
            </div>
        </nav>
    </header>
    <main>
        <h1>My Wishlist</h1>
        <p class="intro"><span id="wishlistCount">0</span> saved products</p>
        <div class="wishlist-grid" id="wishlistGrid"></div>
        <div class="empty" id="emptyWishlist" hidden>
            <i class="fa-regular fa-heart"></i>
            <h2>Your wishlist is empty</h2>
            <p>Save products here to find them quickly later.</p>
        </div>
    </main>
    <script>
        const products = <?php echo json_encode($products, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const wishlistKey = '24shop_wishlist';
        const grid = document.getElementById('wishlistGrid');
        const emptyState = document.getElementById('emptyWishlist');
        const count = document.getElementById('wishlistCount');

        function getWishlist() {
            try {
                const saved = JSON.parse(localStorage.getItem(wishlistKey) || '[]');
                return Array.isArray(saved) ? saved.map(Number) : [];
            } catch (error) {
                return [];
            }
        }

        function imagePath(product) {
            const image = product.image || '1784561108_polot-shirt.webp';
            return 'product image/' + image;
        }

        function renderWishlist() {
            const savedIds = getWishlist();
            const savedProducts = products.filter(product => savedIds.includes(Number(product.product_id)));
            count.textContent = savedProducts.length;
            grid.innerHTML = savedProducts.map(product => {
                const origPrice = Number(product.price || 0);
                const hasDiscount = product.discountprice && Number(product.discountprice) < origPrice;
                const sellingPrice = hasDiscount ? Number(product.discountprice) : origPrice;

                return `
                <article class="product-card">
                    <img src="${imagePath(product)}" alt="${escapeHtml(product.product_name)}">
                    <div class="product-info">
                        <div class="product-name">${escapeHtml(product.product_name)}</div>
                        <div class="price">
                            ₹${sellingPrice.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                            ${hasDiscount ? `<span style="font-size: 0.85rem; color: #94a3b8; text-decoration: line-through; margin-left: 6px; font-weight: normal;">₹${origPrice.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>` : ''}
                        </div>
                    </div>
                    <div class="card-actions">
                        <a class="view-btn" href="product.php?product_id=${Number(product.product_id)}">View</a>
                        <button class="remove-btn" onclick="removeFromWishlist(${Number(product.product_id)})">Remove</button>
                    </div>
                </article>
                `;
            }).join('');
            emptyState.hidden = savedProducts.length > 0;
        }

        function removeFromWishlist(productId) {
            const updated = getWishlist().filter(id => id !== Number(productId));
            localStorage.setItem(wishlistKey, JSON.stringify(updated));
            renderWishlist();
        }

        function escapeHtml(value) {
            return String(value).replace(/[&<>'"]/g, character => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                "'": '&#039;',
                '"': '&quot;'
            } [character]));
        }

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

        window.addEventListener('storage', () => {
            renderWishlist();
            updateCartBadge();
        });
        renderWishlist();
        updateCartBadge();
    </script>
</body>

</html>