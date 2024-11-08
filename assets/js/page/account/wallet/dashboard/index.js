import { initializeChartButtons } from './chart/chart.js';
import { initializeModals } from './modal/modal.js';
import { initializeRightSideMenuContent } from './right_side_menu/right_side_menu.js';
import { handlePageSpinner } from '@component/spinner/page_spinner.js';

document.addEventListener('DOMContentLoaded', function () {
  handlePageSpinner('account-wallet-dashboard-page', 'loadingPageSpinner');
  initializeChartButtons();
  initializeModals();
  initializeRightSideMenuContent();
});
