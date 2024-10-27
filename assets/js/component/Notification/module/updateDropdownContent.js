export const updateDropdownContent = () => {
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
