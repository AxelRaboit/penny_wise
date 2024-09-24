document.addEventListener('DOMContentLoaded', function () {
    const checkbox = document.getElementById('transaction_for_wallet_budgetDefinedTroughAmount');
    const budgetField = document.getElementById('js-container-budget');
    const categoryField = document.getElementById('transaction_for_wallet_transactionCategory');

    // Fonction pour gérer la visibilité des champs budget et checkbox en fonction de la catégorie
    const toggleFieldsByCategory = () => {
        const selectedCategory = categoryField.options[categoryField.selectedIndex]?.text.toLowerCase();

        // Cache le champ budget et la checkbox si la catégorie est 'incomes'
        if (selectedCategory === 'incomes') {
            budgetField.classList.add('hidden'); // Cache le champ budget
            if (checkbox) {
                checkbox.closest('.flex').classList.add('hidden'); // Cache la checkbox
            }
        } else {
            // Montre la checkbox et gère la visibilité du champ budget en fonction de la checkbox
            if (checkbox) {
                checkbox.closest('.flex').classList.remove('hidden');
                toggleBudgetField(); // Met à jour la visibilité du champ budget selon l'état de la checkbox
            } else {
                budgetField.classList.remove('hidden'); // Affiche le champ budget si la checkbox n'existe pas
            }
        }
    };

    // Fonction pour basculer la visibilité du champ budget en fonction de la case à cocher
    const toggleBudgetField = () => {
        if (checkbox && checkbox.checked) {
            budgetField.classList.add('hidden'); // Cache le champ budget si la checkbox est cochée
        } else {
            budgetField.classList.remove('hidden'); // Affiche le champ budget si la checkbox n'est pas cochée
        }
    };

    // Si la checkbox n'existe pas (mode édition), assure que le champ budget est toujours visible
    if (!checkbox) {
        budgetField.classList.remove('hidden');
    }

    // Vérifie initialement l'état de la catégorie sélectionnée et de la checkbox
    toggleFieldsByCategory();

    // Écoute les changements sur la catégorie et met à jour les champs
    categoryField.addEventListener('change', toggleFieldsByCategory);

    // Si la checkbox existe, écoute les changements de son état pour afficher/cacher le champ budget
    if (checkbox) {
        checkbox.addEventListener('change', toggleBudgetField);
    }
});
