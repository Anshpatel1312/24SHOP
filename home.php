<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>24SHOP</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="index.css">
    <style>
        body {
            margin: 0;
            background: #f3f3f3;
            font-family: Arial, sans-serif;
        }

        /* search bar */
        .search-bar {
            width: 400px;
            height: 40px;
            box-shadow: 10px 10px 10px #e7e6e7;

        }

        .search-bar .fa-magnifying-glass {
            color: black;
        }

        .search-bar input {
            color: black;

        }

        /* slider */

        .p_sider {
            max-width: 1515px;
            height: 420px;
            margin: 24px 10px 0 10px;
            overflow: hidden;
            position: relative;
            border-radius: 22px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.14);
            background: #fff;
            display: block;
        }

        .slider-track {
            display: flex;
            width: 100%;
            height: 100%;
            transition: transform 0.8s ease-in-out;
        }

        .slide {
            min-width: 100%;
            height: 100%;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f5f5f5, #e7e7e7);
            overflow: hidden;
        }

        .slide::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(to right, rgba(0, 0, 0, 0.35), rgba(0, 0, 0, 0.05));
            pointer-events: none;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
            background-color: #f8fafc;
        }

        .slide-caption {
            position: absolute;
            left: 40px;
            bottom: 40px;
            z-index: 2;
            color: white;
            max-width: 380px;
            background: rgba(0, 0, 0, 0.35);
            padding: 14px 18px;
            border-radius: 12px;
            backdrop-filter: blur(3px);
        }

        .slide-caption h3 {
            margin: 0 0 6px;
            font-size: 22px;
        }

        .slide-caption p {
            margin: 0;
            font-size: 14px;
            opacity: 0.95;
        }

        .slider-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 3;
            border: none;
            background: rgba(255, 255, 255, 0.95);
            color: #111;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
            transition: all 0.25s ease;
        }

        .slider-btn:hover {
            background: white;
            transform: translateY(-50%) scale(1.08);
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.25);
        }

        .slider-btn.prev {
            left: 16px;
        }

        .slider-btn.next {
            right: 16px;
        }

        .slider-dots {
            position: absolute;
            bottom: 16px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 8px;
            z-index: 3;
        }

        .slider-dots span {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.6);
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .slider-dots span.active {
            background: white;
            transform: scale(1.1);
        }


        .cetegories {
            display: flex;
            width: 100%;
            gap: 20px;
            padding: 10px 20px 0;
        }

        .cetegories-container {
            flex: 1;
            width: auto;
            height: 185px;
            border: 1.5px solid rgba(0, 0, 0, 0.1);
            background-color: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            padding: 16px;
            border-radius: 18px;
            margin: 12px 0;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .cetegories-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
            border-color: rgba(37, 99, 235, 0.2);
        }

        .cetegories-container a {
            display: block;
            color: inherit;
            text-decoration: none;
        }

        .cetegories-container i {
            display: grid;
            width: 100px;
            height: 100px;
            margin: 0 auto;
            place-items: center;
            border-radius: 16px;
            color: #1769aa;
            background: #edf6ff;
            font-size: 40px;
            transition: transform 0.3s ease;
        }

        .cetegories-container:hover i {
            transform: scale(1.1);
        }

        .cetegories-container h3 {
            margin: 10px 0 0;
            color: #172033;
            font-size: 16px;
            text-align: center;
        }

        /* product-section */

        .product-section a {
            width: 300px;
            height: 360px;
            text-decoration: none;
            border: 1.5px solid #7e99d1;
            display: inline-block;
            overflow: hidden;
            border-radius: 16px;
            background: #fff;
            text-align: left;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
            margin: 0 0 12px 20px;
            transition: all 0.32s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .product-section a:hover {
            transform: translateY(-6px);
            box-shadow: 0 14px 30px rgba(37, 99, 235, 0.15);
            border-color: rgba(37, 99, 235, 0.4);
        }

        .product-info h3,
        .product-info p {
            color: black;
        }

        /* Fix price visibility inside home page product cards */
        .product-section a .new-price {
            display: block;
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--primary-accent, #2563eb) !important;
        }

        .product-section a .old-price {
            display: block;
            font-size: 0.85rem;
            color: #94a3b8 !important;
            text-decoration: line-through;
        }

        .product-section a .price {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 8px 10px 4px;
            border-top: 1px solid #e2e8f0;
            margin-top: 4px;
        }

        .product-section a .product-info {
            padding: 10px 12px 8px;
            overflow: visible;
        }

        .product-section a {
            height: auto;
            min-height: 340px;
        }

        @media (max-width: 700px) {
            .cetegories {
                flex-wrap: wrap;
                gap: 10px;
            }

            .cetegories-container {
                flex: 1 1 calc(50% - 10px);
            }
        }
    </style>
</head>

<body>

    <header>
        <div class="nav-container">
            <!-- Brand Logo -->
            <a class="logo-container" href="home.php">
                <div class="logo-icon">
                    <i class="fa-solid fa-cart-shopping logo-cart"></i>
                </div>

                <div class="logo-text">
                    <span class="brand-num">24</span>
                    <span class="brand-word">SHOP</span>
                </div>
            </a>


            <!-- Search & Actions -->
            <div class="nav-actions">
                <form action="categories.php" method="GET" class="search-bar" style="margin:0;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="q" placeholder="Search products...">
                </form>
                <a href="categories.php" class="action-btn" title="Browse Categories">
                    <i class="fa-solid fa-layer-group"></i>
                </a>
                <a href="account.php" class="action-btn" title="Customer Account">
                    <i class="fa-regular fa-user"></i>
                </a>
                <a href="order-history.php" class="action-btn" title="Order History">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </a>
                <a href="cart.php" class="action-btn cart-btn" title="Shopping Cart">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span class="cart-badge" id="cartCount">0</span>
                </a>
            </div>
        </div>
    </header>
    <div class="p_sider">
        <button class="slider-btn prev" type="button" aria-label="Previous slide">&#10094;</button>
        <button class="slider-btn next" type="button" aria-label="Next slide">&#10095;</button>

        <div class="slider-track">
            <?php
            require_once __DIR__ . "/db.php";
            $sql = "SELECT * FROM product_details WHERE visible = 1 ORDER BY product_id DESC LIMIT 7";
            $result = mysqli_query($conn, $sql);
            $products = [];

            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $products[] = $row;
                }
            }

            if (!empty($products)) {
                $sliderProducts = [$products[count($products) - 1], ...$products, $products[0]];

                foreach ($sliderProducts as $row) {
            ?>
                    <div class="slide">
                        <a href="product.php?product_id=<?= (int)$row['product_id'] ?>" style="display:block; width:100%; height:100%;">
                            <img src="product image/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['product_name']) ?>" class="product-image" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500&auto=format&fit=crop&q=60';">
                        </a>
                    </div>
            <?php
                }
            }
            ?>
        </div>

        <div class="slider-dots"></div>
    </div>

    <section class="cetegories">
        <div class="cetegories-container">
            <a href="categories.php?category=Electronics" aria-label="View Electronics products">
                <i class="fa-solid fa-laptop" aria-hidden="true"></i>
                <h3>Electronics</h3>
            </a>
        </div>
        <div class="cetegories-container">
            <a href="categories.php?category=Fashion" aria-label="View Fashion products">
                <i class="fa-solid fa-shirt" aria-hidden="true"></i>
                <h3>Fashion</h3>
            </a>
        </div>
        <div class="cetegories-container">
            <a href="categories.php?category=Men%27s%20Fashion" aria-label="View Men's Fashion products">
                <i class="fa-solid fa-house" aria-hidden="true"></i>
                <h3>Men's Fashion</h3>
            </a>
        </div>
        <div class="cetegories-container">
            <a href="categories.php?category=Beauty" aria-label="View Beauty products">
                <i class="fa-solid fa-spray-can-sparkles" aria-hidden="true"></i>
                <h3>Beauty</h3>
            </a>
        </div>
    </section>


    <!-- product-section -->

    <section class="product-section">

        <?php
        $sql = "SELECT * FROM product_details WHERE visible = 1 ORDER BY product_id DESC LIMIT 8";
        $result = mysqli_query($conn, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $has_discount = !empty($row['discountprice']) && floatval($row['discountprice']) < floatval($row['price']);
        ?>
                <a href="product.php?product_id=<?php echo $row['product_id']; ?>" class="product-card">

                    <div class="image-box">
                        <img src="product image/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500&auto=format&fit=crop&q=60';">
                    </div>

                    <div class="product-info">

                        <h3><?php echo htmlspecialchars($row['product_name']); ?></h3>

                        <div class="price">

                            <?php if ($has_discount): ?>
                                <span class="new-price">
                                    ₹<?php echo number_format($row['discountprice'], 2); ?>
                                </span>
                                <span class="old-price">
                                    ₹<?php echo number_format($row['price'], 2); ?>
                                </span>
                            <?php else: ?>
                                <span class="new-price">
                                    ₹<?php echo number_format($row['price'], 2); ?>
                                </span>
                            <?php endif; ?>

                        </div>

                    </div>

                </a>
        <?php
            }
        }
        ?>
    </section>

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
                    <li><a href="categories.php">Categories</a></li>
                    <li><a href="about.php">About 24SHOP</a></li>
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

        <div class="footer-bottom">
            <p>© <?php echo date("Y"); ?> 24SHOP. All rights reserved. Built for fast, modern e-commerce.</p>
        </div>
    </footer>


    <!-- -------------------------------------------------------------------------------------------------- -->
    <script>
        const track = document.querySelector('.slider-track');
        const slides = Array.from(document.querySelectorAll('.slide'));
        const dotsContainer = document.querySelector('.slider-dots');
        const prevBtn = document.querySelector('.slider-btn.prev');
        const nextBtn = document.querySelector('.slider-btn.next');

        if (track && slides.length) {
            let index = 1;
            let timer;

            const renderDots = () => {

                dotsContainer.innerHTML = '';
                for (let i = 0; i < slides.length - 2; i++) {
                    const dot = document.createElement('span');
                    dot.classList.toggle('active', i === index - 1);
                    dot.addEventListener('click', () => {
                        index = i + 1;
                        update(true);
                        restart();
                    });
                    dotsContainer.appendChild(dot);
                }
            };

            const update = (animate = true) => {
                track.style.transition = animate ? 'transform 0.8s ease-in-out' : 'none';
                track.style.transform = `translateX(-${index * 100}%)`;
                renderDots();
            };
            // next Image slide
            const next = () => {
                index++;
                update(true);
                if (index === slides.length - 1) {
                    setTimeout(() => {
                        index = 1;
                        update(false);
                    }, 800);
                }
            };

            // prev image slide
            const prev = () => {
                index--;
                update(true);
                if (index === 0) {
                    setTimeout(() => {
                        index = slides.length - 2;
                        update(false);
                    }, 800);
                }
            };
            // restart timer
            const restart = () => {
                clearInterval(timer);
                timer = setInterval(next, 4000);
            };

            prevBtn?.addEventListener('click', () => {
                prev();
                restart();
            });
            nextBtn?.addEventListener('click', () => {
                next();
                restart();
            });

            update(false);
            renderDots();
            restart();
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

        updateCartBadge();
        window.addEventListener('storage', updateCartBadge);
    </script>
</body>

</html>