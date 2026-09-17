<?php
// TAB 3: SELLERS & SHOPS
$parent_dir = dirname(__DIR__);
if (!isset($conn) || !$conn) {
    if (file_exists($parent_dir . '/db.php')) {
        include_once $parent_dir . '/db.php';
    }
}
if (!isset($sellers) && isset($conn) && $conn) {
    $sellers = [];
    $seller_res = mysqli_query($conn, "SELECT s.*, sh.shop_id, sh.shop_name, sh.owner_name, sh.gst_number, sh.shop_address, sh.shop_city, sh.shop_state, sh.shop_pincode, sh.registration_certificate FROM seller_details s LEFT JOIN shop_details sh ON s.seller_id = sh.seller_id ORDER BY s.seller_id DESC");
    if ($seller_res) {
        while ($r = mysqli_fetch_assoc($seller_res)) {
            $sellers[] = $r;
        }
    }
}
?>
<div class="tab-panel" id="tab-sellers">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fa-solid fa-store"></i> Registered Sellers & Shops</h3>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Seller ID</th>
                        <th>Seller Name</th>
                        <th>Shop Name</th>
                        <th>Contact</th>
                        <th>GST Number</th>
                        <th>Location</th>
                        <th>Certificate</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($sellers)): ?>
                        <?php foreach ($sellers as $s): ?>
                            <tr>
                                <td>#<?php echo $s['seller_id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($s['sellername']); ?></strong>
                                    <div style="font-size: 11px; color: var(--text-light);"><?php echo htmlspecialchars($s['email']); ?></div>
                                </td>
                                <td><span class="badge-pill badge-info"><?php echo htmlspecialchars(!empty($s['shop_name']) ? $s['shop_name'] : 'N/A'); ?></span></td>
                                <td><?php echo htmlspecialchars($s['mobile']); ?></td>
                                <td><code><?php echo htmlspecialchars(!empty($s['gst_number']) ? $s['gst_number'] : 'N/A'); ?></code></td>
                                <td><?php echo htmlspecialchars($s['city'] . ', ' . $s['state']); ?></td>
                                <td>
                                    <?php if (!empty($s['registration_certificate'])): ?>
                                        <a href="uploads/<?php echo htmlspecialchars($s['registration_certificate']); ?>" target="_blank" style="color: var(--brand-primary); text-decoration: underline;">
                                            <i class="fa-solid fa-file-pdf"></i> View Doc
                                        </a>
                                    <?php else: ?>
                                        <span style="color: var(--text-light);">None</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" onsubmit="return confirm('Remove seller #<?php echo $s['seller_id']; ?>?');">
                                        <input type="hidden" name="action" value="delete_seller">
                                        <input type="hidden" name="seller_id" value="<?php echo $s['seller_id']; ?>">
                                        <button type="submit" class="btn-primary" style="padding: 4px 8px; font-size: 12px; background: var(--brand-danger);">
                                            <i class="fa-solid fa-user-xmark"></i> Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colSpan="8" style="text-align: center; color: var(--text-muted); padding: 24px;">No registered sellers found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
