document.addEventListener('DOMContentLoaded', function () {
    const useDefaultColorCheckbox = document.querySelector('.js-transaction-tag-use-default-color');
    const colorField = document.querySelector('#js-transaction-tag-color-field');

    function toggleColorField() {
        colorField.classList.toggle('hidden', useDefaultColorCheckbox.checked);
    }

    useDefaultColorCheckbox.addEventListener('change', toggleColorField);

    // Set initial state
    toggleColorField();
});
