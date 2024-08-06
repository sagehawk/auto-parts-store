<?php
require_once('../includes/functions.php');
require_once('../config/db_connect.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $orderId = $_POST['order_id'];
        $newStatus = $_POST['new_status'];
        if (isset($_SESSION['orders'][$orderId])) {
            $_SESSION['orders'][$orderId]['status'] = $newStatus;
            echo "Order status updated successfully";
        } else {
            echo "Order not found";
        }
    } elseif (isset($_POST['update_inventory'])) {
        $partNumber = $_POST['part_number'];
        $quantity = $_POST['quantity'];
        updateInventory($partNumber, $quantity);
        echo "Inventory updated successfully";
    }
}

$pendingOrders = array_filter($_SESSION['orders'] ?? [], function($order) {
    return $order['status'] === 'pending';
});

// Get current inventory
$inventory = getInventory();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Interface</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Warehouse Interface</h1>
        </div>
    </header>

    <main class="container">
        <section id="order-processing">
            <h2>Pending Orders</h2>
            <table class="order-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Total</th>
                        <th>Shipping Cost</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingOrders as $orderId => $order): ?>
                    <tr>
                        <td data-label="Order ID"><?php echo $orderId; ?></td>
                        <td data-label="Customer Name"><?php echo $order['customer_name']; ?></td>
                        <td data-label="Total">$<?php echo number_format($order['total_cost'], 2); ?></td>
                        <td data-label="Shipping Cost">$<?php echo number_format($order['shipping_cost'] ?? 0, 2); ?></td>
                        <td data-label="Action">
                            <button class="view-order" data-order-id="<?php echo $orderId; ?>">View Order</button>
                            <form action="warehouse.php" method="POST" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?php echo $orderId; ?>">
                                <input type="hidden" name="new_status" value="shipped">
                                <button type="submit" name="update_status">Mark as Shipped</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section id="inventory-management">
            <h2>Inventory Management</h2>
            <table class="order-table">
                <thead>
                    <tr>
                        <th>Part Number</th>
                        <th>Description</th>
                        <th>Quantity on Hand</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inventory as $part): ?>
                    <tr>
                        <td><?php echo $part['number']; ?></td>
                        <td><?php echo $part['description']; ?></td>
                        <td><?php echo getInventoryQuantity($part['number']); ?></td>
                        <td data-label="Action">
                            <form action="warehouse.php" method="POST">
                                <input type="hidden" name="part_number" value="<?php echo $part['number']; ?>">
                                <input type="number" name="quantity" placeholder="Quantity to add" required>
                                <button type="submit" name="update_inventory">Update</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

    <div id="orderModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Order Details</h2>
            <div id="orderDetails"></div>
        </div>
    </div>

    <script src="js/warehouse.js"></script>
</body>
</html>