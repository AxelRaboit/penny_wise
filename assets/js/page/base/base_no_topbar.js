import { handlePageSpinner } from '@component/spinner/page_spinner.js';
import { initializeThemeSwitcher } from '@page/base/component/theme_switcher';

document.addEventListener('DOMContentLoaded', function () {
  handlePageSpinner('base-no-topbar-page', 'loadingPageSpinner');
  initializeThemeSwitcher();
});
