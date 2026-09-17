<?php
$seller_orders = $seller_orders ?? [];
$order_statuses = ['Order Confirmed', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
$orders_total = count($seller_orders);
$orders_active = 0;
$orders_revenue = 0;
foreach ($seller_orders as $seller_order) {
    if ($seller_order['status'] !== 'Cancelled') {
        $orders_active++;
        $orders_revenue += (float) $seller_order['seller_total'];
    }
}
?>
<style>
    .order-summary-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; margin-bottom: 20px; }
    .order-summary { padding: 16px; border: 1px solid var(--border-line); border-radius: 10px; background: #f8fafc; }
    .order-summary strong { display: block; margin-top: 6px; font: 700 22px 'Space Grotesk', sans-serif; }
    .order-customer { min-width: 180px; }
    .order-customer small, .order-items small { display: block; color: var(--text-muted); margin-top: 4px; }
    .order-items { min-width: 230px; }
    .order-item-line { padding: 3px 0; }
    .order-status-form { display: flex; gap: 6px; align-items: center; }
    .order-status-form select { padding: 7px; border: 1px solid var(--border-line); border-radius: 6px; background: #fff; }
    .nav-count { margin-left: auto; min-width: 22px; padding: 2px 6px; border-radius: 12px; background: var(--brand-accent); color: #0f172a; text-align: center; font-size: 11px; font-weight: 700; }
    @media (max-width: 760px) { .order-summary-grid { grid-template-columns: 1fr; } }
</style>
<div class="tab-panel" id="tab-orders">
    <div class="order-summary-grid">
        <div class="order-summary"><span class="kpi-title">Seller orders</span><strong><?php echo $orders_total; ?></strong></div>
        <div class="order-summary"><span class="kpi-title">Active orders</span><strong><?php echo $orders_active; ?></strong></div>
        <div class="order-summary"><span class="kpi-title">Product revenue</span><strong>₹<?php echo number_format($orders_revenue, 2); ?></strong></div>
    </div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fa-solid fa-receipt"></i> Connected Orders</h3>
            <div class="controls-row" style="margin: 0;">
                <div class="search-box"><i class="fa-solid fa-magnifying-glass"></i><input id="orderSearch" type="search" placeholder="Search order or customer..." oninput="filterOrders()"></div>
                <select class="select-box" id="orderStatusFilter" onchange="filterOrders()"><option value="">All statuses</option><?php foreach ($order_statuses as $status): ?><option><?php echo htmlspecialchars($status); ?></option><?php endforeach; ?></select>
            </div>
        </div>
        <div class="table-responsive">
            <table id="ordersTable">
                <thead><tr><th>Order</th><th>Customer</th><th>Your products</th><th>Your total</th><th>Status</th><th>Placed</th></tr></thead>
                <tbody>
                    <?php if (empty($seller_orders)): ?><tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:30px;">No orders contain your products yet.</td></tr><?php endif; ?>
                    <?php foreach ($seller_orders as $order): ?>
                        <tr data-order="<?php echo htmlspecialchars(strtolower($order['order_id'] . ' ' . $order['customer_name'])); ?>" data-status="<?php echo htmlspecialchars($order['status']); ?>">
                            <td><strong><?php echo htmlspecialchars($order['order_id']); ?></strong><small><?php echo htmlspecialchars($order['payment_method']); ?></small></td>
                            <td class="order-customer"><strong><?php echo htmlspecialchars($order['customer_name']); ?></strong><small><?php echo htmlspecialchars($order['phone']); ?></small><small><?php echo htmlspecialchars($order['address']); ?></small></td>
                            <td class="order-items"><?php foreach ($order['seller_items'] as $item): ?><div class="order-item-line"><?php echo htmlspecialchars($item['name'] ?? $item['product_name'] ?? 'Product'); ?> <small>x<?php echo max(1, (int) ($item['qty'] ?? $item['quantity'] ?? 1)); ?></small></div><?php endforeach; ?></td>
                            <td><strong>₹<?php echo number_format($order['seller_total'], 2); ?></strong></td>
                            <td><form method="POST" class="order-status-form"><input type="hidden" name="action" value="update_order_status"><input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>"><select name="status" aria-label="Order status"><?php foreach ($order_statuses as $status): ?><option <?php echo $order['status'] === $status ? 'selected' : ''; ?>><?php echo htmlspecialchars($status); ?></option><?php endforeach; ?></select><button class="product-action-button" style="background: var(--brand-primary);" title="Save status" aria-label="Save status"><i class="fa-solid fa-check"></i></button></form></td>
                            <td><?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($order['created_at']))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    function filterOrders() {
        const query = document.getElementById('orderSearch').value.toLowerCase();
        const status = document.getElementById('orderStatusFilter').value;
        document.querySelectorAll('#ordersTable tbody tr[data-order]').forEach(row => {
            row.style.display = row.dataset.order.includes(query) && (!status || row.dataset.status === status) ? '' : 'none';
        });
    }
</script>
