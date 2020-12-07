<?php include 'view/header.php'; ?>
<?php include 'view/sidebar_admin.php'; ?>
<style>
<?php include 'main.css'; ?>
</style>
<main>
    <p>
        <a href="?action=orders_shipped">View Shipped Orders</a><br><br>
        <a href="?action=orders_unshipped">View Orders Not Shipped</a><br><br>
        <a href="?action=list_orders">View All Orders</a>
    </p>
    <br>
     <h1>Shipping Confirmation</h1>
     <br>
         <?php foreach($shipped_order as $order) : ?>
     
     <h2 style="color: #097054;">Order Number</h2>
     
     <p><?php echo $order['orderID']; ?></p>
         <br>
         
     <h2 style="color: #097054;">Customer Name</h2>
     
     <p><?php echo $order['lastName'];?>, <?php echo $order['firstName']; ?></p>
         <br>
         
         
     <h2 style="color: #097054;">Shipping Address</h2>
         <p><?php echo $order['line1']; ?> <?php echo $order['city'];?>, <?php echo $order['state'];?> <?php echo $order['zipCode'];?></p>
         <?php endforeach; ?>
         <br>
         <?php foreach($product_info as $product) : ?>
         
         <h2 style="color: #097054;"> Items Shipped</h2>
         <table>
             <tr>
             <th>Item Name</th>
             <th>Quantity</th>
             </tr>
             
             <tr>
                 <td><?php echo $product['productName']; ?></td>
                 <td><?php echo $product['quantity']; ?></td>
             </tr>
         </table>
         
         <?php endforeach; ?>
</main>
<?php include 'view/footer.php'; ?>