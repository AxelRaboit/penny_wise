import {initializeWalletListModals} from "./account_list.js";
import {handlePageSpinner} from "@component/Spinner/page_spinner.js";
import {initializeAccountListSideMenu} from "./side_menu.js";

document.addEventListener('DOMContentLoaded', function () {
    handlePageSpinner('account-list-page', 'loadingPageSpinner');
    initializeAccountListSideMenu();
    initializeWalletListModals();
});
