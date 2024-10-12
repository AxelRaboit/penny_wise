import {initializeChartButtons} from "./chart.js";
import {initializeMonthlyModals} from "./monthly.js";
import {initializeMonthlySideMenu} from "./side_menu.js";
import {handlePageSpinner} from "../../component/Spinner/page-spinner.js";

document.addEventListener('DOMContentLoaded', function () {
    handlePageSpinner('wallet-dashboard-page', 'loadingPageSpinner');
    initializeChartButtons();
    initializeMonthlyModals();
    initializeMonthlySideMenu();
});