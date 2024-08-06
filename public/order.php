<?php
require_once('../includes/functions.php');
require_once('../config/db_connect.php');
require_once('../includes/order_processing.php');

// This prevents undefined index errors
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderData = $_POST;
    $cartItems = json_decode($_POST['cart_items'], true);
    
    // Calculate total weight and price
    $totalWeight = 0;
    $totalPrice = 0;
    foreach ($cartItems as $item) {
        $product = getProductById($item['id']);
        $totalWeight += $product['weight'] * $item['quantity'];
        $totalPrice += $product['price'] * $item['quantity'];
    }
    
    $shippingCost = calculateShipping($totalWeight);
    $totalPrice += $shippingCost;
    
    // Process payment
    $paymentResult = processPayment([
        'number' => $orderData['card-number'],
        'name' => $orderData['card-name'],
        'expiry' => $orderData['card-expiry'],
        'cvv' => $orderData['card-cvv']
    ], $totalPrice);
    
    if ($paymentResult['success']) {
        // Save order to database
        $orderId = saveOrder($orderData, $cartItems, $paymentResult['authorization']);
        
        // Send confirmation email
        sendOrderConfirmationEmail($orderData['email'], $orderId);
        
        echo json_encode(['success' => true, 'orderId' => $orderId]);
    } else {
        echo json_encode(['success' => false, 'message' => $paymentResult['error']]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
