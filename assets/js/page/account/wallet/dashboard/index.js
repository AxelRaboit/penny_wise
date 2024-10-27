import {initializeChartButtons} from "./chart/chart.js";
import {initializeModals} from "./modal/modal.js";
import {initializeSideMenu} from "./rightSideMenu/right_side_menu.js";
import {handlePageSpinner} from "@component/Spinner/page_spinner.js";

document.addEventListener('DOMContentLoaded', function () {
    handlePageSpinner('account-wallet-dashboard-page', 'loadingPageSpinner');
    initializeChartButtons();
    initializeModals();
    initializeSideMenu();
});