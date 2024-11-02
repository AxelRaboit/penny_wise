export const update_dropdown_content = () => {
    const dropdownContent = document.querySelector('#notifications-dropdown-content');
    const remainingNotifications = dropdownContent.querySelectorAll('li[id^="topbar-notification-"]').length;

    if (remainingNotifications === 0) {
        dropdownContent.innerHTML = `
            <li class="px-4 py-3 text-sm text-dynamic-gray text-center">
                No new notifications
            </li>
        `;
    }
};
