<?php
// TAB 2: PRODUCTS CATALOG & DETAILS
$parent_dir = dirname(__DIR__);
if (!isset($conn) || !$conn) {
    if (file_exists($parent_dir . '/db.php')) {
        include_once $parent_dir . '/db.php';
    }
}
if (!isset($upload_dir)) {
    $upload_dir = $parent_dir . "/product image/";
}
if (!isset($products) && isset($conn) && $conn) {
    $products = [];
    $prod_res = mysqli_query($conn, "SELECT * FROM product_details ORDER BY product_id DESC");
    if ($prod_res) {
        while ($r = mysqli_fetch_assoc($prod_res)) {
            $products[] = $r;
        }
    }
}
if (!isset($categories_cnt)) {
    $categories_cnt = [];
    if (!empty($products)) {
        foreach ($products as $p_item) {
            $c_item = !empty($p_item['category']) ? $p_item['category'] : 'Uncategorized';
            if (!isset($categories_cnt[$c_item])) $categories_cnt[$c_item] = 0;
            $categories_cnt[$c_item]++;
        }
    }
}
?>
<style>
    .product-actions { display: flex; align-items: center; gap: 6px; white-space: nowrap; }
    .product-actions form { display: inline-flex; margin: 0; }
    .product-action-button { display: inline-flex; width: 34px; height: 32px; align-items: center; justify-content: center; padding: 0; border: 0; border-radius: 5px; color: #fff; cursor: pointer; }
    a.product-action-button { text-decoration: none; }
</style>
<div class="tab-panel" id="tab-products">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fa-solid fa-boxes-stacked"></i> Products Catalog Management</h3>
        </div>

        <div class="controls-row">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="prodSearch" placeholder="Search by name, brand, or ID..." onkeyup="filterProducts()">
            </div>
            <select class="select-box" id="catFilter" onchange="filterProducts()">
                <option value="">All Categories</option>
                <?php foreach (array_keys($categories_cnt) as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="table-responsive">
            <table id="productsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Brand</th>
                        <th>Price</th>
                        <th>Discount</th>
                        <th>Stock</th>
                        <th>Visibility</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p):
                        $img = !empty($p['image']) ? $p['image'] : '1784561108_polot-shirt.webp';
                        $imgPath = file_exists($upload_dir . $img) ? "product image/" . $img : (file_exists("uploads/" . $img) ? "uploads/" . $img : "product image/" . $img);
                    ?>
                        <tr data-name="<?php echo htmlspecialchars(strtolower($p['product_name'] . ' ' . $p['brand'] . ' ' . $p['product_id'])); ?>" data-cat="<?php echo htmlspecialchars($p['category']); ?>">
                            <td><?php echo $p['product_id']; ?></td>
                            <td>
                                <a href="product.php?product_id=<?php echo $p['product_id']; ?>" target="_blank" title="View Product Page">
                                    <img src="<?php echo htmlspecialchars($imgPath); ?>" class="img-thumb" alt="Product">
                                </a>
                            </td>
                            <td>
                                <a href="product.php?product_id=<?php echo $p['product_id']; ?>" target="_blank" style="color: inherit; text-decoration: none;">
                                    <strong><?php echo htmlspecialchars($p['product_name']); ?></strong>
                                </a>
                                <div style="font-size: 11px; color: var(--text-light);"><?php echo htmlspecialchars($p['gender']); ?></div>
                            </td>
                            <td><span class="badge-pill badge-info"><?php echo htmlspecialchars($p['category']); ?></span></td>
                            <td><?php echo htmlspecialchars($p['brand']); ?></td>
                            <td><strong>₹<?php echo number_format($p['price'], 2); ?></strong></td>
                            <td><?php echo !empty($p['discountprice']) ? '₹' . number_format($p['discountprice'], 2) : '-'; ?></td>
                            <td>
                                <?php if ($p['stockqty'] == 0): ?>
                                    <span class="badge-pill badge-danger">0 (Out)</span>
                                <?php elseif ($p['stockqty'] <= 10): ?>
                                    <span class="badge-pill badge-warning"><?php echo $p['stockqty']; ?></span>
                                <?php else: ?>
                                    <span class="badge-pill badge-success"><?php echo $p['stockqty']; ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge-pill <?php echo !empty($p['visible']) ? 'badge-success' : 'badge-danger'; ?>">
                                    <?php echo !empty($p['visible']) ? 'Shown' : 'Hidden'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="product-actions">
                                <a href="product.php?product_id=<?php echo $p['product_id']; ?>" target="_blank" class="product-action-button" style="background: #0284c7;" title="View Product Page" aria-label="View Product Page">
                                    <i class="fa-solid fa-book"></i>
                                </a>
                                <button class="product-action-button" style="background: #64748b;" onclick="openEditProductModal(<?php echo htmlspecialchars(json_encode($p)); ?>)" title="Edit Product" aria-label="Edit Product">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <form method="POST">
                                    <input type="hidden" name="action" value="<?php echo !empty($p['visible']) ? 'hide_product' : 'show_product'; ?>">
                                    <input type="hidden" name="product_id" value="<?php echo $p['product_id']; ?>">
                                    <button type="submit" class="product-action-button" style="background: <?php echo !empty($p['visible']) ? '#f59e0b' : '#16a34a'; ?>;" title="<?php echo !empty($p['visible']) ? 'Hide from shop' : 'Show in shop'; ?>" aria-label="<?php echo !empty($p['visible']) ? 'Hide from shop' : 'Show in shop'; ?>">
                                        <i class="fa-solid <?php echo !empty($p['visible']) ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                                    </button>
                                </form>
                                <form method="POST" onsubmit="return confirm('Delete product #<?php echo $p['product_id']; ?>?');">
                                    <input type="hidden" name="action" value="delete_product">
                                    <input type="hidden" name="product_id" value="<?php echo $p['product_id']; ?>">
                                    <button type="submit" class="product-action-button" style="background: var(--brand-danger);" title="Delete Product" aria-label="Delete Product">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>