import { initializeClock } from './component/clock.js';
import {initializeAddFlash} from './component/add_flash.js';
import {handlePageSpinner} from "@component/Spinner/page_spinner.js";
import {initializeBurgerMenu} from "./component/burger_menu";
import {initializeLeftSideMenuContent} from "@page/base/leftSideMenu/left_side_menu.js";

document.addEventListener('DOMContentLoaded', function() {
    handlePageSpinner('base-page', 'loadingPageSpinner');
    initializeLeftSideMenuContent();
    initializeAddFlash();
    initializeBurgerMenu();
    initializeClock();
});
