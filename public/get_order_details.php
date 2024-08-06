<?php
require_once('../includes/functions.php');
require_once('../config/db_connect.php');
session_start();

if (isset($_GET['id'])) {
    $orderId = $_GET['id'];
    if (isset($_SESSION['orders'][$orderId])) {
        $order = $_SESSION['orders'][$orderId];
        
        $orderDetails = [
            'orderId' => $orderId,
            'customerName' => $order['customer_name'],
            'customerEmail' => $order['customer_email'],
            'shippingAddress' => $order['shipping_address'],
            'total' => $order['total_cost'],
            'items' => $order['items'],
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