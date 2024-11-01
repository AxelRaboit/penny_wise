import { attachModalEvents } from '@component/Modal/modal.js';
import {handlePageSpinner} from "@component/Spinner/page_spinner.js";

document.addEventListener('DOMContentLoaded', function () {
    handlePageSpinner('transaction-tag-list-page', 'loadingPageSpinner');
});

attachModalEvents({
    modalId: 'deleteTransactionTagModal',
    modalTitle: 'Delete transaction tag',
    modalMessageHeader: 'Are you sure you want to delete the transaction tag?',
    triggerButtonSelector: '.js-delete-transaction-tag-button',
    confirmButtonId: 'confirmDeleteTransactionTag',
    confirmLabel: 'Delete',
    cancelButtonId: 'cancelDeleteTransactionTag',
    cancelLabel: 'Cancel',
    isDeleteAction: true,
});