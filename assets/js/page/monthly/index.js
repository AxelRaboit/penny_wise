import {initializeChartButtons} from "./chart.js";
import {initializeMonthlyModals} from "./monthly.js";
import {initializeMonthlySideMenu} from "./side_menu.js";

document.addEventListener('DOMContentLoaded', function () {
    initializeChartButtons();
    initializeMonthlyModals();
    initializeMonthlySideMenu();
});