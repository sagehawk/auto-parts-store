<?php
require_once('../includes/db_connect.php');
require_once('../includes/functions.php');

$products = getProducts();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Product Catalog</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h1>Product Catalog</h1>

    <div id="products">
        <?php foreach ($products as $product): ?>
            <div class="product">
                <h3><?= $product['description'] ?></h3>
                <img src="<?= $product['pictureURL'] ?>" alt="<?= $product['description'] ?>">
                <p>Price: $<?= number_format($product['price'], 2) ?></p>
                <p>Weight: <?= $product['weight'] ?> lbs</p>
                <button class="add-to-cart" data-product-id="<?= $product['number'] ?>">Add to Cart</button>
            </div>
        <?php endforeach; ?>
    </div>

    <h2>Shopping Cart</h2>
    <div id="cart-items">
        </div>
    <p>Total: $<span id="cart-total">0.00</span></p>
    <button id="checkout-button">Proceed to Checkout</button>

    <script src="js/main.js"></script> 
</body>
</html>