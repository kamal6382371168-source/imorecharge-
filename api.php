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

// Define packages securely on backend to prevent user tampering (price manipulation)
$packs = [
  'p10'    => ['label' => '10 Diamonds', 'price' => 30, 'd' => 10],
  'p50'    => ['label' => '50 Diamonds', 'price' => 90, 'd' => 50],
  'p100'   => ['label' => '100 Diamonds', 'price' => 175, 'd' => 100],
  'p200'   => ['label' => '200 Diamonds', 'price' => 350, 'd' => 200],
  'p500'   => ['label' => '500 Diamonds', 'price' => 875, 'd' => 500],
  'p1000'  => ['label' => '1000 Diamonds', 'price' => 1750, 'd' => 1000],
  'p2000'  => ['label' => '2000 Diamonds', 'price' => 3499, 'd' => 2000],
  'p5000'  => ['label' => '5000 Diamonds', 'price' => 8700, 'd' => 5000],
  'p10000' => ['label' => '10000 Diamonds', 'price' => 17399, 'd' => 10000],
];

if ($action === 'create-order') {
    $pack_id = $_POST['pack_id'];
    $qty = intval($_POST['qty']);
    $imo = $_POST['imo'];
    $customer_mobile = $_POST['customer_mobile'];
    $order_id = $_POST['order_id'];
    $redirect_url = $_POST['redirect_url'];
    
    if (!isset($packs[$pack_id]) || $qty < 1 || $qty > 10) {
        echo json_encode(["status" => false, "message" => "Invalid package or quantity!"]);
        exit;
    }
    
    // Calculate amount securely on the backend
    $amount = $packs[$pack_id]['price'] * $qty;
    $pack_label = $packs[$pack_id]['label'] . ($qty > 1 ? " × $qty" : "");
    
    // Save order details to a local JSON file so webhook can read it later
    if (!is_dir('orders')) {
        mkdir('orders', 0777, true);
    }
    $order_data = [
        'order_id' => $order_id,
        'imo' => $imo,
        'mobile' => $customer_mobile,
        'pack' => $pack_label,
        'amount' => $amount,
        'status' => 'PENDING',
        'time' => time()
    ];
    file_put_contents("orders/{$order_id}.json", json_encode($order_data));

    // Create payment order request
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
        'customer_mobile' => $customer_mobile,
        'user_token' => $user_token,
        'amount' => $amount,
        'order_id' => $order_id,
        'redirect_url' => $redirect_url,
        'remark1' => 'IMO: ' . $imo,
        'remark2' => $pack_label,
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
