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
        window.location.href = 'checkout.php';
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
                cart.push(product);
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
                <p>Quantity: 1</p>
            `;
            cartItems.appendChild(itemElement);
            total += parseFloat(item.price);
        });

        cartTotal.textContent = total.toFixed(2);
    }
});
