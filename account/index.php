<?php
require_once('../util/main.php');
require_once('model/customer_db.php');
require_once('model/address_db.php');
require_once('model/order_db.php');
require_once('model/product_db.php');
require_once('model/fields.php');
require_once('model/validate.php');
require_once('./class.PHPMailer.php');

$action = filter_input(INPUT_POST, 'action');
if ($action == NULL) {
    $action = filter_input(INPUT_GET, 'action');
    if ($action == NULL) {        
        $action = 'view_login';
        if (isset($_SESSION['user'])) {
            $action = 'view_account';
        }
    }
}

// Set up all possible fields to validate
$validate = new Validate();
$fields = $validate->getFields();

// for the Registration page and other pages
$fields->addField('email', 'Must be valid email.');
$fields->addField('password_1');
$fields->addField('password_2');
$fields->addField('first_name');
$fields->addField('last_name');
$fields->addField('ship_line1');
$fields->addField('ship_line2');
$fields->addField('ship_city');
$fields->addField('ship_state');
$fields->addField('ship_zip');
$fields->addField('ship_phone');
$fields->addField('bill_line1');
$fields->addField('bill_line2');
$fields->addField('bill_city');
$fields->addField('bill_state');
$fields->addField('bill_zip');
$fields->addField('bill_phone');

// for the Login page
$fields->addField('password');

// for the Edit Address page
$fields->addField('line1');
$fields->addField('line2');
$fields->addField('city');
$fields->addField('state');
$fields->addField('zip');
$fields->addField('phone');

