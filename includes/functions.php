<?php
require_once('../config/db_connect.php');

function getProducts() {
    global $conn;
    $sql = "SELECT * FROM parts";
    $result = $conn->query($sql);
    $products = $result->fetch_all(MYSQLI_ASSOC);
    
    // Add inventory quantity to each product
    foreach ($products as &$product) {
        $product['inventory'] = getInventoryQuantity($product['number']);
    }
    
    return $products;
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

function getInventory() {
    global $conn;
    $result = $conn->query("SELECT number, description, weight FROM parts");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function updateInventory($partNumber, $quantity) {
    // Since we can't modify the database, we'll store inventory in session
    if (!isset($_SESSION['inventory'])) {
        $_SESSION['inventory'] = [];
    }
    if (!isset($_SESSION['inventory'][$partNumber])) {
        $_SESSION['inventory'][$partNumber] = 0;
    }
    $_SESSION['inventory'][$partNumber] += $quantity;
    return true;
}

function getInventoryQuantity($partNumber) {
    return $_SESSION['inventory'][$partNumber] ?? 0;
}

function updateInventoryOnPurchase($partNumber, $quantity) {
    if (!isset($_SESSION['inventory'][$partNumber])) {
        return false; // Item not in inventory
    }
    if ($_SESSION['inventory'][$partNumber] < $quantity) {
        return false; // Not enough stock
    }
    $_SESSION['inventory'][$partNumber] -= $quantity;
    return true;
}


?>