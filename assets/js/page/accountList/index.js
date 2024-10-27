import { initializeWalletListModals } from "./account_list.js";
import { initializeAccountListSideMenu } from "./rightSideMenu/right_side_menu.js";
import { handlePageSpinner } from "@component/Spinner/page_spinner.js";

document.addEventListener('DOMContentLoaded', function () {
    handlePageSpinner('account-list-page', 'loadingPageSpinner');
    initializeAccountListSideMenu();
    initializeWalletListModals();
});
