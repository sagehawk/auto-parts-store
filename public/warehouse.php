<?php
require_once('../includes/warehouse_functions.php');
require_once('../includes/db_connect.php');
require_once('../includes/functions.php');

// This file would contain the HTML and PHP code for the warehouse interface
// Here's a simple example of how you might use the functions:

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $orderId = $_POST['order_id'];
        $newStatus = $_POST['new_status'];
        if (updateOrderStatus($orderId, $newStatus)) {
            echo "Order status updated successfully";
        } else {
            echo "Failed to update order status";
        }
    } elseif (isset($_POST['print_packing_list'])) {
        $orderId = $_POST['order_id'];
        $packingList = printPackingList($orderId);
        // Display packing list (you'd want to format this nicely in a real application)
        print_r($packingList);
    } elseif (isset($_POST['send_shipping_confirmation'])) {
        $orderId = $_POST['order_id'];
        if (sendShippingConfirmation($orderId)) {
            echo "Shipping confirmation sent successfully";
        } else {
            echo "Failed to send shipping confirmation";
        }
    } elseif (isset($_POST['receive_inventory'])) {
        $partNumber = $_POST['part_number'];
        $quantity = $_POST['quantity'];
        if (receiveInventory($partNumber, $quantity)) {
            echo "Inventory updated successfully";
        } else {
            echo "Failed to update inventory";
        }
    }
}

// The rest of this file would contain the HTML for the warehouse interface
$pendingOrders = getPendingOrders(); // Implement this function in functions.php
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
                        <th>Customer</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingOrders as $order): ?>
                    <tr>
                        <td><?php echo $order['id']; ?></td>
                        <td><?php echo $order['customer_name']; ?></td>
                        <td>
                            <a href="print_packing_list.php?id=<?php echo $order['id']; ?>">Print Packing List</a>
                            <form action="warehouse.php" method="POST" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <button type="submit" name="update_order_status">Mark as Shipped</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section id="inventory-management">
            <h2>Receive Inventory</h2>
            <form action="warehouse.php" method="POST">
                <label for="part_number">Part Number:</label>
                <input type="text" id="part_number" name="part_number" required>
                
                <label for="quantity">Quantity:</label>
                <input type="number" id="quantity" name="quantity" required>
                
                <button type="submit" name="receive_inventory">Update Inventory</button>
            </form>
        </section>

        <section id="inventory-list">
    <h2>Current Inventory</h2>
    <table>
        <thead>
            <tr>
                <th>Part Number</th>
                <th>Description</th>
                <th>Quantity</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $inventory = getInventory(); // Implement this function
            foreach ($inventory as $item):
            ?>
            <tr>
                <td><?php echo $item['number']; ?></td>
                <td><?php echo $item['description']; ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td>
                    <button onclick="updateInventory(<?php echo $item['number']; ?>)">Update</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

    <script>
    function updateInventory(partNumber) {
        const newQuantity = prompt("Enter new quantity:");
        if (newQuantity !== null) {
            // Send AJAX request to update inventory
            fetch('update_inventory.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `part_number=${partNumber}&quantity=${newQuantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Inventory updated successfully');
                    location.reload();
                } else {
                    alert('Failed to update inventory');
                }
            });
        }
    }
    </script>

    </main>
</body>
</html>