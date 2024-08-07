document.addEventListener('DOMContentLoaded', function() {
    console.log('Checkout JS loaded');
    const checkoutForm = document.getElementById('checkout-form');
    
    if (checkoutForm) {
        console.log('Checkout form found');
        checkoutForm.addEventListener('submit', function(e) {
            console.log('Form submitted');
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('order.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showOrderConfirmation(data.orderId);
                    // Clear the cart
                    sessionStorage.removeItem('cart');
                } else {
                    showErrorModal('Failed to place order: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorModal('An unexpected error occurred. Please try again.');
            });
        });
    }

    function showOrderConfirmation(orderId) {
        const modal = document.createElement('div');
        modal.className = 'modal order-confirmation';
        modal.innerHTML = `
            <div class="modal-content">
                <span class="close">&times;</span>
                <div class="confirmation-icon">✓</div>
                <h2>Order Placed Successfully!</h2>
                <p>Your order has been received and is being processed.</p>
                <p class="order-id">Order ID: ${orderId}</p>
                <button class="btn-primary" onclick="window.location.href='index.php'">Continue Shopping</button>
            </div>
        `;
        document.body.appendChild(modal);

        const closeBtn = modal.querySelector('.close');
        closeBtn.onclick = function() {
            document.body.removeChild(modal);
            window.location.href = 'index.php'; // Redirect to home page after closing
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                document.body.removeChild(modal);
                window.location.href = 'index.php'; // Redirect to home page after closing
            }
        }

        modal.style.display = 'block';
    }

    function showErrorModal(message) {
        const modal = document.createElement('div');
        modal.className = 'modal error-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <span class="close">&times;</span>
                <div class="error-icon">!</div>
                <h2>Error</h2>
                <p>${message}</p>
                <button class="btn-secondary" onclick="closeModal(this)">Close</button>
            </div>
        `;
        document.body.appendChild(modal);

        const closeBtn = modal.querySelector('.close');
        closeBtn.onclick = function() {
            document.body.removeChild(modal);
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                document.body.removeChild(modal);
            }
        }

        modal.style.display = 'block';
    }

    function closeModal(element) {
        const modal = element.closest('.modal');
        document.body.removeChild(modal);
    }
});