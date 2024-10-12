import {initializeWalletListModals} from "./account_list.js";
import {handlePageSpinner} from "../../component/Spinner/page-spinner.js";

document.addEventListener('DOMContentLoaded', function () {
    handlePageSpinner('account-list-page', 'loadingPageSpinner');
    initializeWalletListModals();
});
