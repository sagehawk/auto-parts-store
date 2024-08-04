<?php
require_once('../config/db_connect.php');

function updateOrderStatus($orderId, $newStatus) {
    global $conn;
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $stmt->bind_param("ss", $newStatus, $orderId);
    $stmt->execute();
    return $stmt->affected_rows > 0;
}

function printPackingList($orderId) {
    global $conn;
    $stmt = $conn->prepare("SELECT o.order_id, c.name AS customer_name, p.number, p.description, oi.quantity 
                            FROM orders o
                            JOIN customers c ON o.customer_id = c.customer_id
                            JOIN order_items oi ON o.order_id = oi.order_id
                            JOIN parts p ON oi.part_number = p.number
                            WHERE o.order_id = ?");
    $stmt->bind_param("s", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $packingList = [
        'order_id' => $orderId,
        'customer_name' => '',
        'items' => []
    ];
    
    while ($row = $result->fetch_assoc()) {
        $packingList['customer_name'] = $row['customer_name'];
        $packingList['items'][] = [
            'part_number' => $row['number'],
            'description' => $row['description'],
            'quantity' => $row['quantity']
        ];
    }
    
    return $packingList;
}

function sendShippingConfirmation($orderId) {
    global $conn;
    $stmt = $conn->prepare("SELECT c.email, o.total_cost 
                            FROM orders o
                            JOIN customers c ON o.customer_id = c.customer_id
                            WHERE o.order_id = ?");
    $stmt->bind_param("s", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    
    $to = $order['email'];
    $subject = "Shipping Confirmation for Order #$orderId";
    $message = "Your order #$orderId has been shipped. Total cost: $" . number_format($order['total_cost'], 2);
    $headers = "From: noreply@autoparts.com\r\n";
    
    return mail($to, $subject, $message, $headers);
}

function receiveInventory($partNumber, $quantity) {
    global $conn;
    $stmt = $conn->prepare("UPDATE inventory SET quantity = quantity + ? WHERE part_number = ?");
    $stmt->bind_param("ii", $quantity, $partNumber);
    $stmt->execute();
    return $stmt->affected_rows > 0;
}
?>