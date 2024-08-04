<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once('../config/db_connect.php');
require_once('../includes/inventory_management.php');
require_once('../includes/credit_card_processing.php');


function placeOrder($customerId, $items, $shippingAddress, $creditCardInfo) {
    global $conn;
    
    // Start transaction
    $conn->begin_transaction();

    try {
        // Calculate total weight and cost
        $totalWeight = 0;
        $totalCost = 0;
        foreach ($items as $item) {
            $stmt = $conn->prepare("SELECT price, weight FROM parts WHERE number = ?");
            $stmt->bind_param("i", $item['part_number']);
            $stmt->execute();
            $result = $stmt->get_result();
            $part = $result->fetch_assoc();  // Use fetch_assoc() here
            $totalWeight += $part['weight'] * $item['quantity'];
            $totalCost += $part['price'] * $item['quantity'];
        }

        // Calculate shipping cost (you'll need to implement this based on weight brackets)
        $shippingCost = calculateShippingCost($totalWeight);
        $totalCost += $shippingCost;

        // Process credit card
        $authorizationNumber = processCreditCard($creditCardInfo, $totalCost);

        if (strpos($authorizationNumber, 'Error') === 0) {
            throw new Exception($authorizationNumber);
        }

        // Create order
        $orderId = uniqid();
        $stmt = $conn->prepare("INSERT INTO orders (order_id, customer_id, total_cost, shipping_address, authorization_number) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sidss", $orderId, $customerId, $totalCost, $shippingAddress, $authorizationNumber);
        $stmt->execute();

        // Add order items and update inventory
        foreach ($items as $item) {
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, part_number, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("sii", $orderId, $item['part_number'], $item['quantity']);
            $stmt->execute();

            updateInventory($item['part_number'], $item['quantity']);
        }

        // Commit transaction
        $conn->commit();

        // Send confirmation email
        sendOrderConfirmationEmail($customerId, $orderId, $totalCost);

        return array('success' => true, 'orderId' => $orderId);
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        return array('success' => false, 'message' => "Error placing order: " . $e->getMessage());
    }
}

function calculateShippingCost($weight) {
    // Implement shipping cost calculation based on weight brackets
    // This is a placeholder implementation
    if ($weight < 5) return 5.00;
    elseif ($weight < 10) return 10.00;
    else return 15.00;
}

header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure your checkout.php sends the cart data correctly
    if (!empty($_POST['cart_items'])) { 
        $items = json_decode($_POST['cart_items'], true);
        $shippingAddress = $_POST['shipping_address'];
        $creditCardInfo = array(
            'number' => $_POST['card_number'],
            'expiration' => $_POST['card_expiry'],
            'name' => $_POST['card_name']
        );

        $result = placeOrder($_POST['customer_id'], $items, $shippingAddress, $creditCardInfo);

        echo json_encode($result);
    } else {
        echo json_encode(array('success' => false, 'message' => 'No items in cart'));
    }
}
?>