import {handlePageSpinner} from "../../../component/Spinner/page-spinner.js";

document.addEventListener('DOMContentLoaded', function () {
    handlePageSpinner('transaction-tag-new-page', 'loadingPageSpinner');
});

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