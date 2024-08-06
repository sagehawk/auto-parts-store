document.addEventListener('DOMContentLoaded', function () {
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    const cartItems = document.getElementById('cart-items');
    const cartTotal = document.getElementById('cart-total');
    const checkoutButton = document.getElementById('checkout-button');

    let cart = [];

    // Load cart from local storage or session
    const storedCart = localStorage.getItem('cart') || sessionStorage.getItem('cart');
    if (storedCart) {
        cart = JSON.parse(storedCart);
        updateCartDisplay();
    }

    addToCartButtons.forEach(button => {
        button.addEventListener('click', () => {
            const productId = button.dataset.productId;
            addToCart(productId);
        });
    });

    checkoutButton.addEventListener('click', () => {
        // Store cart in session storage before redirecting
        sessionStorage.setItem('cart', JSON.stringify(cart));

        // Use fetch to send cart data to checkout.php before redirecting
        fetch('checkout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `cart=${encodeURIComponent(JSON.stringify(cart))}`
        }).then(response => response.text())
          .then(() => {
              window.location.href = 'checkout.php';
          }).catch(error => {
              console.error('Fetch error:', error);
          });
    });

    function addToCart(productId) {
        fetch(`get_product.php?id=${productId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(product => {
                console.log('Product added:', product); // Log product for debugging
                const existingItem = cart.find(item => item.id === product.id);
                if (existingItem) {
                    existingItem.quantity += 1; // Increment quantity if item already in cart
                } else {
                    product.quantity = 1; // Add quantity field
                    cart.push(product);
                }
                updateCartDisplay();
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
            `;
            cartItems.appendChild(itemElement);
            total += item.price * item.quantity;
        });

        cartTotal.textContent = total.toFixed(2);
    }

    fetch('order.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Order placed successfully! Order ID: ' + data.orderId);
            cart = []; // Empty the cart
            updateCartDisplay();
            localStorage.removeItem('cart'); // Clear cart from local storage
            sessionStorage.removeItem('cart'); // Clear cart from session storage
        } else {
            alert('Error placing order: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
});
