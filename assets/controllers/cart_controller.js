import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['quantity', 'addButton'];
    static values = {
        productId: Number,
        locale: String
    };

    connect() {
    }

    async addToCart(event) {
        event.preventDefault();

        const quantity = this.hasQuantityTarget ? parseInt(this.quantityTarget.value) : 1;
        const productId = this.productIdValue;
        const locale = this.localeValue || 'fr';

        if (this.hasAddButtonTarget) {
            this.addButtonTarget.disabled = true;
            this.addButtonTarget.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Ajout...';
        }

        try {
            const formData = new FormData();
            formData.append('quantity', quantity);

            const response = await fetch(`/api/${locale}/cart/add/${productId}`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (response.ok && data.success) {
                this.showNotification(data.message, 'success');

                // event pour update badge panier
                window.dispatchEvent(new CustomEvent('cart-updated', {
                    detail: {
                        count: data.cartCount,
                        total: data.cartTotal
                    }
                }));

                if (this.hasQuantityTarget) {
                    this.quantityTarget.value = 1;
                }
            } else {
                this.showNotification(data.error || 'Erreur lors de l\'ajout au panier', 'error');
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
            this.showNotification('Erreur de connexion', 'error');
        } finally {
            if (this.hasAddButtonTarget) {
                this.addButtonTarget.disabled = false;
                this.addButtonTarget.innerHTML = '<i class="bi bi-cart-plus"></i> Ajouter au panier';
            }
        }
    }

    showNotification(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        document.body.appendChild(alertDiv);

        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 150);
        }, 3000);
    }
}
