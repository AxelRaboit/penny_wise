document.addEventListener('DOMContentLoaded', function() {
    initUserDropdown();
});

/**
 * Initialize the user dropdown functionality.
 */
function initUserDropdown() {
    const dropdownButton = document.getElementById('js-user-dropdown-button');
    const dropdownMenu = document.getElementById('js-user-dropdown-menu');

    if (dropdownButton && dropdownMenu) {
        dropdownButton.addEventListener('click', function () {
            dropdownMenu.classList.toggle('hidden');
        });

        window.addEventListener('click', function (event) {
            if (!dropdownButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                dropdownMenu.classList.add('hidden');
            }
        });
    }
}
