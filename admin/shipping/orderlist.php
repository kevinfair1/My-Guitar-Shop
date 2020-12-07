<?php include 'view/header.php'; ?>
<?php include 'view/sidebar_admin.php'; ?>
<style>
<?php include 'main.css'; ?>
</style>
<main>
    <h1>All Orders</h1>
    <br>
    <p><a href="?action=orders_shipped">View Shipped Orders</a><br><br>
        <a href="?action=orders_unshipped">View Orders Not Shipped</a></p>

    <br>
    <section>       
        <table>
            <tr>
                <th>Order ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Order Date</th>
                <th>Ship Date</th>
            </tr>
            <?php foreach ($orders as $order) :
                
                
                ?>
            
            <tr>
                <td><?php echo $order['orderID']; ?></td>
                <td><?php echo $order['firstName']; ?></td>
                <td><?php echo $order['lastName']; ?></td>
                <td><?php echo $order['orderDate']; ?></td>
                <td><?php echo $order['shipDate']; ?></td>                
                
            </tr>
            <?php endforeach; ?>
        </table>                
              
    </section>
</main>
<?php include 'view/footer.php'; ?>