switch ($action) {
    case 'view_register':
        // Clear user data
        $email = '';
        $first_name = '';
        $last_name = '';
        $ship_line1 = '';
        $ship_line2 = '';
        $ship_city = '';
        $ship_state = '';
        $ship_zip = '';
        $ship_phone = '';
        $use_shipping = '';
        $bill_line1 = '';
        $bill_line2 = '';
        $bill_city = '';
        $bill_state = '';
        $bill_zip = '';
        $bill_phone = '';
        
        include 'account_register.php';
        break;
    case 'register':
        // Store user data in local variables
        $email = filter_input(INPUT_POST, 'email');
        $password_1 = filter_input(INPUT_POST, 'password_1');
        $password_2 = filter_input(INPUT_POST, 'password_2');
        $first_name = filter_input(INPUT_POST, 'first_name');
        $last_name = filter_input(INPUT_POST, 'last_name');
        $ship_line1 = filter_input(INPUT_POST, 'ship_line1');
        $ship_line2 = filter_input(INPUT_POST, 'ship_line2');
        $ship_city = filter_input(INPUT_POST, 'ship_city');
        $ship_state = filter_input(INPUT_POST, 'ship_state');
        $ship_zip = filter_input(INPUT_POST, 'ship_zip');
        $ship_phone = filter_input(INPUT_POST, 'ship_phone');
        $use_shipping = isset($_POST['use_shipping']);
        $bill_line1 = filter_input(INPUT_POST, 'bill_line1');
        $bill_line2 = filter_input(INPUT_POST, 'bill_line2');
        $bill_city = filter_input(INPUT_POST, 'bill_city');
        $bill_state = filter_input(INPUT_POST, 'bill_state');
        $bill_zip = filter_input(INPUT_POST, 'bill_zip');
        $bill_phone = filter_input(INPUT_POST, 'bill_phone');
        
        // Validate user data       
        $validate->email('email', $email);
        $validate->text('password_1', $password_1, true, 6, 30);
        $validate->text('password_2', $password_2, true, 6, 30);        
        $validate->text('first_name', $first_name);
        $validate->text('last_name', $last_name);
        $validate->text('ship_line1', $ship_line1);        
        $validate->text('ship_line2', $ship_line2, false);        
        $validate->text('ship_city', $ship_city);        
        $validate->text('ship_state', $ship_state);        
        $validate->text('ship_zip', $ship_zip);        
        $validate->text('ship_phone', $ship_phone, false);        
        if (!$use_shipping) {
            $validate->text('bill_line1', $bill_line1);        
            $validate->text('bill_line2', $bill_line2, false);        
            $validate->text('bill_city', $bill_city);        
            $validate->text('bill_state', $bill_state);        
            $validate->text('bill_zip', $bill_zip);        
            $validate->text('bill_phone', $bill_phone, false);
        }
        
        // If necessary, clear billing address data
        if ($use_shipping) {        
            $bill_line1 = '';
            $bill_line2 = '';
            $bill_city = '';
            $bill_state = '';
            $bill_zip = '';
            $bill_phone = '';            
        }

        // If validation errors, redisplay Register page and exit controller
        if ($fields->hasErrors()) {
            include 'account/account_register.php';
            break;
        }

        // If passwords don't match, redisplay Register page and exit controller
        if ($password_1 !== $password_2) {
            $password_message = 'Passwords do not match.';
            include 'account/account_register.php';
            break;
        }

        // Validate the data for the customer
        if (is_valid_customer_email($email)) {
            display_error('The e-mail address ' . $email . ' is already in use.');
        }
        
        // Add the customer data to the database
        $customer_id = add_customer($email, $first_name,
                                    $last_name, $password_1);

        // Add the shipping address
        $shipping_id = add_address($customer_id, $ship_line1, $ship_line2,
                                   $ship_city, $ship_state, $ship_zip,
                                   $ship_phone);
        customer_change_shipping_id($customer_id, $shipping_id);

        // Add the billing address
        if ($use_shipping) {
            $billing_id = add_address($customer_id, $ship_line1, $ship_line2,
                                       $ship_city, $ship_state, $ship_zip,
                                       $ship_phone);
        } else {
            $billing_id = add_address($customer_id, $bill_line1, $bill_line2,
                                   $bill_city, $bill_state, $bill_zip,
                                   $bill_phone);
        }
        customer_change_billing_id($customer_id, $billing_id);

        // Store user data in session
        $_SESSION['user'] = get_customer($customer_id);
        
        // Redirect to the Checkout application if necessary
        if (isset($_SESSION['checkout'])) {
            unset($_SESSION['checkout']);
            redirect('../checkout');
        } else {
            redirect('.');
        }        
        break;
    case 'view_login':
        // Clear login data
        $email = '';
        $password = '';
        $password_message = '';
        
        include 'account_login_register.php';
        break;
    case 'login':
        $email = filter_input(INPUT_POST, 'email');
        $password = filter_input(INPUT_POST, 'password');
        
        // Validate user data
        $validate->email('email', $email);
        $validate->text('password', $password, true, 6, 30);        

        // If validation errors, redisplay Login page and exit controller
        if ($fields->hasErrors()) {
            include 'account/account_login_register.php';
            break;
        }
        
        // Check email and password in database
        if (is_valid_customer_login($email, $password)) {
            $_SESSION['user'] = get_customer_by_email($email);
        } else {
            $password_message = 'Login failed. Invalid email or password.';
            include 'account/account_login_register.php';
            break;
        }

        // If necessary, redirect to the Checkout app
        // Redirect to the Checkout application
        if (isset($_SESSION['checkout'])) {
            unset($_SESSION['checkout']);
            redirect('../checkout');
        } else {
            redirect('.');
        }        
        break;
    case 'view_account':
        $customer_name = $_SESSION['user']['firstName'] . ' ' .
                         $_SESSION['user']['lastName'];
        $email = $_SESSION['user']['emailAddress'];        

        $shipping_address = get_address($_SESSION['user']['shipAddressID']);
        $billing_address = get_address($_SESSION['user']['billingAddressID']);        

        $orders = get_orders_by_customer_id($_SESSION['user']['customerID']);
        if (!isset($orders)) {
            $orders = array();
        }        
        include 'account_view.php';
        break;
    case 'view_order':
        $order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
        $order = get_order($order_id);
        $customer_name = $_SESSION['user']['firstName'] . ' ' .
                         $_SESSION['user']['lastName'];
        $email = $_SESSION['user']['emailAddress'];
        $order_date = strtotime($order['orderDate']);
        $order_date = date('M j, Y', $order_date);
        $order_items = get_order_items($order_id);

        $shipping_address = get_address($order['shipAddressID']);
        $billing_address = get_address($order['billingAddressID']);
        
 //----------------------------------------------------------------------------------------------------------------
        
               set_time_limit(0);


$messageHTML = '<html>
<head>
<title><?echo $product?></title>
</head>
<body>
<p>Hi</p><br> 
We are have our Grand Opening <br> 
You are invited <br> 
<a href="http://www.indeed.com/viewjob?jk=c86f8129249f8304&q=c%23&l=Augusta,GA&tk=18899c2de06ag7tq&from=ja&alid=775532c681841a1c&utm_source=jobseeker_emails&utm_medium=email&utm_campaign=job_alerts">Important stuff</a><br><br> 
</body>
</html>';

$message =  ' This is information that will not be HTML friendly for Emails that do not support HTML';
 

$email = new PHPMailer();

$email->IsSMTP();

// The XXXXXXXX in the following will need to be adjusted as with Host if not using gmail
// $email->IsSendmail();
//$email->Mailer = "smtp";
$email->Host       = "smtp.gmail.com";   //Will need to be modified
$email->SMTPAuth   = true;  
//$email->Port       = 465;                // The PORTS will vary
$email->Port       = 587;
$email->SMTPDebug  = 1;                     // enables SMTP debug information (for testing)  
$email->SMTPSecure = 'tls';            // ssl is most recent but may need tls 
//$email->SMTPSecure = 'ssl';    
$email->Username   = "guitarshopATC@gmail.com"; // SMTP account username Will need to be modified
$email->Password   = "augustatech";        // SMTP account password Will need to be modified
$email->SetFrom('guitarshopATC@gmail.com', 'Kevin');  //Will need to be modified – identifies email of sender
$email->MsgHTML($message);
$email->SingleTo  = true;	// true allows that only one person will receive an email per array group
$email->From      = 'guitarshopATC@gmail.com'; //Will need to be modified – identifies email of sender
$email->FromName  = 'My Guitar Shop'; //Will need to be modified – identifies email of sender
$email->Subject   = 'Purchases from Guitar Shop PHP Class'; // appears in subject of email
$email->Body      = 
'<h1> Thank you for ordering from My Guitar Shop!</h1>'
. '<b>You have completed a successful order!</b></br>'
.'Name: '.$order_id.'</br>'
.'Surname: '.$order_date.'</br>'
;

// the body will interpret HTML - $messageHTML identified above
$email->AltBody = $message;            // the AltBody will not interpret HTML - $message identified above
$destination_email_address = $_SESSION['user']['emailAddress']; // Destination address
$destination_user_name = $_SESSION['user']['firstName'] . ' ' .
                         $_SESSION['user']['lastName'];; // Destination name

// $email->AddAddress( 'xxxx@xxxx.xxx' );

//$file_to_attach = 'images.pdf';  // Used if you want attachments to email - This is the name of your attachment file.
                                 // File has to be located in same folder as index.php
//$email->AddAttachment( $file_to_attach ); // Used if you want attachments to email

       //$email->AddAddress($this->$destination_email_address, $destination_user_name); 
       $email->AddAddress($destination_email_address, $destination_user_name);
	// AddAddress method identifies destination and sends email	
         if(!$email->Send()) {
         echo "Mailer Error: " . $email->ErrorInfo;
          } else {
           echo "Message sent!";
          }
  
//-------------------------------------------------------------------------------------------------------------------------------------------------
        
        include 'account_view_order.php';
        break;
    case 'view_account_edit':
        $email = $_SESSION['user']['emailAddress'];
        $first_name = $_SESSION['user']['firstName'];
        $last_name = $_SESSION['user']['lastName'];
        $shipping_id = $_SESSION['user']['shipAddressID'];
        $billing_id = $_SESSION['user']['billingAddressID'];

        $password_message = '';        

        include 'account_edit.php';
        break;
    case 'update_account':
        // Get the customer data
        $customer_id = $_SESSION['user']['customerID'];
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $first_name = filter_input(INPUT_POST, 'first_name');
        $last_name = filter_input(INPUT_POST, 'last_name');
        $password_1 = filter_input(INPUT_POST, 'password_1');
        $password_2 = filter_input(INPUT_POST, 'password_2');
        $password_message = '';

        // Get the old data for the customer
        $old_customer = get_customer($customer_id);

        // Validate user data
        $validate->email('email', $email);
        $validate->text('password_1', $password_1, false, 6, 30);
        $validate->text('password_2', $password_2, false, 6, 30);        
        $validate->text('first_name', $first_name);
        $validate->text('last_name', $last_name);        
        
        // Check email change and display message if necessary
        if ($email != $old_customer['emailAddress']) {
            display_error('You can\'t change the email address for an account.');
        }

        // If validation errors, redisplay Login page and exit controller
        if ($fields->hasErrors()) {
            include 'account/account_edit.php';
            break;
        }
        
        // Only validate the passwords if they are NOT empty
        if (!empty($password_1) && !empty($password_2)) {            
            if ($password_1 !== $password_2) {
                $password_message = 'Passwords do not match.';
                include 'account/account_edit.php';
                break;
            }
        }

        // Update the customer data
        update_customer($customer_id, $email, $first_name, $last_name,
            $password_1, $password_2);

        // Set the new customer data in the session
        $_SESSION['user'] = get_customer($customer_id);

        redirect('.');
        break;
    case 'view_address_edit':
        // Set up variables for address type
        $address_type = filter_input(INPUT_POST, 'address_type');
        if ($address_type == 'billing') {
            $address_id = $_SESSION['user']['billingAddressID'];
            $heading = 'Update Billing Address';
        } else {
            $address_id = $_SESSION['user']['shipAddressID'];
            $heading = 'Update Shipping Address';
        }

        // Get the data for the address
        $address = get_address($address_id);
        $line1 = $address['line1'];
        $line2 = $address['line2'];
        $city = $address['city'];
        $state = $address['state'];
        $zip = $address['zipCode'];
        $phone = $address['phone'];

        // Display the data on the page
        include 'address_edit.php';
        break;
    case 'update_address':
        $customer_id = $_SESSION['user']['customerID'];
    
        // Set up variables for address type
        $address_type = filter_input(INPUT_POST, 'address_type');
        if ($address_type == 'billing') {
            $address_id = $_SESSION['user']['billingAddressID'];
            $heading = 'Update Billing Address';
        } else {
            $address_id = $_SESSION['user']['shipAddressID'];
            $heading = 'Update Shipping Address';
        }

        // Get the post data
        $line1 = filter_input(INPUT_POST, 'line1');
        $line2 = filter_input(INPUT_POST, 'line2');
        $city = filter_input(INPUT_POST, 'city');
        $state = filter_input(INPUT_POST, 'state');
        $zip = filter_input(INPUT_POST, 'zip');
        $phone = filter_input(INPUT_POST, 'phone');

        // Validate the data
        $validate->text('line1', $line1);        
        $validate->text('line2', $line2, false);        
        $validate->text('city', $city);        
        $validate->text('state', $state);        
        $validate->text('zip', $zip);        
        $validate->text('phone', $phone, false);        

        // If validation errors, redisplay Login page and exit controller
        if ($fields->hasErrors()) {
            include 'account/address_edit.php';
            break;
        }
        
        // If the old address has orders, disable it
        // Otherwise, delete it
        disable_or_delete_address($address_id);

        // Add the new address
        $address_id = add_address($customer_id, $line1, $line2, $city,
                                   $state, $zip, $phone);

        // Relate the address to the customer account
        if ($address_type == 'billing') {
            customer_change_billing_id($customer_id, $address_id);
        } else {
            customer_change_shipping_id($customer_id, $address_id);
        }

        // Set the user data in the session
        $_SESSION['user'] = get_customer($customer_id);

        redirect('.');
        break;
    case 'logout':
        unset($_SESSION['user']);
        redirect('..');
        break;
    default:
        display_error("Unknown account action: " . $action);
        break;
}
?>