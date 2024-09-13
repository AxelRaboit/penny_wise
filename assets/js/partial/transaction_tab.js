import { attachModalEvents } from '../component/modal.js';

attachModalEvents({
    modalId: 'deleteTransactionModal',
    modalTitle: 'Confirm Deletion',
    modalMessage: 'Are you sure you want to delete this transaction?',
    triggerButtonSelector: '.js-delete-button',
    confirmButtonId: 'confirmTransactionDelete',
    confirmLabel: 'Delete',
    cancelButtonId: 'cancelTransactionDelete',
    cancelLabel: 'Cancel',
});
