<?php
// Start output buffering
ob_start();

require_once('../includes/functions.php');
require_once('../config/db_connect.php');
session_start();

$response = ['success' => false, 'message' => 'An unexpected error occurred.'];

try {
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
        
        $shippingCost = calculateShippingCost($totalWeight);
        $totalPrice += $shippingCost;
        
        // Process payment
        $paymentResult = processPayment([
            'number' => $orderData['card-number'],
            'name' => $orderData['card-name'],
            'expiry' => $orderData['card-expiry'],
        ], $totalPrice);
        
        if ($paymentResult['success']) {
            // Get customer from database
            $customer = getCustomerByDetails($orderData['customer_name'], $orderData['city'], $orderData['street']);
            if ($customer) {
                // Create order in session
                $orderId = createOrder($customer, $totalPrice, $shippingCost, $cartItems);
                
                // Update inventory
                updateInventoryAfterOrder($cartItems);
                
                $response = ['success' => true, 'orderId' => $orderId];
            } else {
                $response = ['success' => false, 'message' => 'Customer not found in the database'];
            }
        } else {
            $response = ['success' => false, 'message' => $paymentResult['error']];
        }
    } else {
        http_response_code(405);
        $response = ['success' => false, 'message' => 'Method not allowed'];
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()];
}

// Clear the output buffer and discard its contents
ob_end_clean();

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>