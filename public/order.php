<?php
require_once('../includes/functions.php');
require_once('../config/db_connect.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderData = $_POST;
    $cartItems = json_decode($_POST['cart_items'], true);
    
    // Calculate total weight and price
    $totalWeight = 0;
    $totalPrice = 0;
    foreach ($cartItems as $item) {
        $stmt = $conn->prepare("SELECT price, weight FROM parts WHERE number = ?");
        $stmt->bind_param("i", $item['id']);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        
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
        // Get an existing customer or use a random one
        $customer = getCustomerByEmail($orderData['email']);
        if (!$customer) {
            $customer = getRandomCustomer();
        }
        
        $shippingCost = calculateShipping($totalWeight);
        $totalPrice += $shippingCost;

        // Store order in session
        $orderId = uniqid();
        $_SESSION['orders'][$orderId] = [
            'customer_id' => $customer['id'],
            'customer_name' => $customer['name'],
            'customer_email' => $customer['contact'],
            'shipping_address' => $orderData['shipping_address'],
            'total_cost' => $totalPrice,
            'shipping_cost' => $shippingCost,
            'items' => $cartItems,
            'status' => 'pending',
            'date' => date('Y-m-d H:i:s')
        ];
        
        echo json_encode(['success' => true, 'orderId' => $orderId]);
    } else {
        echo json_encode(['success' => false, 'message' => $paymentResult['error']]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>