import { initializeClock } from './clock.js';
import {initializeAddFlash} from './addFlash.js';

document.addEventListener('DOMContentLoaded', function() {
    initializeAddFlash();
    initializeClock();
});
