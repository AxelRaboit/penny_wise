import { update_dropdown_content } from './update_dropdown_content.js';

export const mark_notification_as_read = () => {
  const markAsReadButtons = document.querySelectorAll(
    '.js-topbar-mark-as-read',
  );
  markAsReadButtons.forEach((button) => {
    button.addEventListener('click', () => {
      const notificationId = button.getAttribute('data-notification-id');
      fetch(`/notifications/mark-as-read/${notificationId}`, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/json',
        },
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            const notificationElement = document.getElementById(
              `topbar-notification-${notificationId}`,
            );
            notificationElement.remove();

            const unreadNotificationCountElement = document.getElementById(
              'js-unread-notification-count',
            );
            let currentCount = parseInt(
              unreadNotificationCountElement.textContent,
            );

            if (currentCount > 1) {
              currentCount--;
              unreadNotificationCountElement.textContent =
                currentCount > 99 ? '99+' : currentCount.toString();
            } else {
              unreadNotificationCountElement.textContent = '';
              unreadNotificationCountElement.classList.add('hidden');
            }

            update_dropdown_content();
          } else {
            console.error(data.error || 'An error occurred');
          }
        })
        .catch((error) => console.error('Error:', error));
    });
  });
};
