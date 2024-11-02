import { update_dropdown_content } from './update_dropdown_content';

export const clear_all_notifications = () => {
    const clearAllNotificationsButton = document.getElementById('js-topbar-clear-all-notifications');
    if (!clearAllNotificationsButton) return;

    clearAllNotificationsButton.addEventListener('click', () => {
        fetch('/notifications/mark-all-as-read', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
            },
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const notificationElements = document.querySelectorAll('#notifications-dropdown-content li[id^="topbar-notification-"]');
                    notificationElements.forEach(notification => notification.remove());

                    const unreadNotificationCountElement = document.getElementById('js-unread-notification-count');
                    unreadNotificationCountElement.textContent = '';
                    unreadNotificationCountElement.classList.add('hidden');

                    update_dropdown_content();
                } else {
                    console.error(data.error || 'An error occurred');
                }
            })
            .catch(error => console.error('Error:', error));
    });
};
