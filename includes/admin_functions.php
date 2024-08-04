<?php
require_once('../config/db_connect.php');

function setShippingCharges($weightBrackets) {
    global $conn;
    $conn->begin_transaction();
    
    try {
        $stmt = $conn->prepare("DELETE FROM shipping_charges");
        $stmt->execute();
        
        $stmt = $conn->prepare("INSERT INTO shipping_charges (min_weight, max_weight, charge) VALUES (?, ?, ?)");
        foreach ($weightBrackets as $bracket) {
            $stmt->bind_param("ddd", $bracket['min'], $bracket['max'], $bracket['charge']);
            $stmt->execute();
        }
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

function viewOrders() {
    global $conn;
    $result = $conn->query("SELECT o.order_id, c.name AS customer_name, o.status, o.total_cost, o.order_date 
                            FROM orders o
                            JOIN customers c ON o.customer_id = c.customer_id
                            ORDER BY o.order_date DESC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function searchOrders($criteria) {
    global $conn;
    $query = "SELECT o.order_id, c.name AS customer_name, o.status, o.total_cost, o.order_date 
              FROM orders o
              JOIN customers c ON o.customer_id = c.customer_id
              WHERE 1=1";
    $params = [];
    $types = "";
    
    if (isset($criteria['start_date']) && isset($criteria['end_date'])) {
        $query .= " AND o.order_date BETWEEN ? AND ?";
        $params[] = $criteria['start_date'];
        $params[] = $criteria['end_date'];
        $types .= "ss";
    }
    
    if (isset($criteria['status'])) {
        $query .= " AND o.status = ?";
        $params[] = $criteria['status'];
        $types .= "s";
    }
    
    if (isset($criteria['min_price']) && isset($criteria['max_price'])) {
        $query .= " AND o.total_cost BETWEEN ? AND ?";
        $params[] = $criteria['min_price'];
        $params[] = $criteria['max_price'];
        $types .= "dd";
    }
    
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}
?>