import { clear_all_notifications } from './module/clear_all_notifications';
import { mark_notification_as_read } from './module/mark_notification_as_read';
import { update_dropdown_content } from './module/update_dropdown_content';

document.addEventListener('DOMContentLoaded', () => {
    clear_all_notifications();
    mark_notification_as_read();
    update_dropdown_content();
});
