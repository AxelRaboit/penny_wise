import { initializeWalletListModals } from "./account_list.js";
import { initializeAccountListSideMenu } from "./right_side_menu/right_side_menu.js";
import { handlePageSpinner } from "@component/spinner/page_spinner.js";

document.addEventListener('DOMContentLoaded', function () {
    handlePageSpinner('account-list-page', 'loadingPageSpinner');
    initializeAccountListSideMenu();
    initializeWalletListModals();
});
