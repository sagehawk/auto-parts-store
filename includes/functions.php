<?php

require_once('../config/db_connect.php');

function getProducts() {
    global $conn;

    $sql = "SELECT * FROM parts";
    $result = $conn->query($sql);
    $products = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }

    return $products;
}


function calculateShipping($weight) {
    // Implement shipping calculation based on weight
    // This is a placeholder implementation
    if ($weight < 5) return 5.00;
    elseif ($weight < 10) return 10.00;
    else return 15.00;
}

function processPayment($cardInfo, $amount) {
    $url = 'http://blitz.cs.niu.edu/CreditCard/';
    $data = array(
        'vendor' => 'VE001-99',
        'trans' => uniqid(),
        'cc' => $cardInfo['number'],
        'name' => $cardInfo['name'],
        'exp' => $cardInfo['expiry'],
        'amount' => $amount
    );

    $options = array(
        'http' => array(
            'header' => array('Content-type: application/json', 'Accept: application/json'),
            'method' => 'POST',
            'content' => json_encode($data)
        )
    );

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $response = json_decode($result, true);

    if (isset($response['authorization'])) {
        return ['success' => true, 'authorization' => $response['authorization']];
    } else {
        return ['success' => false, 'error' => $response['error'] ?? 'Unknown error'];
    }
}

function getInventoryQuantity($partNumber) {
    global $conn;
    $stmt = $conn->prepare("SELECT quantity FROM inventory WHERE part_number = ?");
    $stmt->bind_param("i", $partNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['quantity'];
    }
    return 0;
}

function saveOrder($orderData, $cartItems, $authorizationNumber) {
    global $conn;

    // 1. Prepare SQL query for inserting into orders table
    $stmt = $conn->prepare("INSERT INTO orders (customer_id, total_cost, shipping_address, authorization_number, status) 
                            VALUES (?, ?, ?, ?, 'pending')"); // Initial status is pending

    // 2. Bind parameters from $orderData
    $stmt->bind_param("idss", $orderData['customer_id'], $orderData['total_cost'], $orderData['shipping_address'], $authorizationNumber);

    // 3. Execute the query
    $stmt->execute();
    $orderId = $conn->insert_id; // Get the generated order ID

    // 4. Insert into order_items table for each cart item
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, part_number, quantity) VALUES (?, ?, ?)");
    foreach ($cartItems as $item) {
        $stmt->bind_param("iii", $orderId, $item['id'], $item['quantity']);
        $stmt->execute();
    }

    return $orderId;
}


function sendOrderConfirmationEmail($email, $orderId) {
    // Implement email sending logic
}

function getInventory() {
    global $conn;
    $result = $conn->query("SELECT p.number, p.description, i.quantity FROM parts p LEFT JOIN inventory i ON p.number = i.part_number");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getProductById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM parts WHERE number = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row;
    }
    return null;
}

function getPendingOrders() {
    global $conn;
    $stmt = $conn->prepare("SELECT o.id, o.customer_name, o.status 
                            FROM orders o 
                            WHERE o.status = 'authorized' 
                            ORDER BY o.order_date ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}


?>