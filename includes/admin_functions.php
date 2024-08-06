<?php
session_start();
require_once('../config/db_connect.php');

function setShippingCharges($weightBrackets) {
    $_SESSION['shipping_rates'] = $weightBrackets;
    return true;
}

function viewOrders() {
    return $_SESSION['orders'] ?? [];
}

function searchOrders($criteria) {
    $orders = $_SESSION['orders'] ?? [];
    return array_filter($orders, function($order) use ($criteria) {
        $matchesSearch = empty($criteria['search']) || 
                         stripos($order['customer_name'], $criteria['search']) !== false || 
                         stripos($order['customer_email'], $criteria['search']) !== false;
        $matchesDate = (empty($criteria['start_date']) || strtotime($order['date']) >= strtotime($criteria['start_date'])) &&
                       (empty($criteria['end_date']) || strtotime($order['date']) <= strtotime($criteria['end_date']));
        $matchesStatus = empty($criteria['status']) || $order['status'] == $criteria['status'];
        $matchesPrice = (empty($criteria['min_price']) || $order['total_cost'] >= floatval($criteria['min_price'])) &&
                        (empty($criteria['max_price']) || $order['total_cost'] <= floatval($criteria['max_price']));

        return $matchesSearch && $matchesDate && $matchesStatus && $matchesPrice;
    });
}
?>