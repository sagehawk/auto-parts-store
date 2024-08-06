document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('orderModal');
    const closeBtn = document.querySelector('.close');
    const viewButtons = document.querySelectorAll('.view-order');

    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            fetch(`get_order_details.php?id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    document.getElementById('orderDetails').innerHTML = `
                        <p><strong>Order ID:</strong> ${data.orderId}</p>
                        <p><strong>Customer Name:</strong> ${data.customer_name}</p>
                        <p><strong>Customer Contact:</strong> ${data.customer_contact}</p>
                        <p><strong>Shipping Address:</strong> ${data.customer_street}, ${data.customer_city}</p>
                        <p><strong>Total Cost:</strong> $${data.total_cost.toFixed(2)}</p>
                        <p><strong>Shipping Cost:</strong> $${data.shipping_cost.toFixed(2)}</p>
                        <p><strong>Status:</strong> ${data.status}</p>
                        <h3>Items:</h3>
                        <ul>
                            ${data.items.map(item => `<li>${item.quantity} x Part #${item.id}</li>`).join('')}
                        </ul>
                        <p><strong>Order Date:</strong> ${data.date}</p>
                    `;
                    modal.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while fetching order details.');
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