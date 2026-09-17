<?php
// TAB 4: INVENTORY & RESTOCK
$products = $products ?? [];
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
$inventory_image_dir = $parent_dir . "/product image/";
?>
<div class="tab-panel" id="tab-inventory">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fa-solid fa-warehouse"></i> Inventory Stock Management</h3>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Current Stock</th>
                        <th>Stock Health</th>
                        <th>Add Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p):
                        $inventory_image = !empty($p['image']) ? $p['image'] : '1784561108_polot-shirt.webp';
                        $inventory_image_path = file_exists($inventory_image_dir . $inventory_image) ? 'product image/' . $inventory_image : 'product image/1784561108_polot-shirt.webp';
                    ?>
                        <tr>
                            <td><?php echo $p['product_id']; ?></td>
                            <td>
                                <img src="<?php echo htmlspecialchars($inventory_image_path); ?>" alt="<?php echo htmlspecialchars($p['product_name']); ?>" style="width: 58px; height: 58px; object-fit: contain; border: 1px solid var(--border-line); border-radius: 6px; background: #f8fafc;">
                            </td>
                            <td><strong><?php echo htmlspecialchars($p['product_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($p['category']); ?></td>
                            <td><strong style="font-size: 16px;"><?php echo $p['stockqty']; ?></strong> units</td>
                            <td>
                                <?php if ($p['stockqty'] == 0): ?>
                                    <span class="badge-pill badge-danger">Out of Stock</span>
                                <?php elseif ($p['stockqty'] <= 10): ?>
                                    <span class="badge-pill badge-warning">Low Stock Warning</span>
                                <?php else: ?>
                                    <span class="badge-pill badge-success">Sufficient</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" style="display: flex; gap: 6px; align-items: center;">
                                    <input type="hidden" name="action" value="restock">
                                    <input type="hidden" name="product_id" value="<?php echo $p['product_id']; ?>">
                                    <input type="number" name="add_qty" placeholder="Qty" min="1" required style="width: 70px; padding: 6px; border: 1px solid var(--border-line); border-radius: 6px;">
                                    <button type="submit" class="btn-primary" style="padding: 6px 12px; font-size: 12px;">+ Restock</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
