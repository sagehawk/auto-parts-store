document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('orderModal');
    const closeBtn = document.getElementsByClassName('close')[0];
    const viewButtons = document.querySelectorAll('.view-order');

    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            fetch(`get_order_details.php?id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('orderDetails').innerHTML = `
                        <p>Order ID: ${data.orderId}</p>
                        <p>Customer Name: ${data.customerName}</p>
                        <p>Customer Email: ${data.customerEmail}</p>
                        <p>Shipping Address: ${data.shippingAddress}</p>
                        <p>Total: $${data.total}</p>
                        <h3>Items:</h3>
                        <ul>
                            ${data.items.map(item => `<li>${item.quantity} x Part #${item.id}</li>`).join('')}
                        </ul>
                        <p>Date: ${data.date}</p>
                    `;
                    modal.style.display = 'block';
                });
        });
    });

    closeBtn.onclick = function() {
        modal.style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
});