<?php

function get_orders() {
    global $db;
    $query = 'SELECT * FROM orders JOIN customers ON orders.customerID = customers.customerID';

    $statement = $db->prepare($query);
    $statement->execute();
    return $statement;   
}

function get_shipped_orders() {
    global $db;
    $query = 'SELECT * FROM orders JOIN customers ON orders.customerID = customers.customerID WHERE shipDate IS NOT NULL';

    $statement = $db->prepare($query);
    $statement->execute();
    return $statement;   
}

function get_unshipped_orders() {
    global $db;
    $query = 'SELECT * FROM orders JOIN customers ON orders.customerID = customers.customerID WHERE shipDate IS NULL';

    $statement = $db->prepare($query);
    $statement->execute();
    return $statement;   
}

function ship_order($order_id) {
    global $db;
    $ship_date = date("Y-m-d H:i:s");
    $query = '
         UPDATE orders
         SET shipDate = :ship_date
         WHERE orderID = :order_id';
    $statement = $db->prepare($query);
    $statement->bindValue(':ship_date', $ship_date);
    $statement->bindValue(':order_id', $order_id);
    $statement->execute();
    $statement->closeCursor();
}

function get_shipped_info($order_id) {
    global $db;    
    $query = '
         SELECT * FROM orders JOIN customers ON orders.customerID = customers.customerID
         JOIN addresses ON customers.shipAddressID = addresses.addressID
         WHERE orderID = :order_id';
    $statement = $db->prepare($query);   
    $statement->bindValue(':order_id', $order_id);
    $statement->execute();
    $result = $statement->fetchAll();
    $statement->closeCursor();
    return $result;
}

function get_product_info ($order_id) {
    global $db;    
    $query = '
         SELECT * FROM orderitems 
         JOIN products ON orderitems.productID = products.productID 
         WHERE orderitems.orderID = :order_id';
    $statement = $db->prepare($query);   
    $statement->bindValue(':order_id', $order_id);
    $statement->execute();
    $result = $statement->fetchAll();
    $statement->closeCursor();
    return $result;
    
}
?>
