document.addEventListener('DOMContentLoaded', function () {
    const checkbox = document.getElementById('transaction_for_wallet_budgetDefinedTroughAmount');
    const budgetField = document.getElementById('js-container-budget');

    const toggleBudgetField = () => {
        if (checkbox.checked) {
            budgetField.classList.add('hidden');
        } else {
            budgetField.classList.remove('hidden');
        }
    }

    toggleBudgetField();

    checkbox.addEventListener('change', toggleBudgetField);
});