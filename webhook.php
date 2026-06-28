<?php
/**
 * AdityaLoot Payment Gateway - Webhook Callback Handler
 * URL to configure in dashboard: https://kamalseller.com/webhook.php
 */

// 1. Get the raw POST data sent by the AdityaLoot server
$json_data = file_get_contents('php://input');

// 2. Decode the JSON data into a PHP array
$data = json_decode($json_data, true);

// 3. Verify that the payload contains an order_id
if ($data && isset($data['order_id'])) {
    
    // Extract the transaction details
    $status = $data['status']; // SUCCESS or FAILURE
    $order_id = $data['order_id'];
    $amount = $data['amount'];
    $paymentApp = isset($data['paymentApp']) ? $data['paymentApp'] : 'Unknown';
    $utr = isset($data['UTR']) ? $data['UTR'] : 'N/A';
    
    // ----------------------------------------------------------------------
    // TODO: Add your custom logic here!
    // For example: Update your database, send an email, or call another API.
    // ----------------------------------------------------------------------

    if ($status === 'SUCCESS') {
        // Payment was successful!
        // Log the successful transaction to a text file
        $log_entry = date('Y-m-d H:i:s') . " [SUCCESS] Order: $order_id | Amount: ₹$amount | UTR: $utr | App: $paymentApp\n";
        file_put_contents('transactions.log', $log_entry, FILE_APPEND);
        
    } else {
        // Payment failed or is pending
        $log_entry = date('Y-m-d H:i:s') . " [FAILED] Order: $order_id | Amount: ₹$amount\n";
        file_put_contents('transactions.log', $log_entry, FILE_APPEND);
    }

    // 4. Return a 200 OK response to the payment gateway so they know it was received
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode(["status" => true, "message" => "Webhook received successfully"]);
    
} else {
    // Invalid data received
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["status" => false, "error" => "Invalid Payload"]);
}
?>
