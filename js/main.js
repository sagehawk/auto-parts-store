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
            .then(response => response.json())
            .then(product => {
                cart.push(product);
                updateCartDisplay();
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

    // Form validation
    function validateCheckoutForm() {
        const form = document.getElementById('checkout-form');
        const name = form.elements['name'].value;
        const email = form.elements['email'].value;
        const address = form.elements['address'].value;
        const cardNumber = form.elements['card-number'].value;
        const cardExpiry = form.elements['card-expiry'].value;
        const cardCVV = form.elements['card-cvv'].value;

        if (!name || !email || !address || !cardNumber || !cardExpiry || !cardCVV) {
            alert('Please fill in all fields');
            return false;
        }

        // Add more specific validation (e.g., email format, card number format) here

        return true;
    }

    // Load cart from session storage on checkout page
    if (window.location.pathname.includes('checkout.php')) {
        const storedCart = JSON.parse(sessionStorage.getItem('cart') || '[]');
        cart = storedCart;
        updateCartDisplay();
    }
});
