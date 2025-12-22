import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['quantity', 'itemTotal', 'updateButton', 'removeButton'];
    static values = {
        itemId: Number,
        locale: String
    };

    connect() {
    }

    async increase(event) {
        event.preventDefault();
        await this.updateQuantity('increase');
    }

    async decrease(event) {
        event.preventDefault();
        await this.updateQuantity('decrease');
    }

    async remove(event) {
        event.preventDefault();

        if (!confirm('Êtes-vous sûr de vouloir retirer cet article ?')) {
            return;
        }

        const itemId = this.itemIdValue;
        const locale = this.localeValue || 'fr';

        if (this.hasRemoveButtonTarget) {
            this.removeButtonTarget.disabled = true;
        }

        try {
            const formData = new FormData();

            const response = await fetch(`/api/${locale}/cart/remove/${itemId}`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (response.ok && data.success) {
                this.showNotification(data.message, 'success');

                window.dispatchEvent(new CustomEvent('cart-updated', {
                    detail: {
                        count: data.cartCount,
                        total: data.cartTotal
                    }
                }));

                this.element.remove();

                if (data.cartCount === 0) {
                    location.reload();
                }
            } else {
                this.showNotification(data.error || 'Erreur lors de la suppression', 'error');
                if (this.hasRemoveButtonTarget) {
                    this.removeButtonTarget.disabled = false;
                }
            }
        } catch (error) {
            console.error('Error removing item:', error);
            this.showNotification('Erreur de connexion', 'error');
            if (this.hasRemoveButtonTarget) {
                this.removeButtonTarget.disabled = false;
            }
        }
    }

    async updateQuantity(action) {
        const itemId = this.itemIdValue;
        const locale = this.localeValue || 'fr';

        try {
            const formData = new FormData();
            formData.append('action', action);

            const response = await fetch(`/api/${locale}/cart/update/${itemId}`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (response.ok && data.success) {
                if (this.hasQuantityTarget) {
                    this.quantityTarget.value = data.itemQuantity;
                }

                if (this.hasItemTotalTarget) {
                    this.itemTotalTarget.textContent = data.itemTotal.toFixed(2) + ' €';
                }

                window.dispatchEvent(new CustomEvent('cart-updated', {
                    detail: {
                        count: data.cartCount,
                        total: data.cartTotal
                    }
                }));

                const cartTotalElement = document.querySelector('[data-cart-total]');
                if (cartTotalElement) {
                    cartTotalElement.textContent = data.cartTotal.toFixed(2) + ' €';
                }
            } else {
                this.showNotification(data.error || 'Erreur lors de la mise à jour', 'error');
            }
        } catch (error) {
            console.error('Error updating quantity:', error);
            this.showNotification('Erreur de connexion', 'error');
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
