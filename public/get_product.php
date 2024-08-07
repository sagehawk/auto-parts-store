<?php
require_once('../config/db_connect.php');
require_once('../includes/functions.php');

if (isset($_GET['id'])) {
    $productId = (int) $_GET['id']; // Cast to integer for security

    // Fetch the product data
    $product = getProductById($productId);

    if ($product) {
        $productData = [
            'id'          => $product['number'],
            'description' => $product['description'],
            'price'       => number_format($product['price'], 2), // Format as string for display
            'weight'      => $product['weight'],
            'pictureURL'  => $product['pictureURL']
        ];

        // Send the product data as JSON
        header('Content-Type: application/json');
        echo json_encode($productData);
    } else {
        // Handle the case where the product is not found
        header("HTTP/1.1 404 Not Found");
        echo json_encode(['error' => 'Product not found']);
    }
} else {
    // Handle missing product ID
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['error' => 'Missing product ID']);
}
