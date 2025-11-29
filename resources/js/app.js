import './bootstrap';
import $ from 'jquery';

import initDialog from './site/dialog.js';

( () => {
	initDialog();
} )();

// Export $ to global scope
window.$ = $;
window.jQuery = $;