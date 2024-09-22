import { attachModalEvents } from '../component/modal.js';

attachModalEvents({
    modalId: 'deleteTransactionTagModal',
    modalTitle: 'Delete transaction tag',
    modalMessage: 'Are you sure you want to delete the transaction tag?',
    triggerButtonSelector: '.js-delete-transaction-tag-button',
    confirmButtonId: 'confirmDeleteTransactionTag',
    confirmLabel: 'Delete',
    cancelButtonId: 'cancelDeleteTransactionTag',
    cancelLabel: 'Cancel',
});