import { attachModalEvents } from '../component/modal.js';

attachModalEvents({
    modalId: 'copyBillsModal',
    modalTitle: 'Copy previous month\'s bills',
    modalMessage: 'Are you sure you want to copy the bills from the previous month to the current month?',
    triggerButtonSelector: '.js-copy-bills-button',
    confirmButtonId: 'confirmCopyBills',
    confirmLabel: 'Copy',
    cancelButtonId: 'cancelCopyBills',
    cancelLabel: 'Cancel',
});

attachModalEvents({
    modalId: 'copyExpensesModal',
    modalTitle: 'Copy previous month\'s expenses',
    modalMessage: 'Are you sure you want to copy the expenses from the previous month to the current month?',
    triggerButtonSelector: '.js-copy-expenses-button',
    confirmButtonId: 'confirmCopyExpenses',
    confirmLabel: 'Copy',
    cancelButtonId: 'cancelCopyExpenses',
    cancelLabel: 'Cancel',
});

attachModalEvents({
    modalId: 'createPreviousWalletModal',
    modalTitle: 'Create previous month\'s wallet',
    modalMessage: 'Are you sure you want to create a wallet for the previous month?',
    triggerButtonSelector: '.js-create-previous-wallet-button',
    confirmButtonId: 'confirmCreatePreviousWallet',
    confirmLabel: 'Create',
    cancelButtonId: 'cancelCreatePreviousWallet',
    cancelLabel: 'Cancel',
});

attachModalEvents({
    modalId: 'createNextWalletModal',
    modalTitle: 'Create next month\'s wallet',
    modalMessage: 'Are you sure you want to create a wallet for the next month?',
    triggerButtonSelector: '.js-create-next-wallet-button',
    confirmButtonId: 'confirmCreateNextWallet',
    confirmLabel: 'Create',
    cancelButtonId: 'cancelCreateNextWallet',
    cancelLabel: 'Cancel',
});

attachModalEvents({
    modalId: 'deleteWalletModal',
    modalTitle: 'Delete month\'s wallet and transactions',
    modalMessage: 'Are you sure you want to delete the monthly wallet?',
    triggerButtonSelector: '.js-delete-wallet-button',
    confirmButtonId: 'confirmDeleteWallet',
    confirmLabel: 'Delete',
    cancelButtonId: 'cancelDeleteWallet',
    cancelLabel: 'Cancel',
});

attachModalEvents({
    modalId: 'copyLeftToSpendModal',
    modalTitle: 'Copy left to spend from previous month',
    modalMessage: 'Are you sure you want to copy the left to spend from the previous month to the current month?',
    triggerButtonSelector: '.js-copy-left-to-spend-button',
    confirmButtonId: 'confirmCopyLeftToSpend',
    confirmLabel: 'Initialize',
    cancelButtonId: 'cancelCopyLeftToSpend',
    cancelLabel: 'Cancel',
});

attachModalEvents({
    modalId: 'copyIncomesModal',
    modalTitle: 'Copy previous month\'s incomes',
    modalMessage: 'Are you sure you want to copy the incomes from the previous month to the current month?',
    triggerButtonSelector: '.js-copy-incomes-button',
    confirmButtonId: 'confirmCopyIncomes',
    confirmLabel: 'Copy',
    cancelButtonId: 'cancelCopyIncomes',
    cancelLabel: 'Cancel',
});

attachModalEvents({
    modalId: 'copyDebtsModal',
    modalTitle: 'Copy previous month\'s debts',
    modalMessage: 'Are you sure you want to copy the debts from the previous month to the current month?',
    triggerButtonSelector: '.js-copy-debts-button',
    confirmButtonId: 'confirmCopyDebts',
    confirmLabel: 'Copy',
    cancelButtonId: 'cancelCopyDebts',
    cancelLabel: 'Cancel',
});

attachModalEvents({
    modalId: 'deleteTransactionsFromCategoryModal',
    modalTitle: 'Delete transactions',
    modalMessage: 'Are you sure you want to delete all transactions from the selected category?',
    triggerButtonSelector: '.js-delete-transactions-from-category-button',
    confirmButtonId: 'confirmDeleteTransactionsFromCategory',
    confirmLabel: 'Delete',
    cancelButtonId: 'cancelDeleteTransactionsFromCategory',
    cancelLabel: 'Cancel',
});
