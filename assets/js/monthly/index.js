import { loadTransactionFormInModal } from 'monthly';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelector('#add-transaction-button').addEventListener('click', () => {
        loadTransactionFormInModal('/transaction/new');
    });
});