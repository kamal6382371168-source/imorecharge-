<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Your secure API Token - Kept safe on the server!
$user_token = '6c276aafc0ae980450dd3ef93932ecb0';

if (!isset($_POST['action'])) {
    echo json_encode(["status" => false, "message" => "No action specified"]);
    exit;
}

$action = $_POST['action'];

if ($action === 'create-order') {
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://wp.adityaloot.com/api/create-order',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => http_build_query(array(
        'customer_mobile' => $_POST['customer_mobile'],
        'user_token' => $user_token,
        'amount' => $_POST['amount'],
        'order_id' => $_POST['order_id'],
        'redirect_url' => $_POST['redirect_url'],
        'remark1' => isset($_POST['remark1']) ? $_POST['remark1'] : '',
        'remark2' => isset($_POST['remark2']) ? $_POST['remark2'] : '',
        'expiry_seconds' => '7200'
      )),
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/x-www-form-urlencoded'
      ),
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    
    if ($err) {
        echo json_encode(["status" => false, "message" => "cURL Error: " . $err]);
    } else {
        echo $response;
    }
    exit;
}

if ($action === 'check-order-status') {
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://wp.adityaloot.com/api/check-order-status',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => http_build_query(array(
        'user_token' => $user_token,
        'order_id' => $_POST['order_id']
      )),
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/x-www-form-urlencoded'
      ),
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    
    if ($err) {
        echo json_encode(["status" => false, "message" => "cURL Error: " . $err]);
    } else {
        echo $response;
    }
    exit;
}

echo json_encode(["status" => false, "message" => "Invalid action"]);
?>
