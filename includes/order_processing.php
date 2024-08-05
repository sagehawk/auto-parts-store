<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once('../config/db_connect.php');
require_once('../includes/inventory_management.php');
require_once('../includes/credit_card_processing.php');

function placeOrder($customerId, $items, $shippingAddress, $creditCardInfo) {
    global $conn;
    
    $conn->begin_transaction();

    try {
        $totalWeight = 0;
        $totalCost = 0;
        foreach ($items as $item) {
            $stmt = $conn->prepare("SELECT price, weight FROM parts WHERE number = ?");
            $stmt->bind_param("i", $item['part_number']);
            $stmt->execute();
            $result = $stmt->get_result();
            $part = $result->fetch_assoc();
            $totalWeight += $part['weight'] * $item['quantity'];
            $totalCost += $part['price'] * $item['quantity'];
        }

        $shippingCost = calculateShippingCost($totalWeight);
        $totalCost += $shippingCost;

        $authorizationNumber = processCreditCard($creditCardInfo, $totalCost);

        if (strpos($authorizationNumber, 'Error') === 0) {
            throw new Exception($authorizationNumber);
        }

        $orderId = uniqid();
        $stmt = $conn->prepare("INSERT INTO orders (order_id, customer_id, total_cost, shipping_address, authorization_number) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sidss", $orderId, $customerId, $totalCost, $shippingAddress, $authorizationNumber);
        $stmt->execute();

        foreach ($items as $item) {
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, part_number, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("sii", $orderId, $item['part_number'], $item['quantity']);
            $stmt->execute();

            updateInventory($item['part_number'], $item['quantity']);
        }

        $conn->commit();
        sendOrderConfirmationEmail($customerId, $orderId, $totalCost);

        return array('success' => true, 'orderId' => $orderId);
    } catch (Exception $e) {
        $conn->rollback();
        return array('success' => false, 'message' => "Error placing order: " . $e->getMessage());
    }
}

function calculateShippingCost($weight) {
    if ($weight < 5) return 5.00;
    elseif ($weight < 10) return 10.00;
    else return 15.00;
}
?>
