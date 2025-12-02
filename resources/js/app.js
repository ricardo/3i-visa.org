import './bootstrap';
import $ from 'jquery';

import initDialog from './site/dialog.js';
import initMobileMenu from './site/menu.js';

( () => {
	initDialog();
	initMobileMenu();
} )();

// Export $ to global scope
window.$ = $;
window.jQuery = $;