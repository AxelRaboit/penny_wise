import { clear_all_notifications } from './module/clear_all_notifications';
import { mark_notification_as_read } from './module/mark_notification_as_read';
import { update_dropdown_content } from './module/update_dropdown_content';

document.addEventListener('DOMContentLoaded', () => {
    const notificationsDropdownContent = document.getElementById('js-notifications-dropdown-content');
    if (notificationsDropdownContent) {
        notificationsDropdownContent.classList.remove('hidden');
    }

    clear_all_notifications();
    mark_notification_as_read();
    update_dropdown_content();
});
