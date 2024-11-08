import { handlePageSpinner } from '@component/spinner/page_spinner.js';

document.addEventListener('DOMContentLoaded', function () {
  handlePageSpinner('transaction-tag-edit-page', 'loadingPageSpinner');
});

document.addEventListener('DOMContentLoaded', function () {
  const useDefaultColorCheckbox = document.querySelector(
    '.js-transaction-tag-use-default-color',
  );
  const colorField = document.querySelector('#js-transaction-tag-color-field');

  function toggleColorField() {
    colorField.classList.toggle('hidden', useDefaultColorCheckbox.checked);
  }

  useDefaultColorCheckbox.addEventListener('change', toggleColorField);

  // Set initial state
  toggleColorField();
});
