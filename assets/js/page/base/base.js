import { initializeClock } from './component/clock.js';
import {initializeAddFlash} from './component/addFlash.js';
import {initializeSideMenu} from './component/sideMenu.js';
import {handlePageSpinner} from "@component/Spinner/page-spinner.js";

document.addEventListener('DOMContentLoaded', function() {
    handlePageSpinner('base-page', 'loadingPageSpinner');
    initializeSideMenu();
    initializeAddFlash();
    initializeClock();
});
