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
    modalId: 'createPreviousBudgetModal',
    modalTitle: 'Create previous month\'s budget',
    modalMessage: 'Are you sure you want to create a budget for the previous month?',
    triggerButtonSelector: '.js-create-previous-budget-button',
    confirmButtonId: 'confirmCreatePreviousBudget',
    confirmLabel: 'Create',
    cancelButtonId: 'cancelCreatePreviousBudget',
    cancelLabel: 'Cancel',
});

attachModalEvents({
    modalId: 'createNextBudgetModal',
    modalTitle: 'Create next month\'s budget',
    modalMessage: 'Are you sure you want to create a budget for the next month?',
    triggerButtonSelector: '.js-create-next-budget-button',
    confirmButtonId: 'confirmCreateNextBudget',
    confirmLabel: 'Create',
    cancelButtonId: 'cancelCreateNextBudget',
    cancelLabel: 'Cancel',
});

attachModalEvents({
    modalId: 'deleteBudgetModal',
    modalTitle: 'Delete month\'s budget and transactions',
    modalMessage: 'Are you sure you want to delete the monthly budget?',
    triggerButtonSelector: '.js-delete-budget-button',
    confirmButtonId: 'confirmDeleteBudget',
    confirmLabel: 'Delete',
    cancelButtonId: 'cancelDeleteBudget',
    cancelLabel: 'Cancel',
});
