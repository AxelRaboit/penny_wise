import { initializeClock } from './component/clock.js';
import {initializeAddFlash} from './component/addFlash.js';
import {initializeSideMenu} from "./component/sideMenu.js";

document.addEventListener('DOMContentLoaded', function() {
    initializeSideMenu();
    initializeAddFlash();
    initializeClock();
});
