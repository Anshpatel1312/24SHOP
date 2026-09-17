<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>24SHOP - Always Open, Anytime, Anywhere</title>
    <!-- Google Fonts for modern typography -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="index.css">
</head>

<body>

    <!-- Header / Navigation -->
    <header>
        <div class="nav-container">
            <!-- Brand Logo -->
            <a class="logo-container" href="index.php">
                <div class="logo-icon">
                    <i class="fa-solid fa-cart-shopping logo-cart"></i>
                </div>

                <div class="logo-text">
                    <span class="brand-num">24</span>
                    <span class="brand-word">SHOP</span>
                </div>
            </a>

            <!-- Navigation Links -->
            <ul class="nav-menu">
                <li><a href="index.php" class="active">Home</a></li>
                <li><a href="categories.php">Categories</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="#deals-section">Deals</a></li>
                <li><a href="#features-section">Why Us</a></li>
                <li><a href="#footer-section">Contact</a></li>
            </ul>

            <!-- Search & Actions -->
            <div class="nav-actions">
                <form action="categories.php" method="GET" class="search-bar" style="margin:0;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="q" placeholder="Search products...">
                </form>
                <a href="account.php" class="action-btn" title="Customer Account">
                    <i class="fa-regular fa-user"></i>
                </a>
                <a href="order-history.php" class="action-btn" title="Order History">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </a>
                <a href="shoplogin.php" class="action-btn" title="Vendor / Shop">
                    <i class="fa-solid fa-shop"></i>
                </a>
                <a href="cart.php" class="action-btn cart-btn" title="Shopping Cart">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span class="cart-badge" id="cartCount">0</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Hero / Main Banner Content -->
    <main>
        <section class="hero">
            <div class="hero-content">
                <p class="eyebrow"><i class="fa-solid fa-bolt"></i> Your 24/7 Shopping Destination</p>
                <h1>Always Open.<br><span>Anytime, Anywhere.</span></h1>
                <p>Discover top-rated products, enjoy lightning-fast delivery, and experience a seamless shopping journey built for your lifestyle.</p>

                <div class="cta-buttons">
                    <a class="btn btn-primary" href="home.php">Shop Now <i class="fa-solid fa-arrow-right"></i></a>
                    <a class="btn btn-secondary" href="login.php">Create Account</a>
                </div>

                <div class="hero-stats">
                    <div class="stat-card">
                        <strong>50k+</strong>
                        <span>Happy Customers</span>
                    </div>
                    <div class="stat-card">
                        <strong>24/7</strong>
                        <span>Live Support</span>
                    </div>
                    <div class="stat-card">
                        <strong>99%</strong>
                        <span>On-Time Delivery</span>
                    </div>
                </div>
            </div>

            <div class="hero-visual">
                <div class="visual-card">
                    <div class="visual-badge">Trending</div>
                    <div class="visual-icon">
                        <i class="fa-solid fa-basket-shopping"></i>
                    </div>
                    <div class="visual-text">
                        <h3>Fast & Easy Shopping</h3>
                        <p>Browse, order, and track delivery in just a few clicks.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Category Showcase -->
        <section id="categories-section" class="categories-container">
            <div class="section-header">
                <div>
                    <span class="sub-heading">Explore</span>
                    <h2>Featured Categories</h2>
                </div>
            </div>

            <div class="category-grid">
                <a href="categories.php?category=Electronics" style="text-decoration: none; color: inherit;">
                    <div class="cat-card">
                        <div class="cat-icon"><i class="fa-solid fa-laptop"></i></div>
                        <h3>Electronics</h3>
                        <p>Gadgets & Tech</p>
                    </div>
                </a>
                <a href="categories.php?category=Fashion" style="text-decoration: none; color: inherit;">
                    <div class="cat-card">
                        <div class="cat-icon"><i class="fa-solid fa-shirt"></i></div>
                        <h3>Fashion</h3>
                        <p>Trendy Apparel</p>
                    </div>
                </a>
                <a href="categories.php?category=Home%20%26%20Living" style="text-decoration: none; color: inherit;">
                    <div class="cat-card">
                        <div class="cat-icon"><i class="fa-solid fa-house"></i></div>
                        <h3>Home & Living</h3>
                        <p>Decor & Comfort</p>
                    </div>
                </a>
                <a href="categories.php?category=Beauty" style="text-decoration: none; color: inherit;">
                    <div class="cat-card">
                        <div class="cat-icon"><i class="fa-solid fa-spray-can-sparkles"></i></div>
                        <h3>Beauty</h3>
                        <p>Skincare & Care</p>
                    </div>
                </a>
            </div>
        </section>

        <!-- Horizontal Products Grid Section -->
        <section id="deals-section" class="product-section">
            <div class="section-header">
                <div>
                    <span class="sub-heading">Hot Selection</span>
                    <h2>Today's Deals & Products</h2>
                </div>
                <a href="home.php" class="view-all-link">See all deals <i class="fa-solid fa-chevron-right"></i></a>
            </div>

            <!-- Horizontal Grid Container for Products -->
            <div id="categories" class="product-grid">

                <?php
                // Use shared db.php connection
                require_once __DIR__ . '/db.php';
                $db_has_products = false;

                $sql = "SELECT * FROM product_details WHERE visible = 1 ORDER BY product_id DESC LIMIT 6";
                $result = mysqli_query($conn, $sql);

                if ($result && mysqli_num_rows($result) > 0) {
                    $db_has_products = true;
                    while ($row = mysqli_fetch_assoc($result)) {
                        // Only treat discountprice as a discount when it is strictly less than the full price
                        $has_discount = !empty($row['discountprice']) && floatval($row['discountprice']) < floatval($row['price']);
                        $discount = $has_discount ? floatval($row['discountprice']) : null;
                        $price = floatval($row['price']);
                        $image = !empty($row['image']) ? 'product image/' . $row['image'] : 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500&auto=format&fit=crop&q=60';
                ?>
                            <div class="product-card">
                                <a href="product.php?product_id=<?php echo $row['product_id']; ?>" class="product-link">
                                    <div class="image-box">
                                        <?php if ($discount): ?>
                                            <span class="badge-tag">SALE</span>
                                        <?php endif; ?>
                                        <img src="<?php echo $image; ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500&auto=format&fit=crop&q=60';">
                                        <?php 
                                        $final_unit_price = $discount ?? $price;
                                        $productJson = htmlspecialchars(json_encode([
                                            'id' => (int)$row['product_id'],
                                            'name' => $row['product_name'],
                                            'price' => (float)$final_unit_price,
                                            'image' => $image
                                        ]), ENT_QUOTES, 'UTF-8');
                                        ?>
                                        <button type="button" class="quick-add-btn" title="Add to Cart" onclick="quickAddToCart(event, <?php echo $productJson; ?>)"><i class="fa-solid fa-cart-plus"></i></button>
                                    </div>

                                    <div class="product-info">
                                        <div class="rating">
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star-half-stroke"></i>
                                            <span>(4.8)</span>
                                        </div>

                                        <h3><?php echo htmlspecialchars($row['product_name']); ?></h3>

                                        <div class="price">
                                            <?php if ($discount !== null): ?>
                                                <span class="new-price">₹<?php echo number_format($discount, 2); ?></span>
                                                <span class="old-price">₹<?php echo number_format($price, 2); ?></span>
                                            <?php else: ?>
                                                <span class="new-price">₹<?php echo number_format($price, 2); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                            </div>
                <?php
                    }
                }
                ?>

            </div>

        </section>

        <!-- Features / Trust Badges Section -->
        <section id="features-section" class="features-wrapper">
            <div class="feature-item">
                <div class="feature-icon"><i class="fa-solid fa-truck-fast"></i></div>
                <div>
                    <h4>Free & Fast Shipping</h4>
                    <p>On all orders over $50</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon"><i class="fa-solid fa-shield-halved"></i></div>
                <div>
                    <h4>Secure Checkout</h4>
                    <p>100% encrypted payment</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon"><i class="fa-solid fa-rotate-left"></i></div>
                <div>
                    <h4>Easy 7-Day Returns</h4>
                    <p>Hassle-free guarantee</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon"><i class="fa-solid fa-headset"></i></div>
                <div>
                    <h4>24/7 Dedicated Support</h4>
                    <p>Always here to assist you</p>
                </div>
            </div>
        </section>

    </main>

    <!-- FOOTER -->
    <footer id="footer-section" class="site-footer">
        <div class="footer-content">
            <div class="footer-brand">
                <div class="logo-container light-logo">
                    <div class="logo-icon">
                        <i class="fa-solid fa-cart-shopping logo-cart"></i>
                    </div>
                    <div class="logo-text">
                        <span class="brand-num">24</span>
                        <span class="brand-word">SHOP</span>
                    </div>
                </div>
                <p>Your trusted 24/7 shopping destination for everyday essentials and premium goods.</p>
                <div class="social-links">
                    <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" aria-label="X"><i class="fa-brands fa-x"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
                </div>
            </div>

            <div class="footer-links">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="#categories-section">Categories</a></li>
                    <li><a href="#deals-section">Deals</a></li>
                    <li><a href="#features-section">Why Us</a></li>
                </ul>
            </div>

            <div class="footer-links">
                <h4>Customer Care</h4>
                <ul>
                    <li><a href="#">Track Order</a></li>
                    <li><a href="#">Shipping Policy</a></li>
                    <li><a href="#">Returns & Refunds</a></li>
                    <li><a href="#">FAQ & Support</a></li>
                </ul>
            </div>

            <div class="footer-links contact-info">
                <h4>Contact Us</h4>
                <ul>
                    <li><i class="fa-solid fa-envelope"></i> support@24shop.com</li>
                    <li><i class="fa-solid fa-phone"></i> +91 98765 43210</li>
                    <li><i class="fa-solid fa-location-dot"></i> Ahmedabad, Gujarat, India</li>
                </ul>
            </div>
        </div>

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

        function quickAddToCart(e, product) {
            e.preventDefault();
            e.stopPropagation();

            let cart = [];
            try {
                cart = JSON.parse(localStorage.getItem('24shop_cart') || '[]');
            } catch(err) {
                cart = [];
            }

            const item = {
                id: Number(product.id),
                name: product.name,
                price: parseFloat(product.price),
                image: product.image,
                color: '',
                size: '',
                qty: 1
            };

            const existingIndex = cart.findIndex(i => Number(i.id) === item.id);
            if (existingIndex > -1) {
                cart[existingIndex].qty = Number(cart[existingIndex].qty || 1) + 1;
            } else {
                cart.push(item);
            }

            localStorage.setItem('24shop_cart', JSON.stringify(cart));
            updateCartBadge();
            alert(`"${product.name}" added to cart!`);
        }

        document.addEventListener('DOMContentLoaded', updateCartBadge);
        window.addEventListener('storage', updateCartBadge);
    </script>
</body>

</html>