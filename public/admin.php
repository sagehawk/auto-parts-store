<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require_once('../includes/admin_functions.php');
    require_once('../includes/db_connect.php');
    require_once('../includes/functions.php');

    // This file would contain the HTML and PHP code for the admin interface
    // Here's a simple example of how you might use the functions:

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['set_shipping_charges'])) {
            $weightBrackets = json_decode($_POST['weight_brackets'], true);
            if (setShippingCharges($weightBrackets)) {
                echo "Shipping charges updated successfully";
            } else {
                echo "Failed to update shipping charges";
            }
        }
    }

    //$orders = getOrders();

    if (isset($_GET['view_orders'])) {
        $orders = viewOrders();
        // Display orders (you'd want to format this nicely in a real application)
        print_r($orders);
    }

    if (isset($_GET['search_orders'])) {
        $criteria = [
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null,
            'status' => $_GET['status'] ?? null,
            'min_price' => $_GET['min_price'] ?? null,
            'max_price' => $_GET['max_price'] ?? null
        ];
        $orders = searchOrders($criteria);
        // Display search results (you'd want to format this nicely in a real application)
        print_r($orders);
    }

    // The rest of this file would contain the HTML for the admin interface
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <h1>Admin Panel</h1>
    </header>

    <main>
        <section id="shipping-charges">
            <h2>Set Shipping Charges</h2>
            <form action="admin.php" method="POST">
                <div id="weight-brackets">
                    <div class="weight-bracket">
                        <input type="number" name="weight[]" placeholder="Max Weight" required>
                        <input type="number" name="charge[]" step="0.01" placeholder="Charge" required>
                    </div>
                </div>
                <button type="button" onclick="addWeightBracket()">Add Weight Bracket</button>
                <button type="submit" name="set_shipping_charges">Update Shipping Charges</button>
            </form>
        </section>

        <script>
        function addWeightBracket() {
            const container = document.getElementById('weight-brackets');
            const newBracket = document.createElement('div');
            newBracket.className = 'weight-bracket';
            newBracket.innerHTML = `
                <input type="number" name="weight[]" placeholder="Max Weight" required>
                <input type="number" name="charge[]" step="0.01" placeholder="Charge" required>
            `;
            container.appendChild(newBracket);
        }
        </script>

        <section id="order-management">
            <h2>Order Management</h2>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo $order['id']; ?></td>
                        <td><?php echo $order['customer_name']; ?></td>
                        <td>$<?php echo number_format($order['total'], 2); ?></td>
                        <td><?php echo $order['status']; ?></td>
                        <td><a href="view_order.php?id=<?php echo $order['id']; ?>">View</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>