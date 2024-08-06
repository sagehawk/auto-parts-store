<?php
session_start();
require_once('../includes/functions.php');

if (isset($_GET['id'])) {
    $orderId = $_GET['id'];
    if (isset($_SESSION['orders'][$orderId])) {
        $order = $_SESSION['orders'][$orderId];
        
        // Fetch product details for each item in the order
        foreach ($order['items'] as &$item) {
            $product = getProductById($item['id']);
            $item['description'] = $product['description'];
            $item['price'] = $product['price'];
        }

        $orderDetails = [
            'orderId' => $orderId,
            'customer_name' => $order['customer_name'],
            'customer_contact' => $order['customer_contact'],
            'customer_street' => $order['customer_street'],
            'customer_city' => $order['customer_city'],
            'total_cost' => $order['total_cost'],
            'shipping_cost' => $order['shipping_cost'],
            'items' => $order['items'],
            'status' => $order['status'],
            'date' => $order['date']
        ];

        echo json_encode($orderDetails);
    } else {
        echo json_encode(['error' => 'Order not found']);
    }
} else {
    echo json_encode(['error' => 'No order ID provided']);
}
?>