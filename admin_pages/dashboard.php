<?php
// TAB 1: OVERVIEW DASHBOARD
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
?>
<div class="tab-panel active" id="tab-dashboard">
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">

        <!-- Recent Products Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fa-solid fa-clock-rotate-left"></i> Recently Added Products</h3>
                <button class="btn-primary" style="padding: 6px 12px; font-size: 12px;" onclick="switchTabById('products')">View All</button>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($products)): ?>
                            <?php foreach (array_slice($products, 0, 5) as $p):
                                $img = !empty($p['image']) ? $p['image'] : '1784561108_polot-shirt.webp';
                                $imgPath = file_exists($upload_dir . $img) ? "product image/" . $img : (file_exists("uploads/" . $img) ? "uploads/" . $img : "product image/" . $img);
                            ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <a href="product.php?product_id=<?php echo $p['product_id']; ?>" target="_blank" title="View Product Details">
                                                <img src="<?php echo htmlspecialchars($imgPath); ?>" class="img-thumb" alt="Product">
                                            </a>
                                            <div>
                                                <a href="product.php?product_id=<?php echo $p['product_id']; ?>" target="_blank" style="color: inherit; text-decoration: none;">
                                                    <strong><?php echo htmlspecialchars($p['product_name']); ?></strong>
                                                </a>
                                                <!-- <div style="font-size: 11px; color: var(--text-light);">ID:<?php echo $p['product_id']; ?></div> -->
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($p['category']); ?></td>
                                    <td><strong>₹<?php echo number_format($p['price'], 2); ?></strong></td>
                                    <td>
                                        <?php if ($p['stockqty'] == 0): ?>
                                            <span class="badge-pill badge-danger">Out of Stock</span>
                                        <?php elseif ($p['stockqty'] <= 10): ?>
                                            <span class="badge-pill badge-warning"><?php echo $p['stockqty']; ?> left</span>
                                        <?php else: ?>
                                            <span class="badge-pill badge-success"><?php echo $p['stockqty']; ?> units</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colSpan="4" style="text-align: center; color: var(--text-muted);">No products found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Category Breakdown Chart -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fa-solid fa-chart-pie"></i> Category Share</h3>
            </div>
            <div style="height: 240px; position: relative;">
                <canvas id="categoryPieChart"></canvas>
            </div>
        </div>

    </div>
</div>