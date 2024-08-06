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
    }
}

$pendingOrders = array_filter($_SESSION['orders'] ?? [], function($order) {
    return $order['status'] === 'pending';
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Interface</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <h1>Warehouse Interface</h1>
    </header>

    <main>
        <section id="order-processing">
            <h2>Pending Orders</h2>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingOrders as $orderId => $order): ?>
                    <tr>
                        <td><?php echo $orderId; ?></td>
                        <td><?php echo $order['customer_name']; ?></td>
                        <td>$<?php echo number_format($order['total_cost'], 2); ?></td>
                        <td>
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