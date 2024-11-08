import { attachModalEvents } from '@component/modal/modal.js';

export function initializeModals() {
  attachModalEvents({
    modalId: 'copyBillsModal',
    modalTitle: "Copy previous month's bills",
    modalMessageHeader:
      'Are you sure you want to copy the bills from the previous month to the current month?',
    triggerButtonSelector: '.js-copy-bills-button',
    confirmButtonId: 'confirmCopyBills',
    confirmLabel: 'Copy',
    cancelButtonId: 'cancelCopyBills',
    cancelLabel: 'Cancel',
  });

  attachModalEvents({
    modalId: 'copyExpensesModal',
    modalTitle: "Copy previous month's expenses",
    modalMessageHeader:
      'Are you sure you want to copy the expenses from the previous month to the current month?',
    triggerButtonSelector: '.js-copy-expenses-button',
    confirmButtonId: 'confirmCopyExpenses',
    confirmLabel: 'Copy',
    cancelButtonId: 'cancelCopyExpenses',
    cancelLabel: 'Cancel',
  });

  attachModalEvents({
    modalId: 'createPreviousWalletModal',
    modalTitle: "Create previous month's wallet",
    modalMessageHeader:
      'Are you sure you want to create a wallet for the previous month?',
    triggerButtonSelector: '.js-create-previous-wallet-button',
    confirmButtonId: 'confirmCreatePreviousWallet',
    confirmLabel: 'Create',
    cancelButtonId: 'cancelCreatePreviousWallet',
    cancelLabel: 'Cancel',
  });

  attachModalEvents({
    modalId: 'createNextWalletModal',
    modalTitle: "Create next month's wallet",
    modalMessageHeader:
      'Are you sure you want to create a wallet for the next month?',
    triggerButtonSelector: '.js-create-next-wallet-button',
    confirmButtonId: 'confirmCreateNextWallet',
    confirmLabel: 'Create',
    cancelButtonId: 'cancelCreateNextWallet',
    cancelLabel: 'Cancel',
  });

  attachModalEvents({
    modalId: 'deleteWalletModal',
    modalTitle: "Delete wallet and all of it's transactions",
    modalMessageHeader: 'Are you sure you want to delete the wallet?',
    triggerButtonSelector: '.js-delete-wallet-button',
    confirmButtonId: 'confirmDeleteWallet',
    confirmLabel: 'Delete',
    cancelButtonId: 'cancelDeleteWallet',
    cancelLabel: 'Cancel',
    isDeleteAction: true,
  });

  attachModalEvents({
    modalId: 'copyLeftToSpendModal',
    modalTitle: 'Copy left to spend from previous month',
    modalMessageHeader:
      'Are you sure you want to copy the left to spend from the previous month to the current month?',
    triggerButtonSelector: '.js-copy-left-to-spend-button',
    confirmButtonId: 'confirmCopyLeftToSpend',
    confirmLabel: 'Initialize',
    cancelButtonId: 'cancelCopyLeftToSpend',
    cancelLabel: 'Cancel',
  });

  attachModalEvents({
    modalId: 'resetStartBalanceModal',
    modalTitle: 'Reset starting balance',
    modalMessageHeader:
      'Are you sure you want to reset the starting balance of the current month?',
    triggerButtonSelector: '.js-reset-start-balance-button',
    confirmButtonId: 'confirmResetStartBalance',
    confirmLabel: 'Reset',
    cancelButtonId: 'cancelResetStartBalance',
    cancelLabel: 'Cancel',
    isDeleteAction: true,
  });

  attachModalEvents({
    modalId: 'copyIncomesModal',
    modalTitle: "Copy previous month's incomes",
    modalMessageHeader:
      'Are you sure you want to copy the incomes from the previous month to the current month?',
    triggerButtonSelector: '.js-copy-incomes-button',
    confirmButtonId: 'confirmCopyIncomes',
    confirmLabel: 'Copy',
    cancelButtonId: 'cancelCopyIncomes',
    cancelLabel: 'Cancel',
  });

  attachModalEvents({
    modalId: 'copyDebtsModal',
    modalTitle: "Copy previous month's debts",
    modalMessageHeader:
      'Are you sure you want to copy the debts from the previous month to the current month?',
    triggerButtonSelector: '.js-copy-debts-button',
    confirmButtonId: 'confirmCopyDebts',
    confirmLabel: 'Copy',
    cancelButtonId: 'cancelCopyDebts',
    cancelLabel: 'Cancel',
  });

  attachModalEvents({
    modalId: 'deleteTransactionsFromCategoryModal',
    modalTitle: 'Delete transactions',
    modalMessageHeader:
      'Are you sure you want to delete all transactions from the selected category?',
    triggerButtonSelector: '.js-delete-transactions-from-category-button',
    confirmButtonId: 'confirmDeleteTransactionsFromCategory',
    confirmLabel: 'Delete',
    cancelButtonId: 'cancelDeleteTransactionsFromCategory',
    cancelLabel: 'Cancel',
    isDeleteAction: true,
  });
}
