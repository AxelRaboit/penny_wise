document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.js-mark-as-read').forEach(button => {
        button.addEventListener('click', () => {
            const notificationId = button.getAttribute('data-notification-id');
            markAsRead(notificationId);
        });
    });
});

const markAsRead = (notificationId) => {
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
                    unreadCountElement.textContent = currentCount.toString();
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
};

const updateDropdownContent = () => {
    const dropdownContent = document.querySelector('#notifications-dropdown-content');
    const remainingNotifications = dropdownContent.querySelectorAll('li[id^="topbar-notification-"]').length;

    if (remainingNotifications === 0) {
        dropdownContent.innerHTML = `
            <li class="px-4 py-3 text-sm text-senary text-center">
                No new notifications
            </li>
        `;
    }
};
