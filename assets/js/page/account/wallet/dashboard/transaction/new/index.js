import { handlePageSpinner } from '@component/spinner/page_spinner.js';

document.addEventListener('DOMContentLoaded', function () {
  handlePageSpinner(
    'account-wallet-dashboard-transaction-new-page',
    'loadingPageSpinner',
  );
});
