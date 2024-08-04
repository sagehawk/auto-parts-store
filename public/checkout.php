<?php
session_start();

// Retrieve cart items from the session
if (isset($_SESSION['cart'])) {
    $cartItems = $_SESSION['cart'];
} else {
    $cartItems = [];
}

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
            $total = 0;
            foreach ($cartItems as $item): 
                $product = getProductById($item['id']);
                $itemTotal = $product['price'] * $item['quantity'];
                $total += $itemTotal;
                ?>
                <div class="order-item">
                    <p><?= $product['description'] ?> x <?= $item['quantity'] ?></p>
                    <p>$<?= number_format($itemTotal, 2) ?></p>
                </div>
            <?php endforeach; ?>
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


        </form>
    
    <script>
        function validateForm() {
            // Implement your form validation logic here.
            // Return true if the form is valid, false otherwise.

            // Example validation (you'll need to add more specific checks):
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