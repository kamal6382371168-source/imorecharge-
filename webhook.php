<?php
/**
 * AdityaLoot Payment Gateway - Webhook Callback Handler
 * URL to configure in dashboard: https://kamalseller.com/webhook.php
 */

$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if ($data && isset($data['order_id'])) {
    
    $status = $data['status']; // SUCCESS or FAILURE
    $order_id = $data['order_id'];
    $amount = $data['amount'];
    $paymentApp = isset($data['paymentApp']) ? $data['paymentApp'] : 'Unknown';
    $utr = isset($data['UTR']) ? $data['UTR'] : 'N/A';
    
    // Read the securely saved order details
    $order_file = "orders/{$order_id}.json";
    $order_data = [];
    if (file_exists($order_file)) {
        $order_data = json_decode(file_get_contents($order_file), true);
    }

    if ($status === 'SUCCESS') {
        $log_entry = date('Y-m-d H:i:s') . " [SUCCESS] Order: $order_id | Amount: ₹$amount | UTR: $utr | App: $paymentApp\n";
        file_put_contents('transactions.log', $log_entry, FILE_APPEND);
        
        if (!empty($order_data)) {
            // Forward success to Google Apps Script (Backend to Backend)
            $apps_script_url = 'https://script.google.com/macros/s/AKfycbxrVQaUAj8xvphQgjEjGXPh7wfG0X1cLUrKGkLbYlPX_5MEY_piLlqoomZ_qodJc6-i/exec';
            
            $post_data = json_encode([
                'order_id'        => $order_id,
                'utr_number'      => $utr,
                'customer_id'     => $order_data['imo'],
                'game_app_id'     => 'IMO',
                'package_details' => $order_data['pack'] . ' — ₹' . $amount
            ]);
            
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $apps_script_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $post_data,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));
            curl_exec($curl);
            curl_close($curl);
            
            // Mark order as paid
            $order_data['status'] = 'PAID';
            $order_data['utr'] = $utr;
            file_put_contents($order_file, json_encode($order_data));
        }
        
    } else {
        $log_entry = date('Y-m-d H:i:s') . " [FAILED] Order: $order_id | Amount: ₹$amount\n";
        file_put_contents('transactions.log', $log_entry, FILE_APPEND);
    }

    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode(["status" => true, "message" => "Webhook processed securely"]);
    
} else {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["status" => false, "error" => "Invalid Payload"]);
}
?>
