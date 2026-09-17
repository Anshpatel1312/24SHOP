<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About 24SHOP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="index.css">
    <style>
        .about-main { max-width: 1100px; margin: auto; padding: 4rem 40px 6rem; }
        .about-hero { padding: 3rem; border-radius: 16px; background: #fff; box-shadow: var(--card-shadow); border: 1px solid var(--gray-border); }
        .about-hero h1 { margin: .4rem 0 1rem; color: var(--primary-blue); font-size: 2.5rem; }
        .about-hero p { max-width: 720px; color: #64748b; font-size: 1.05rem; }
        .about-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; margin-top: 1.5rem; }
        .about-item { padding: 1.5rem; border: 1px solid var(--gray-border); border-radius: 12px; background: #fff; }
        .about-item i { color: var(--accent-orange); font-size: 1.5rem; }
        .about-item h2 { margin: .8rem 0 .4rem; color: var(--primary-blue); font-size: 1.1rem; }
        .about-item p { color: #64748b; font-size: .92rem; }
        @media (max-width: 700px) { .about-main { padding: 2rem 18px 4rem; } .about-hero { padding: 1.5rem; } .about-hero h1 { font-size: 2rem; } .about-grid { grid-template-columns: 1fr; } }
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
                <a href="account.php" class="action-btn" title="Customer Account"><i class="fa-regular fa-user"></i></a>
                <a href="order-history.php" class="action-btn" title="Order History"><i class="fa-solid fa-clock-rotate-left"></i></a>
                <a href="cart.php" class="action-btn" title="Shopping Cart"><i class="fa-solid fa-bag-shopping"></i></a>
            </div>
        </div>
    </header>

    <main class="about-main">
        <section class="about-hero">
            <span class="sub-heading">About us</span>
            <h1>Shopping made simple at 24SHOP.</h1>
            <p>24SHOP brings useful everyday products together in one convenient online store. We focus on clear choices, dependable service, and a smooth experience from browsing to delivery.</p>
        </section>
        <section class="about-grid">
            <article class="about-item"><i class="fa-solid fa-bolt"></i><h2>Fast discovery</h2><p>Browse products and categories quickly with a clean, easy-to-use store.</p></article>
            <article class="about-item"><i class="fa-solid fa-shield-halved"></i><h2>Trusted service</h2><p>Your account and order information are handled with care throughout your shopping journey.</p></article>
            <article class="about-item"><i class="fa-solid fa-headset"></i><h2>Customer first</h2><p>Our customer care team is here to help with questions about your orders and products.</p></article>
        </section>
    </main>
</body>
</html>
