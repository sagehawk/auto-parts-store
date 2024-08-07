<?php
require_once('../config/db_connect.php');

function getProducts() {
    global $conn;
    $sql = "SELECT * FROM parts";
    $result = $conn->query($sql);
    $products = $result->fetch_all(MYSQLI_ASSOC);
    
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

function calculateShippingCost($weight) {
    $shippingRates = $_SESSION['shipping_rates'] ?? [];
    
    if (empty($shippingRates)) {
        // Default shipping charges if no rates are set
        if ($weight < 0) return 0.00;
        elseif ($weight < 10) return 10.00;
        else return 15.00;
    }

    foreach ($shippingRates as $rate) {
        if ($weight >= $rate['min'] && $weight < $rate['max']) {
            return $rate['charge'];
        }
    }

    // If weight exceeds all brackets, use the highest charge
    return end($shippingRates)['charge'];
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
    // Implement email sending logic (No longer needed)
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

function getCustomerByDetails($name, $city, $street) {
    global $conn;
    $stmt = $conn->prepare("SELECT id, name, city, street, contact FROM customers WHERE name = ? AND city = ? AND street = ?");
    $stmt->bind_param("sss", $name, $city, $street);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function createOrder($customerDetails, $totalCost, $shippingCost, $items) {
    $orderId = uniqid();
    $_SESSION['orders'][$orderId] = [
        'customer_id' => $customerDetails['id'],
        'customer_name' => $customerDetails['name'],
        'customer_city' => $customerDetails['city'],
        'customer_street' => $customerDetails['street'],
        'customer_contact' => $customerDetails['contact'],
        'total_cost' => $totalCost,
        'shipping_cost' => $shippingCost,
        'items' => $items,
        'status' => 'pending',
        'date' => date('Y-m-d h:i:A', strtotime('7 hours ago'))
    ];
    return $orderId;
}

function createCustomer($name, $city, $street) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO customers (name, city, street) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $city, $street);
    $stmt->execute();
    return $conn->insert_id;
}

function updateInventoryAfterOrder($cartItems) {
    foreach ($cartItems as $item) {
        // Check if the item exists in the session inventory
        if (!isset($_SESSION['inventory'][$item['id']])) {
            // If not, initialize it
            $_SESSION['inventory'][$item['id']] = getInitialQuantityFromDatabase($item['id']);
        }
        
        // Update the session inventory
        $_SESSION['inventory'][$item['id']] -= $item['quantity'];
        
        // Ensure quantity doesn't go below zero
        $_SESSION['inventory'][$item['id']] = max(0, $_SESSION['inventory'][$item['id']]);
    }
}

// Helper function to get the initial quantity from the database
function getInitialQuantityFromDatabase($part_number) {
    global $conn;
    $stmt = $conn->prepare("SELECT quantity FROM inventory WHERE part_number = ?");
    $stmt->bind_param("i", $part_number);
    $stmt->execute();
    $stmt->bind_result($quantity);
    $stmt->fetch();
    $stmt->close();
    return $quantity;
}


?>