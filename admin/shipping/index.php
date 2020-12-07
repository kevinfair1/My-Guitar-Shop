<?php
require_once('../../util/main.php');
require_once('model/customer_db.php');
require_once('model/address_db.php');
require_once('model/order_db.php');
require_once('model/product_db.php');
require('../../model/database.php');
require('../../model/shipping_db.php');


$action = filter_input(INPUT_POST, 'action');

if ($action == NULL) {
    $action = filter_input(INPUT_GET, 'action');
    if ($action == NULL) {
        $action = 'list_orders';
    }
}

if ($action == 'list_orders') {   
        
    $orders = get_orders();
    
    include('orderlist.php');
}

else if ($action == 'orders_shipped') {  
        
    $orders = get_shipped_orders();
    
    include('orders_shipped.php');
}

else if ($action == 'orders_unshipped') {
    
    $orders = get_unshipped_orders();
    
    include('orders_unshipped.php');
}

else if ($action == 'ship_order') {
    $order_id = filter_input(INPUT_POST, 'orderID', 
                FILTER_VALIDATE_INT);    
        ship_order($order_id);
        $shipped_order = get_shipped_info($order_id);
        $product_info = get_product_info($order_id);
        
        include('ship_order.php');        
    
}


    ?>