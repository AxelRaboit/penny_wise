import { loadBudgetFormInModal } from 'budget';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelector('#add-budget-button').addEventListener('click', () => {
        loadBudgetFormInModal('/budget/new');
    });
});