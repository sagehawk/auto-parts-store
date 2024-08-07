<?php
require_once('../includes/admin_functions.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['set_shipping_charges'])) {
        $weightBrackets = [];
        for ($i = 0; $i < count($_POST['min_weight']); $i++) {
            $weightBrackets[] = [
                'min' => floatval($_POST['min_weight'][$i]),
                'max' => floatval($_POST['max_weight'][$i]),
                'charge' => floatval($_POST['charge'][$i])
            ];
        }
        if (setShippingCharges($weightBrackets)) {
            $message = "Shipping charges updated successfully";
        }
    }
}

$shippingRates = $_SESSION['shipping_rates'] ?? [];

// Order management
$orders = viewOrders();

// Search functionality
if (isset($_GET['search'])) {
    $searchCriteria = [
        'search' => $_GET['search'] ?? '',
        'start_date' => $_GET['start_date'] ?? '',
        'end_date' => $_GET['end_date'] ?? '',
        'status' => $_GET['status'] ?? '',
        'min_price' => $_GET['min_price'] ?? '',
        'max_price' => $_GET['max_price'] ?? ''
    ];
    $orders = searchOrders($searchCriteria);
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
            <?php if (isset($message)): ?>
                <p><?php echo $message; ?></p>
            <?php endif; ?>
            <form action="admin.php" method="POST">
                <div id="weight-brackets">
                    <?php foreach ($shippingRates as $rate): ?>
                        <div class="weight-bracket">
                            <input type="number" step="0.01" name="min_weight[]" placeholder="Min Weight" value="<?php echo $rate['min']; ?>" required>
                            <input type="number" step="0.01" name="max_weight[]" placeholder="Max Weight" value="<?php echo $rate['max']; ?>" required>
                            <input type="number" step="0.01" name="charge[]" placeholder="Charge" value="<?php echo $rate['charge']; ?>" required>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($shippingRates)): ?>
                        <div class="weight-bracket">
                            <input type="number" step="0.01" name="min_weight[]" placeholder="Min Weight" required>
                            <input type="number" step="0.01" name="max_weight[]" placeholder="Max Weight" required>
                            <input type="number" step="0.01" name="charge[]" placeholder="Charge" required>
                        </div>
                    <?php endif; ?>
                </div>
                <button type="button" onclick="addWeightBracket()">Add Weight Bracket</button>
                <button type="submit" name="set_shipping_charges">Update Shipping Charges</button>
            </form>
        </section>

        <section id="order-management">
            <h2>Order Management</h2>
            <form action="admin.php" method="GET">
                <input type="text" name="search" placeholder="Search by customer name">
                <input type="date" name="start_date">
                <input type="date" name="end_date">
                <select name="status">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="shipped">Shipped</option>
                </select>
                <input type="number" name="min_price" placeholder="Min Price">
                <input type="number" name="max_price" placeholder="Max Price">
                <button type="submit">Search</button>
            </form>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $orderId => $order): ?>
                    <tr>
                        <td><?php echo $orderId; ?></td>
                        <td><?php echo $order['customer_name']; ?></td>
                        <td>$<?php echo number_format($order['total_cost'], 2); ?></td>
                        <td><?php echo $order['status']; ?></td>
                        <td><?php echo $order['date']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

    <script>
    function addWeightBracket() {
        const container = document.getElementById('weight-brackets');
        const newBracket = document.createElement('div');
        newBracket.className = 'weight-bracket';
        newBracket.innerHTML = `
            <input type="number" step="0.01" name="min_weight[]" placeholder="Min Weight" required>
            <input type="number" step="0.01" name="max_weight[]" placeholder="Max Weight" required>
            <input type="number" step="0.01" name="charge[]" placeholder="Charge" required>
        `;
        container.appendChild(newBracket);
    }
    </script>
</body>
</html>