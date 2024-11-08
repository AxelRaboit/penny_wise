import { initializeChat } from './chat/chat.js';
import { handlePageSpinner } from '@component/spinner/page_spinner';

document.addEventListener('DOMContentLoaded', function () {
  handlePageSpinner('messenger-talk-view-page', 'loadingPageSpinner', 'flex');
  initializeChat();
});
