<?php
/**
 * Discora - PayHere IPN Notification Endpoint
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/constants.php';

$merchant_id = $_POST['merchant_id'] ?? '';
$order_id = $_POST['order_id'] ?? '';
$payhere_amount = $_POST['payhere_amount'] ?? '';
$payhere_currency = $_POST['payhere_currency'] ?? '';
$status_code = $_POST['status_code'] ?? '';
$md5sig = $_POST['md5sig'] ?? '';
$custom_1 = $_POST['custom_1'] ?? '';
$custom_2 = $_POST['custom_2'] ?? '';

$merchant_secret = 'MTA1ODA1NjgzMDI4OTAyNzI1ODEzNjU1NTE0OTE1MTA3NTg2ODEzMw=='; 

$local_md5sig = strtoupper(
    md5(
        $merchant_id . 
        $order_id . 
        $payhere_amount . 
        $payhere_currency . 
        $status_code . 
        strtoupper(md5($merchant_secret)) 
    ) 
);

if (($local_md5sig === $md5sig) && ($status_code == 2) ){
    // Payment was successful
    try {
        $db = Database::getConnection();
        
        // Update payment status
        $updatePayStmt = $db->prepare("UPDATE payments SET payment_status = 'Completed' WHERE order_id = ?");
        $updatePayStmt->execute([$order_id]);
        
        // Update order status if you want to set it as processing or paid
        $updateOrderStmt = $db->prepare("UPDATE orders SET order_status = 'Processing' WHERE order_id = ?");
        $updateOrderStmt->execute([$order_id]);
        
    } catch (Exception $e) {
        error_log("PayHere IPN Error: " . $e->getMessage());
    }
} else {
    // Payment failed or invalid signature
    error_log("PayHere IPN Invalid Signature or Failed Status. Status: " . $status_code);
}
?>
