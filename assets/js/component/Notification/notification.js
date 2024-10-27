import { clearAllNotifications } from './module/clearAllNotifications';
import { markNotificationAsRead } from './module/markNotificationAsRead';
import { updateDropdownContent } from './module/updateDropdownContent';

document.addEventListener('DOMContentLoaded', () => {
    clearAllNotifications();
    markNotificationAsRead();
    updateDropdownContent();
});
