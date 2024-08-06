<?php
require_once('../config/db_connect.php');
require_once('../includes/admin_functions.php');
require_once('../includes/credit_card_processing.php');
require_once('../includes/inventory_management.php');
require_once('../includes/order_processing.php');
require_once('../includes/warehouse_functions.php');
require_once('../includes/functions.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process the form data and place the order
    $items = json_decode($_POST['items'], true);
    $creditCardInfo = array(
        'number' => $_POST['card_number'],
        'expiration' => $_POST['card_expiry'],
        'name' => $_POST['card_name'],
        'cvv' => $_POST['card_cvv'] // Include CVV if needed
    );
    
    $result = placeOrder($_POST['customer_id'], $items, $_POST['shipping_address'], $creditCardInfo);

    // Return the result as JSON
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;  // Make sure to exit after sending JSON response
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <script src="js/main.js" defer></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto Parts Catalog</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <h1>Auto Parts Catalog</h1>
        <nav>
            <ul>
                <li><a href="#catalog">Catalog</a></li>
                <li><a href="#cart">Cart</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section id="catalog">
            <h2>Product Catalog</h2>
            <?php
            // Fetch products from the database
            $products = getProducts();

            foreach ($products as $product) {
                echo "<div class='product'>";
                echo "<h3>{$product['description']}</h3>";
                echo "<img src='{$product['pictureURL']}' alt='{$product['description']}'>";
                echo "<p>Price: $" . number_format($product['price'], 2) . "</p>";
                echo "<input type='number' class='product-quantity' min='1' value='1' data-product-id='" . $product['number'] . "'>";
                echo "<p>Weight: {$product['weight']} lbs</p>";
                echo "<button class='add-to-cart' data-product-id='{$product['number']}'>Add to Cart</button>";
                echo "</div>";
            }
            ?>
        </section>

        <section id="cart">
            <h2>Shopping Cart</h2>
            <div id="cart-items"></div>
            <p>Total: $<span id="cart-total">0.00</span></p>
            <button id="checkout-button">Proceed to Checkout</button>
        </section>
    </main>
</body>
</html>
