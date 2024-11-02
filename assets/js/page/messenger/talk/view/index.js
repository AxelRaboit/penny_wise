import {handlePageSpinner} from "@component/spinner/page_spinner.js";
import {initializeChat} from "./chat/chat.js";

document.addEventListener('DOMContentLoaded', function() {
    handlePageSpinner('messenger-talk-view-page', 'loadingPageSpinner');
    initializeChat();
});