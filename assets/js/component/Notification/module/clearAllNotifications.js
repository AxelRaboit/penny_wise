import { updateDropdownContent } from './updateDropdownContent';

export const clearAllNotifications = () => {
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

                    const unreadCountElement = document.getElementById('unread-count');
                    unreadCountElement.textContent = '';
                    unreadCountElement.classList.add('hidden');

                    updateDropdownContent();
                } else {
                    console.error(data.error || 'An error occurred');
                }
            })
            .catch(error => console.error('Error:', error));
    });
};
