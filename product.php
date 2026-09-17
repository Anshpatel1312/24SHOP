<?php
session_start();
require_once __DIR__ . '/db.php';

// Safe retrieval of product_id
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$row = null;

if (isset($conn) && $conn && $product_id > 0) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM product_details WHERE product_id = ? AND visible = 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $product_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
        }
        mysqli_stmt_close($stmt);
    }
}

// Fallback: If no product found by ID or no ID provided, fetch latest product from DB
if (!$row && isset($conn) && $conn) {
    $res = mysqli_query($conn, "SELECT * FROM product_details ORDER BY product_id DESC LIMIT 1");
    if ($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        $product_id = intval($row['product_id']);
    }
}

// Fallback demo product if DB is empty or connection failed
if (!$row) {
    $row = [
        'product_id' => $product_id > 0 ? $product_id : 101,
        'product_name' => 'Premium Men\'s Slim Fit Cotton Polo Shirt',
        'category' => 'Men\'s Fashion',
        'gender' => 'male',
        'description' => 'Upgrade your wardrobe with this premium breathable cotton polo shirt. Crafted from 100% organic comb cotton, it features a sleek modern fit, reinforced collar, dynamic stretch fabric, and moisture-wicking technology for all-day comfort and effortless style.',
        'brand' => '24SHOP Exclusive',
        'price' => 1799.00,
        'discountprice' => 1299.00,
        'stockqty' => 18,
        'color' => 'Navy Blue',
        'size' => 'M, L, XL',
        'image' => '1784561108_polot-shirt.webp'
    ];
}

$selected_color = trim((string) ($row['color'] ?? ''));
if ($selected_color === '') {
    $selected_color = 'Midnight Navy';
}
$color_map = [
    'black' => '#0f172a',
    'charcoal black' => '#0f172a',
    'blue' => '#2563eb',
    'midnight navy' => '#1e3a8a',
    'navy blue' => '#1e3a8a',
    'gray' => '#94a3b8',
    'grey' => '#94a3b8',
    'heather gray' => '#94a3b8',
    'red' => '#dc2626',
    'crimson red' => '#dc2626',
    'green' => '#16a34a',
    'white' => '#ffffff'
];
$selected_color_key = strtolower($selected_color);
$selected_color_hex = $color_map[$selected_color_key] ?? '#64748b';
if (preg_match('/^#[0-9a-f]{3,8}$/i', $selected_color)) {
    $selected_color_hex = $selected_color;
}

$available_sizes = array_values(array_filter(array_map('trim', explode(',', (string) ($row['size'] ?? '')))));

// Determine image path
$image_file = !empty($row['image']) ? $row['image'] : '1784561108_polot-shirt.webp';
$image_path = "product image/" . $image_file;
if (!file_exists($image_path) && file_exists("uploads/" . $image_file)) {
    $image_path = "uploads/" . $image_file;
}

// Pricing calculations:
// 'price' in DB is original regular/MRP price.
// 'discountprice' is the discounted selling price (valid only when strictly less than 'price').
$original_price = floatval($row['price'] ?? 0);
$has_discount = !empty($row['discountprice']) && floatval($row['discountprice']) < $original_price;
$current_price = $has_discount ? floatval($row['discountprice']) : $original_price;
$mrp_price = $original_price;
$savings_amount = $has_discount ? ($original_price - $current_price) : 0;
$discount_percentage = ($has_discount && $original_price > 0) ? round(($savings_amount / $original_price) * 100) : 0;

