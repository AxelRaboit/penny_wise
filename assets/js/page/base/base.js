import { initializeClock } from './component/clock.js';
import {initializeAddFlash} from './component/add_flash.js';
import {handlePageSpinner} from "@component/Spinner/page_spinner.js";
import {initializeBurgerMenu} from "./component/burger_menu";

document.addEventListener('DOMContentLoaded', function() {
    handlePageSpinner('base-page', 'loadingPageSpinner');
    initializeAddFlash();
    initializeBurgerMenu();
    initializeClock();
});
