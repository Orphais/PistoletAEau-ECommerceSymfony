import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['searchInput', 'autocompleteResults', 'searchResults', 'loadingIndicator', 'noResults'];
    static values = {
        locale: String
    };

    connect() {
        this.searchTimeout = null;
        this.selectedIndex = -1;
    }

    disconnect() {
        this.hideAutocomplete();
    }

    handleKeyup(event) {
        const key = event.key;

        if (key === 'ArrowDown') {
            event.preventDefault();
            this.navigateAutocomplete(1);
        } else if (key === 'ArrowUp') {
            event.preventDefault();
            this.navigateAutocomplete(-1);
        } else if (key === 'Enter') {
            event.preventDefault();
            this.selectCurrentItem();
        } else if (key === 'Escape') {
            this.hideAutocomplete();
        }
    }

    search(event) {
        const query = event.target.value.trim();

        clearTimeout(this.searchTimeout);

        if (query.length < 2) {
            this.hideAutocomplete();
            this.showDefaultMessage();
            return;
        }

        // debounce 300ms
        this.searchTimeout = setTimeout(() => {
            this.performAutocomplete(query);
            this.performSearch(query);
        }, 300);
    }

    async performAutocomplete(query) {
        const locale = this.localeValue || 'fr';
        const url = `/${locale}/api/search/autocomplete?query=${encodeURIComponent(query)}`;

        try {
            const response = await fetch(url);
            const data = await response.json();

            if (data.results && data.results.length > 0) {
                this.displayAutocompleteResults(data.results);
            } else {
                this.hideAutocomplete();
            }
        } catch (error) {
            console.error('Autocomplete error:', error);
        }
    }

    async performSearch(query) {
        const locale = this.localeValue || 'fr';

        this.showLoading();

        try {
            const response = await fetch(`/${locale}/api/search/products?q=${encodeURIComponent(query)}`);
            const data = await response.json();

            this.hideLoading();

            if (data.products && data.products.length > 0) {
                this.displaySearchResults(data.products);
            } else {
                this.showNoResults();
            }
        } catch (error) {
            console.error('Search error:', error);
            this.hideLoading();
            this.showNoResults();
        }
    }

    displayAutocompleteResults(results) {
        this.selectedIndex = -1;

        this.autocompleteResultsTarget.innerHTML = results.map((result, index) => `
            <button type="button"
                    class="list-group-item list-group-item-action"
                    data-index="${index}"
                    data-product-id="${result.id}"
                    data-action="click->product-search#selectItem">
                <div class="d-flex align-items-center">
                    ${result.image ? `
                        <img src="${result.image}"
                             alt="${result.text}"
                             style="width: 40px; height: 40px; object-fit: cover;"
                             class="me-3">
                    ` : `
                        <div class="bg-secondary me-3" style="width: 40px; height: 40px;"></div>
                    `}
                    <div class="flex-grow-1">
                        <div>${result.text}</div>
                        <small class="text-muted">${result.price.toFixed(2)} €</small>
                    </div>
                    <span class="badge bg-${this.getStatusBadgeClass(result.status)}">
                        ${this.getStatusText(result.status)}
                    </span>
                </div>
            </button>
        `).join('');

        this.autocompleteResultsTarget.style.display = 'block';
    }

    displaySearchResults(products) {
        const locale = this.localeValue || 'fr';

        this.searchResultsTarget.innerHTML = `
            <h4 class="mb-3">${products.length} produit(s) trouvé(s)</h4>
            <div class="row">
                ${products.map(product => `
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            ${product.image ? `
                                <img src="${product.image}"
                                     class="card-img-top"
                                     alt="${product.name}"
                                     style="height: 200px; object-fit: cover;">
                            ` : `
                                <div class="bg-secondary" style="height: 200px;"></div>
                            `}
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">${product.name}</h5>
                                <p class="card-text text-muted small">${product.description ? product.description.substring(0, 100) + '...' : ''}</p>
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="h5 mb-0">${product.price.toFixed(2)} €</span>
                                        <span class="badge bg-${this.getStatusBadgeClass(product.status)}">
                                            ${this.getStatusText(product.status)}
                                        </span>
                                    </div>
                                    <a href="${product.url}" class="btn btn-primary w-100">
                                        Voir le produit
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;

        this.searchResultsTarget.style.display = 'block';
        this.noResultsTarget.style.display = 'none';
    }

    navigateAutocomplete(direction) {
        const items = this.autocompleteResultsTarget.querySelectorAll('.list-group-item');

        if (items.length === 0) return;

        if (this.selectedIndex >= 0) {
            items[this.selectedIndex].classList.remove('active');
        }

        this.selectedIndex += direction;

        if (this.selectedIndex < 0) {
            this.selectedIndex = items.length - 1;
        } else if (this.selectedIndex >= items.length) {
            this.selectedIndex = 0;
        }

        items[this.selectedIndex].classList.add('active');
        items[this.selectedIndex].scrollIntoView({ block: 'nearest' });
    }

    selectCurrentItem() {
        const items = this.autocompleteResultsTarget.querySelectorAll('.list-group-item');

        if (this.selectedIndex >= 0 && this.selectedIndex < items.length) {
            items[this.selectedIndex].click();
        }
    }

    selectItem(event) {
        const productId = event.currentTarget.dataset.productId;
        const locale = this.localeValue || 'fr';

        window.location.href = `/${locale}/product/${productId}`;
    }

    hideAutocomplete() {
        if (this.hasAutocompleteResultsTarget) {
            this.autocompleteResultsTarget.style.display = 'none';
            this.autocompleteResultsTarget.innerHTML = '';
        }
        this.selectedIndex = -1;
    }

    showLoading() {
        this.loadingIndicatorTarget.style.display = 'block';
        this.searchResultsTarget.style.display = 'none';
        this.noResultsTarget.style.display = 'none';
    }

    hideLoading() {
        this.loadingIndicatorTarget.style.display = 'none';
    }

    showDefaultMessage() {
        const locale = this.localeValue || 'fr';
        const message = locale === 'fr'
            ? 'Commencez à taper pour rechercher des produits'
            : 'Start typing to search for products';

        this.searchResultsTarget.innerHTML = `
            <div class="text-center text-muted py-5">
                <i class="bi bi-search" style="font-size: 3rem;"></i>
                <p class="mt-3">${message}</p>
            </div>
        `;
        this.searchResultsTarget.style.display = 'block';
        this.noResultsTarget.style.display = 'none';
    }

    showNoResults() {
        this.searchResultsTarget.style.display = 'none';
        this.noResultsTarget.style.display = 'block';
    }

    clear() {
        this.searchInputTarget.value = '';
        this.hideAutocomplete();
        this.showDefaultMessage();
        this.searchInputTarget.focus();
    }

    getStatusBadgeClass(status) {
        switch (status) {
            case 'AVAILABLE':
                return 'success';
            case 'PREORDER':
                return 'warning';
            case 'OUT_OF_STOCK':
                return 'danger';
            default:
                return 'secondary';
        }
    }

    getStatusText(status) {
        switch (status) {
            case 'AVAILABLE':
                return 'Disponible';
            case 'PREORDER':
                return 'Précommande';
            case 'OUT_OF_STOCK':
                return 'Rupture';
            default:
                return status;
        }
    }
}
