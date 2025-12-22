import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['count', 'total'];
    static values = {
        locale: String
    };

    connect() {
        this.loadCartCount();

        this.boundUpdateCart = this.updateCart.bind(this);
        window.addEventListener('cart-updated', this.boundUpdateCart);
    }

    disconnect() {
        window.removeEventListener('cart-updated', this.boundUpdateCart);
    }

    async loadCartCount() {
        const locale = this.localeValue || 'fr';

        try {
            const response = await fetch(`/api/${locale}/cart/count`);
            const data = await response.json();

            this.updateBadge(data.count, data.total);
        } catch (error) {
            console.error('Error loading cart count:', error);
        }
    }

    updateCart(event) {
        const { count, total } = event.detail;
        this.updateBadge(count, total);
    }

    updateBadge(count, total) {
        if (this.hasCountTarget) {
            this.countTarget.textContent = count;

            if (count > 0) {
                this.countTarget.classList.remove('d-none');
            } else {
                this.countTarget.classList.add('d-none');
            }
        }

        if (this.hasTotalTarget) {
            this.totalTarget.textContent = total.toFixed(2);
        }
    }
}