// Fetch related products from DB
$related_products = [];
if (isset($conn) && $conn) {
    $current_id = isset($row['product_id']) ? intval($row['product_id']) : 0;
    $rel_stmt = mysqli_prepare($conn, "SELECT * FROM product_details WHERE product_id != ? AND visible = 1 ORDER BY RAND() LIMIT 4");
    if ($rel_stmt) {
        mysqli_stmt_bind_param($rel_stmt, "i", $current_id);
        mysqli_stmt_execute($rel_stmt);
        $rel_res = mysqli_stmt_get_result($rel_stmt);
        if ($rel_res && mysqli_num_rows($rel_res) > 0) {
            while ($rel_row = mysqli_fetch_assoc($rel_res)) {
                $related_products[] = $rel_row;
            }
        }
        mysqli_stmt_close($rel_stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($row['product_name']); ?> | 24SHOP</title>
    <meta name="description" content="<?php echo htmlspecialchars(substr(strip_tags($row['description']), 0, 160)); ?>">

    <!-- Google Fonts for modern typography -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main Design System CSS -->
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="product.css">
    <style>
        .product-page-wrapper {
            max-width: 1320px;
            margin: 0 auto;
            padding: 28px 24px;
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(0, 1.1fr);
            gap: 34px;
            align-items: start;
        }

        .gallery-column,
        .details-column {
            background: rgba(255, 255, 255, 0.62);
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-card);
        }

        .gallery-column {
            padding: 18px 16px;
        }

        .main-image-container {
            position: relative;
            height: 540px;
            overflow: hidden;
            border-radius: 18px;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: crosshair;
        }

        .main-image-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .zoom-lens {
            position: absolute;
            z-index: 3;
            width: 140px;
            height: 140px;
            display: none;
            pointer-events: none;
            border: 2px solid rgba(37, 99, 235, 0.7);
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
        }

        .zoom-preview-pane {
            position: absolute;
            z-index: 5;
            top: 18px;
            right: -300px;
            width: 280px;
            height: 280px;
            display: none;
            overflow: hidden;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            background-color: #fff;
            background-repeat: no-repeat;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.16);
            pointer-events: none;
        }

        .fullscreen-btn {
            position: absolute;
            right: 14px;
            bottom: 14px;
            z-index: 4;
            width: 40px;
            height: 40px;
            border: 0;
            border-radius: 50%;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.92);
        }

        .gallery-thumbs-row {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-top: 16px;
        }

        .gallery-thumb-item {
            height: 92px;
            overflow: hidden;
            border: 2px solid transparent;
            border-radius: 10px;
            cursor: pointer;
        }

        .gallery-thumb-item.active {
            border-color: #2563eb;
        }

        .gallery-thumb-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .size-pills-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .size-pill-btn {
            min-width: 48px;
            padding: 9px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #fff;
            cursor: pointer;
        }

        .size-pill-btn.active,
        .size-pill-btn:hover {
            border-color: #2563eb;
            background: #eff6ff;
            color: #1d4ed8;
        }

        @media (max-width: 760px) {
            .product-page-wrapper {
                grid-template-columns: 1fr;
                padding: 18px 16px;
            }

            .main-image-container {
                height: 380px;
            }

            .zoom-preview-pane {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <!-- Top Notice Banner -->
    <div class="top-notice-bar">
        <span><i class="fa-solid fa-truck-fast"></i> Free Express Shipping on orders over ₹499</span>
        <span>|</span>
        <span><i class="fa-solid fa-shield-halved"></i> 100% Genuine Guarantee</span>
        <span>|</span>
        <span><i class="fa-solid fa-clock-rotate-left"></i> 24/7 Customer Support</span>
    </div>

    <!-- Main Navigation Header -->
    <header>
        <div class="nav-container">
            <!-- Brand Logo -->
            <a href="index.php" class="logo-container">
                <div class="logo-icon">
                    <i class="fa-solid fa-cart-shopping logo-cart"></i>
                </div>
                <div class="logo-text">
                    <span class="brand-num">24</span>
                    <span class="brand-word">SHOP</span>
                </div>
            </a>

            <!-- Navigation Links -->
            <!-- <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="home.php" class="active">Products</a></li>
                <li><a href="index.php#categories-section">Categories</a></li>
                <li><a href="index.php#deals-section">Deals</a></li>
                <li><a href="index.php#footer-section">Contact</a></li>
            </ul> -->

            <!-- Header Action Buttons -->
            <div class="nav-actions">
                <form action="categories.php" method="GET" class="search-bar" style="margin:0;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="q" placeholder="Search products...">
                </form>

                <a href="wishlist.php" class="action-btn" id="wishlistTrigger" title="Wishlist">
                    <i class="fa-regular fa-heart"></i>
                    <span class="wish-badge-count" id="wishCount">0</span>
                </a>

                <div class="action-btn cart-btn" id="cartTrigger" title="Shopping Cart" onclick="window.location.href='cart.php'">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span class="cart-badge-count" id="cartCount">0</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Breadcrumb Context -->
    <div class="breadcrumb-wrapper">
        <div class="breadcrumb">
            <a href="home.php"><i class="fa-solid fa-house"></i> Home</a>
            <i class="fa-solid fa-chevron-right"></i>
            <a href="categories.php">Shop</a>
            <i class="fa-solid fa-chevron-right"></i>
            <a href="categories.php?category=<?php echo urlencode($row['category']); ?>"><?php echo htmlspecialchars($row['category']); ?></a>
            <i class="fa-solid fa-chevron-right"></i>
            <span class="current"><?php echo htmlspecialchars($row['product_name']); ?></span>
        </div>
    </div>

    <!-- Product Details Section -->
    <main class="product-page-wrapper">

        <!-- Left: Image Gallery & Zoom Lens -->
        <div class="gallery-column">
            <div class="main-image-container" id="mainImageWrapper">
                <?php if ($has_discount && $discount_percentage > 0): ?>
                    <span class="badge-discount-tag">-<?php echo $discount_percentage; ?>% OFF</span>
                <?php endif; ?>

                <img id="mainImage" src="<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>">

                <div class="zoom-lens" id="zoomLens"></div>
                <button class="fullscreen-btn" onclick="openFullscreenImage()" title="Fullscreen View">
                    <i class="fa-solid fa-expand"></i>
                </button>
            </div>

            <!-- Magnified Lens Preview Pane -->
            <div class="zoom-preview-pane" id="zoomPreview"></div>

            <!-- Gallery Thumbnails Carousel -->
            <div class="gallery-thumbs-row" id="thumbGallery">
                <div class="gallery-thumb-item active" onclick="switchMainImage('<?php echo htmlspecialchars($image_path); ?>', this)">
                    <img src="<?php echo htmlspecialchars($image_path); ?>" alt="Main View">
                </div>
                <!-- Interactive Multi-Angle Gallery Preview Thumbnails -->
                <div class="gallery-thumb-item" onclick="switchMainImage('<?php echo htmlspecialchars($image_path); ?>', this)">
                    <img src="<?php echo htmlspecialchars($image_path); ?>" alt="Angle 2" style="filter: brightness(1.05) contrast(1.05);">
                </div>
                <div class="gallery-thumb-item" onclick="switchMainImage('<?php echo htmlspecialchars($image_path); ?>', this)">
                    <img src="<?php echo htmlspecialchars($image_path); ?>" alt="Detail Fabric" style="filter: hue-rotate(15deg);">
                </div>
            </div>
        </div>

        <!-- Right: Interactive Product Information Console -->
        <div class="details-column">

            <div class="brand-category-meta">
                <span class="brand-badge"><i class="fa-solid fa-tag"></i> <?php echo htmlspecialchars($row['brand']); ?></span>
                <span class="stock-status-pill">In Stock (<?php echo intval($row['stockqty']); ?> available)</span>
            </div>

            <h1 class="product-title"><?php echo htmlspecialchars($row['product_name']); ?></h1>

            <!-- Ratings Bar -->
            <div class="rating-summary-row">
                <div class="stars-container">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star-half-stroke"></i>
                </div>
                <span class="rating-score">4.8</span>
                <span style="color: #cbd5e1;">|</span>
                <span class="reviews-count-link" onclick="switchTab('tab-reviews')">128 Customer Reviews</span>
            </div>

            <!-- Dynamic Pricing Hero Card -->
            <div class="pricing-hero-card">
                <div>
                    <div class="main-price-display">
                        <span>₹<span id="displayPriceVal"><?php echo number_format($current_price, 2); ?></span></span>
                        <?php if ($has_discount): ?>
                            <span class="original-mrp-price">₹<?php echo number_format($mrp_price, 2); ?></span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 12px; color: #64748b; margin-top: 2px;">Inclusive of all taxes</div>
                </div>

                <?php if ($has_discount && $savings_amount > 0): ?>
                    <div class="savings-pill">
                        <i class="fa-solid fa-fire"></i> Save ₹<?php echo number_format($savings_amount, 2); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Interactive Color Selection Swatches -->
            <div class="option-section">
                <div class="option-label-header">
                    <span>Select Color:</span>
                    <span class="selected-option-val" id="selectedColorName"><?php echo htmlspecialchars($selected_color); ?></span>
                </div>
                <div class="color-swatch-list">

                    <button class="color-swatch-btn active" onclick="selectColor(<?php echo htmlspecialchars(json_encode($selected_color), ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars(json_encode($selected_color_hex), ENT_QUOTES, 'UTF-8'); ?>, this)">
                        <span class="swatch-dot" style="background: <?php echo htmlspecialchars($selected_color_hex); ?>;"></span> <?php echo htmlspecialchars($selected_color); ?>
                    </button>

                </div>
            </div>

            <!-- Interactive Size Selection -->
            <div class="option-section">
                <div class="option-label-header">
                    <span>Select Size:</span>
                    <button class="size-guide-trigger" onclick="openSizeGuideModal()"><i class="fa-solid fa-ruler-horizontal"></i> Size Guide</button>
                </div>
                <div class="size-pills-list">
                    <?php foreach ($available_sizes as $size_index => $size_value): ?>
                        <button class="size-pill-btn<?php echo $size_index === 0 ? ' active' : ''; ?>" onclick="selectSize(<?php echo htmlspecialchars(json_encode($size_value), ENT_QUOTES, 'UTF-8'); ?>, this)"><?php echo htmlspecialchars($size_value); ?></button>
                    <?php endforeach; ?>
                    <?php if (empty($available_sizes)): ?>
                        <span>No sizes available</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Interactive Quantity Stepper & Price Calculation -->
            <div class="quantity-total-row">
                <div>
                    <div style="font-size: 13px; font-weight: 600; margin-bottom: 6px;">Quantity</div>
                    <div class="quantity-stepper">
                        <button class="qty-stepper-btn" onclick="updateQuantity(-1)"><i class="fa-solid fa-minus"></i></button>
                        <input type="number" id="qtyInput" class="qty-input-num" value="1" min="1" max="<?php echo intval($row['stockqty']); ?>" readonly>
                        <button class="qty-stepper-btn" onclick="updateQuantity(1)"><i class="fa-solid fa-plus"></i></button>
                    </div>
                </div>

                <div class="computed-subtotal">
                    <div class="subtotal-label">Total Subtotal</div>
                    <div class="subtotal-amount">₹<span id="calculatedSubtotal"><?php echo number_format($current_price, 2); ?></span></div>
                </div>
            </div>

            <!-- Primary Action Buttons -->
            <div class="actions-button-grid">
                <button class="btn-add-to-cart" id="btnAddToCart" onclick="handleAddToCart()">
                    <i class="fa-solid fa-cart-plus"></i> Add to Cart
                </button>
                <button class="btn-buy-now" onclick="openQuickCheckout(true)">
                    <i class="fa-solid fa-bolt"></i> Buy Now
                </button>
            </div>

            <!-- Secondary Interactive Actions -->
            <div class="secondary-actions-row">
                <button class="btn-icon-action" id="wishlistBtn" onclick="toggleWishlist()">
                    <i class="fa-regular fa-heart" id="wishIcon"></i> Wishlist
                </button>
                <button class="btn-icon-action" onclick="openShareModal()">
                    <i class="fa-solid fa-share-nodes"></i> Share
                </button>
            </div>

            <!-- Trust Badges -->
            <div class="trust-perks-grid">
                <div class="perk-item">
                    <i class="fa-solid fa-rotate-left"></i>
                    <strong>7 Days Return</strong> Easy hassle-free
                </div>
                <div class="perk-item">
                    <i class="fa-solid fa-shield-check"></i>
                    <strong>100% Genuine</strong> Direct from brand
                </div>
                <div class="perk-item">
                    <i class="fa-solid fa-lock"></i>
                    <strong>Secure Payment</strong> SSL encrypted
                </div>
            </div>

        </div>
    </main>

    <!-- Interactive Tabs System -->
    <section class="tabs-section-wrapper">
        <div class="tabs-nav-bar">
            <button class="tab-btn active" onclick="switchTab('tab-specs', this)">
                <i class="fa-solid fa-list-check"></i> Specifications
            </button>
            <button class="tab-btn" onclick="switchTab('tab-features', this)">
                <i class="fa-solid fa-circle-info"></i> Details & Highlights
            </button>
            <button class="tab-btn" onclick="switchTab('tab-reviews', this)">
                <i class="fa-solid fa-star"></i> Reviews (128)
            </button>
            <button class="tab-btn" onclick="switchTab('tab-faq', this)">
                <i class="fa-solid fa-circle-question"></i> Shipping & FAQ
            </button>
        </div>

        <!-- Specifications Tab Pane -->
        <div class="tab-pane active" id="tab-specs">
            <table class="specs-grid-table">
                <tr>
                    <td>Brand Name</td>
                    <td><?php echo htmlspecialchars($row['brand']); ?></td>
                </tr>
                <tr>
                    <td>Category</td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                </tr>
                <tr>
                    <td>Ideal For</td>
                    <td><?php echo htmlspecialchars(ucfirst($row['gender'])); ?></td>
                </tr>

            </table>
        </div>

        <!-- Details & Highlights Tab Pane -->
        <div class="tab-pane" id="tab-features">
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 20px; font-weight: 700; margin-bottom: 14px;">Product Overview</h3>
            <p style="font-size: 15px; color: #475569; line-height: 1.7; margin-bottom: 24px;">
                <?php echo htmlspecialchars($row['description']); ?>
            </p>

            <div class="features-cards-grid">
                <div class="feature-micro-card">
                    <i class="fa-solid fa-feather"></i>
                    <h4>Ultra Soft Touch</h4>
                    <p>Woven with ultra-fine cotton yarns for maximum softness against skin.</p>
                </div>
                <div class="feature-micro-card">
                    <i class="fa-solid fa-wind"></i>
                    <h4>Breathable Tech</h4>
                    <p>Engineered air channels keep you cool during warm summer days.</p>
                </div>
                <div class="feature-micro-card">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <h4>Anti-Pilling & Fade</h4>
                    <p>Color lock dye ensures vibrancy even after 50+ wash cycles.</p>
                </div>
            </div>
        </div>

        <!-- Customer Reviews Tab Pane -->
        <div class="tab-pane" id="tab-reviews">
            <div class="reviews-summary-box">
                <div class="overall-score-card">
                    <div class="score-big">4.8</div>
                    <div class="stars-container" style="justify-content: center; margin: 8px 0;">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star-half-stroke"></i>
                    </div>
                    <div style="font-size: 13px; color: #64748b;">Based on 128 Reviews</div>
                    <button class="btn-buy-now" style="width: 100%; height: 40px; font-size: 13px; margin-top: 16px;" onclick="openReviewModal()">Write a Review</button>
                </div>

                <div class="rating-bars-list">
                    <div class="rating-bar-row">
                        <span style="width: 50px;">5 Stars</span>
                        <div class="bar-container">
                            <div class="bar-fill" style="width: 82%;"></div>
                        </div>
                        <span style="width: 35px; text-align: right; color: #64748b;">82%</span>
                    </div>
                    <div class="rating-bar-row">
                        <span style="width: 50px;">4 Stars</span>
                        <div class="bar-container">
                            <div class="bar-fill" style="width: 12%;"></div>
                        </div>
                        <span style="width: 35px; text-align: right; color: #64748b;">12%</span>
                    </div>
                    <div class="rating-bar-row">
                        <span style="width: 50px;">3 Stars</span>
                        <div class="bar-container">
                            <div class="bar-fill" style="width: 4%;"></div>
                        </div>
                        <span style="width: 35px; text-align: right; color: #64748b;">4%</span>
                    </div>
                    <div class="rating-bar-row">
                        <span style="width: 50px;">2 Stars</span>
                        <div class="bar-container">
                            <div class="bar-fill" style="width: 1%;"></div>
                        </div>
                        <span style="width: 35px; text-align: right; color: #64748b;">1%</span>
                    </div>
                </div>
            </div>

            <!-- Review Cards List -->
            <div id="reviewsListContainer">
                <div class="user-review-card">
                    <div class="review-user-header">
                        <div class="reviewer-info">
                            <div class="avatar-circle">RK</div>
                            <div>
                                <div class="reviewer-name">Rahul K. <i class="fa-solid fa-circle-check" style="color: var(--brand-green);" title="Verified Purchase"></i></div>
                                <div class="review-date">2 days ago</div>
                            </div>
                        </div>
                        <div class="stars-container"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
                    </div>
                    <div class="review-text">Extremely impressed with the quality and fit! Fabric is super soft and breathable. Worth every rupee!</div>
                </div>

                <div class="user-review-card">
                    <div class="review-user-header">
                        <div class="reviewer-info">
                            <div class="avatar-circle" style="background: #ec4899;">PS</div>
                            <div>
                                <div class="reviewer-name">Priya S. <i class="fa-solid fa-circle-check" style="color: var(--brand-green);" title="Verified Purchase"></i></div>
                                <div class="review-date">1 week ago</div>
                            </div>
                        </div>
                        <div class="stars-container"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
                    </div>
                    <div class="review-text">Bought this for my brother. Delivery was fast within 2 days. Fits perfectly!</div>
                </div>
            </div>
        </div>

        <!-- FAQ Tab Pane -->
        <div class="tab-pane" id="tab-faq">
            <div class="faq-accordion-item active">
                <button class="faq-question-btn" onclick="toggleFaq(this)">
                    How long does standard delivery take?
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq-answer-content">
                    Orders are processed within 24 hours. Delivery typically takes 2-4 business days depending on your location.
                </div>
            </div>

            <div class="faq-accordion-item">
                <button class="faq-question-btn" onclick="toggleFaq(this)">
                    What is the return policy?
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq-answer-content">
                    We offer a 7-day hassle-free exchange and return window from the day of delivery. Item must be unworn with tags attached.
                </div>
            </div>

            <div class="faq-accordion-item">
                <button class="faq-question-btn" onclick="toggleFaq(this)">
                    Is cash on delivery (COD) available?
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq-answer-content">
                    Yes! Cash on Delivery is available across 25,000+ pincodes nationwide.
                </div>
            </div>
        </div>
    </section>

    <!-- Related Products Carousel Grid -->
    <section class="related-products-wrapper">
        <h3 class="section-header-title">
            <i class="fa-solid fa-fire-flame-curved" style="color: var(--brand-orange);"></i> You Might Also Like
        </h3>
        <div class="related-grid">
            <?php if (!empty($related_products)): ?>
                <?php foreach ($related_products as $rel):
                    $rel_img = !empty($rel['image']) ? $rel['image'] : '1784561108_polot-shirt.webp';
                    $rel_path = "product image/" . $rel_img;
                    if (!file_exists($rel_path) && file_exists("uploads/" . $rel_img)) {
                        $rel_path = "uploads/" . $rel_img;
                    }
                    $rel_orig_price = floatval($rel['price']);
                    $rel_has_discount = !empty($rel['discountprice']) && floatval($rel['discountprice']) < $rel_orig_price;
                    $rel_selling_price = $rel_has_discount ? floatval($rel['discountprice']) : $rel_orig_price;
                ?>
                    <a href="product.php?product_id=<?php echo intval($rel['product_id']); ?>" class="related-card">
                        <div class="related-img-box">
                            <img src="<?php echo htmlspecialchars($rel_path); ?>" alt="<?php echo htmlspecialchars($rel['product_name']); ?>">
                        </div>
                        <div class="related-title"><?php echo htmlspecialchars($rel['product_name']); ?></div>
                        <div class="stars-container" style="font-size: 12px; margin-bottom: 6px;">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star-half-stroke"></i>
                        </div>
                        <div class="related-price">
                            ₹<?php echo number_format($rel_selling_price, 2); ?>
                            <?php if ($rel_has_discount): ?>
                                <span style="font-size: 12px; color: #94a3b8; text-decoration: line-through; margin-left: 6px; font-weight: normal;">₹<?php echo number_format($rel_orig_price, 2); ?></span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <a href="product.php?product_id=1" class="related-card">
                    <div class="related-img-box">
                        <img src="product image/1784561202_polot-shirt.webp" alt="Casual Slim Fit Shirt">
                    </div>
                    <div class="related-title">Men's Casual Cotton Polo Shirt</div>
                    <div class="stars-container" style="font-size: 12px; margin-bottom: 6px;"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i> (48)</div>
                    <div class="related-price">₹1,199.00</div>
                </a>

                <a href="product.php?product_id=2" class="related-card">
                    <div class="related-img-box">
                        <img src="product image/1784956953_casual trousers.webp" alt="Casual Trousers">
                    </div>
                    <div class="related-title">Stretchable Casual Chino Trousers</div>
                    <div class="stars-container" style="font-size: 12px; margin-bottom: 6px;"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star-half-stroke"></i> (92)</div>
                    <div class="related-price">₹1,599.00</div>
                </a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-container" style="max-width: 1320px; margin: 0 auto; padding: 40px 24px; text-align: center; color: #64748b; font-size: 14px; background-color: #f0f0f0;">
            <p>&copy; <?php echo date("Y"); ?> 24SHOP. All rights reserved. Always Open, Anytime, Anywhere.</p>
        </div>
    </footer>

    <!-- Mobile Floating Action Bar -->
    <div class="mobile-floating-bar">
        <div>
            <div style="font-size: 11px; color: #64748b;">Total Price</div>
            <div class="mobile-price-val">₹<span id="mobileSubtotal"><?php echo number_format($current_price, 2); ?></span></div>
        </div>
        <button class="btn-add-to-cart" style="height: 44px; padding: 0 24px; font-size: 14px;" onclick="handleAddToCart()">
            <i class="fa-solid fa-cart-shopping"></i> Add to Cart
        </button>
    </div>

    <!-- Size Guide Modal -->
    <div class="modal-overlay" id="sizeGuideModal">
        <div class="modal-box">
            <button class="modal-close-icon" onclick="closeModal('sizeGuideModal')"><i class="fa-solid fa-xmark"></i></button>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 20px; font-weight: 700; margin-bottom: 16px;"><i class="fa-solid fa-ruler"></i> Size Measurement Guide</h3>
            <table class="specs-grid-table" style="margin-bottom: 20px;">
                <tr style="background: var(--brand-primary); color: white;">
                    <td>Size</td>
                    <td>Chest (Inches)</td>
                    <td>Length (Inches)</td>
                </tr>
                <tr>
                    <td>S</td>
                    <td>38"</td>
                    <td>27"</td>
                </tr>
                <tr>
                    <td>M</td>
                    <td>40"</td>
                    <td>28"</td>
                </tr>
                <tr>
                    <td>L</td>
                    <td>42"</td>
                    <td>29"</td>
                </tr>
                <tr>
                    <td>XL</td>
                    <td>44"</td>
                    <td>30"</td>
                </tr>
                <tr>
                    <td>XXL</td>
                    <td>46"</td>
                    <td>31"</td>
                </tr>
            </table>
            <p style="font-size: 13px; color: #64748b;">Measure around the fullest part of your chest, keeping the tape horizontal.</p>
        </div>
    </div>

    <!-- Write Review Modal -->
    <div class="modal-overlay" id="writeReviewModal">
        <div class="modal-box">
            <button class="modal-close-icon" onclick="closeModal('writeReviewModal')"><i class="fa-solid fa-xmark"></i></button>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 20px; font-weight: 700; margin-bottom: 16px;">Write a Review</h3>
            <form onsubmit="submitUserReview(event)">
                <div class="form-group-item">
                    <label>Your Name</label>
                    <input type="text" id="reviewAuthor" placeholder="Enter your full name..." required>
                </div>
                <div class="form-group-item">
                    <label>Rating</label>
                    <div class="stars-container" style="cursor: pointer; font-size: 20px;" id="starPicker">
                        <i class="fa-solid fa-star" onclick="setReviewRating(1)"></i>
                        <i class="fa-solid fa-star" onclick="setReviewRating(2)"></i>
                        <i class="fa-solid fa-star" onclick="setReviewRating(3)"></i>
                        <i class="fa-solid fa-star" onclick="setReviewRating(4)"></i>
                        <i class="fa-solid fa-star" onclick="setReviewRating(5)"></i>
                    </div>
                </div>
                <div class="form-group-item">
                    <label>Review Details</label>
                    <textarea id="reviewText" rows="4" placeholder="Describe your experience with this product..." required></textarea>
                </div>
                <button type="submit" class="btn-buy-now" style="width: 100%;">Submit Review</button>
            </form>
        </div>
    </div>



    <!-- Share Modal -->
    <div class="modal-overlay" id="shareModal">
        <div class="modal-box">
            <button class="modal-close-icon" onclick="closeModal('shareModal')"><i class="fa-solid fa-xmark"></i></button>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 20px; font-weight: 700; margin-bottom: 16px;"><i class="fa-solid fa-share"></i> Share Product</h3>
            <div style="display: flex; gap: 10px; margin-bottom: 16px;">
                <input type="text" id="shareUrlInput" readonly style="flex: 1; padding: 10px; border-radius: 8px; border: 1px solid var(--card-border);">
                <button class="btn-buy-now" style="height: auto; padding: 0 16px;" onclick="copyShareLink()">Copy</button>
            </div>
        </div>
    </div>

    <!-- Toast Notifications Area -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- JavaScript Interactive Engine -->
    <script>
        // Core Product State
        const productData = {
            id: <?php echo intval($row['product_id']); ?>,
            name: <?php echo json_encode($row['product_name']); ?>,
            price: <?php echo floatval($current_price); ?>,
            image: <?php echo json_encode($image_path); ?>,
            maxStock: <?php echo intval($row['stockqty']); ?>
        };

        let currentQty = 1;
        let selectedColor = <?php echo json_encode($selected_color); ?>;
        let selectedSize = <?php echo json_encode($available_sizes[0] ?? ''); ?>;
        let isWishlisted = false;

        // Initialize state on load
        document.addEventListener('DOMContentLoaded', () => {
            updateCalculatedPrice();
            initZoomLens();
            loadCartItems();
            loadWishlistState();
            document.getElementById('shareUrlInput').value = window.location.href;
        });

        /* --- Image Gallery & Zoom Lens --- */
        function switchMainImage(src, thumbElem) {
            const mainImg = document.getElementById('mainImage');
            mainImg.src = src;

            document.querySelectorAll('.gallery-thumb-item').forEach(el => el.classList.remove('active'));
            thumbElem.classList.add('active');
        }

        function openFullscreenImage() {
            const src = document.getElementById('mainImage').src;
            window.open(src, '_blank');
        }

        function initZoomLens() {
            const container = document.getElementById('mainImageWrapper');
            const img = document.getElementById('mainImage');
            const lens = document.getElementById('zoomLens');
            const preview = document.getElementById('zoomPreview');

            container.addEventListener('mousemove', (e) => {
                if (window.innerWidth < 1200) return;

                lens.style.display = 'block';
                preview.style.display = 'block';
                preview.style.backgroundImage = `url('${img.src}')`;

                const rect = container.getBoundingClientRect();
                let x = e.clientX - rect.left - (lens.offsetWidth / 2);
                let y = e.clientY - rect.top - (lens.offsetHeight / 2);

                if (x > container.offsetWidth - lens.offsetWidth) x = container.offsetWidth - lens.offsetWidth;
                if (x < 0) x = 0;
                if (y > container.offsetHeight - lens.offsetHeight) y = container.offsetHeight - lens.offsetHeight;
                if (y < 0) y = 0;

                lens.style.left = x + 'px';
                lens.style.top = y + 'px';

                const scaleX = preview.offsetWidth / lens.offsetWidth;
                const scaleY = preview.offsetHeight / lens.offsetHeight;
                const imageWidth = img.getBoundingClientRect().width;
                const imageHeight = img.getBoundingClientRect().height;
                preview.style.backgroundSize = (imageWidth * scaleX) + 'px ' + (imageHeight * scaleY) + 'px';
                preview.style.backgroundPosition = "-" + (x * scaleX) + "px -" + (y * scaleY) + "px";
            });

            container.addEventListener('mouseleave', () => {
                lens.style.display = 'none';
                preview.style.display = 'none';
            });
        }

        /* --- Options Selectors --- */
        function selectColor(colorName, colorHex, btnElem) {
            selectedColor = colorName;
            document.getElementById('selectedColorName').innerText = colorName;
            document.querySelectorAll('.color-swatch-btn').forEach(b => b.classList.remove('active'));
            btnElem.classList.add('active');
        }

        function selectSize(sizeVal, btnElem) {
            selectedSize = sizeVal;
            document.querySelectorAll('.size-pill-btn').forEach(b => b.classList.remove('active'));
            btnElem.classList.add('active');
        }

        /* --- Quantity & Price Calculations --- */
        function updateQuantity(change) {
            let newQty = currentQty + change;
            if (newQty >= 1 && newQty <= productData.maxStock) {
                currentQty = newQty;
                document.getElementById('qtyInput').value = currentQty;
                updateCalculatedPrice();
            } else if (newQty > productData.maxStock) {
                showToast(`Only ${productData.maxStock} items available in stock!`);
            }
        }

        function updateCalculatedPrice() {
            const total = productData.price * currentQty;
            const formatted = total.toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            document.getElementById('calculatedSubtotal').innerText = formatted;
            document.getElementById('mobileSubtotal').innerText = formatted;
        }

        /* --- Pincode Checker --- */
        function checkPincode() {
            const pin = document.getElementById('pincodeInput').value.trim();
            const resultBox = document.getElementById('pincodeResult');
            if (pin.length === 6 && /^\d+$/.test(pin)) {
                resultBox.style.display = 'block';
                showToast('Pincode verified! Delivery available.');
            } else {
                alert('Please enter a valid 6-digit pincode.');
                resultBox.style.display = 'none';
            }
        }

        /* --- Cart System --- */
        let cart = [];

        function loadCartItems() {
            try {
                const savedCart = localStorage.getItem('24shop_cart');
                cart = savedCart ? JSON.parse(savedCart) : [];
                if (!Array.isArray(cart)) cart = [];
            } catch (e) {
                cart = [];
            }
            updateCartBadge();
        }

        function handleAddToCart() {
            const item = {
                id: productData.id,
                name: productData.name,
                price: productData.price,
                image: productData.image,
                color: selectedColor,
                size: selectedSize,
                qty: currentQty
            };

            const existingIndex = cart.findIndex(i => i.id === item.id && i.color === item.color && i.size === item.size);
            if (existingIndex > -1) {
                cart[existingIndex].qty = Number(cart[existingIndex].qty || 1) + currentQty;
            } else {
                cart.push(item);
            }

            localStorage.setItem('24shop_cart', JSON.stringify(cart));
            updateCartBadge();

            // Button feedback animation
            const btn = document.getElementById('btnAddToCart');
            btn.classList.add('added-success');
            btn.innerHTML = `<i class="fa-solid fa-circle-check"></i> Added to Cart!`;
            showToast(`${productData.name} added to your cart!`);

            setTimeout(() => {
                btn.classList.remove('added-success');
                btn.innerHTML = `<i class="fa-solid fa-cart-plus"></i> Add to Cart`;
            }, 1200);
        }

        function updateCartBadge() {
            const totalCount = cart.reduce((sum, item) => sum + Number(item.qty || item.quantity || 1), 0);
            const badge = document.getElementById('cartCount');
            if (badge) badge.innerText = totalCount;
        }

        window.addEventListener('storage', () => {
            loadCartItems();
            loadWishlistState();
        });


        /* --- Wishlist System --- */
        function loadWishlistState() {
            const wish = localStorage.getItem('24shop_wishlist');
            const wishArray = wish ? JSON.parse(wish) : [];
            document.getElementById('wishCount').innerText = wishArray.length;
            if (wishArray.includes(productData.id)) {
                isWishlisted = true;
                setWishlistUI(true);
            }
        }

        function toggleWishlist() {
            let wish = localStorage.getItem('24shop_wishlist');
            let wishArray = wish ? JSON.parse(wish) : [];

            isWishlisted = !isWishlisted;
            if (isWishlisted) {
                if (!wishArray.includes(productData.id)) wishArray.push(productData.id);
                showToast('Added to Wishlist!');
            } else {
                wishArray = wishArray.filter(id => id !== productData.id);
                showToast('Removed from Wishlist');
            }
            localStorage.setItem('24shop_wishlist', JSON.stringify(wishArray));
            document.getElementById('wishCount').innerText = wishArray.length;
            setWishlistUI(isWishlisted);
        }

        function setWishlistUI(active) {
            const btn = document.getElementById('wishlistBtn');
            const icon = document.getElementById('wishIcon');
            if (active) {
                btn.classList.add('active-wish');
                icon.className = 'fa-solid fa-heart';
            } else {
                btn.classList.remove('active-wish');
                icon.className = 'fa-regular fa-heart';
            }
        }

        function toggleWishlistModal() {
            showToast('Wishlist items saved to your profile!');
        }

        /* --- Toast Notifications --- */
        function showToast(message) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = 'toast-msg';
            toast.innerHTML = `<i class="fa-solid fa-bell"></i> ${message}`;
            container.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        /* --- Tabs Switcher --- */
        function switchTab(tabId, btnElem) {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));

            document.getElementById(tabId).classList.add('active');
            if (btnElem) {
                btnElem.classList.add('active');
            } else {
                const targetBtn = Array.from(document.querySelectorAll('.tab-btn')).find(b => b.getAttribute('onclick').includes(tabId));
                if (targetBtn) targetBtn.classList.add('active');
            }
        }

        /* --- FAQ Accordion --- */
        function toggleFaq(btn) {
            const item = btn.parentElement;
            item.classList.toggle('active');
        }

        /* --- Modals Handlers --- */
        function openSizeGuideModal() {
            document.getElementById('sizeGuideModal').style.display = 'flex';
        }

        function openReviewModal() {
            document.getElementById('writeReviewModal').style.display = 'flex';
        }

        function openQuickCheckout(isDirectBuy = false) {
            if (isDirectBuy) {
                const item = {
                    id: productData.id,
                    name: productData.name,
                    price: productData.price,
                    image: productData.image,
                    color: selectedColor,
                    size: selectedSize,
                    qty: currentQty
                };

                const existingIndex = cart.findIndex(i => i.id === item.id && i.color === item.color && i.size === item.size);
                if (existingIndex > -1) {
                    cart[existingIndex].qty += currentQty;
                } else {
                    cart.push(item);
                }
                localStorage.setItem('24shop_cart', JSON.stringify(cart));
            }
            window.location.href = 'cart.php';
        }

        function openShareModal() {
            document.getElementById('shareModal').style.display = 'flex';
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        function copyShareLink() {
            const input = document.getElementById('shareUrlInput');
            input.select();
            document.execCommand('copy');
            showToast('Product link copied to clipboard!');
            closeModal('shareModal');
        }

        function submitUserReview(e) {
            e.preventDefault();
            const author = document.getElementById('reviewAuthor').value;
            const text = document.getElementById('reviewText').value;

            const newReviewHtml = `
                <div class="user-review-card">
                    <div class="review-user-header">
                        <div class="reviewer-info">
                            <div class="avatar-circle">${author.substring(0,2).toUpperCase()}</div>
                            <div>
                                <div class="reviewer-name">${author} <i class="fa-solid fa-circle-check" style="color: var(--brand-green);" title="Verified Purchase"></i></div>
                                <div class="review-date">Just now</div>
                            </div>
                        </div>
                        <div class="stars-container"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
                    </div>
                    <div class="review-text">${text}</div>
                </div>
            `;

            document.getElementById('reviewsListContainer').insertAdjacentHTML('afterbegin', newReviewHtml);
            closeModal('writeReviewModal');
            showToast('Thank you! Your review has been published.');
        }
    </script>
</body>

</html>