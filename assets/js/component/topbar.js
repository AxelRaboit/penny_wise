document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des menus
    initMobileMenu();
    initUserDropdown();
});

/**
 * Initialize the mobile menu toggle functionality.
 */
function initMobileMenu() {
    const menuToggle = document.getElementById('js-menu-toggle');
    const mobileMenu = document.getElementById('js-mobile-menu');

    if (menuToggle && mobileMenu) {
        menuToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }
}

/**
 * Initialize the user dropdown functionality.
 */
function initUserDropdown() {
    const dropdownButton = document.getElementById('userDropdownButton');
    const dropdownMenu = document.getElementById('userDropdownMenu');

    if (dropdownButton && dropdownMenu) {
        dropdownButton.addEventListener('click', function () {
            dropdownMenu.classList.toggle('hidden');
        });

        window.addEventListener('click', function (event) {
            // Cache le menu si l'utilisateur clique en dehors
            if (!dropdownButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                dropdownMenu.classList.add('hidden');
            }
        });
    }
}
