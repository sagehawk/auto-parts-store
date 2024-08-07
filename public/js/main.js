document.addEventListener('DOMContentLoaded', function () {
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    const cartItems = document.getElementById('cart-items');
    const cartTotal = document.getElementById('cart-total');
    const checkoutButton = document.getElementById('checkout-button');

    let cart = [];

    // Load cart from session storage
    const storedCart = sessionStorage.getItem('cart');
    if (storedCart) {
        cart = JSON.parse(storedCart);
        updateCartDisplay();
    }

    addToCartButtons.forEach(button => {
        button.addEventListener('click', () => {
            const productId = button.dataset.productId;
            const quantityInput = button.parentElement.querySelector('.product-quantity');
            const quantity = parseInt(quantityInput.value);
            addToCart(productId, quantity);
        });
    });

    checkoutButton.addEventListener('click', () => {
        sessionStorage.setItem('cart', JSON.stringify(cart));
        saveCartToSessionAndRedirect();
    });

    function saveCartToSessionAndRedirect() {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'checkout.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status === 200) {
                window.location.href = 'checkout.php';
            }
        };
        xhr.send('cart=' + encodeURIComponent(JSON.stringify(cart)));
    }

    function addToCart(productId, quantity) {
        fetch(`get_product.php?id=${productId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(product => {
                const existingItem = cart.find(item => item.id === product.id);
                if (existingItem) {
                    existingItem.quantity += quantity;
                } else {
                    product.quantity = quantity;
                    cart.push(product);
                }
                updateCartDisplay();
                updateInventoryDisplay(product.id, -quantity);
            })
            .catch(error => {
                console.error('Fetch error:', error);
            });
    }

    function updateCartDisplay() {
        cartItems.innerHTML = '';
        let total = 0;

        cart.forEach(item => {
            const itemElement = document.createElement('div');
            itemElement.classList.add('cart-item');
            itemElement.innerHTML = `
                <p>${item.description}</p>
                <p>Price: $${item.price}</p>
                <p>Quantity: ${item.quantity}</p>
                <button class="remove-item" data-product-id="${item.id}">Remove</button>
            `;
            cartItems.appendChild(itemElement);
            total += item.price * item.quantity;

            itemElement.querySelector('.remove-item').addEventListener('click', () => removeFromCart(item.id));
        });

        cartTotal.textContent = total.toFixed(2);
        sessionStorage.setItem('cart', JSON.stringify(cart));
    }

    function removeFromCart(productId) {
        const index = cart.findIndex(item => item.id === productId);
        if (index !== -1) {
            const removedQuantity = cart[index].quantity;
            cart.splice(index, 1);
            updateCartDisplay();
            updateInventoryDisplay(productId, removedQuantity);
        }
    }

    function updateInventoryDisplay(productId, quantityChange) {
        const productElement = document.querySelector(`.product button[data-product-id="${productId}"]`).parentNode;
        const inventoryElement = productElement.querySelector('.inventory-count');
        const quantityInput = productElement.querySelector('.product-quantity');
        let currentInventory = parseInt(inventoryElement.textContent);
        currentInventory += quantityChange;
        inventoryElement.textContent = currentInventory;

        const addButton = productElement.querySelector('.add-to-cart');
        if (currentInventory === 0) {
            addButton.disabled = true;
            addButton.textContent = 'Out of Stock';
        } else {
            addButton.disabled = false;
            addButton.textContent = 'Add to Cart';
        }

        quantityInput.max = currentInventory;
        if (parseInt(quantityInput.value) > currentInventory) {
            quantityInput.value = currentInventory;
        }
    }
});
