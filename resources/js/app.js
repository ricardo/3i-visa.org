import './bootstrap';
import $ from 'jquery';

import initDialog from './site/dialog.js';
import initMobileMenu from './site/menu.js';
import initDropdown from './site/dropdown.js';

( () => {
	initDialog();
	initMobileMenu();
	initDropdown();
} )();

// Export $ to global scope
window.$ = $;
window.jQuery = $;