<?php
require_once('../config/db_connect.php');

function getProducts() {
    global $conn;
    $sql = "SELECT * FROM parts";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getCustomerByEmail($email) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM customers WHERE contact = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getRandomCustomer() {
    global $conn;
    $result = $conn->query("SELECT * FROM customers ORDER BY RAND() LIMIT 1");
    return $result->fetch_assoc();
}

function calculateShipping($weight) {
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
        return ['success' => false, 'error' => $response['error'] ?? 'Unknown error: ' . $result];
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

function sendOrderConfirmationEmail($email, $orderId) {
    // Implement email sending logic
}

function createCustomer($name, $email, $address) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO customers (name, contact, street) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $address);
    $stmt->execute();
    return $conn->insert_id;
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





?>