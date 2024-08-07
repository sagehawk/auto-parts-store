<?php
require_once('../config/db_connect.php');
require_once('../includes/admin_functions.php');
require_once('../includes/credit_card_processing.php');
require_once('../includes/order_processing.php');
require_once('../includes/warehouse_functions.php');
require_once('../includes/functions.php');

// Initialize inventory if not set
if (!isset($_SESSION['inventory'])) {
    $_SESSION['inventory'] = [];
    $products = getProducts();
    foreach ($products as $product) {
        $_SESSION['inventory'][$product['number']] = 10; // Set initial inventory to 10 for each product
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process the form data and place the order
    $items = json_decode($_POST['items'], true);
    $creditCardInfo = array(
        'number' => $_POST['card_number'],
        'expiration' => $_POST['card_expiry'],
        'name' => $_POST['card_name'],
    );
    
    $result = placeOrder($_POST['customer_id'], $items, $_POST['shipping_address'], $creditCardInfo);

    // Return the result as JSON
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

$products = getProducts();
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
    <?php foreach ($products as $product): ?>
        <div class="product">
            <h3><?= htmlspecialchars($product['description']) ?></h3>
            <img src="<?= htmlspecialchars($product['pictureURL']) ?>" alt="<?= htmlspecialchars($product['description']) ?>">
            <p>Price: $<?= number_format($product['price'], 2) ?></p>
            <p>Weight: <?= $product['weight'] ?> lbs</p>
            <?php
            $productNumber = $product['number'];
            $inventoryCount = isset($_SESSION['inventory'][$productNumber]) ? $_SESSION['inventory'][$productNumber] : 0;
            ?>
            <p>In Stock: <span class="inventory-count"><?= $inventoryCount ?></span></p>
            <input type="number" class="product-quantity" min="1" max="<?= $inventoryCount ?>" value="1" data-product-id="<?= $productNumber ?>">
            <button class="add-to-cart" data-product-id="<?= $productNumber ?>" <?= $inventoryCount == 0 ? 'disabled' : '' ?>>
                <?= $inventoryCount == 0 ? 'Out of Stock' : 'Add to Cart' ?>
            </button>
        </div>
    <?php endforeach; ?>
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