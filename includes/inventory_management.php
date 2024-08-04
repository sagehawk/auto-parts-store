<?php
require_once('../config/db_connect.php');

function updateInventory($partNumber, $quantity) {
    global $conn;
    $stmt = $conn->prepare("UPDATE inventory SET quantity = quantity - ? WHERE part_number = ?");
    $stmt->bind_param("ii", $quantity, $partNumber);
    $stmt->execute();
    $stmt->close();
}

// You might need to populate this table initially from the parts table
?>