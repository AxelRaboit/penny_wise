import './bootstrap.js';

Turbo.session.drive = false;
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';
import './js/partial/form/asterisk.js';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
