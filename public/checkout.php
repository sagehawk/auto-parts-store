<?php
session_start();
require_once('../includes/functions.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart'])) {
    $_SESSION['cart'] = json_decode($_POST['cart'], true);
    echo 'Cart data saved to session';
    exit;
}

$cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>   
    <link rel="stylesheet" href="css/styles.css">   
</head>
<body>
    <h1>Checkout</h1>

    <form id="checkout-form" action="order.php" method="POST" onsubmit="return validateForm()">
        <h2>Order Summary</h2>
        <div id="order-summary">
            <?php 
            foreach ($cartItems as $item): 
                $product = getProductById($item['id']);
                if (isset($item['quantity'])) {
                    $itemTotal = $product['price'] * $item['quantity']; // Use quantity
                    $total += $itemTotal;
                    ?>
                    <div class="order-item">
                        <p><?= htmlspecialchars($product['description']) ?> x <?= $item['quantity'] ?></p>
                        <p>$<?= number_format($itemTotal, 2) ?></p>
                    </div>
                <?php } else { ?>
                    <p>Quantity not set for <?= htmlspecialchars($product['description']) ?></p>
                <?php }
            endforeach; ?>
            <p>Total: $<span id="order-total"><?= number_format($total, 2) ?></span></p>
        </div>

        <h2>Customer Information</h2>
        <input type="hidden" name="cart_items" id="cart_items_input" value='<?= htmlspecialchars(json_encode($cartItems)) ?>'>

        <label for="customer_name">Customer Name:</label>
        <input type="text" id="customer_name" name="customer_name" required><br><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>

        <label for="shipping_address">Shipping Address:</label>
        <textarea id="shipping_address" name="shipping_address" required></textarea><br><br>

        <label for="card-number">Card Number:</label>
        <input type="text" id="card-number" name="card-number" required><br><br>

        <label for="card-name">Cardholder Name:</label>
        <input type="text" id="card-name" name="card-name" required><br><br>

        <label for="card-expiry">Expiry Date:</label>
        <input type="text" id="card-expiry" name="card-expiry" required><br><br>

        <label for="card-cvv">CVV:</label>
        <input type="text" id="card-cvv" name="card-cvv" required><br><br>

        <button type="submit">Place Order</button>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const storedCart = sessionStorage.getItem('cart');
            if (storedCart) {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'checkout.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        console.log(xhr.responseText);
                    }
                };
                xhr.send('cart=' + encodeURIComponent(storedCart));
            }
        });

        function validateForm() {
            const customerName = document.getElementById('customer_name').value;
            const email = document.getElementById('email').value;
            const shippingAddress = document.getElementById('shipping_address').value;

            if (customerName === "" || email === "" || shippingAddress === "") {
                alert("Please fill in all fields.");
                return false;
            }

            return true; 
        }
    </script>
</body>
</html>
