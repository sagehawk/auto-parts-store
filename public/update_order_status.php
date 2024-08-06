<?php
require_once('../includes/functions.php');
require_once('../config/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['orderId'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE warehouse_orders SET status = ? WHERE order_id = ?");
    $stmt->bind_param("ss", $status, $orderId);
    $result = $stmt->execute();

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}