import { updateDropdownContent } from './updateDropdownContent';

export const markNotificationAsRead = () => {
    const markAsReadButtons = document.querySelectorAll('.js-topbar-mark-as-read');
    markAsReadButtons.forEach(button => {
        button.addEventListener('click', () => {
            const notificationId = button.getAttribute('data-notification-id');
            fetch(`/notifications/mark-as-read/${notificationId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                },
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const notificationElement = document.getElementById(`topbar-notification-${notificationId}`);
                        notificationElement.remove();

                        const unreadCountElement = document.getElementById('unread-count');
                        let currentCount = parseInt(unreadCountElement.textContent);

                        if (currentCount > 1) {
                            currentCount--;
                            unreadCountElement.textContent = currentCount > 99 ? '99+' : currentCount.toString();
                        } else {
                            unreadCountElement.textContent = '';
                            unreadCountElement.classList.add('hidden');
                        }

                        updateDropdownContent();
                    } else {
                        console.error(data.error || 'An error occurred');
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    });
};
