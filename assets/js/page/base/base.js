import { initializeClock } from './component/clock.js';
import { initializeAddFlash } from './component/add_flash.js';
import { handlePageSpinner } from '@component/spinner/page_spinner.js';
import { initializeLeftSideMenuContent } from '@page/base/left_side_menu/left_side_menu.js';
import { initializeThemeSwitcher } from './component/theme_switcher.js';

document.addEventListener('DOMContentLoaded', function () {
  initializeThemeSwitcher();
  initializeLeftSideMenuContent();
  handlePageSpinner('base-page', 'loadingPageSpinner');
  initializeAddFlash();
  initializeClock();
});
