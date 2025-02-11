import { attachModalEvents } from '@component/modal/modal.js';

attachModalEvents({
  modalId: 'deleteTransactionModal',
  modalTitle: 'Confirm Deletion',
  modalMessageHeader: 'Are you sure you want to delete this transaction?',
  triggerButtonSelector: '.js-delete-button',
  confirmButtonId: 'confirmTransactionDelete',
  confirmLabel: 'Delete',
  cancelButtonId: 'cancelTransactionDelete',
  cancelLabel: 'Cancel',
  isDeleteAction: true,
});
