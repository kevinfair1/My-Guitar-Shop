<?php include 'view/header.php'; ?>
<?php include 'view/sidebar_admin.php'; ?>
<style>
    table {
    border: 1px solid #001963;
    border-collapse: collapse;
    }
    td, th {
        border: 1px dashed #001963;
        padding: .2em .5em .2em .5em;
        vertical-align: top;
        text-align: left;
    }
</style>
<main>
    <?php if($action == 'view_new_orders'): ?>
    <h1>Outstanding Orders</h1>
    <a href="index.php?action=view_old_orders">View Shipped Orders</a>
    <?php endif; ?>
    <?php if($action == 'view_old_orders'): ?>
    <h1>Shipped Orders</h1>
    <a href="index.php?action=view_new_orders">View Outstanding Orders</a>
    <?php endif; ?>
    <?php if (count($orders) > 0 ) : ?>
    <table>
        <tr>
            <th>Order Number</th>
            <th>Customer</th>
            <th>Order Date</th>
            <th>&nbsp;</th>
        </tr>
        <?php foreach($orders as $order): 
            $order_id = $order['orderID'];
            $order_date = strtotime($order['orderDate']);
            $order_date = date('M j, Y', $order_date);
            $url = $app_path . 'admin/orders' . '?action=view_order&amp;order_id=' . $order_id;
        ?>
        <tr>
            <td><?php echo $order_id; ?></td>
            <td><?php echo htmlspecialchars($order['firstName']) . ' ' . htmlspecialchars($order['lastName']); ?></td>
            <td><?php echo $order_date; ?></td>
            <td><a href='<?php echo $url; ?>'>View</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php else: ?>
        <p>There are no orders.</p>
    <?php endif; ?>
</main>
<?php include 'view/footer.php'; ?>
