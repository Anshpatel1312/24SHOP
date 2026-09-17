<?php
// TAB 5: ANALYTICS
$parent_dir = dirname(__DIR__);
if (!isset($conn) || !$conn) {
    if (file_exists($parent_dir . '/db.php')) {
        include_once $parent_dir . '/db.php';
    }
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
<div class="tab-panel" id="tab-analytics">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fa-solid fa-chart-bar"></i> Inventory Volume per Category</h3>
            </div>
            <div style="height: 280px; position: relative;">
                <canvas id="categoryBarChart"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fa-solid fa-tags"></i> Top Priced Products</h3>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Brand</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sorted_prods = $products;
                        usort($sorted_prods, function ($a, $b) {
                            return $b['price'] <=> $a['price'];
                        });
                        foreach (array_slice($sorted_prods, 0, 6) as $sp):
                        ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($sp['product_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($sp['brand']); ?></td>
                                <td><strong>₹<?php echo number_format($sp['price'], 2); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
