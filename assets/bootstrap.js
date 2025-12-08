import { startStimulusApp } from '@symfony/stimulus-bundle';

const app = startStimulusApp();

// Import custom controllers
import CartController from './controllers/cart_controller.js';
import CartBadgeController from './controllers/cart-badge_controller.js';
import CartItemController from './controllers/cart-item_controller.js';

// Register custom controllers
app.register('cart', CartController);
app.register('cart-badge', CartBadgeController);
app.register('cart-item', CartItemController);
