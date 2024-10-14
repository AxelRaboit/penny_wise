import { initializeClock } from './component/clock.js';
import {initializeAddFlash} from './component/addFlash.js';

document.addEventListener('DOMContentLoaded', function() {
    initializeAddFlash();
    initializeClock();
});
